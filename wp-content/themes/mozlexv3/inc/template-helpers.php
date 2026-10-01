<?php
/**
 * Template helpers dùng chung cho mọi component.
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Giá trị từ theme options (mặc định rỗng, quản lý từ Admin → Mozlex).
 */
function mozlex_opt( $key, $default = '' ) {
	$opts = get_option( 'mozlex_options', array() );
	return isset( $opts[ $key ] ) && '' !== $opts[ $key ] ? $opts[ $key ] : $default;
}

/**
 * Định dạng giá catalogue: 9.800.000đ — không tự bịa khi thiếu.
 */
function mozlex_price_text( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$price   = (float) get_post_meta( $post_id, 'mozlex_price', true );
	if ( $price > 0 ) {
		return number_format( $price, 0, ',', '.' ) . 'đ';
	}
	$status = get_post_meta( $post_id, 'mozlex_price_status', true );
	if ( 'an-gia' === $status ) {
		return __( 'Liên hệ tư vấn', 'mozlex' );
	}
	return '';
}

/**
 * Các tính năng thực sự gán cho model (mảng term names).
 */
function mozlex_product_features( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$terms   = wp_get_post_terms( $post_id, 'feature', array( 'orderby' => 'term_order' ) );
	return wp_list_pluck( $terms, 'name' );
}

/**
 * Dòng thông số kỹ thuật từ meta (các cặp label|value).
 * Trả về mảng [label, value] đã lọc rỗng.
 */
function mozlex_technical_specs( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$raw     = get_post_meta( $post_id, 'mozlex_tech_specs', true );

	// Dữ liệu chuẩn hoá từ importer: store serialized array.
	if ( is_array( $raw ) ) {
		return array_values( array_filter( $raw ) );
	}

	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		if ( false === strpos( $line, '|' ) ) {
			continue;
		}
		list( $label, $value ) = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( $label && $value ) {
			$rows[] = array( $label, $value );
		}
	}
	return $rows;
}

/**
 * Breadcrumb đơn giản đúng cấu trúc thư mục catalogue.
 */
function mozlex_breadcrumb() {
	$items = array( array( 'label' => __( 'Trang chủ', 'mozlex' ), 'url' => home_url( '/' ) ) );

	if ( is_singular( 'product' ) ) {
		$cats = wp_get_post_terms( get_the_ID(), 'product_category' );
		if ( $cats ) {
			$cat     = $cats[0];
			$items[] = array( 'label' => $cat->name, 'url' => get_term_link( $cat ) );
		} else {
			$items[] = array( 'label' => __( 'Sản phẩm', 'mozlex' ), 'url' => get_post_type_archive_link( 'product' ) );
		}
		$items[] = array( 'label' => get_the_title() );
	} elseif ( is_tax( 'product_category' ) ) {
		$items[] = array( 'label' => single_term_title( '', false ) );
	} elseif ( is_page() && ! is_front_page() ) {
		$items[] = array( 'label' => get_the_title() );
	}

	echo '<nav class="breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'mozlex' ) . '"><ol>';
	$total = count( $items );
	foreach ( $items as $i => $item ) {
		$last    = ( $i + 1 ) === $total;
		$has_url = ! empty( $item['url'] );
		if ( ! $has_url || $last ) {
			echo '<li aria-current="page">' . esc_html( $item['label'] ) . '</li>';
		} else {
			echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
		}
	}
	echo '</ol></nav>';
}

/**
 * Section header component dùng lại giữa các page.
 */
function mozlex_section_header( $eyebrow, $title, $intro = '' ) {
	echo '<header class="section-header">';
	if ( $eyebrow ) {
		echo '<p class="eyebrow">' . esc_html( $eyebrow ) . '</p>';
	}
	echo '<h2 class="section-title">' . esc_html( $title ) . '</h2>';
	if ( $intro ) {
		echo '<p class="section-intro">' . esc_html( $intro ) . '</p>';
	}
	echo '</header>';
}

/**
 * Logo: custom logo nếu có, fallback wordmark text.
 */
function mozlex_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	echo '<span class="wordmark">MOZLEX</span>';
}

/**
 * So sánh: các model người dùng chọn (query param ?compare=a16,gf300 tối đa 3).
 */
function mozlex_compare_slugs() {
	$raw = isset( $_GET['compare'] ) ? sanitize_text_field( wp_unslash( $_GET['compare'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter UI.
	$raw = preg_replace( '/[^a-z0-9\-,]/', '', strtolower( $raw ) );
	$arr = array_filter( explode( ',', $raw ) );
	return array_slice( $arr, 0, 3 );
}

/**
 * Chuẩn hoá slug model → filename ảnh dự phòng (dùng khi chưa gắn featured image).
 */
function mozlex_model_slug() {
	$model = get_post_meta( get_the_ID(), 'mozlex_model', true );
	if ( ! $model ) {
		$model = get_the_title();
	}
	return strtolower( str_replace( array( ' ', '.' ), '-', preg_replace( '/[^a-z0-9 \.]/i', '', $model ) ) );
}
