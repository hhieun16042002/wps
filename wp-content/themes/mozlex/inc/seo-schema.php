<?php
/**
 * SEO fallback: title/meta/canonical/OG + JSON-LD.
 * Không ghi đè khi Rank Math / Yoast đang hoạt động (chúng tự can thiệp wp_head).
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Theme chỉ render meta khi không có plugin SEO chủ động. */
function mozlex_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' );
}

function mozlex_meta_description() {
	if ( is_singular( 'product' ) ) {
		$excerpt = get_the_excerpt();
		return $excerpt ?: wp_trim_words( wp_strip_all_tags( get_the_content() ), 30, '…' );
	}
	if ( is_page() || is_single() ) {
		$parts = explode( "\n", trim( wp_strip_all_tags( get_the_content() ) ) );
		return wp_trim_words( implode( ' ', $parts ), 30, '…' );
	}
	return __( 'Mozlex — khóa cao cấp cho không gian sống hiện đại. 13 năm kinh nghiệm phân phối khóa thông minh, khóa tay gạt tại thị trường Châu Âu, Mỹ.', 'mozlex' );
}

add_action( 'wp_head', function () {
	if ( mozlex_has_seo_plugin() ) {
		return;
	}

	$title = wp_get_document_title();
	$desc  = mozlex_meta_description();
	$url   = is_front_page() ? home_url('/') : ( is_singular() ? get_permalink() : '' );
	if ( is_tax() && get_queried_object() instanceof WP_Term ) {
		$url = get_term_link( get_queried_object() );
	}

	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( $url ) {
		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	}
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( is_singular( 'product' ) ? 'product' : 'website' ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	if ( $url ) {
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	}

	$thumb_id = is_singular() ? get_post_thumbnail_id() : (int) get_theme_mod( 'custom_logo' );
	if ( $thumb_id && ( $src = wp_get_attachment_image_url( $thumb_id, 'large' ) ) ) { // phpcs:ignore
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $src ) );
	}
}, 20 );

/**
 * Filtered archive URLs (?price=...) → noindex để tránh index query vô nghĩa.
 */
add_action( 'wp_head', function () {
	if ( is_archive() && ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only check.
		echo '<meta name="robots" content="noindex,follow">' . "\n";
	}
}, 1 );

/**
 * JSON-LD — dữ liệu thật từ catalogue, không giá/rating giả.
 */
add_action( 'wp_head', function () {
	$graph = array();

	$graph[] = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => 'MOZLEX',
		'url'   => home_url( '/' ),
	);

	if ( is_singular( 'product' ) ) {
		$post = get_queried_object();
		$item = array(
			'@type' => 'Product',
			'name'  => $post->post_title,
			'url'   => get_permalink( $post ),
			'sku'   => get_post_meta( $post->ID, 'mozlex_model', true ) ?: $post->post_title,
		);
		$brand = array( '@type' => 'Brand', 'name' => 'MOZLEX' );
		$item['brand'] = $brand;

		$image = get_the_post_thumbnail_url( $post, 'large' );
		if ( $image ) {
			$item['image'] = $image;
		}
		$desc = get_the_excerpt( $post );
		if ( $desc ) {
			$item['description'] = $desc;
		}
		$price = (float) get_post_meta( $post->ID, 'mozlex_price', true );
		// Chỉ khai báo offer khi có giá thật.
		if ( $price > 0 ) {
			$item['offers'] = array(
				'@type'         => 'Offer',
				'price'         => number_format( $price, 0, '', '' ),
				'priceCurrency' => 'VND',
				'url'           => get_permalink( $post ),
			);
		} else {
			unset( $item['offers'] ); // Không fake availability.
		}
		$graph[] = $item;
	}

	// BreadcrumbList trên mọi page chính.
	$crumbs = array();
	if ( is_singular( 'product' ) || is_tax( 'product_category' ) ) {
		$crumbs[] = array( 'name' => 'Trang chủ', 'url' => home_url( '/' ) );
		if ( is_singular( 'product' ) ) {
			$cats = wp_get_post_terms( get_the_ID(), 'product_category' );
			if ( $cats ) {
				$crumbs[] = array( 'name' => $cats[0]->name, 'url' => get_term_link( $cats[0] ) );
			}
			$crumbs[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
		} else {
			$crumbs[] = array( 'name' => single_term_title( '', false ), 'url' => '' );
		}
	}
	if ( count( $crumbs ) > 1 ) {
		$elements = array();
		foreach ( $crumbs as $i => $c ) {
			$el = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c['name'] );
			if ( $c['url'] ) {
				$el['item'] = $c['url'];
			}
			$elements[] = $el;
		}
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $elements,
		);
	}

	echo '<script type="application/ld+json">' .
		wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS
		) . '</script>' . "\n";
}, 30 );

/**
 * Rewrite chuẩn URL catalogue: /khoa-thong-minh/, /khoa-co/.
 * Term slug giữ nguyên trong DB, chỉ giao diện đường dẫn thay đổi.
 */
add_action( 'init', function () {
	add_rewrite_rule( '^khoa-thong-minh/?$', 'index.php?product_category=khoa-thong-minh', 'top' );
	add_rewrite_rule( '^khoa-co/?$', 'index.php?product_category=khoa-tay-gat', 'top' );
}, 10 );

add_filter( 'term_link', function ( $link, $term ) {
	if ( 'product_category' === $term->taxonomy ) {
		if ( 'khoa-thong-minh' === $term->slug ) {
			return home_url( '/khoa-thong-minh/' );
		}
		if ( 'khoa-tay-gat' === $term->slug ) {
			return home_url( '/khoa-co/' );
		}
	}
	return $link;
}, 10, 2 );
