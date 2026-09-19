<?php
/**
 * Bộ trích xuất dữ liệu sản phẩm: JSON-LD, Microdata, OpenGraph, DOM Fallback & Thông số kỹ thuật dạng cấu trúc.
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Product_Extractor {

	/**
	 * Phân tích và trích xuất dữ liệu sản phẩm từ chuỗi HTML.
	 *
	 * @param string $html Nội dung HTML của trang sản phẩm.
	 * @param string $source_url URL gốc của trang.
	 * @return array Mảng dữ liệu sản phẩm đã chuẩn hóa.
	 */
	public static function extract( string $html, string $source_url ): array {
		$product = array(
			'source_url'        => $source_url,
			'name'              => '',
			'sku'               => '',
			'model'             => '',
			'brand'             => '',
			'price'             => null,
			'sale_price'        => null,
			'currency'          => 'VND',
			'availability'      => 'InStock',
			'description'       => '',
			'short_description' => '',
			'category'          => '',
			'main_image'        => '',
			'gallery_images'    => array(),
			'specifications'    => array(), // Mảng key-value: ['Điện áp' => '220V', ...]
			'tech_specs_rows'   => array(), // Mảng hàng cho Mozlex: [['Điện áp', '220V'], ...]
			'documents'         => array(), // File tài liệu/PDF: [['title' => '...', 'url' => '...']]
			'extraction_source' => 'unknown',
		);

		if ( empty( $html ) ) {
			return $product;
		}

		// 1. Trích xuất từ JSON-LD (Ưu tiên cao nhất)
		$json_ld_data = self::extract_json_ld( $html, $source_url );
		if ( ! empty( $json_ld_data['name'] ) ) {
			$product = array_merge( $product, array_filter( $json_ld_data, fn( $v ) => ! empty( $v ) ) );
			$product['extraction_source'] = 'json-ld';
		}

		// Khởi tạo DOMDocument và DOMXPath để bổ sung dữ liệu còn thiếu
		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		// Thêm meta charset utf-8 để DOMDocument đọc đúng tiếng Việt
		$html_with_charset = '<?xml encoding="utf-8" ?>' . $html;
		@$dom->loadHTML( mb_convert_encoding( $html_with_charset, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_NOWARNING | LIBXML_NOERROR );
		$xpath = new DOMXPath( $dom );
		libxml_clear_errors();

		// 2. Trích xuất từ OpenGraph & Meta Tags nếu còn thiếu
		$og_data = self::extract_opengraph( $xpath, $source_url );
		foreach ( array( 'name', 'description', 'short_description', 'main_image', 'brand', 'price' ) as $key ) {
			if ( empty( $product[ $key ] ) && ! empty( $og_data[ $key ] ) ) {
				$product[ $key ] = $og_data[ $key ];
				if ( 'unknown' === $product['extraction_source'] ) {
					$product['extraction_source'] = 'opengraph';
				}
			}
		}

		// 3. Fallback Heuristics & DOM Selectors
		if ( empty( $product['name'] ) ) {
			$product['name'] = self::find_text( $xpath, array(
				'//h1[contains(@class, "product_title")]',
				'//h1[contains(@class, "product-title")]',
				'//*[contains(@class, "woocommerce-product-title")]//h1',
				'//h1[contains(@class, "entry-title")]',
				'//h1',
				'//meta[@property="og:title"]/@content',
				'//title',
			) );
		}

		if ( ! empty( $product['name'] ) ) {
			// Cắt bỏ phần hậu tố website nếu có dạng " - Sản phẩm..." hoặc " | Tên Website"
			$product['name'] = preg_replace( '/\s*[-–|]\s*(Sản phẩm cửa thép|Cửa thép vân gỗ|King Bac|Official).*$/iu', '', $product['name'] );
			$product['name'] = trim( $product['name'] );
		}

		if ( empty( $product['sku'] ) ) {
			$sku_text = self::find_text( $xpath, array(
				'//*[contains(@class, "sku")]',
				'//*[@itemprop="sku"]',
				'//*[contains(@class, "product-sku")]',
				'//*[contains(text(), "Mã SP:")]',
				'//*[contains(text(), "Mã sản phẩm:")]',
				'//*[contains(text(), "Model:")]',
			) );
			if ( ! empty( $sku_text ) ) {
				$sku_text = preg_replace( '/^(Mã SP|Mã sản phẩm|Model|SKU)\s*:\s*/i', '', trim( $sku_text ) );
				$product['sku']   = sanitize_text_field( $sku_text );
				$product['model'] = $product['sku'];
			}
		}

		if ( null === $product['price'] ) {
			$price_text = self::find_text( $xpath, array(
				'//*[contains(@class, "woocommerce-Price-amount")]',
				'//*[contains(@class, "product-price")]',
				'//*[contains(@class, "current-price")]',
				'//*[@itemprop="price"]',
				'//*[@data-price]/@data-price',
				'//*[contains(@class, "price")]',
			) );
			if ( ! empty( $price_text ) ) {
				$parsed_price = self::parse_price( $price_text );
				if ( null !== $parsed_price ) {
					$product['price'] = $parsed_price;
				}
			}
		}

		// 3. Trích xuất Mô tả sản phẩm (Full Description & Short Description)
		// Luôn ưu tiên quét DOM để tìm Mô tả chi tiết phong phú (Rich HTML Content)
		$desc_selectors = array(
			// Elementor WooCommerce Single Product content (VD: cuathepvangokingbac.com)
			'//*[contains(@class, "elementor-widget-woocommerce-product-content")]//*[contains(@class, "elementor-widget-container")]',
			'//*[contains(@class, "elementor-widget-woocommerce-product-content")]',
			'//*[contains(@class, "elementor-widget-theme-post-content")]//*[contains(@class, "elementor-widget-container")]',
			'//*[contains(@class, "elementor-widget-theme-post-content")]',
			// WooCommerce standard description tabs
			'//*[contains(@class, "woocommerce-Tabs-panel--description")]',
			'//*[@id="tab-description"]',
			'//*[contains(@class, "woocommerce-tabs")]//*[contains(@class, "panel")]',
			// Common product description containers
			'//*[contains(@class, "product-description")]',
			'//*[@id="product-description"]',
			'//*[contains(@class, "product-detail-description")]',
			'//*[contains(@class, "product-detail__description")]',
			'//*[contains(@class, "product-content")]',
			'//*[contains(@class, "product-details-content")]',
			'//*[contains(@class, "single-product-description")]',
			'//*[contains(@class, "product-single__description")]',
			'//*[contains(@class, "product-info-description")]',
			'//*[contains(@class, "product-overview")]',
			'//*[@id="description"]',
			'//*[contains(@class, "entry-content")]',
		);
		$dom_desc_html = self::find_html( $xpath, $desc_selectors );

		if ( ! empty( trim( strip_tags( $dom_desc_html ) ) ) ) {
			// Nếu trước đó JSON-LD đã lưu đoạn text ngắn, chuyển đoạn đó sang short_description
			if ( ! empty( $product['description'] ) && empty( $product['short_description'] ) && strlen( $product['description'] ) < strlen( $dom_desc_html ) ) {
				$product['short_description'] = sanitize_textarea_field( $product['description'] );
			}
			$product['description'] = WP_Crawler_Security::sanitize_html_content( html_entity_decode( $dom_desc_html, ENT_QUOTES, 'UTF-8' ) );
		}

		// Trích xuất mô tả ngắn (Short Description) nếu còn trống
		if ( empty( $product['short_description'] ) ) {
			$short_desc = self::find_text( $xpath, array(
				'//*[contains(@class, "woocommerce-product-details__short-description")]',
				'//*[contains(@class, "elementor-widget-woocommerce-product-short-description")]//*[contains(@class, "elementor-widget-container")]',
				'//*[contains(@class, "elementor-widget-woocommerce-product-short-description")]',
				'//*[contains(@class, "product-short-description")]',
				'//*[contains(@class, "summary")]//div[contains(@class, "short-description")]',
				'//*[contains(@class, "summary")]//p',
				'//meta[@name="description"]/@content',
				'//meta[@property="og:description"]/@content',
			) );
			if ( ! empty( $short_desc ) ) {
				$product['short_description'] = sanitize_textarea_field( $short_desc );
			}
		}

		// 3. Trích xuất danh mục sản phẩm chính xác từ website nguồn
		$product['category'] = self::extract_category( $xpath, $html, $source_url, $product );

		// 4. Trích xuất thông số kỹ thuật (Technical Specifications Table/DL/List)
		$extracted_specs = self::extract_specifications( $xpath );
		if ( ! empty( $extracted_specs ) ) {
			$product['specifications'] = array_merge( $product['specifications'], $extracted_specs );
		}

		// 5. Trích xuất Hình ảnh (Main Image & Gallery)
		$extracted_images = self::extract_images( $xpath, $source_url );
		if ( empty( $product['main_image'] ) && ! empty( $extracted_images['main'] ) ) {
			$product['main_image'] = $extracted_images['main'];
		}
		if ( ! empty( $extracted_images['gallery'] ) ) {
			$product['gallery_images'] = array_unique( array_merge( $product['gallery_images'], $extracted_images['gallery'] ) );
		}

		// 6. Trích xuất tài liệu đính kèm (PDF, Catalogue, Manual)
		$product['documents'] = self::extract_documents( $xpath, $source_url );

		// Chuẩn hóa Model & SKU
		if ( empty( $product['model'] ) && ! empty( $product['sku'] ) ) {
			$product['model'] = $product['sku'];
		}
		if ( empty( $product['sku'] ) && ! empty( $product['model'] ) ) {
			$product['sku'] = $product['model'];
		}
		if ( empty( $product['sku'] ) && ! empty( $product['name'] ) ) {
			// Nếu không có SKU, tạo mã định danh dựa trên tên
			$product['sku']   = strtoupper( sanitize_title( $product['name'] ) );
			$product['model'] = $product['sku'];
		}

		// Chuyển đổi thông số kỹ thuật sang dạng mảng hàng cho Mozlex `mozlex_tech_specs`
		$product['tech_specs_rows'] = array();
		foreach ( $product['specifications'] as $label => $val ) {
			$product['tech_specs_rows'][] = array( sanitize_text_field( $label ), sanitize_text_field( $val ) );
		}

		// Đảm bảo URL ảnh là URL tuyệt đối hợp lệ
		$client = WP_Crawler_Client::get_instance();
		if ( ! empty( $product['main_image'] ) ) {
			$product['main_image'] = $client->resolve_relative_url( $product['main_image'], $source_url );
		}
		$resolved_gallery = array();
		foreach ( $product['gallery_images'] as $g_img ) {
			$resolved = $client->resolve_relative_url( $g_img, $source_url );
			if ( $resolved !== $product['main_image'] && ! in_array( $resolved, $resolved_gallery, true ) ) {
				$resolved_gallery[] = $resolved;
			}
		}
		$product['gallery_images'] = $resolved_gallery;

		return $product;
	}

	/**
	 * Trích xuất từ cấu trúc JSON-LD schema.org
	 */
	private static function extract_json_ld( string $html, string $source_url ): array {
		$data = array();
		if ( ! preg_match_all( '#<script\s+[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $matches ) ) {
			return $data;
		}

		foreach ( $matches[1] as $json_str ) {
			$json = json_decode( trim( $json_str ), true );
			if ( ! is_array( $json ) ) {
				continue;
			}

			// Xử lý @graph nếu có
			$nodes = isset( $json['@graph'] ) && is_array( $json['@graph'] ) ? $json['@graph'] : array( $json );

			foreach ( $nodes as $node ) {
				if ( ! is_array( $node ) || empty( $node['@type'] ) ) {
					continue;
				}

				$type = is_array( $node['@type'] ) ? implode( ' ', $node['@type'] ) : (string) $node['@type'];
				if ( stripos( $type, 'Product' ) === false ) {
					continue;
				}

				// Tìm thấy Product Node
				if ( ! empty( $node['name'] ) ) {
					$data['name'] = html_entity_decode( sanitize_text_field( $node['name'] ), ENT_QUOTES, 'UTF-8' );
				}
				if ( ! empty( $node['description'] ) ) {
					$data['description'] = sanitize_textarea_field( $node['description'] );
				}
				if ( ! empty( $node['sku'] ) ) {
					$data['sku']   = sanitize_text_field( $node['sku'] );
					$data['model'] = $data['sku'];
				} elseif ( ! empty( $node['mpn'] ) ) {
					$data['sku']   = sanitize_text_field( $node['mpn'] );
					$data['model'] = $data['sku'];
				}

				// Brand
				if ( ! empty( $node['brand'] ) ) {
					$data['brand'] = is_array( $node['brand'] ) ? sanitize_text_field( $node['brand']['name'] ?? '' ) : sanitize_text_field( $node['brand'] );
				}

				// Image
				if ( ! empty( $node['image'] ) ) {
					if ( is_string( $node['image'] ) ) {
						$data['main_image'] = esc_url_raw( $node['image'] );
					} elseif ( is_array( $node['image'] ) ) {
						$images = array();
						foreach ( $node['image'] as $img_item ) {
							if ( is_string( $img_item ) ) {
								$images[] = esc_url_raw( $img_item );
							} elseif ( is_array( $img_item ) && ! empty( $img_item['url'] ) ) {
								$images[] = esc_url_raw( $img_item['url'] );
							}
						}
						if ( ! empty( $images ) ) {
							$data['main_image']     = array_shift( $images );
							$data['gallery_images'] = $images;
						}
					}
				}

				// Offers / Price
				if ( ! empty( $node['offers'] ) ) {
					$offer = is_array( $node['offers'] ) && isset( $node['offers'][0] ) ? $node['offers'][0] : $node['offers'];
					if ( is_array( $offer ) ) {
						if ( isset( $offer['price'] ) ) {
							$data['price'] = (float) $offer['price'];
						} elseif ( isset( $offer['lowPrice'] ) ) {
							$data['price'] = (float) $offer['lowPrice'];
						}
						if ( ! empty( $offer['priceCurrency'] ) ) {
							$data['currency'] = sanitize_text_field( $offer['priceCurrency'] );
						}
						if ( ! empty( $offer['availability'] ) ) {
							$data['availability'] = stripos( $offer['availability'], 'InStock' ) !== false ? 'InStock' : 'OutOfStock';
						}
					}
				}

				// Additional Properties (Specifications)
				if ( ! empty( $node['additionalProperty'] ) && is_array( $node['additionalProperty'] ) ) {
					$specs = array();
					foreach ( $node['additionalProperty'] as $prop ) {
						if ( is_array( $prop ) && ! empty( $prop['name'] ) && isset( $prop['value'] ) ) {
							$specs[ sanitize_text_field( $prop['name'] ) ] = sanitize_text_field( $prop['value'] );
						}
					}
					if ( ! empty( $specs ) ) {
						$data['specifications'] = $specs;
					}
				}

				// Nếu đã trích xuất được tên và giá thì dừng duyệt
				if ( ! empty( $data['name'] ) ) {
					break 2;
				}
			}
		}

		return $data;
	}

	/**
	 * Trích xuất từ OpenGraph & Meta
	 */
	private static function extract_opengraph( DOMXPath $xpath, string $source_url ): array {
		$data = array();

		$og_title = self::find_text( $xpath, array( '//meta[@property="og:title"]/@content', '//meta[@name="twitter:title"]/@content' ) );
		if ( ! empty( $og_title ) ) {
			$data['name'] = $og_title;
		}

		$og_desc = self::find_text( $xpath, array( '//meta[@property="og:description"]/@content', '//meta[@name="description"]/@content' ) );
		if ( ! empty( $og_desc ) ) {
			$data['short_description'] = $og_desc;
		}

		$og_img = self::find_text( $xpath, array( '//meta[@property="og:image"]/@content', '//meta[@name="twitter:image"]/@content' ) );
		if ( ! empty( $og_img ) ) {
			$data['main_image'] = $og_img;
		}

		$og_price = self::find_text( $xpath, array( '//meta[@property="product:price:amount"]/@content', '//meta[@property="og:price:amount"]/@content' ) );
		if ( ! empty( $og_price ) ) {
			$data['price'] = (float) $og_price;
		}

		$og_brand = self::find_text( $xpath, array( '//meta[@property="product:brand"]/@content' ) );
		if ( ! empty( $og_brand ) ) {
			$data['brand'] = $og_brand;
		}

		return $data;
	}

	/**
	 * Trích xuất thông số kỹ thuật (Technical Specifications) dạng bảng hoặc danh sách.
	 *
	 * @param DOMXPath $xpath
	 * @return array Mảng kết hợp key-value của thông số kỹ thuật.
	 */
	public static function extract_specifications( DOMXPath $xpath ): array {
		$specs = array();

		// 1. Duyệt qua các bảng thông số kỹ thuật: table tr th & td
		$table_rows = $xpath->query( '//table[contains(@class, "spec") or contains(@class, "attribute") or contains(@class, "detail") or contains(@class, "table") or contains(@id, "spec")]//tr' );
		if ( ! $table_rows || 0 === $table_rows->length ) {
			$table_rows = $xpath->query( '//table//tr' );
		}

		if ( $table_rows && $table_rows->length > 0 ) {
			foreach ( $table_rows as $tr ) {
				$th = $xpath->query( './/th', $tr );
				$td = $xpath->query( './/td', $tr );

				$label = '';
				$value = '';

				if ( $th->length >= 1 && $td->length >= 1 ) {
					$label = trim( $th->item( 0 )->textContent );
					$value = trim( $td->item( 0 )->textContent );
				} elseif ( $td->length >= 2 ) {
					$label = trim( $td->item( 0 )->textContent );
					$value = trim( $td->item( 1 )->textContent );
				}

				if ( ! empty( $label ) && ! empty( $value ) && strlen( $label ) < 100 && strlen( $value ) < 500 ) {
					// Làm sạch dấu hai chấm ở cuối nhãn
					$label = rtrim( $label, ": \t\n\r\0\x0B" );
					$specs[ sanitize_text_field( $label ) ] = sanitize_text_field( $value );
				}
			}
		}

		// 2. Duyệt qua danh sách định nghĩa <dl><dt>...</dt><dd>...</dd></dl>
		$dts = $xpath->query( '//dl//dt' );
		if ( $dts && $dts->length > 0 ) {
			foreach ( $dts as $dt ) {
				$dd = $xpath->query( 'following-sibling::dd[1]', $dt );
				if ( $dd && $dd->length > 0 ) {
					$label = trim( $dt->textContent );
					$value = trim( $dd->item( 0 )->textContent );
					if ( ! empty( $label ) && ! empty( $value ) ) {
						$label = rtrim( $label, ": \t\n\r\0\x0B" );
						$specs[ sanitize_text_field( $label ) ] = sanitize_text_field( $value );
					}
				}
			}
		}

		// 3. Duyệt danh sách <li> có chứa nhãn phân tách bằng dấu hai chấm
		if ( empty( $specs ) ) {
			$lis = $xpath->query( '//*[contains(@class, "spec") or contains(@class, "feature") or contains(@class, "thong-so")]//li' );
			if ( $lis && $lis->length > 0 ) {
				foreach ( $lis as $li ) {
					$text = trim( $li->textContent );
					if ( str_contains( $text, ':' ) ) {
						list( $l, $v ) = array_map( 'trim', explode( ':', $text, 2 ) );
						if ( ! empty( $l ) && ! empty( $v ) && strlen( $l ) < 60 ) {
							$specs[ sanitize_text_field( $l ) ] = sanitize_text_field( $v );
						}
					}
				}
			}
		}

		return $specs;
	}

	/**
	 * Trích xuất hình ảnh sản phẩm từ DOM (Ảnh chính và Gallery)
	 */
	private static function extract_images( DOMXPath $xpath, string $source_url ): array {
		$result = array(
			'main'    => '',
			'gallery' => array(),
		);

		// Tìm ảnh chính
		$main_nodes = $xpath->query( array(
			'//*[contains(@class, "woocommerce-product-gallery__image")]//img',
			'//*[contains(@class, "product-image")]//img',
			'//*[@id="product-image"]',
			'//img[contains(@class, "wp-post-image")]',
		)[0] );

		$img_urls = array();

		// Duyệt qua tất cả các ảnh sản phẩm tiềm năng
		$all_imgs = $xpath->query( '//img[contains(@class, "product") or contains(@class, "gallery") or contains(@class, "attachment") or @data-large_image or @data-zoom-image]' );
		if ( ! $all_imgs || 0 === $all_imgs->length ) {
			$all_imgs = $xpath->query( '//img' );
		}

		if ( $all_imgs && $all_imgs->length > 0 ) {
			foreach ( $all_imgs as $img ) {
				// Ưu tiên ảnh kích thước lớn nhất
				$src = $img->getAttribute( 'data-large_image' ) ?:
					( $img->getAttribute( 'data-zoom-image' ) ?:
					( $img->getAttribute( 'data-full' ) ?:
					( $img->getAttribute( 'data-src' ) ?:
					( $img->getAttribute( 'src' ) ?: '' ) ) ) );

				$src = trim( $src );
				if ( empty( $src ) || str_starts_with( $src, 'data:image' ) ) {
					continue;
				}

				// Lọc bỏ icon, logo nhỏ
				$width  = (int) $img->getAttribute( 'width' );
				$height = (int) $img->getAttribute( 'height' );
				if ( ( $width > 0 && $width < 80 ) || ( $height > 0 && $height < 80 ) ) {
					continue;
				}

				if ( preg_match( '/\.(jpg|jpeg|png|webp|avif)($|\?)/i', $src ) ) {
					$img_urls[] = $src;
				}
			}
		}

		$img_urls = array_values( array_unique( $img_urls ) );
		if ( ! empty( $img_urls ) ) {
			$result['main']    = array_shift( $img_urls );
			$result['gallery'] = array_slice( $img_urls, 0, 15 ); // Tối đa 15 ảnh gallery
		}

		return $result;
	}

	/**
	 * Trích xuất các tài liệu kỹ thuật / file đính kèm (PDF, Manual, Catalogue)
	 */
	private static function extract_documents( DOMXPath $xpath, string $source_url ): array {
		$docs  = array();
		$links = $xpath->query( '//a[contains(@href, ".pdf") or contains(@href, ".doc") or contains(@href, ".docx")]' );

		if ( $links && $links->length > 0 ) {
			$client = WP_Crawler_Client::get_instance();
			foreach ( $links as $link ) {
				$href  = trim( $link->getAttribute( 'href' ) );
				$title = trim( $link->textContent ) ?: basename( $href );

				if ( ! empty( $href ) ) {
					$docs[] = array(
						'title' => sanitize_text_field( $title ),
						'url'   => $client->resolve_relative_url( $href, $source_url ),
					);
				}
			}
		}

		return array_slice( $docs, 0, 5 );
	}

	/**
	 * Tìm chuỗi văn bản đầu tiên khớp với danh sách các biểu thức XPath
	 */
	private static function find_text( DOMXPath $xpath, array $expressions ): string {
		foreach ( $expressions as $expr ) {
			$nodes = $xpath->query( $expr );
			if ( $nodes && $nodes->length > 0 ) {
				$text = trim( $nodes->item( 0 )->textContent );
				if ( ! empty( $text ) ) {
					return html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
				}
			}
		}
		return '';
	}

	/**
	 * Tìm nội dung HTML của node đầu tiên khớp với biểu thức XPath
	 */
	private static function find_html( DOMXPath $xpath, array $expressions ): string {
		foreach ( $expressions as $expr ) {
			$nodes = $xpath->query( $expr );
			if ( $nodes && $nodes->length > 0 ) {
				$node = $nodes->item( 0 );
				$html = '';
				foreach ( $node->childNodes as $child ) {
					$html .= $node->ownerDocument->saveHTML( $child );
				}
				if ( ! empty( trim( $html ) ) ) {
					return $html;
				}
			}
		}
		return '';
	}

	/**
	 * Chuyển đổi chuỗi tiền tệ bất kỳ thành số thực float (VD: "1.250.000 đ" -> 1250000.0)
	 */
	public static function parse_price( string $price_str ): ?float {
		$clean = trim( html_entity_decode( $price_str, ENT_QUOTES, 'UTF-8' ) );

		// Tìm mẫu số có định dạng giá
		if ( preg_match( '/([\d\.,]+)\s*(?:₫|đ|vnd|usd|\$|€)?/iu', $clean, $m ) ) {
			$num_str = $m[1];
			// Nếu có dấu chấm phân cách hàng nghìn kiểu Việt Nam (VD: 1.250.000)
			if ( substr_count( $num_str, '.' ) > 1 || ( substr_count( $num_str, '.' ) === 1 && substr_count( $num_str, ',' ) === 1 ) ) {
				$num_str = str_replace( '.', '', $num_str );
				$num_str = str_replace( ',', '.', $num_str );
			} elseif ( substr_count( $num_str, '.' ) === 1 && strlen( substr( $num_str, strpos( $num_str, '.' ) + 1 ) ) === 3 ) {
				// Dạng 150.000
				$num_str = str_replace( '.', '', $num_str );
			} elseif ( substr_count( $num_str, ',' ) === 1 && strlen( substr( $num_str, strpos( $num_str, ',' ) + 1 ) ) === 3 ) {
				// Dạng 150,000
				$num_str = str_replace( ',', '', $num_str );
			} else {
				$num_str = str_replace( ',', '', $num_str );
			}

			$val = (float) $num_str;
			return $val > 0 ? $val : null;
		}

		return null;
	}

	/**
	 * Làm sạch và chuẩn hóa tên danh mục trích xuất từ website nguồn.
	 *
	 * @param string $name Tên danh mục thô.
	 * @return string Tên danh mục sạch, chuẩn UTF-8, không chứa ký tự rác/bom/html entity.
	 */
	public static function clean_category_name( string $name ): string {
		if ( empty( $name ) ) {
			return '';
		}

		// Giải mã HTML entities (kể cả &nbsp;, &amp;, &quot;,...)
		$cleaned = html_entity_decode( $name, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// Thay thế khoảng trắng đặc biệt (non-breaking space \u{00a0}, zero-width space \u{200b}) bằng khoảng trắng chuẩn
		$cleaned = preg_replace( '/[\x{00a0}\x{200b}\x{feff}\s]+/u', ' ', $cleaned );

		// Xóa các ký tự phân tách rác, dấu hỏi dò mã hỏng, gạch chéo ở đầu/cuối chuỗi
		$cleaned = trim( $cleaned, " \t\n\r\0\x0B?/-›»>|:" );

		// Loại bỏ các tiền tố thông dụng như "Danh mục:", "Chuyên mục:", "Category:"
		$cleaned = preg_replace( '/^(Danh\s+mục|Chuyên\s+mục|Category|Nhóm\s+sản\s+phẩm)\s*:\s*/iu', '', $cleaned );

		// Chuẩn hóa một lần nữa
		$cleaned = trim( $cleaned, " \t\n\r\0\x0B?/-›»>|:" );

		return sanitize_text_field( $cleaned );
	}

	/**
	 * Trích xuất danh mục sản phẩm chính xác từ website nguồn.
	 *
	 * @param DOMXPath $xpath Đối tượng DOMXPath.
	 * @param string   $html HTML thô của trang.
	 * @param string   $source_url URL của trang sản phẩm.
	 * @param array    $product_data Dữ liệu đã trích xuất sơ bộ (để đối chiếu tránh nhầm với tên sản phẩm).
	 * @return string Tên danh mục chính xác từ website nguồn.
	 */
	public static function extract_category( DOMXPath $xpath, string $html, string $source_url, array $product_data = array() ): string {
		$generic_terms = array( 'trang chủ', 'home', 'sản phẩm', 'products', 'shop', 'cửa hàng', 'danh mục sản phẩm' );
		$product_name  = self::clean_category_name( $product_data['name'] ?? '' );
		$norm_pname    = strtolower( $product_name );

		// 1. Trích xuất từ JSON-LD BreadcrumbList (chính xác và cấu trúc chuẩn nhất)
		if ( preg_match_all( '#<script\s+[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $matches ) ) {
			foreach ( $matches[1] as $jstr ) {
				$json = json_decode( trim( $jstr ), true );
				if ( ! is_array( $json ) ) {
					continue;
				}

				$nodes = isset( $json['@graph'] ) && is_array( $json['@graph'] ) ? $json['@graph'] : array( $json );
				foreach ( $nodes as $node ) {
					if ( ! is_array( $node ) || empty( $node['@type'] ) ) {
						continue;
					}

					$type = is_array( $node['@type'] ) ? implode( ' ', $node['@type'] ) : (string) $node['@type'];

					// A. Kiểm tra BreadcrumbList
					if ( stripos( $type, 'BreadcrumbList' ) !== false && ! empty( $node['itemListElement'] ) && is_array( $node['itemListElement'] ) ) {
						$items = $node['itemListElement'];
						usort( $items, function( $a, $b ) {
							return (int) ( $a['position'] ?? 0 ) <=> (int) ( $b['position'] ?? 0 );
						} );

						$crumbs = array();
						foreach ( $items as $elem ) {
							$name = '';
							if ( is_array( $elem ) ) {
								if ( ! empty( $elem['item'] ) && is_array( $elem['item'] ) && ! empty( $elem['item']['name'] ) ) {
									$name = $elem['item']['name'];
								} elseif ( ! empty( $elem['name'] ) && is_string( $elem['name'] ) ) {
									$name = $elem['name'];
								}
							}
							$cleaned_name = self::clean_category_name( $name );
							if ( ! empty( $cleaned_name ) && ! in_array( strtolower( $cleaned_name ), $generic_terms, true ) ) {
								$crumbs[] = $cleaned_name;
							}
						}

						if ( count( $crumbs ) >= 2 ) {
							// Mục cuối cùng luôn là chính trang sản phẩm hiện tại, mục kế cuối là danh mục trực tiếp
							return $crumbs[ count( $crumbs ) - 2 ];
						} elseif ( 1 === count( $crumbs ) ) {
							$single = $crumbs[0];
							if ( empty( $norm_pname ) || strtolower( $single ) !== $norm_pname ) {
								return $single;
							}
						}
					}

					// B. Kiểm tra Product Node schema.org có trường category
					if ( stripos( $type, 'Product' ) !== false && ! empty( $node['category'] ) && is_string( $node['category'] ) ) {
						$cat = self::clean_category_name( $node['category'] );
						if ( ! empty( $cat ) && ! in_array( strtolower( $cat ), $generic_terms, true ) && ( empty( $norm_pname ) || strtolower( $cat ) !== $norm_pname ) ) {
							return $cat;
						}
					}
				}
			}
		}

		// 2. Trích xuất từ DOM các khối phân loại chuẩn WooCommerce & E-commerce (posted_in, product-category, cat-links)
		$dom_cat_queries = array(
			'//*[contains(@class, "posted_in")]//a',
			'//*[contains(@class, "product-category")]//a',
			'//*[contains(@class, "cat-links")]//a',
			'//*[@itemprop="category"]',
		);
		foreach ( $dom_cat_queries as $query ) {
			$nodes = $xpath->query( $query );
			if ( $nodes && $nodes->length > 0 ) {
				foreach ( $nodes as $node ) {
					$txt = self::clean_category_name( $node->textContent );
					if ( ! empty( $txt ) && ! in_array( strtolower( $txt ), $generic_terms, true ) ) {
						if ( empty( $norm_pname ) || strtolower( $txt ) !== $norm_pname ) {
							return $txt;
						}
					}
				}
			}
		}

		// 3. Trích xuất từ thẻ liên kết trong Breadcrumb DOM
		$bc_links = $xpath->query( '//nav[contains(@class, "woocommerce-breadcrumb")]//a | //nav[contains(@class, "breadcrumb")]//a | //*[contains(@class, "breadcrumbs")]//a | //*[contains(@class, "breadcrumb")]//a' );
		if ( $bc_links && $bc_links->length > 0 ) {
			$valid_links = array();
			foreach ( $bc_links as $link ) {
				$txt  = self::clean_category_name( $link->textContent );
				$href = trim( $link->getAttribute( 'href' ) );
				if ( empty( $txt ) || in_array( strtolower( $txt ), $generic_terms, true ) ) {
					continue;
				}
				if ( ! empty( $source_url ) && rtrim( $href, '/' ) === rtrim( $source_url, '/' ) ) {
					continue;
				}
				if ( ! empty( $norm_pname ) && strtolower( $txt ) === $norm_pname ) {
					continue;
				}
				$valid_links[] = $txt;
			}
			if ( ! empty( $valid_links ) ) {
				// Lấy link danh mục sâu nhất (link cuối cùng trước sản phẩm)
				return end( $valid_links );
			}
		}

		// 4. Fallback: Phân tích chuỗi văn bản Breadcrumb thuần túy
		$raw_bc = self::find_text( $xpath, array(
			'//*[contains(@class, "woocommerce-breadcrumb")]',
			'//*[contains(@class, "breadcrumb")]',
			'//nav[contains(@class, "breadcrumbs")]',
		) );
		if ( ! empty( $raw_bc ) ) {
			$raw_crumbs     = preg_split( '/[\/›»>|]/u', $raw_bc );
			$cleaned_crumbs = array();
			foreach ( $raw_crumbs as $rc ) {
				$c = self::clean_category_name( $rc );
				if ( ! empty( $c ) && ! in_array( strtolower( $c ), $generic_terms, true ) ) {
					$cleaned_crumbs[] = $c;
				}
			}
			if ( count( $cleaned_crumbs ) >= 2 ) {
				// Phần tử cuối là tên sản phẩm, phần tử áp chót là danh mục
				return $cleaned_crumbs[ count( $cleaned_crumbs ) - 2 ];
			} elseif ( 1 === count( $cleaned_crumbs ) ) {
				$c = $cleaned_crumbs[0];
				if ( empty( $norm_pname ) || strtolower( $c ) !== $norm_pname ) {
					return $c;
				}
			}
		}

		return '';
	}
}
