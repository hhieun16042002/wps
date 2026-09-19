<?php
/**
 * Động cơ Crawler: Nhận diện loại URL, phân tích Sitemap, duyệt danh mục và phát hiện sản phẩm.
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Crawler_Engine {

	/**
	 * Tự động nhận diện loại URL được cung cấp.
	 *
	 * @param string $url
	 * @return string 'sitemap' | 'product' | 'category' | 'listing' | 'homepage'
	 */
	public static function detect_url_type( string $url ): string {
		$parts = wp_parse_url( $url );
		$path  = strtolower( $parts['path'] ?? '/' );

		// 1. Kiểm tra Sitemap
		if ( str_ends_with( $path, '.xml' ) || str_contains( $path, 'sitemap' ) ) {
			return 'sitemap';
		}

		// 2. Kiểm tra Trang chủ
		if ( '/' === $path || empty( $path ) ) {
			return 'homepage';
		}

		// 3. Kiểm tra Mẫu Danh mục / Nhóm sản phẩm
		$cat_patterns = array(
			'/category/',
			'/categories/',
			'/nhom-san-pham/',
			'/danh-muc/',
			'/collections/',
			'/collection/',
			'/shop/',
			'/cua-hang/',
			'/san-pham/',
			'/products/',
			'/catalog/',
		);
		foreach ( $cat_patterns as $p ) {
			if ( str_contains( $path, $p ) ) {
				// Nếu kết thúc bằng số hoặc ID cụ thể có thể là chi tiết sản phẩm
				if ( preg_match( '#/(?:product|san-pham|item|p)/[^/]+/?$#i', $path ) ) {
					return 'product';
				}
				return 'category';
			}
		}

		// 4. Kiểm tra Mẫu trang chi tiết sản phẩm
		$prod_patterns = array(
			'/product/',
			'/products/',
			'/p/',
			'/item/',
			'/chi-tiet/',
			'-sp-',
			'.html',
		);
		foreach ( $prod_patterns as $p ) {
			if ( str_contains( $path, $p ) ) {
				return 'product';
			}
		}

		return 'listing';
	}

	/**
	 * Khám phá danh sách các URL sản phẩm từ URL nguồn.
	 *
	 * @param string $url URL ban đầu.
	 * @param int $job_id ID của phiên quét.
	 * @param int $max_products Giới hạn số lượng sản phẩm tối đa.
	 * @param int $max_depth Độ sâu tối đa khi quét phân trang.
	 * @return array Mảng danh sách URL sản phẩm tìm thấy.
	 */
	public function discover_product_urls( string $url, int $job_id, int $max_products = 50, int $max_depth = 3 ): array {
		$job_mgr  = WP_Crawler_Job_Manager::get_instance();
		$client   = WP_Crawler_Client::get_instance();
		$url_type = self::detect_url_type( $url );

		$job_mgr->log( $job_id, 'INFO', sprintf( __( 'Bắt đầu quét URL: %s [Kiểu: %s]', 'wp-product-crawler' ), $url, strtoupper( $url_type ) ) );

		// 1. Trường hợp là URL trang sản phẩm đơn lẻ
		if ( 'product' === $url_type ) {
			$job_mgr->log( $job_id, 'INFO', __( 'Phát hiện URL sản phẩm đơn lẻ.', 'wp-product-crawler' ) );
			return array( $url );
		}

		// 2. Trường hợp là Sitemap XML
		if ( 'sitemap' === $url_type ) {
			return $this->discover_from_sitemap( $url, $job_id, $max_products );
		}

		// 3. Trường hợp Danh mục, Trang danh sách hoặc Trang chủ
		// Thử kiểm tra xem website có sitemap.xml tự động không trước
		$discovered_from_sitemap = $this->try_detect_sitemap( $url, $job_id, $max_products );
		if ( ! empty( $discovered_from_sitemap ) ) {
			$job_mgr->log( $job_id, 'INFO', sprintf( __( 'Đã tự động tìm thấy %d liên kết sản phẩm từ sitemap.xml của website.', 'wp-product-crawler' ), count( $discovered_from_sitemap ) ) );
			return $discovered_from_sitemap;
		}

		// 4. Nếu không có sitemap, quét trực tiếp cấu trúc HTML và phân trang
		return $this->discover_from_html_pages( $url, $job_id, $max_products, $max_depth );
	}

	/**
	 * Khám phá URL sản phẩm từ Sitemap XML
	 */
	private function discover_from_sitemap( string $sitemap_url, int $job_id, int $max_products ): array {
		$job_mgr = WP_Crawler_Job_Manager::get_instance();
		$client  = WP_Crawler_Client::get_instance();

		$res = $client->get( $sitemap_url );
		if ( is_wp_error( $res ) || 200 !== $res['code'] ) {
			$job_mgr->log( $job_id, 'WARN', sprintf( __( 'Không thể tải Sitemap: %s', 'wp-product-crawler' ), $sitemap_url ) );
			return array();
		}

		$xml = @simplexml_load_string( $res['body'] );
		if ( false === $xml ) {
			$job_mgr->log( $job_id, 'WARN', __( 'Cú pháp Sitemap XML không hợp lệ.', 'wp-product-crawler' ) );
			return array();
		}

		$product_urls = array();

		// A. Trường hợp Sitemap Index (chứa các sitemap con)
		if ( isset( $xml->sitemap ) ) {
			$job_mgr->log( $job_id, 'INFO', __( 'Phát hiện Sitemap Index, đang quét các sitemap con...', 'wp-product-crawler' ) );
			foreach ( $xml->sitemap as $sub ) {
				$sub_url = (string) $sub->loc;
				// Ưu tiên các sitemap có chứa chữ 'product', 'san-pham', hoặc quét lần lượt
				if ( stripos( $sub_url, 'product' ) !== false || stripos( $sub_url, 'san-pham' ) !== false || count( $product_urls ) < $max_products ) {
					$sub_urls = $this->discover_from_sitemap( $sub_url, $job_id, $max_products - count( $product_urls ) );
					$product_urls = array_merge( $product_urls, $sub_urls );
					if ( count( $product_urls ) >= $max_products ) {
						break;
					}
				}
			}
		}

		// B. Trường hợp URL List trong Sitemap
		if ( isset( $xml->url ) ) {
			foreach ( $xml->url as $u ) {
				$loc = trim( (string) $u->loc );
				if ( empty( $loc ) ) {
					continue;
				}

				if ( $this->is_product_url( $loc ) ) {
					if ( WP_Crawler_Security::is_allowed_by_robots( $loc ) ) {
						$product_urls[] = $loc;
						if ( count( $product_urls ) >= $max_products ) {
							break;
						}
					}
				}
			}
		}

		return array_values( array_unique( $product_urls ) );
	}

	/**
	 * Thử phát hiện sitemap.xml tại gốc domain
	 */
	private function try_detect_sitemap( string $url, int $job_id, int $max_products ): array {
		$parts = wp_parse_url( $url );
		if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return array();
		}

		$base = $parts['scheme'] . '://' . $parts['host'];
		$candidates = array(
			$base . '/sitemap.xml',
			$base . '/sitemap_index.xml',
			$base . '/product-sitemap.xml',
		);

		$client = WP_Crawler_Client::get_instance();
		foreach ( $candidates as $s_url ) {
			$res = $client->get( $s_url, array( 'timeout' => 5 ) );
			if ( ! is_wp_error( $res ) && 200 === $res['code'] && str_contains( $res['body'], '<urlset' ) ) {
				return $this->discover_from_sitemap( $s_url, $job_id, $max_products );
			}
		}

		return array();
	}

	/**
	 * Quét các trang HTML danh sách sản phẩm và duyệt phân trang
	 */
	private function discover_from_html_pages( string $start_url, int $job_id, int $max_products, int $max_depth ): array {
		$job_mgr      = WP_Crawler_Job_Manager::get_instance();
		$client       = WP_Crawler_Client::get_instance();
		$product_urls = array();
		$visited_pages= array();
		$pages_to_visit = array( array( 'url' => $start_url, 'depth' => 1 ) );

		$host = wp_parse_url( $start_url, PHP_URL_HOST );

		while ( ! empty( $pages_to_visit ) && count( $product_urls ) < $max_products ) {
			$current = array_shift( $pages_to_visit );
			$page_url = $current['url'];
			$depth    = $current['depth'];

			if ( in_array( $page_url, $visited_pages, true ) || $depth > $max_depth ) {
				continue;
			}
			$visited_pages[] = $page_url;

			$job_mgr->log( $job_id, 'INFO', sprintf( __( 'Đang quét trang danh sách (Tầng %d): %s', 'wp-product-crawler' ), $depth, $page_url ) );

			$res = $client->get( $page_url );
			if ( is_wp_error( $res ) || 200 !== $res['code'] ) {
				continue;
			}

			// Phân tích HTML bằng DOM
			libxml_use_internal_errors( true );
			$dom = new DOMDocument();
			@$dom->loadHTML( mb_convert_encoding( $res['body'], 'HTML-ENTITIES', 'UTF-8' ), LIBXML_NOWARNING | LIBXML_NOERROR );
			$xpath = new DOMXPath( $dom );
			libxml_clear_errors();

			$links = $xpath->query( '//a[@href]' );
			if ( $links && $links->length > 0 ) {
				foreach ( $links as $link ) {
					$href = trim( $link->getAttribute( 'href' ) );
					if ( empty( $href ) || str_starts_with( $href, '#' ) || str_starts_with( $href, 'javascript:' ) ) {
						continue;
					}

					$abs_url = $client->resolve_relative_url( $href, $page_url );
					$link_host = wp_parse_url( $abs_url, PHP_URL_HOST );

					// Chỉ quét các liên kết cùng hostname
					if ( strtolower( (string) $link_host ) !== strtolower( (string) $host ) ) {
						continue;
					}

					// Loại trừ hash và tham số theo dõi
					$clean_url = strtok( $abs_url, '#' );

					// Kiểm tra xem liên kết có phải sản phẩm không
					if ( $this->is_product_url( $clean_url ) ) {
						if ( ! in_array( $clean_url, $product_urls, true ) && WP_Crawler_Security::is_allowed_by_robots( $clean_url ) ) {
							$product_urls[] = $clean_url;
							if ( count( $product_urls ) >= $max_products ) {
								break;
							}
						}
					} elseif ( $depth < $max_depth && $this->is_pagination_or_category_link( $link, $clean_url ) ) {
						// Thêm vào hàng đợi phân trang nếu chưa đi qua
						if ( ! in_array( $clean_url, $visited_pages, true ) ) {
							$pages_to_visit[] = array( 'url' => $clean_url, 'depth' => $depth + 1 );
						}
					}
				}
			}
		}

		return array_values( array_unique( $product_urls ) );
	}

	/**
	 * Kiểm tra xem một liên kết có khả năng cao là trang sản phẩm không
	 */
	public function is_product_url( string $url ): bool {
		$parts = wp_parse_url( $url );
		$path  = strtolower( $parts['path'] ?? '' );

		// Các mẫu nhận dạng sản phẩm phổ biến
		$patterns = array(
			'#/product/[^/]+#i',
			'#/products/[^/]+#i',
			'#/p/[^/]+#i',
			'#/item/[^/]+#i',
			'#/san-pham/[^/]+#i',
			'#/chi-tiet-san-pham/[^/]+#i',
			'#-sp-\d+#i',
			'#-p\d+\.html#i',
			'#/sp/[^/]+#i',
		);

		foreach ( $patterns as $p ) {
			if ( preg_match( $p, $path ) ) {
				// Không phải là trang phân loại / trang danh mục tổng
				if ( ! preg_match( '#/(?:category|danh-muc|collections?)/#i', $path ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Kiểm tra liên kết phân trang (Next, Page 2,...) hoặc danh mục con
	 */
	private function is_pagination_or_category_link( DOMElement $link, string $url ): bool {
		$rel   = $link->getAttribute( 'rel' );
		$class = $link->getAttribute( 'class' );
		$text  = strtolower( trim( $link->textContent ) );

		if ( 'next' === $rel || str_contains( $class, 'next' ) || str_contains( $class, 'pagination' ) ) {
			return true;
		}

		if ( preg_match( '#[?&](?:page|paged|p)=\d+#i', $url ) || preg_match( '#/page/\d+/?$#i', $url ) ) {
			return true;
		}

		return false;
	}
}
