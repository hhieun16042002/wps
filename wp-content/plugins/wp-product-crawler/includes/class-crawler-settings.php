<?php
/**
 * Lớp quản lý cài đặt Crawler.
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Crawler_Settings {

	private static ?WP_Crawler_Settings $instance = null;
	const OPTION_NAME = 'dt_crawler_settings';

	public static function get_instance(): WP_Crawler_Settings {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Giá trị cài đặt mặc định an toàn
	 */
	public function get_defaults(): array {
		return array(
			'request_timeout'         => 15,          // Giây
			'requests_per_minute'     => 30,          // Tối đa request mỗi phút (Rate limit)
			'concurrent_requests'     => 1,           // Số kết nối đồng thời
			'max_products'            => 50,          // Giới hạn sản phẩm quét
			'max_crawl_depth'         => 3,           // Độ sâu phân trang/danh mục
			'max_image_size_mb'       => 5,           // MB tối đa cho mỗi ảnh
			'download_images'         => 1,           // Tải ảnh về thư viện WordPress
			'import_descriptions'     => 1,           // Nhập mô tả sản phẩm
			'import_specifications'   => 1,           // Nhập thông số kỹ thuật dạng cấu trúc
			'import_prices'           => 1,           // Nhập giá bán
			'default_product_status'  => 'publish',   // Trạng thái mặc định: 'publish' (Đang hiển thị) hoặc 'draft'
			'default_import_mode'     => 'create_update', // 'create_only', 'update_existing', 'create_update'
			'auto_publish'            => 1,           // Tự động xuất bản (mặc định bật)
			'user_agent'              => 'Mozilla/5.0 (compatible; MozlexCrawler/1.0; +https://mozlex.vn)',
		);
	}

	public function init_defaults(): void {
		$saved = get_option( self::OPTION_NAME );
		if ( false === $saved ) {
			add_option( self::OPTION_NAME, $this->get_defaults() );
		} elseif ( is_array( $saved ) && ( ! isset( $saved['default_product_status'] ) || 'draft' === $saved['default_product_status'] ) ) {
			$saved['default_product_status'] = 'publish';
			$saved['auto_publish']            = 1;
			update_option( self::OPTION_NAME, $saved );
		}
	}

	public function get_all(): array {
		$saved = get_option( self::OPTION_NAME, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $this->get_defaults() );
	}

	public function get( string $key, mixed $default = null ): mixed {
		$settings = $this->get_all();
		return $settings[ $key ] ?? $default;
	}

	public function update( array $new_settings ): bool {
		$defaults = $this->get_defaults();
		$clean    = array();

		$clean['request_timeout']        = max( 3, min( 60, absint( $new_settings['request_timeout'] ?? $defaults['request_timeout'] ) ) );
		$clean['requests_per_minute']    = max( 5, min( 120, absint( $new_settings['requests_per_minute'] ?? $defaults['requests_per_minute'] ) ) );
		$clean['concurrent_requests']    = max( 1, min( 5, absint( $new_settings['concurrent_requests'] ?? $defaults['concurrent_requests'] ) ) );
		$clean['max_products']           = max( 1, min( 500, absint( $new_settings['max_products'] ?? $defaults['max_products'] ) ) );
		$clean['max_crawl_depth']        = max( 1, min( 10, absint( $new_settings['max_crawl_depth'] ?? $defaults['max_crawl_depth'] ) ) );
		$clean['max_image_size_mb']      = max( 1, min( 20, absint( $new_settings['max_image_size_mb'] ?? $defaults['max_image_size_mb'] ) ) );
		$clean['download_images']        = ! empty( $new_settings['download_images'] ) ? 1 : 0;
		$clean['import_descriptions']    = ! empty( $new_settings['import_descriptions'] ) ? 1 : 0;
		$clean['import_specifications']  = ! empty( $new_settings['import_specifications'] ) ? 1 : 0;
		$clean['import_prices']          = ! empty( $new_settings['import_prices'] ) ? 1 : 0;
		$clean['default_product_status'] = ( isset( $new_settings['default_product_status'] ) && 'draft' === $new_settings['default_product_status'] ) ? 'draft' : 'publish';
		$clean['default_import_mode']    = in_array( $new_settings['default_import_mode'] ?? '', array( 'create_only', 'update_existing', 'create_update' ), true ) ? $new_settings['default_import_mode'] : 'create_update';
		$clean['auto_publish']           = ! empty( $new_settings['auto_publish'] ) ? 1 : 0;
		$clean['user_agent']             = sanitize_text_field( $new_settings['user_agent'] ?? $defaults['user_agent'] );

		return update_option( self::OPTION_NAME, $clean );
	}
}
