<?php
/**
 * Security Hardening Suite for Mozlex / Duc Tri 226
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Chặn Username Enumeration qua REST API (/wp-json/wp/v2/users) cho khách vãng lai
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		if ( isset( $endpoints['/wp/v2/users'] ) ) {
			unset( $endpoints['/wp/v2/users'] );
		}
		if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
			unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
	}
	return $endpoints;
} );

// 2. Chặn User Enumeration qua query ?author=N và trang Author Archive
add_action( 'parse_request', function ( $wp ) {
	if ( ! is_user_logged_in() && ( isset( $_GET['author'] ) || isset( $wp->query_vars['author'] ) ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

add_action( 'template_redirect', function () {
	if ( ! is_user_logged_in() && ( is_author() || isset( $_GET['author'] ) ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

// 3. Vô hiệu hóa XML-RPC
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', function () {
	return array();
} );

// 4. Ẩn WordPress Version khỏi header HTML và RSS feeds
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// 5. Ẩn thông báo lỗi đăng nhập chi tiết (tránh tiết lộ username đúng/sai)
add_filter( 'login_errors', function () {
	return __( 'Thông tin đăng nhập không chính xác. Vui lòng kiểm tra lại.', 'mozlex' );
} );
