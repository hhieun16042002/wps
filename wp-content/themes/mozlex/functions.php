<?php
/**
 * Mozlex theme bootstrap.
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MOZLEX_VERSION', '2.8.76' );
define( 'MOZLEX_DIR', get_template_directory() );

require_once MOZLEX_DIR . '/inc/cpt.php';
require_once MOZLEX_DIR . '/inc/template-helpers.php';
require_once MOZLEX_DIR . '/inc/meta-boxes.php';
require_once MOZLEX_DIR . '/inc/theme-options.php';
require_once MOZLEX_DIR . '/inc/contact-handler.php';
require_once MOZLEX_DIR . '/inc/seo-schema.php';
require_once MOZLEX_DIR . '/inc/nav-fallback.php';
require_once MOZLEX_DIR . '/inc/rest.php';
require_once MOZLEX_DIR . '/inc/importer.php';
require_once MOZLEX_DIR . '/inc/category-thumbnails.php';
require_once MOZLEX_DIR . '/inc/admin-specs.php';

/**
 * Theme supports.
 */
add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'mozlex', MOZLEX_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array(
		'height'      => 64,
		'width'       => 220,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	register_nav_menus( array(
		'primary' => __( 'Menu chính', 'mozlex' ),
	) );

	// Image sizes cho catalogue layout.
	add_image_size( 'mozlex-card', 640, 640, false );
	add_image_size( 'mozlex-hero', 1400, 1600, false );
	add_image_size( 'mozlex-category', 900, 1100, false );
} );

/**
 * Assets — một CSS, một JS, không library ngoài.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'mozlex-main',
		get_template_directory_uri() . '/assets/css/main.css',
		array(),
		MOZLEX_VERSION
	);

	wp_enqueue_script(
		'mozlex-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		MOZLEX_VERSION,
		array( 'strategy' => 'defer' )
	);
	wp_localize_script( 'mozlex-main', 'MozlexData', array(
		'restUrl'   => esc_url_raw( rest_url( 'mozlex/v1/' ) ),
		'nonce'     => wp_create_nonce( 'wp_rest' ),
		'searchAll' => __( 'Tất cả sản phẩm', 'mozlex' ),
		'themeUri'  => get_template_directory_uri(),
	) );
} );

/**
 * Preload hero đầu trang để tránh LCP chậm (được gọi trong template).
 */
add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
	if ( is_front_page() && 'preconnect' === $relation ) {
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}, 10, 2 );

add_action( 'wp_head', function () {
	// Inter/Manrope (footer) + Roboto (body) + Cabin (nav) + Literata (headings) + Monsieur La Doulaise (decorative) + Be Vietnam Pro.
	$fonts = 'Inter:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&family=Roboto:wght@400;500;600;700&family=Cabin:wght@400;500;600;700&family=Literata:opsz,wght@7..72,400;7..72,600;7..72,700&family=Monsieur+La+Doulaise&family=Be+Vietnam+Pro:wght@400;500;600;700;800';
	$url = 'https://fonts.googleapis.com/css2?family=' . $fonts . '&display=swap';
	echo '<link rel="preload" as="style" href="' . esc_url( $url ) . '">' . "\n";
	echo '<link rel="stylesheet" href="' . esc_url( $url ) . '" media="print" onload="this.media=\'all\'">' . "\n";
	echo '<noscript><link rel="stylesheet" href="' . esc_url( $url ) . '"></noscript>' . "\n";
}, 4 );

/**
 * Remove emoji scripts & extra head bloat.
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_generator' );

/**
 * Security headers — giảm XSS/clickjacking/MIME sniffing.
 */
add_action( 'send_headers', function () {
	if ( is_admin() ) return;
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
	// CSP lỏng cho Google Fonts/Maps — chặn inline script lạ
	header( "Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval'; img-src 'self' https: data: blob:; frame-src https://www.google.com https://www.facebook.com https://www.youtube.com https://youtube.com https://www.youtube-nocookie.com https://mozilla.github.io;" );
} );

/**
 * Tắt hoàn toàn Đánh giá / bình luận cho sản phẩm (theo yêu cầu xóa form Đánh giá).
 */
add_action( 'init', function () {
	remove_post_type_support( 'product', 'comments' );
	remove_post_type_support( 'product', 'trackbacks' );
}, 100 );
add_filter( 'comments_open', function ( $open, $post_id ) {
	if ( get_post_type( $post_id ) === 'product' ) return false;
	return $open;
}, 10, 2 );
add_filter( 'pings_open', function ( $open, $post_id ) {
	if ( get_post_type( $post_id ) === 'product' ) return false;
	return $open;
}, 10, 2 );
// Ẩn CSS nếu review HTML còn sót trong content cũ
add_action( 'wp_head', function () {
	if ( is_singular( 'product' ) ) {
		echo '<style id="mozlex-no-reviews">.woocommerce-Reviews,#reviews,#respond,.comment-respond,.comment-form,.woocommerce-Tabs-panel--reviews{display:none !important;}</style>';
	}
}, 99 );

/**
 * Fix ảnh lỗi khi xem qua tunnel: thay localhost trong post_content thành host hiện tại (https)
 */
add_filter( 'the_content', function( $content ){
	if ( is_admin() ) return $content;
	$host = $_SERVER['HTTP_HOST'] ?? '';
	if ( str_ends_with( strtolower($host), '.trycloudflare.com' ) ) {
		$content = str_replace( 'http://localhost:8080', 'https://' . $host, $content );
		$content = str_replace( 'http://' . $host, 'https://' . $host, $content );
	}
	return $content;
}, 20 );

/**
 * Catalogue flipbook như https://mozlex.vn/?r3d=catalogue-mozlex-2023
 */
add_action( 'template_redirect', function(){
	if ( isset($_GET['r3d']) && $_GET['r3d']==='catalogue-mozlex-2023' ) {
		include get_template_directory() . '/page-catalogue.php';
		exit;
	}
});

/**
 * Exccerpt length cho product intro ngắn gọn.
 */
add_filter( 'excerpt_length', fn() => 28, 999 );
add_filter( 'excerpt_more', fn() => '…' );
// Fix /linh-vuc-hoat-dong/nhanh-thuong-mai 404 -> ?nhanh=thuong-mai
add_action('template_redirect', function(){
  if (preg_match('#/linh-vuc-hoat-dong/nhanh-([^/]+)/?#', $_SERVER['REQUEST_URI']??'', $m)){
    wp_redirect(home_url('/linh-vuc-hoat-dong/?nhanh='.$m[1]), 301);
    exit;
  }
});
