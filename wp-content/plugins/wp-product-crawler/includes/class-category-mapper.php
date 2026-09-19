<?php
/**
 * Bộ ánh xạ danh mục sản phẩm nguồn sang danh mục cục bộ (product_category).
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Category_Mapper {

	/**
	 * Lấy danh sách toàn bộ các nhóm sản phẩm hiện có trong hệ thống.
	 *
	 * @return array Mảng các đối tượng term ['id' => int, 'name' => string, 'slug' => string, 'parent' => int]
	 */
	public static function get_local_categories(): array {
		$terms = get_terms( array(
			'taxonomy'   => 'product_category',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		$list = array();
		foreach ( $terms as $term ) {
			$list[] = array(
				'id'     => (int) $term->term_id,
				'name'   => $term->name,
				'slug'   => $term->slug,
				'parent' => (int) $term->parent,
			);
		}

		return $list;
	}

	/**
	 * Gợi ý hoặc đồng bộ danh mục nội bộ chính xác theo tên danh mục trên website nguồn.
	 *
	 * @param string $source_category Tên danh mục nguồn (VD: "CỬA THÉP VÂN GỖ 4 CÁNH KING BAC").
	 * @param bool   $auto_create Tự động tạo danh mục với tên chính xác nếu chưa tồn tại.
	 * @return array ['term_id' => int, 'term_name' => string, 'confidence' => float, 'is_exact' => bool]
	 */
	public static function suggest_mapping( string $source_category, bool $auto_create = true ): array {
		$source = WP_Product_Extractor::clean_category_name( $source_category );
		if ( empty( $source ) ) {
			return array(
				'term_id'    => 0,
				'term_name'  => '',
				'confidence' => 0.0,
				'is_exact'   => false,
			);
		}

		$local_cats = self::get_local_categories();

		// 1. Tìm kiếm khớp chính xác 100% (không phân biệt hoa thường và khoảng trắng thừa)
		$norm_source = WP_Duplicate_Detector::normalize_string( $source );
		foreach ( $local_cats as $cat ) {
			$norm_local = WP_Duplicate_Detector::normalize_string( $cat['name'] );
			if ( $norm_source === $norm_local ) {
				return array(
					'term_id'    => $cat['id'],
					'term_name'  => $cat['name'],
					'confidence' => 1.0,
					'is_exact'   => true,
				);
			}
		}

		// 2. Nếu chưa có danh mục khớp chính xác và bật auto_create:
		// Tự động tạo đúng tên danh mục như trên website nguồn theo yêu cầu người dùng
		if ( $auto_create ) {
			$created_id = self::create_local_category( $source );
			if ( ! is_wp_error( $created_id ) && $created_id > 0 ) {
				return array(
					'term_id'    => $created_id,
					'term_name'  => $source,
					'confidence' => 1.0,
					'is_exact'   => true,
				);
			}
		}

		// 3. Fallback: Nếu không tự tạo (hoặc tạo lỗi), tìm kiếm danh mục có độ tương đồng cao
		$best_match = null;
		$max_score  = 0.0;

		foreach ( $local_cats as $cat ) {
			$norm_local = WP_Duplicate_Detector::normalize_string( $cat['name'] );

			if ( str_contains( $norm_source, $norm_local ) || str_contains( $norm_local, $norm_source ) ) {
				$score = 0.85;
				if ( $score > $max_score ) {
					$max_score  = $score;
					$best_match = $cat;
				}
			}

			similar_text( $norm_source, $norm_local, $percent );
			$sim_score = $percent / 100.0;
			if ( $sim_score > $max_score ) {
				$max_score  = $sim_score;
				$best_match = $cat;
			}
		}

		if ( $best_match && $max_score >= 0.75 ) {
			return array(
				'term_id'    => $best_match['id'],
				'term_name'  => $best_match['name'],
				'confidence' => round( $max_score, 2 ),
				'is_exact'   => false,
			);
		}

		return array(
			'term_id'    => 0,
			'term_name'  => '',
			'confidence' => 0.0,
			'is_exact'   => false,
		);
	}

	/**
	 * Tạo mới một danh mục sản phẩm nếu chưa có (chuẩn hóa tên sạch UTF-8).
	 *
	 * @param string $cat_name Tên danh mục mới.
	 * @param int $parent_id ID danh mục cha (mặc định 0).
	 * @return int|WP_Error ID của term vừa tạo hoặc WP_Error.
	 */
	public static function create_local_category( string $cat_name, int $parent_id = 0 ): int|WP_Error {
		$name = WP_Product_Extractor::clean_category_name( $cat_name );
		if ( empty( $name ) ) {
			return new WP_Error( 'crawler_empty_cat_name', __( 'Tên danh mục không được để trống.', 'wp-product-crawler' ) );
		}

		$existing = get_term_by( 'name', $name, 'product_category' );
		if ( $existing ) {
			return (int) $existing->term_id;
		}

		$res = wp_insert_term( $name, 'product_category', array(
			'parent' => $parent_id,
			'slug'   => sanitize_title( $name ),
		) );

		if ( is_wp_error( $res ) ) {
			if ( isset( $res->error_data['term_exists'] ) ) {
				return (int) $res->error_data['term_exists'];
			}
			return $res;
		}

		return (int) $res['term_id'];
	}
}
