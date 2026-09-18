<?php
/**
 * REST route: /wp-json/mozlex/v1/products
 * - ?s=term        → autocomplete (model, tính năng, app)
 * - ?compare=a,b,c → dữ liệu bảng so sánh
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'mozlex/v1', '/products', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => 'mozlex_rest_products',
		'args'                => array(
			's'                => array( 'sanitize_callback' => 'sanitize_text_field' ),
			'compare'          => array( 'sanitize_callback' => 'sanitize_text_field' ),
			'product_category' => array( 'sanitize_callback' => 'sanitize_text_field' ),
			'unlock_method'    => array( 'sanitize_callback' => 'sanitize_text_field' ),
			'application'      => array( 'sanitize_callback' => 'sanitize_text_field' ),
			'price_range'      => array( 'sanitize_callback' => 'sanitize_text_field' ),
			'color'            => array( 'sanitize_callback' => 'sanitize_text_field' ),
			'feature'          => array( 'sanitize_callback' => 'sanitize_text_field' ),
			'sort'             => array( 'sanitize_callback' => 'sanitize_text_field' ),
			'page'             => array( 'sanitize_callback' => 'absint' ),
			'per_page'         => array( 'sanitize_callback' => 'absint' ),
		),
	) );
} );

function mozlex_rest_products( WP_REST_Request $request ) {
	$s       = mb_substr( trim( (string) $request->get_param( 's' ) ), 0, 100 );
	$compare = mb_substr( trim( (string) $request->get_param( 'compare' ) ), 0, 100 );
	$has_filter = false;
	foreach ( array( 'product_category', 'unlock_method', 'application', 'price_range', 'color', 'feature' ) as $tax ) {
		if ( trim( (string) $request->get_param( $tax ) ) !== '' ) { $has_filter = true; break; }
	}

	if ( '' === $s && '' === $compare && ! $has_filter ) {
		// Khi không có filter (bỏ chọn hết) thì trả về tất cả sản phẩm như trang /san-pham/ ban đầu, không trả rỗng
		$has_filter = true;
	}

	$per_page = (int) $request->get_param( 'per_page' );
	$per_page = $per_page ? min( 48, max( 1, $per_page ) ) : 16;
	$page     = (int) $request->get_param( 'page' );
	$page     = $page ? max( 1, $page ) : 1;

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'no_found_rows'  => false,
	);

	if ( $s && ! $compare ) {
		$feature_ids = get_terms( array(
			'taxonomy'   => array( 'feature', 'unlock_method' ),
			'hide_empty' => true,
			'search'     => $s,
			'fields'     => 'ids',
		) );
		$args['s'] = $s;
		if ( ! is_wp_error( $feature_ids ) && $feature_ids ) {
			$args['_moz_feature_ids'] = $feature_ids;
		}
	}

	if ( $compare ) {
		$slugs = array_slice( preg_split( '/[,\s]+/', $compare ), 0, 3 );
		$args['post_name__in'] = array_filter( array_map( 'sanitize_title', $slugs ) );
		unset( $args['s'] );
	}

	// Taxonomy filters (chips/sidebar/search).
	$tax_query = array();
	foreach ( array( 'product_category', 'unlock_method', 'application', 'price_range', 'color', 'feature' ) as $tax ) {
		$val = trim( (string) $request->get_param( $tax ) );
		if ( '' === $val ) continue;
		// Support multi values comma-separated.
		$vals = array_filter( array_map( 'sanitize_title', explode( ',', $val ) ) );
		if ( ! $vals ) continue;
		// Validate at least one exists.
		$valid = array();
		foreach ( $vals as $v ) { if ( term_exists( $v, $tax ) ) $valid[] = $v; }
		if ( ! $valid ) continue;
		$tax_query[] = array( 'taxonomy' => $tax, 'field' => 'slug', 'terms' => $valid, 'operator' => 'IN' );
		$has_filter = true;
	}
	if ( count( $tax_query ) > 1 ) $tax_query['relation'] = 'AND';
	if ( $tax_query ) $args['tax_query'] = $tax_query;

	// Sort.
	$sort = sanitize_key( (string) $request->get_param( 'sort' ) );
	if ( 'price-asc' === $sort ) { $args['meta_key'] = 'mozlex_price'; $args['meta_type'] = 'NUMERIC'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'ASC'; }
	elseif ( 'price-desc' === $sort ) { $args['meta_key'] = 'mozlex_price'; $args['meta_type'] = 'NUMERIC'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
	elseif ( 'name-az' === $sort ) { $args['orderby'] = array( 'title' => 'ASC' ); $args['order'] = 'ASC'; }

	if ( ! empty( $args['_moz_feature_ids'] ) ) {
		add_filter( 'posts_clauses', 'mozlex_rest_or_tax_search', 10, 2 );
	}
	$query = new WP_Query( $args );
	remove_filter( 'posts_clauses', 'mozlex_rest_or_tax_search', 10, 2 );

	$out = array();
	foreach ( $query->posts as $post ) {
		$cat_terms = wp_get_post_terms( $post->ID, 'product_category' );
		$cat_name  = ( $cat_terms && ! is_wp_error( $cat_terms ) ) ? $cat_terms[0]->name : '';
		$cat_slug  = ( $cat_terms && ! is_wp_error( $cat_terms ) ) ? $cat_terms[0]->slug : '';
		$thumb     = get_the_post_thumbnail_url( $post->ID, 'medium' );
		$thumb_sm  = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
		$out[] = array(
			'id'         => $post->ID,
			'model'      => get_post_meta( $post->ID, 'mozlex_model', true ) ?: $post->post_title,
			'title'      => $post->post_title,
			'url'        => get_permalink( $post ),
			'price_text' => mozlex_price_text( $post->ID ),
			'price'      => (float) get_post_meta( $post->ID, 'mozlex_price', true ),
			'features'   => wp_list_pluck( wp_get_post_terms( $post->ID, 'feature' ), 'slug' ),
			'thumb'      => $thumb_sm ?: '',
			'thumb_medium' => $thumb ?: $thumb_sm ?: '',
			'category'   => $cat_name,
			'category_slug' => $cat_slug,
		);
	}
	// For filter AJAX we also return total to update count.
	if ( $has_filter ) {
		return rest_ensure_response( array( 'items' => $out, 'total' => (int) $query->found_posts, 'total_pages' => (int) $query->max_num_pages ) );
	}
	return rest_ensure_response( $out );
}

/**
 * Mở rộng search: OR với taxonomy feature/unlock_method ids.
 */
function mozlex_rest_or_tax_search( $clauses, $q ) {
	$ids = $q->get( '_moz_feature_ids' );
	if ( ! $ids || empty( $ids[0] ) ) {
		return $clauses;
	}
	global $wpdb;
	$in        = implode( ',', array_map( 'absint', $ids ) );
	$tax_where = "OR {$wpdb->posts}.ID IN (
		SELECT tr.object_id FROM {$wpdb->term_relationships} tr
		INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy IN ('feature','unlock_method')
		WHERE tt.term_id IN ({$in})
	)";
	$clauses['where'] = preg_replace_callback(
		"/\((?:{$wpdb->posts}\.post_title LIKE[^)]+)\)/",
		function ( $m ) use ( $tax_where ) {
			return '((' . substr( $m[0], 1, -1 ) . ')' . $tax_where . ')';
		},
		$clauses['where'],
		1
	);
	return $clauses;
}
