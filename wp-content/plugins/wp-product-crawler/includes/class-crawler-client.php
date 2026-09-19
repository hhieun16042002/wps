<?php
/**
 * HTTP Client an toàn cho Crawler: Tích hợp SSRF Check, Rate Limiter, Safe Redirect Follower.
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Crawler_Client {

	private static ?WP_Crawler_Client $instance = null;
	private float $last_request_time = 0.0;

	public static function get_instance(): WP_Crawler_Client {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Thực hiện HTTP GET request an toàn với SSRF validation và kiểm tra chuyển hướng.
	 *
	 * @param string $url URL cần tải.
	 * @param array $args Các tham số bổ sung.
	 * @return array|WP_Error Trả về mảng ['code' => int, 'headers' => array, 'body' => string, 'url' => string] hoặc WP_Error.
	 */
	public function get( string $url, array $args = array() ): array|WP_Error {
		$settings = WP_Crawler_Settings::get_instance()->get_all();
		$timeout  = (int) ( $args['timeout'] ?? $settings['request_timeout'] ?? 15 );
		$rpm      = (int) ( $settings['requests_per_minute'] ?? 30 );
		$ua       = (string) ( $settings['user_agent'] ?? 'Mozilla/5.0 (compatible; MozlexCrawler/1.0; +https://mozlex.vn)' );

		// 1. Thực thi Rate Limiter
		$this->enforce_rate_limit( $rpm );

		// 2. Chống SSRF trên URL ban đầu
		$valid = WP_Crawler_Security::validate_url( $url );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		// 3. Thực hiện request với tối đa 5 bước chuyển hướng an toàn (Safe Redirect Tracking)
		$current_url = $url;
		$max_hops    = 5;
		$hops        = 0;

		while ( $hops < $max_hops ) {
			// Kiểm tra lại URL của từng hop
			$valid_hop = WP_Crawler_Security::validate_url( $current_url );
			if ( is_wp_error( $valid_hop ) ) {
				return new WP_Error( 'crawler_ssrf_redirect_blocked', sprintf( __( 'Phát hiện chuyển hướng đến địa chỉ bị cấm: %s (%s)', 'wp-product-crawler' ), esc_html( $current_url ), $valid_hop->get_error_message() ) );
			}

			$req_args = array(
				'timeout'     => $timeout,
				'redirection' => 0, // Tắt auto-redirect của WP để tự kiểm tra IP trước khi follow
				'user-agent'  => $ua,
				'headers'     => array(
					'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
					'Accept-Language' => 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
					'Cache-Control'   => 'no-cache',
				),
				'sslverify'   => apply_filters( 'wp_crawler_sslverify', true ),
			);

			$response = wp_remote_get( $current_url, $req_args );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = wp_remote_retrieve_response_code( $response );

			// Xử lý Redirect (301, 302, 303, 307, 308)
			if ( in_array( $code, array( 301, 302, 303, 307, 308 ), true ) ) {
				$location = wp_remote_retrieve_header( $response, 'location' );
				if ( empty( $location ) ) {
					break;
				}

				// Chuyển đổi location tương đối sang tuyệt đối nếu cần
				$current_url = $this->resolve_relative_url( $location, $current_url );
				++$hops;
				continue;
			}

			// Đã đến trang đích
			return array(
				'code'     => (int) $code,
				'headers'  => wp_remote_retrieve_headers( $response ),
				'body'     => wp_remote_retrieve_body( $response ),
				'final_url'=> $current_url,
			);
		}

		return new WP_Error( 'crawler_too_many_redirects', __( 'Quá nhiều chuyển hướng HTTP (vượt quá 5 lần).', 'wp-product-crawler' ) );
	}

	/**
	 * Rate limiter: Đảm bảo khoảng cách giữa các request tối thiểu bằng 60s / RPM
	 */
	private function enforce_rate_limit( int $rpm ): void {
		if ( $rpm <= 0 ) {
			return;
		}

		$min_interval = 60.0 / $rpm; // Khoảng cách giây giữa 2 request
		$now          = microtime( true );
		$time_passed  = $now - $this->last_request_time;

		if ( $time_passed < $min_interval && $this->last_request_time > 0 ) {
			$sleep_seconds = $min_interval - $time_passed;
			// Giới hạn thời gian sleep tối đa 2 giây mỗi bước AJAX để không làm nghẽn
			if ( $sleep_seconds > 0 && $sleep_seconds <= 2.0 ) {
				usleep( (int) ( $sleep_seconds * 1000000 ) );
			}
		}

		$this->last_request_time = microtime( true );
	}

	/**
	 * Chuyển đổi URL tương đối (/san-pham/...) thành URL tuyệt đối (https://example.com/san-pham/...)
	 */
	public function resolve_relative_url( string $relative_or_absolute, string $base_url ): string {
		$rel = trim( $relative_or_absolute );
		if ( empty( $rel ) ) {
			return $base_url;
		}

		if ( preg_match( '#^(https?://|//)#i', $rel ) ) {
			if ( str_starts_with( $rel, '//' ) ) {
				$base_scheme = parse_url( $base_url, PHP_URL_SCHEME ) ?: 'https';
				return $base_scheme . ':' . $rel;
			}
			return $rel;
		}

		$base_parts = wp_parse_url( $base_url );
		if ( empty( $base_parts['scheme'] ) || empty( $base_parts['host'] ) ) {
			return $rel;
		}

		$root = $base_parts['scheme'] . '://' . $base_parts['host'];
		if ( ! empty( $base_parts['port'] ) ) {
			$root .= ':' . $base_parts['port'];
		}

		if ( str_starts_with( $rel, '/' ) ) {
			return $root . $rel;
		}

		$base_path = $base_parts['path'] ?? '/';
		$dir       = preg_replace( '#/[^/]*$#', '', $base_path );

		return $root . '/' . ltrim( $dir . '/' . $rel, '/' );
	}
}
