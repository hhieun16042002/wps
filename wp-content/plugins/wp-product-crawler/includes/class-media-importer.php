<?php
/**
 * Trình tải và quản lý tập tin Media sản phẩm vào Thư viện WordPress.
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Media_Importer {

	/**
	 * Tải một ảnh từ URL bên ngoài và đăng ký vào WordPress Media Library.
	 *
	 * @param string $image_url URL ảnh cần tải.
	 * @param int $post_id ID bài viết sản phẩm liên kết (mặc định 0).
	 * @param string $title_hint Gợi ý tiêu đề cho ảnh.
	 * @return int|WP_Error ID của attachment hoặc WP_Error nếu thất bại.
	 */
	public static function import_image( string $image_url, int $post_id = 0, string $title_hint = '' ): int|WP_Error {
		$image_url = trim( $image_url );
		if ( empty( $image_url ) ) {
			return new WP_Error( 'crawler_empty_image_url', __( 'URL hình ảnh không được để trống.', 'wp-product-crawler' ) );
		}

		// 1. Kiểm tra SSRF
		$valid_ssrf = WP_Crawler_Security::validate_url( $image_url );
		if ( is_wp_error( $valid_ssrf ) ) {
			return $valid_ssrf;
		}

		// 2. Chống tải trùng lặp (Deduplication): Kiểm tra xem ảnh này đã từng tải chưa
		global $wpdb;
		$existing_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_crawler_source_image_url' AND meta_value = %s LIMIT 1",
			$image_url
		) );
		if ( $existing_id && wp_attachment_is_image( (int) $existing_id ) ) {
			return (int) $existing_id;
		}

		// Nạp các hàm quản lý media WordPress
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$settings    = WP_Crawler_Settings::get_instance()->get_all();
		$max_size_mb = (int) ( $settings['max_image_size_mb'] ?? 5 );
		$timeout     = (int) ( $settings['request_timeout'] ?? 15 );

		// 3. Tải file tạm về máy chủ
		$tmp_file = download_url( $image_url, $timeout );
		if ( is_wp_error( $tmp_file ) ) {
			return $tmp_file;
		}

		// 4. Kiểm tra dung lượng file
		$file_size = @filesize( $tmp_file );
		if ( $file_size > ( $max_size_mb * 1024 * 1024 ) ) {
			@unlink( $tmp_file );
			return new WP_Error( 'crawler_image_too_large', sprintf( __( 'Ảnh vượt quá dung lượng cho phép (%d MB).', 'wp-product-crawler' ), $max_size_mb ) );
		}

		// 5. Kiểm tra MIME Type thực tế
		$finfo     = finfo_open( FILEINFO_MIME_TYPE );
		$mime_type = finfo_file( $finfo, $tmp_file );
		finfo_close( $finfo );

		$allowed_mimes = array(
			'image/jpeg' => 'jpg',
			'image/jpg'  => 'jpg',
			'image/png'  => 'png',
			'image/webp' => 'webp',
			'image/gif'  => 'gif',
			'image/avif' => 'avif',
		);

		if ( ! isset( $allowed_mimes[ $mime_type ] ) ) {
			@unlink( $tmp_file );
			return new WP_Error( 'crawler_invalid_image_mime', sprintf( __( 'Định dạng tệp không được hỗ trợ (%s). Chỉ chấp nhận ảnh JPG, PNG, WebP, GIF, AVIF.', 'wp-product-crawler' ), esc_html( $mime_type ) ) );
		}

		$extension = $allowed_mimes[ $mime_type ];

		// 6. Đặt tên tệp an toàn
		$slug_hint = ! empty( $title_hint ) ? sanitize_title( $title_hint ) : 'product-img';
		$filename  = sprintf( '%s-%s.%s', substr( $slug_hint, 0, 40 ), substr( md5( $image_url . microtime() ), 0, 8 ), $extension );

		$file_array = array(
			'name'     => $filename,
			'tmp_name' => $tmp_file,
		);

		// 7. Nhập vào Media Library
		$attachment_id = media_handle_sideload( $file_array, $post_id, $title_hint );

		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp_file );
			return $attachment_id;
		}

		// Đánh dấu URL nguồn để chống tải trùng lặp lần sau
		update_post_meta( $attachment_id, '_crawler_source_image_url', $image_url );

		return (int) $attachment_id;
	}
}
