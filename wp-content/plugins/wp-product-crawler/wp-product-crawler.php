<?php
/**
 * Plugin Name: WP Product Crawler & Importer
 * Plugin URI: https://mozlex.vn
 * Description: Bộ công cụ quét và nhập tự động sản phẩm từ website vào WordPress & Mozlex Catalogue với hỗ trợ SSRF Protection, JSON-LD, Microdata, Media Library, Mapping danh mục và phát hiện trùng lặp.
 * Version: 1.0.0
 * Author: Mozlex Engineering
 * Author URI: https://mozlex.vn
 * Text Domain: wp-product-crawler
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_PRODUCT_CRAWLER_VERSION', '1.0.0' );
define( 'WP_PRODUCT_CRAWLER_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_PRODUCT_CRAWLER_URL', plugin_dir_url( __FILE__ ) );

// Nạp các module thành phần
require_once WP_PRODUCT_CRAWLER_PATH . 'includes/class-crawler-security.php';
require_once WP_PRODUCT_CRAWLER_PATH . 'includes/class-crawler-settings.php';
require_once WP_PRODUCT_CRAWLER_PATH . 'includes/class-crawler-job-manager.php';
require_once WP_PRODUCT_CRAWLER_PATH . 'includes/class-crawler-client.php';
require_once WP_PRODUCT_CRAWLER_PATH . 'includes/class-product-extractor.php';
require_once WP_PRODUCT_CRAWLER_PATH . 'includes/class-crawler-engine.php';
require_once WP_PRODUCT_CRAWLER_PATH . 'includes/class-duplicate-detector.php';
require_once WP_PRODUCT_CRAWLER_PATH . 'includes/class-category-mapper.php';
require_once WP_PRODUCT_CRAWLER_PATH . 'includes/class-media-importer.php';
require_once WP_PRODUCT_CRAWLER_PATH . 'includes/class-product-importer.php';
require_once WP_PRODUCT_CRAWLER_PATH . 'includes/class-crawler-admin.php';

/**
 * Kích hoạt Plugin & Tạo cấu trúc bảng cơ sở dữ liệu
 */
register_activation_hook( __FILE__, 'wp_product_crawler_activate' );
function wp_product_crawler_activate() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset_collate = $wpdb->get_charset_collate();

	// 1. Bảng Jobs
	$table_jobs = $wpdb->prefix . 'crawler_jobs';
	$sql_jobs   = "CREATE TABLE {$table_jobs} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		source_url text NOT NULL,
		url_type varchar(50) NOT NULL DEFAULT 'unknown',
		status varchar(50) NOT NULL DEFAULT 'pending',
		import_mode varchar(50) NOT NULL DEFAULT 'create_update',
		products_found int(11) NOT NULL DEFAULT 0,
		products_imported int(11) NOT NULL DEFAULT 0,
		products_updated int(11) NOT NULL DEFAULT 0,
		products_skipped int(11) NOT NULL DEFAULT 0,
		errors_count int(11) NOT NULL DEFAULT 0,
		settings_json longtext NULL,
		created_at datetime NOT NULL,
		updated_at datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY status (status),
		KEY created_at (created_at)
	) {$charset_collate};";
	dbDelta( $sql_jobs );

	// 2. Bảng Items
	$table_items = $wpdb->prefix . 'crawler_items';
	$sql_items   = "CREATE TABLE {$table_items} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		job_id bigint(20) unsigned NOT NULL,
		source_url varchar(500) NOT NULL,
		product_name text NULL,
		sku varchar(100) NULL,
		price decimal(15,2) NULL,
		status varchar(50) NOT NULL DEFAULT 'discovered',
		raw_data_json longtext NULL,
		mapped_category_id bigint(20) unsigned NOT NULL DEFAULT 0,
		post_id bigint(20) unsigned NOT NULL DEFAULT 0,
		error_message text NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY job_id (job_id),
		KEY source_url (source_url(191)),
		KEY sku (sku),
		KEY status (status)
	) {$charset_collate};";
	dbDelta( $sql_items );

	// 3. Bảng Logs
	$table_logs = $wpdb->prefix . 'crawler_logs';
	$sql_logs   = "CREATE TABLE {$table_logs} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		job_id bigint(20) unsigned NOT NULL,
		level varchar(20) NOT NULL DEFAULT 'INFO',
		message text NOT NULL,
		context_json text NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY job_id (job_id),
		KEY level (level),
		KEY created_at (created_at)
	) {$charset_collate};";
	dbDelta( $sql_logs );

	// Khởi tạo cài đặt mặc định nếu chưa có
	WP_Crawler_Settings::get_instance()->init_defaults();
}

/**
 * Khởi động Plugin
 */
add_action( 'plugins_loaded', function () {
	WP_Crawler_Admin::get_instance()->init();
} );
