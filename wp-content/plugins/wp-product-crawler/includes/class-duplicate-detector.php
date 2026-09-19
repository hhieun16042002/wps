<?php
/**
 * Bộ phát hiện trùng lặp sản phẩm: Source URL, SKU, Model, Slug, và Tên chuẩn hóa.
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Duplicate_Detector {

	/**
	 * Kiểm tra xem một sản phẩm trích xuất đã tồn tại trong cơ sở dữ liệu hay chưa.
	 *
	 * @param array $product Dữ liệu sản phẩm trích xuất.
	 * @return array Kết quả kiểm tra ['status' => 'NEW'|'DUPLICATE'|'UPDATED'|'ERROR', 'post_id' => int, 'reason' => string]
	 */
	public static function check( array $product ): array {
		global $wpdb;

		// Nếu thiếu tên sản phẩm
		if ( empty( $product['name'] ) ) {
			return array(
				'status'  => 'ERROR',
				'post_id' => 0,
				'reason'  => __( 'Thiếu tên sản phẩm bắt buộc.', 'wp-product-crawler' ),
			);
		}

		$matched_id = 0;
		$reason     = '';

		// 1. Kiểm tra theo Source URL
		if ( ! empty( $product['source_url'] ) ) {
			$found = $wpdb->get_var( $wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_crawler_source_url' AND meta_value = %s LIMIT 1",
				$product['source_url']
			) );
			if ( $found ) {
				$matched_id = (int) $found;
				$reason     = sprintf( __( 'Trùng khớp URL nguồn (%s)', 'wp-product-crawler' ), $product['source_url'] );
			}
		}

		// 2. Kiểm tra theo SKU / Model
		if ( ! $matched_id && ( ! empty( $product['sku'] ) || ! empty( $product['model'] ) ) ) {
			$code = ! empty( $product['sku'] ) ? $product['sku'] : $product['model'];
			$found = $wpdb->get_var( $wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE (meta_key = 'mozlex_model' OR meta_key = '_sku') AND meta_value = %s LIMIT 1",
				$code
			) );
			if ( $found ) {
				$matched_id = (int) $found;
				$reason     = sprintf( __( 'Trùng khớp Model/SKU ("%s")', 'wp-product-crawler' ), $code );
			}
		}

		// 3. Kiểm tra theo Slug bài viết
		if ( ! $matched_id ) {
			$slug  = sanitize_title( $product['name'] );
			$found = $wpdb->get_var( $wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_name = %s LIMIT 1",
				$slug
			) );
			if ( $found ) {
				$matched_id = (int) $found;
				$reason     = sprintf( __( 'Trùng khớp Slug sản phẩm ("%s")', 'wp-product-crawler' ), $slug );
			}
		}

		// 4. Kiểm tra theo Tên chuẩn hóa (Normalized Product Name)
		if ( ! $matched_id ) {
			$norm_name = self::normalize_string( $product['name'] );
			$posts = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status IN ('publish', 'draft') LIMIT 300" );
			foreach ( $posts as $p ) {
				if ( self::normalize_string( $p->post_title ) === $norm_name ) {
					$matched_id = (int) $p->ID;
					$reason     = sprintf( __( 'Trùng tên sản phẩm với ID #%d ("%s")', 'wp-product-crawler' ), $matched_id, $p->post_title );
					break;
				}
			}
		}

		// Nếu không tìm thấy sản phẩm trùng
		if ( ! $matched_id ) {
			return array(
				'status'  => 'NEW',
				'post_id' => 0,
				'reason'  => __( 'Sản phẩm mới hoàn toàn', 'wp-product-crawler' ),
			);
		}

		// Nếu tìm thấy, kiểm tra xem có sự thay đổi về giá hoặc nội dung để xếp vào 'UPDATED' hay 'DUPLICATE'
		$existing_price = (float) get_post_meta( $matched_id, 'mozlex_price', true );
		$new_price      = isset( $product['price'] ) ? (float) $product['price'] : null;

		if ( null !== $new_price && abs( $existing_price - $new_price ) > 0.01 ) {
			return array(
				'status'  => 'UPDATED',
				'post_id' => $matched_id,
				'reason'  => $reason . sprintf( __( ' — Có thay đổi giá: Cũ: %s | Mới: %s', 'wp-product-crawler' ), number_format( $existing_price ), number_format( $new_price ) ),
			);
		}

		return array(
			'status'  => 'DUPLICATE',
			'post_id' => $matched_id,
			'reason'  => $reason,
		);
	}

	/**
	 * Chuẩn hóa chuỗi văn bản (chuyển chữ thường, bỏ dấu tiếng Việt, loại bỏ khoảng trắng thừa)
	 */
	public static function normalize_string( string $str ): string {
		$str = mb_strtolower( trim( $str ), 'UTF-8' );

		// Bảng chuyển đổi ký tự tiếng Việt có dấu
		$accents = array(
			'a' => '/[áàảãạăắằẳẵặâấầẩẫậ]/u',
			'd' => '/[đ]/u',
			'e' => '/[éèẻẽẹêếềểễệ]/u',
			'i' => '/[íìỉĩị]/u',
			'o' => '/[óòỏõọôốồổỗộơớờởỡợ]/u',
			'u' => '/[úùủũụưứừửữự]/u',
			'y' => '/[ýỳỷỹỵ]/u',
		);
		foreach ( $accents as $non_accent => $pattern ) {
			$str = preg_replace( $pattern, $non_accent, $str );
		}

		// Xóa ký tự đặc biệt, chỉ giữ chữ cái và số
		$str = preg_replace( '/[^a-z0-9]/', '', $str );

		return (string) $str;
	}
}
