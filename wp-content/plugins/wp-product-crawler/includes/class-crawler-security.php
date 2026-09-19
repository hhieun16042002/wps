<?php
/**
 * Lớp bảo mật Crawler: Chống SSRF, kiểm tra IP riêng tư, Robots.txt, Quyền & Nonce.
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Crawler_Security {

	/**
	 * Danh sách các dải CIDR IPv4 bị cấm hoàn toàn (SSRF Prevention).
	 */
	private static array $blocked_ipv4_ranges = array(
		'0.0.0.0/8',          // "This network"
		'10.0.0.0/8',         // Private IPv4 Class A
		'100.64.0.0/10',      // Carrier-Grade NAT
		'127.0.0.0/8',        // Loopback addresses
		'169.254.0.0/16',     // Link-Local (Cloud metadata: 169.254.169.254)
		'172.16.0.0/12',      // Private IPv4 Class B
		'192.0.0.0/24',       // IETF Protocol Assignments
		'192.0.2.0/24',       // TEST-NET-1
		'192.88.99.0/24',     // 6to4 Relay Anycast
		'192.168.0.0/16',     // Private IPv4 Class C
		'198.18.0.0/15',      // Benchmarking
		'198.51.100.0/24',    // TEST-NET-2
		'203.0.113.0/24',     // TEST-NET-3
		'224.0.0.0/4',        // Multicast
		'240.0.0.0/4',        // Reserved
		'255.255.255.255/32', // Broadcast
	);

	/**
	 * Kiểm tra tính hợp lệ và an toàn của URL (Chống SSRF).
	 *
	 * @param string $url URL cần kiểm tra.
	 * @return true|WP_Error Trả về true nếu an toàn, hoặc WP_Error nếu có nguy cơ bảo mật.
	 */
	public static function validate_url( string $url ): true|WP_Error {
		$url = trim( $url );
		if ( empty( $url ) ) {
			return new WP_Error( 'crawler_empty_url', __( 'Vui lòng cung cấp URL website hợp lệ.', 'wp-product-crawler' ) );
		}

		$parts = wp_parse_url( $url );
		if ( ! $parts || empty( $parts['host'] ) ) {
			return new WP_Error( 'crawler_invalid_url', __( 'Định dạng URL không hợp lệ.', 'wp-product-crawler' ) );
		}

		// 1. Chỉ cho phép scheme http và https
		$scheme = strtolower( $parts['scheme'] ?? '' );
		if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
			return new WP_Error( 'crawler_forbidden_scheme', sprintf( __( 'Giao thức "%s" không được phép. Chỉ hỗ trợ HTTP và HTTPS.', 'wp-product-crawler' ), esc_html( $scheme ) ) );
		}

		$host = strtolower( $parts['host'] );

		// 2. Chặn các tên miền cục bộ phổ biến
		$blocked_hosts = array(
			'localhost',
			'localhost.localdomain',
			'ip6-localhost',
			'ip6-loopback',
			'broadcasthost',
			'kubernetes.default',
			'metadata.google.internal',
		);
		if ( in_array( $host, $blocked_hosts, true ) || str_ends_with( $host, '.local' ) || str_ends_with( $host, '.internal' ) || str_ends_with( $host, '.localhost' ) ) {
			return new WP_Error( 'crawler_ssrf_blocked_host', sprintf( __( 'Máy chủ lưu trữ "%s" thuộc mạng nội bộ và bị chặn vì lý do an toàn.', 'wp-product-crawler' ), esc_html( $host ) ) );
		}

		// 3. Phân giải DNS sang IP để chặn triệt để bypass qua domain mapping
		$ips = array();
		if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
			$ips[] = $host;
		} else {
			$resolved = @gethostbynamel( $host );
			if ( ! empty( $resolved ) ) {
				$ips = $resolved;
			} else {
				// Nếu không thể phân giải DNS
				return new WP_Error( 'crawler_dns_failed', sprintf( __( 'Không thể phân giải địa chỉ máy chủ "%s". Vui lòng kiểm tra lại URL.', 'wp-product-crawler' ), esc_html( $host ) ) );
			}
		}

		// 4. Kiểm tra từng IP phân giải được
		foreach ( $ips as $ip ) {
			if ( self::is_private_or_reserved_ip( $ip ) ) {
				return new WP_Error(
					'crawler_ssrf_blocked_ip',
					sprintf( __( 'URL giải quyết về địa chỉ IP bị cấm (%s). Truy cập vào dải mạng nội bộ hoặc đám mây bị từ chối nghiêm ngặt.', 'wp-product-crawler' ), esc_html( $ip ) )
				);
			}
		}

		return true;
	}

	/**
	 * Kiểm tra xem một IP có thuộc dải mạng riêng tư / cục bộ / dự trữ không.
	 *
	 * @param string $ip Địa chỉ IP (v4 hoặc v6).
	 * @return bool True nếu là IP bị chặn.
	 */
	public static function is_private_or_reserved_ip( string $ip ): bool {
		// Kiểm tra IPv6
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			// Loopback ::1
			if ( '::1' === $ip || '0000:0000:0000:0000:0000:0000:0000:0001' === $ip ) {
				return true;
			}
			// Unique Local Addresses fc00::/7
			if ( preg_match( '/^[fF][c-dC-D]/', $ip ) ) {
				return true;
			}
			// Link-Local Addresses fe80::/10
			if ( preg_match( '/^[fF][eE][89a-bA-B]/', $ip ) ) {
				return true;
			}
			return false;
		}

		// Kiểm tra IPv4
		$ip_long = ip2long( $ip );
		if ( false === $ip_long ) {
			return true; // Không phân tích được thì từ chối vì lý do bảo mật
		}

		foreach ( self::$blocked_ipv4_ranges as $range ) {
			list( $subnet, $bits ) = explode( '/', $range );
			$subnet_long           = ip2long( $subnet );
			$mask                  = -1 << ( 32 - (int) $bits );
			$subnet_masked         = $subnet_long & $mask;

			if ( ( $ip_long & $mask ) === $subnet_masked ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Kiểm tra file robots.txt của website nguồn xem có cấm path hay không.
	 *
	 * @param string $url URL cần kiểm tra quyền truy cập.
	 * @return bool True nếu được phép truy cập theo robots.txt.
	 */
	public static function is_allowed_by_robots( string $url ): bool {
		$parts = wp_parse_url( $url );
		if ( empty( $parts['host'] ) || empty( $parts['scheme'] ) ) {
			return true;
		}

		$base_url    = $parts['scheme'] . '://' . $parts['host'];
		$robots_url  = $base_url . '/robots.txt';
		$cache_key   = 'dt_crawler_robots_' . md5( $base_url );
		$robots_body = get_transient( $cache_key );

		if ( false === $robots_body ) {
			$res = wp_remote_get( $robots_url, array(
				'timeout'     => 5,
				'redirection' => 2,
				'user-agent'  => 'Mozilla/5.0 (compatible; MozlexCrawler/1.0; +https://mozlex.vn)',
			) );

			if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
				$robots_body = '';
			} else {
				$robots_body = (string) wp_remote_retrieve_body( $res );
			}
			set_transient( $cache_key, $robots_body, DAY_IN_SECONDS );
		}

		if ( empty( $robots_body ) ) {
			return true;
		}

		$path = $parts['path'] ?? '/';
		if ( empty( $path ) ) {
			$path = '/';
		}

		// Đơn giản hóa parser robots.txt: Tìm block "User-agent: *"
		$lines            = explode( "\n", str_replace( "\r", '', $robots_body ) );
		$is_matching_agent = false;
		$disallows        = array();

		foreach ( $lines as $line ) {
			$line = trim( preg_replace( '/#.*$/', '', $line ) );
			if ( empty( $line ) ) {
				continue;
			}

			if ( preg_match( '/^user-agent:\s*(.*)$/i', $line, $m ) ) {
				$agent             = trim( $m[1] );
				$is_matching_agent = ( '*' === $agent || stripos( $agent, 'MozlexCrawler' ) !== false );
				continue;
			}

			if ( $is_matching_agent && preg_match( '/^disallow:\s*(.*)$/i', $line, $m ) ) {
				$disallow_path = trim( $m[1] );
				if ( ! empty( $disallow_path ) ) {
					$disallows[] = $disallow_path;
				}
			}
		}

		foreach ( $disallows as $disallow ) {
			if ( str_starts_with( $path, $disallow ) ) {
				return false; // Bị cấm bởi robots.txt
			}
		}

		return true;
	}

	/**
	 * Kiểm tra quyền hạn và bảo mật Nonce cho các thao tác quản trị.
	 *
	 * @param string $action Tên action nonce.
	 * @param string $nonce_key Khóa nonce trong request (mặc định 'nonce').
	 * @return true|WP_Error
	 */
	public static function verify_admin_request( string $action = 'dt_crawler_action', string $nonce_key = 'nonce' ): true|WP_Error {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'crawler_forbidden', __( 'Bạn không có quyền thực hiện thao tác này.', 'wp-product-crawler' ), array( 'status' => 403 ) );
		}

		$nonce = isset( $_REQUEST[ $nonce_key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $nonce_key ] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			return new WP_Error( 'crawler_bad_nonce', __( 'Phiên làm việc hết hạn hoặc yêu cầu không hợp lệ. Vui lòng tải lại trang.', 'wp-product-crawler' ), array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Làm sạch nội dung HTML chi tiết sản phẩm (Chống XSS).
	 *
	 * @param string $html HTML thô.
	 * @return string HTML đã được làm sạch.
	 */
	public static function sanitize_html_content( string $html ): string {
		return wp_kses_post( $html );
	}
}
