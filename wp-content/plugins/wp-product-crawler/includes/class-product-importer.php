<?php
/**
 * Trình ghi dữ liệu sản phẩm vào WordPress (Posts, Taxonomies, PostMeta, Gallery).
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Product_Importer {

	/**
	 * Nhập hoặc cập nhật một sản phẩm vào hệ thống.
	 *
	 * @param array $product Dữ liệu sản phẩm.
	 * @param array $options Các tùy chọn nhập (import_mode, status, mapped_category_id, job_id).
	 * @return array Kết quả xử lý ['success' => bool, 'action' => string, 'post_id' => int, 'error' => string]
	 */
	public static function import( array $product, array $options = array() ): array {
		$mode        = $options['import_mode'] ?? 'create_update';
		$post_status = $options['status'] ?? 'publish';
		$category_id = (int) ( $options['mapped_category_id'] ?? 0 );
		$job_id      = (int) ( $options['job_id'] ?? 0 );

		// 1. Kiểm tra trùng lặp
		$dup_info    = WP_Duplicate_Detector::check( $product );
		$existing_id = $dup_info['post_id'];

		// Tự động bỏ qua nếu sản phẩm đã tồn tại trên website và không có thay đổi (hoặc ở chế độ chỉ tạo mới)
		if ( $existing_id > 0 && ( 'DUPLICATE' === $dup_info['status'] || 'create_only' === $mode ) ) {
			return array(
				'success' => true,
				'action'  => 'skipped',
				'post_id' => $existing_id,
				'message' => sprintf( __( 'Sản phẩm đã tồn tại trên website (#%d), tự động bỏ qua không nhập lại.', 'wp-product-crawler' ), $existing_id ),
			);
		}

		if ( 'update_existing' === $mode && 0 === $existing_id ) {
			return array(
				'success' => true,
				'action'  => 'skipped',
				'post_id' => 0,
				'message' => __( 'Bỏ qua vì không tìm thấy sản phẩm cũ theo chế độ Chỉ cập nhật.', 'wp-product-crawler' ),
			);
		}

		$is_update = $existing_id > 0;
		$post_id   = $existing_id;

		// 2. Chuẩn bị Post Data
		$slug    = sanitize_title( $product['name'] );
		$postarr = array(
			'post_type'    => 'product',
			'post_title'   => sanitize_text_field( $product['name'] ),
			'post_content' => WP_Crawler_Security::sanitize_html_content( $product['description'] ?? '' ),
			'post_excerpt' => sanitize_textarea_field( $product['short_description'] ?? '' ),
			'post_status'  => in_array( $post_status, array( 'draft', 'publish' ), true ) ? $post_status : 'publish',
		);

		if ( $is_update ) {
			$postarr['ID'] = $post_id;
			$res = wp_update_post( $postarr, true );
		} else {
			$postarr['post_name'] = $slug;
			$res = wp_insert_post( $postarr, true );
			if ( ! is_wp_error( $res ) ) {
				$post_id = $res;
			}
		}

		if ( is_wp_error( $res ) || ! $post_id ) {
			$err = is_wp_error( $res ) ? $res->get_error_message() : __( 'Không thể lưu bài viết sản phẩm.', 'wp-product-crawler' );
			return array(
				'success' => false,
				'action'  => 'error',
				'post_id' => 0,
				'error'   => $err,
			);
		}

		// 3. Xử lý Gán Danh mục sản phẩm (product_category)
		if ( $category_id > 0 ) {
			wp_set_object_terms( $post_id, array( $category_id ), 'product_category', false );
		} elseif ( ! empty( $product['category'] ) ) {
			// Thử tạo hoặc lấy term theo tên danh mục nguồn
			$cat_id = WP_Category_Mapper::create_local_category( $product['category'] );
			if ( ! is_wp_error( $cat_id ) && $cat_id > 0 ) {
				wp_set_object_terms( $post_id, array( $cat_id ), 'product_category', false );
			}
		}

		// 4. Xử lý Tải hình ảnh (Main Image & Gallery)
		$settings        = WP_Crawler_Settings::get_instance()->get_all();
		$download_images = ! empty( $settings['download_images'] );

		if ( $download_images ) {
			// A. Tải ảnh chính
			if ( ! empty( $product['main_image'] ) ) {
				$thumb_id = WP_Media_Importer::import_image( $product['main_image'], $post_id, $product['name'] );
				if ( ! is_wp_error( $thumb_id ) && $thumb_id > 0 ) {
					set_post_thumbnail( $post_id, $thumb_id );
				}
			}

			// B. Tải gallery ảnh
			if ( ! empty( $product['gallery_images'] ) && is_array( $product['gallery_images'] ) ) {
				$gallery_ids = array();
				foreach ( array_slice( $product['gallery_images'], 0, 10 ) as $g_url ) {
					$gid = WP_Media_Importer::import_image( $g_url, $post_id, $product['name'] . ' gallery' );
					if ( ! is_wp_error( $gid ) && $gid > 0 ) {
						$gallery_ids[] = $gid;
					}
				}

				if ( ! empty( $gallery_ids ) ) {
					$gallery_str = implode( ',', $gallery_ids );
					update_post_meta( $post_id, 'mozlex_gallery', $gallery_str );
					update_post_meta( $post_id, '_product_image_gallery', $gallery_str );
				}
			}
		}

		// 5. Cập nhật Post Meta chuẩn Mozlex & E-commerce
		$sku = ! empty( $product['sku'] ) ? $product['sku'] : ( $product['model'] ?? '' );
		update_post_meta( $post_id, 'mozlex_model', sanitize_text_field( $sku ) );
		update_post_meta( $post_id, '_sku', sanitize_text_field( $sku ) );

		if ( isset( $product['price'] ) && null !== $product['price'] ) {
			$price = (float) $product['price'];
			update_post_meta( $post_id, 'mozlex_price', $price );
			update_post_meta( $post_id, '_regular_price', $price );
			update_post_meta( $post_id, '_price', $price );
			update_post_meta( $post_id, 'mozlex_price_status', '' );
		} else {
			update_post_meta( $post_id, 'mozlex_price_status', 'an-gia' );
		}

		if ( ! empty( $product['brand'] ) ) {
			update_post_meta( $post_id, '_crawler_brand', sanitize_text_field( $product['brand'] ) );
		}

		// Thông số kỹ thuật dạng mảng cặp [Nhãn, Giá trị]
		if ( ! empty( $product['tech_specs_rows'] ) && is_array( $product['tech_specs_rows'] ) ) {
			update_post_meta( $post_id, 'mozlex_tech_specs', $product['tech_specs_rows'] );
		} elseif ( ! empty( $product['specifications'] ) && is_array( $product['specifications'] ) ) {
			$rows = array();
			foreach ( $product['specifications'] as $l => $v ) {
				$rows[] = array( sanitize_text_field( $l ), sanitize_text_field( $v ) );
			}
			update_post_meta( $post_id, 'mozlex_tech_specs', $rows );
		}

		// Ghi nhận nguồn gốc cào dữ liệu
		if ( ! empty( $product['source_url'] ) ) {
			update_post_meta( $post_id, '_crawler_source_url', esc_url_raw( $product['source_url'] ) );
		}
		if ( $job_id > 0 ) {
			update_post_meta( $post_id, '_crawler_job_id', $job_id );
		}
		update_post_meta( $post_id, '_crawler_imported_at', current_time( 'mysql' ) );

		return array(
			'success' => true,
			'action'  => $is_update ? 'updated' : 'created',
			'post_id' => $post_id,
			'message' => $is_update
				? sprintf( __( 'Đã cập nhật sản phẩm #%d ("%s").', 'wp-product-crawler' ), $post_id, $product['name'] )
				: sprintf( __( 'Đã tạo mới sản phẩm #%d ("%s").', 'wp-product-crawler' ), $post_id, $product['name'] ),
		);
	}
}
