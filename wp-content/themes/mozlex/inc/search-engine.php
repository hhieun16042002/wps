<?php
/**
 * Advanced Product Search Engine — Technical SEO, Search Intent & Relevance Ranking.
 *
 * Handles Vietnamese normalization (accented/unaccented), model codes (KS19, KS 19),
 * taxonomy and meta/spec matching (Face ID, Inox 304, TTLock, Tuya), intent mapping,
 * and relevance-based ranking.
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove Vietnamese accents / diacritics reliably.
 * Supports both precomposed (NFC) and decomposed (NFD) characters.
 *
 * @param string $str Input string.
 * @return string Unaccented string.
 */
function mozlex_vn_unaccent( $str ) {
	if ( empty( $str ) ) {
		return '';
	}
	if ( class_exists( 'Normalizer' ) ) {
		$str = Normalizer::normalize( $str, Normalizer::FORM_C );
	}
	$dict = array(
		'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ẳ'=>'a','ẵ'=>'a','ặ'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
		'À'=>'A','Á'=>'A','Ả'=>'A','Ã'=>'A','Ạ'=>'A','Ă'=>'A','Ằ'=>'A','Ắ'=>'A','Ẳ'=>'A','Ẵ'=>'A','Ặ'=>'A','Â'=>'A','Ầ'=>'A','Ấ'=>'A','Ẩ'=>'A','Ẫ'=>'A','Ậ'=>'A',
		'è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
		'È'=>'E','É'=>'E','Ẻ'=>'E','Ẽ'=>'E','Ẹ'=>'E','Ê'=>'E','Ề'=>'E','Ế'=>'E','Ể'=>'E','Ễ'=>'E','Ệ'=>'E',
		'ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
		'Ì'=>'I','Í'=>'I','Ỉ'=>'I','Ĩ'=>'I','Ị'=>'I',
		'ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
		'Ò'=>'O','Ó'=>'O','Ỏ'=>'O','Õ'=>'O','Ọ'=>'O','Ô'=>'O','Ồ'=>'O','Ố'=>'O','Ổ'=>'O','Ỗ'=>'O','Ộ'=>'O','Ơ'=>'O','Ờ'=>'O','Ớ'=>'O','Ở'=>'O','Ỡ'=>'O','Ợ'=>'O',
		'ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u',
		'Ù'=>'U','Ú'=>'U','Ủ'=>'U','Ũ'=>'U','Ụ'=>'U','Ư'=>'U','Ừ'=>'U','Ứ'=>'U','Ử'=>'U','Ữ'=>'U','Ự'=>'U',
		'ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y',
		'Ỳ'=>'Y','Ý'=>'Y','Ỷ'=>'Y','Ỹ'=>'Y','Ỵ'=>'Y',
		'đ'=>'d','Đ'=>'D',
	);
	return strtr( $str, $dict );
}

/**
 * Standardize Vietnamese accent variations (old style vs new style: khoá <-> khóa).
 *
 * @param string $str Input string.
 * @return string Normalized string.
 */
function mozlex_normalize_vn_tones( $str ) {
	$pairs = array(
		'khoá' => 'khóa',
		'Khoá' => 'Khóa',
		'KHOÁ' => 'KHÓA',
		'hoà'  => 'hòa',
		'toàn' => 'toàn',
		'thuỷ' => 'thủy',
	);
	return strtr( $str, $pairs );
}

/**
 * Clean and normalize a search query.
 *
 * @param string $query Raw query.
 * @return string Clean query.
 */
function mozlex_clean_search_query( $query ) {
	$clean = wp_strip_all_tags( (string) $query );
	$clean = mozlex_normalize_vn_tones( $clean );
	$clean = preg_replace( '/\s+/', ' ', $clean );
	return trim( $clean );
}

/**
 * Expand search query with synonyms, intents, and SKU variations.
 *
 * @param string $query Clean search query.
 * @return array Array of search variants and tokens.
 */
function mozlex_expand_search_query( $query ) {
	$raw = trim( (string) $query );
	if ( '' === $raw ) {
		return array(
			'original'        => '',
			'clean'           => '',
			'unaccent'        => '',
			'phrases'         => array(),
			'tokens'          => array(),
			'tokens_unaccent' => array(),
			'intent_terms'    => array(),
			'is_smart_intent' => false,
		);
	}

	$clean    = mozlex_clean_search_query( $raw );
	$clean_lc = mb_strtolower( $clean, 'UTF-8' );
	$unaccent = mb_strtolower( mozlex_vn_unaccent( $clean ), 'UTF-8' );

	$phrases = array( $clean_lc );
	if ( $unaccent !== $clean_lc ) {
		$phrases[] = $unaccent;
	}

	// Model code variations: e.g. "KS 19" <-> "KS19", "F 7" <-> "F7", "KC 21" <-> "KC21", "A 16" <-> "A16".
	$model_tokens = array();
	$model_parts  = array();
	if ( preg_match_all( '/\b([a-z]{1,4})\s+([0-9]{1,4})\b/i', $clean_lc, $matches, PREG_SET_ORDER ) ) {
		foreach ( $matches as $m ) {
			$collapsed = strtolower( $m[1] . $m[2] );
			$model_tokens[] = $collapsed;
			$model_parts[]  = strtolower( $m[1] );
			$model_parts[]  = strtolower( $m[2] );
			if ( ! in_array( $collapsed, $phrases, true ) ) {
				$phrases[] = $collapsed;
			}
		}
	}
	if ( preg_match_all( '/\b([a-z]{1,4})([0-9]{1,4})\b/i', $clean_lc, $matches, PREG_SET_ORDER ) ) {
		foreach ( $matches as $m ) {
			$spaced = strtolower( $m[1] . ' ' . $m[2] );
			$model_tokens[] = strtolower( $m[0] );
			if ( ! in_array( $spaced, $phrases, true ) ) {
				$phrases[] = $spaced;
			}
		}
	}

	// Vietnamese compounds relevant to architectural locks & hardware.
	$compounds_map = array(
		'thông minh'          => array( 'thông minh', 'thong minh', 'khóa thông minh', 'smart lock' ),
		'thong minh'          => array( 'thông minh', 'thong minh', 'khóa thông minh', 'smart lock' ),
		'điện tử'             => array( 'điện tử', 'dien tu', 'thông minh', 'smart lock' ),
		'dien tu'             => array( 'điện tử', 'dien tu', 'thông minh', 'smart lock' ),
		'smart lock'          => array( 'thông minh', 'thong minh', 'smart lock' ),
		'vân tay'             => array( 'vân tay', 'van tay', 'fingerprint' ),
		'van tay'             => array( 'vân tay', 'van tay', 'fingerprint' ),
		'fingerprint'         => array( 'vân tay', 'van tay' ),
		'face id'             => array( 'face id', 'faceid', 'nhận diện khuôn mặt' ),
		'faceid'              => array( 'face id', 'faceid', 'nhận diện khuôn mặt' ),
		'nhận diện khuôn mặt' => array( 'face id', 'faceid' ),
		'nhan dien khuon mat' => array( 'face id', 'faceid' ),
		'thông phòng'         => array( 'thông phòng', 'thong phong', 'khóa cửa thông phòng' ),
		'thong phong'         => array( 'thông phòng', 'thong phong', 'khóa cửa thông phòng' ),
		'tay gạt'             => array( 'tay gạt', 'tay gat', 'khóa tay gạt' ),
		'tay gat'             => array( 'tay gạt', 'tay gat', 'khóa tay gạt' ),
		'inox 304'            => array( 'inox 304', 'inox', 'thép không gỉ' ),
		'inox'                => array( 'inox 304', 'inox' ),
		'thép không gỉ'       => array( 'inox 304', 'thép không gỉ' ),
		'thep khong gi'       => array( 'inox 304', 'thep khong gi' ),
		'khóa cơ'             => array( 'tay gạt', 'tay gat', 'khóa tay gạt' ),
		'khoa co'             => array( 'tay gat', 'tay gạt', 'khoa tay gat' ),
		'khóa phòng'          => array( 'thông phòng', 'thong phong', 'khóa cửa thông phòng' ),
		'khoa phong'          => array( 'thong phong', 'thông phòng', 'khoa cua thong phong' ),
		'khóa cửa phòng'      => array( 'thông phòng', 'thong phong', 'khóa cửa thông phòng' ),
		'khoa cua phong'      => array( 'thong phong', 'thông phòng', 'khoa cua thong phong' ),
		'khóa chính'          => array( 'thông minh', 'thong minh', 'khóa thông minh' ),
		'khoa chinh'          => array( 'thông minh', 'thong minh', 'khóa thông minh' ),
		'khóa cửa chính'      => array( 'thông minh', 'thong minh', 'khóa thông minh' ),
		'khoa cua chinh'      => array( 'thong minh', 'thông minh', 'khóa thông minh' ),
		'chống cháy'          => array( 'chống cháy', 'chong chay', 'cửa chống cháy' ),
		'chong chay'          => array( 'chống cháy', 'chong chay', 'cua chong chay' ),
		'thân khóa'           => array( 'thân khóa', 'than khoa' ),
		'than khoa'           => array( 'thân khóa', 'than khoa' ),
	);

	$intent_terms = array();
	$matched_compounds = array();
	foreach ( $compounds_map as $needle => $targets ) {
		if ( false !== strpos( $clean_lc, $needle ) || false !== strpos( $unaccent, $needle ) ) {
			$matched_compounds[] = $needle;
			foreach ( $targets as $t ) {
				$intent_terms[] = $t;
			}
		}
	}

	// Detect if query specifies smart lock intent.
	$is_smart_intent = (
		false !== strpos( $clean_lc, 'thông minh' ) ||
		false !== strpos( $unaccent, 'thong minh' ) ||
		false !== strpos( $clean_lc, 'điện tử' ) ||
		false !== strpos( $unaccent, 'dien tu' ) ||
		false !== strpos( $clean_lc, 'smart' ) ||
		false !== strpos( $clean_lc, 'faceid' ) ||
		false !== strpos( $clean_lc, 'face id' )
	);

	// Detect tokens (split words).
	// Strip conversational stop words if present in Vietnamese.
	$stop_words = array( 'nào', 'tốt', 'nao', 'tot', 'mua', 'ở', 'đâu', 'o', 'dau', 'giá', 'gia', 'rẻ', 're', 'báo', 'bao', 'cho', 'va', 'và', 'la', 'là' );
	$generic_domain_words = array( 'khóa', 'khoa', 'khoá', 'cửa', 'cua', 'loại', 'loai', 'mẫu', 'mau', 'bộ', 'bo', 'sản phẩm', 'san pham' );

	$words = preg_split( '/[\s,\-\+]+/', $clean_lc );
	$compound_syllables = array();
	foreach ( $matched_compounds as $mc ) {
		foreach ( preg_split( '/\s+/', $mc ) as $syllable ) {
			$syllable = trim( $syllable );
			if ( $syllable ) {
				$compound_syllables[] = $syllable;
				$compound_syllables[] = mozlex_vn_unaccent( $syllable );
			}
		}
	}
	$compound_syllables = array_unique( $compound_syllables );

	$tokens          = array();
	$tokens_unaccent = array();
	$specific_tokens = array();

	foreach ( $words as $w ) {
		$w = trim( $w );
		if ( mb_strlen( $w, 'UTF-8' ) >= 2 && ! in_array( $w, $stop_words, true ) ) {
			$tokens[] = $w;
			$unacc_w  = mozlex_vn_unaccent( $w );
			$tokens_unaccent[] = $unacc_w;
			if (
				! in_array( $w, $generic_domain_words, true ) &&
				! in_array( $unacc_w, $generic_domain_words, true ) &&
				! in_array( $w, $compound_syllables, true ) &&
				! in_array( $unacc_w, $compound_syllables, true ) &&
				! in_array( $w, $model_parts, true ) &&
				! in_array( $unacc_w, $model_parts, true )
			) {
				$specific_tokens[] = $w;
				if ( $unacc_w !== $w ) {
					$specific_tokens[] = $unacc_w;
				}
			}
		}
	}

	// Calculate specific terms list: model tokens, phrases, intent terms, compounds, specific tokens.
	$specific_terms = array_unique( array_filter( array_merge(
		$model_tokens,
		$intent_terms,
		$specific_tokens
	) ) );

	return array(
		'original'        => $raw,
		'clean'           => $clean,
		'unaccent'        => $unaccent,
		'phrases'         => array_unique( $phrases ),
		'tokens'          => array_unique( $tokens ),
		'tokens_unaccent' => array_unique( $tokens_unaccent ),
		'intent_terms'    => array_unique( $intent_terms ),
		'specific_terms'  => $specific_terms,
		'is_smart_intent' => $is_smart_intent,
	);
}

/**
 * Filter the search WHERE clause for product queries.
 * Replaces default post_title/post_content search with multi-field matching.
 *
 * @param string   $search Existing search WHERE clause.
 * @param WP_Query $query  The current WP_Query object.
 * @return string Modified search SQL.
 */
function mozlex_filter_product_posts_search( $search, $query ) {
	if ( is_admin() || ! $query->is_search() || empty( $query->get( 's' ) ) ) {
		return $search;
	}

	$post_type = $query->get( 'post_type' );
	if ( ! empty( $post_type ) && 'product' !== $post_type && ( is_array( $post_type ) && ! in_array( 'product', $post_type, true ) ) ) {
		return $search;
	}

	global $wpdb;
	$expanded = mozlex_expand_search_query( $query->get( 's' ) );
	if ( empty( $expanded['clean'] ) ) {
		return $search;
	}

	// If query contains specific modifiers (features, model, intent terms), use them.
	// Only fall back to generic words ("khóa", "cửa") when no specific terms were supplied.
	if ( ! empty( $expanded['specific_terms'] ) ) {
		$search_terms = array_unique( array_filter( array_merge(
			$expanded['phrases'],
			$expanded['specific_terms']
		) ) );
	} else {
		$search_terms = array_unique( array_filter( array_merge(
			$expanded['phrases'],
			$expanded['tokens'],
			$expanded['tokens_unaccent']
		) ) );
	}

	$or_conds = array();
	foreach ( $search_terms as $term ) {
		if ( mb_strlen( $term, 'UTF-8' ) < 2 ) {
			continue;
		}
		$like = '%' . $wpdb->esc_like( $term ) . '%';
		$or_conds[] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $like );
		$or_conds[] = $wpdb->prepare( "{$wpdb->posts}.post_content LIKE %s", $like );
		$or_conds[] = $wpdb->prepare(
			"EXISTS (
				SELECT 1 FROM {$wpdb->postmeta} pm
				WHERE pm.post_id = {$wpdb->posts}.ID
				AND pm.meta_key IN ('mozlex_model','mozlex_specs','mozlex_material','mozlex_color')
				AND pm.meta_value LIKE %s
			)",
			$like
		);
		$or_conds[] = $wpdb->prepare(
			"EXISTS (
				SELECT 1 FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
				WHERE tr.object_id = {$wpdb->posts}.ID
				AND (t.name LIKE %s OR t.slug LIKE %s)
			)",
			$like,
			$like
		);
	}

	if ( empty( $or_conds ) ) {
		return $search;
	}

	return ' AND (' . implode( ' OR ', $or_conds ) . ') ';
}
add_filter( 'posts_search', 'mozlex_filter_product_posts_search', 20, 2 );

/**
 * Filter the search ORDERBY clause to sort by relevance score.
 *
 * @param array    $clauses Existing SQL clauses.
 * @param WP_Query $query   The current WP_Query object.
 * @return array Modified SQL clauses.
 */
function mozlex_filter_product_search_orderby( $clauses, $query ) {
	if ( is_admin() || ! $query->is_search() || empty( $query->get( 's' ) ) ) {
		return $clauses;
	}

	$post_type = $query->get( 'post_type' );
	if ( ! empty( $post_type ) && 'product' !== $post_type && ( is_array( $post_type ) && ! in_array( 'product', $post_type, true ) ) ) {
		return $clauses;
	}

	global $wpdb;
	$expanded = mozlex_expand_search_query( $query->get( 's' ) );
	$all_terms = array_unique( array_filter( array_merge( $expanded['phrases'], $expanded['intent_terms'] ) ) );

	$cases = array();
	foreach ( $all_terms as $ph ) {
		$ph_like = '%' . $wpdb->esc_like( $ph ) . '%';
		// Exact title match: 100
		$cases[] = $wpdb->prepare( "WHEN LOWER({$wpdb->posts}.post_title) = LOWER(%s) THEN 100", $ph );
		// Title starts with phrase: 85
		$cases[] = $wpdb->prepare( "WHEN LOWER({$wpdb->posts}.post_title) LIKE %s THEN 85", $wpdb->esc_like( $ph ) . '%' );
		// Title contains full phrase: 75
		$cases[] = $wpdb->prepare( "WHEN LOWER({$wpdb->posts}.post_title) LIKE %s THEN 75", $ph_like );
		// Model / SKU exact match: 70
		$cases[] = $wpdb->prepare(
			"WHEN EXISTS (
				SELECT 1 FROM {$wpdb->postmeta} pm
				WHERE pm.post_id = {$wpdb->posts}.ID
				AND pm.meta_key = 'mozlex_model'
				AND (LOWER(pm.meta_value) = LOWER(%s) OR LOWER(pm.meta_value) LIKE %s)
			) THEN 70",
			$ph,
			$ph_like
		);
		// Meta specs match (Face ID, Inox 304, TUYA, SKU in specs): 60
		$cases[] = $wpdb->prepare(
			"WHEN EXISTS (
				SELECT 1 FROM {$wpdb->postmeta} pm
				WHERE pm.post_id = {$wpdb->posts}.ID
				AND pm.meta_key = 'mozlex_specs'
				AND pm.meta_value LIKE %s
			) THEN 60",
			$ph_like
		);
		// Category match: 45
		$cases[] = $wpdb->prepare(
			"WHEN EXISTS (
				SELECT 1 FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
				WHERE tr.object_id = {$wpdb->posts}.ID
				AND tt.taxonomy = 'product_category'
				AND (t.name LIKE %s OR t.slug LIKE %s)
			) THEN 45",
			$ph_like,
			$ph_like
		);
		// Feature / Unlock method match: 40
		$cases[] = $wpdb->prepare(
			"WHEN EXISTS (
				SELECT 1 FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
				WHERE tr.object_id = {$wpdb->posts}.ID
				AND tt.taxonomy IN ('feature', 'unlock_method')
				AND (t.name LIKE %s OR t.slug LIKE %s)
			) THEN 40",
			$ph_like,
			$ph_like
		);
	}

	// Token match in title (all tokens present): 30
	if ( count( $expanded['tokens'] ) > 1 ) {
		$tok_conds = array();
		foreach ( $expanded['tokens'] as $tok ) {
			$tok_conds[] = $wpdb->prepare( "LOWER({$wpdb->posts}.post_title) LIKE %s", '%' . $wpdb->esc_like( $tok ) . '%' );
		}
		$cases[] = 'WHEN (' . implode( ' AND ', $tok_conds ) . ') THEN 30';
	}

	// Intent penalty: if query specifically asked for "thông minh" / smart lock, penalize handle locks.
	$penalty = '';
	if ( $expanded['is_smart_intent'] ) {
		$penalty = " - (CASE WHEN NOT (LOWER({$wpdb->posts}.post_title) LIKE '%thông minh%' OR LOWER({$wpdb->posts}.post_title) LIKE '%thong minh%') THEN 50 ELSE 0 END)";
	}

	$clauses['orderby'] = " (CASE " . implode( ' ', $cases ) . " ELSE 10 END)" . $penalty . " DESC, {$wpdb->posts}.post_date DESC";

	return $clauses;
}
add_filter( 'posts_clauses', 'mozlex_filter_product_search_orderby', 20, 2 );

/**
 * Ensure pre_get_posts on search defaults to products and published only.
 *
 * @param WP_Query $query Main query.
 */
add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$query->set( 'post_type', 'product' );
		$query->set( 'post_status', 'publish' );
		if ( ! $query->get( 'posts_per_page' ) ) {
			$query->set( 'posts_per_page', 16 );
		}
	}
} );

/**
 * Search matching categories based on query.
 *
 * @param string $query Clean query string.
 * @param int    $limit Max categories to return.
 * @return array Matching categories list.
 */
function mozlex_search_categories( $query, $limit = 4 ) {
	$expanded = mozlex_expand_search_query( $query );
	if ( empty( $expanded['clean'] ) ) {
		return array();
	}

	global $wpdb;
	$clean_like    = '%' . $wpdb->esc_like( $expanded['clean'] ) . '%';
	$unaccent_like = '%' . $wpdb->esc_like( $expanded['unaccent'] ) . '%';

	$sql = $wpdb->prepare(
		"SELECT t.term_id, t.name, t.slug, tt.count,
			(CASE
				WHEN LOWER(t.name) = LOWER(%s) THEN 100
				WHEN LOWER(t.name) LIKE %s THEN 70
				WHEN LOWER(t.name) LIKE %s THEN 50
				WHEN LOWER(t.slug) LIKE %s THEN 40
				ELSE 10
			END) as score
		FROM {$wpdb->terms} t
		INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
		WHERE tt.taxonomy = 'product_category'
		AND (t.name LIKE %s OR t.name LIKE %s OR t.slug LIKE %s)
		ORDER BY score DESC, tt.count DESC
		LIMIT %d",
		$expanded['clean'],
		$clean_like,
		$unaccent_like,
		$clean_like,
		$clean_like,
		$unaccent_like,
		$clean_like,
		$limit
	);

	$results = $wpdb->get_results( $sql );
	$out = array();
	if ( $results ) {
		foreach ( $results as $r ) {
			$term_obj = get_term( $r->term_id, 'product_category' );
			$url      = ( $term_obj && ! is_wp_error( $term_obj ) ) ? get_term_link( $term_obj ) : home_url( '/nhom-san-pham/' . $r->slug . '/' );
			$out[]    = array(
				'id'    => (int) $r->term_id,
				'name'  => $r->name,
				'slug'  => $r->slug,
				'count' => (int) $r->count,
				'url'   => $url,
			);
		}
	}
	return $out;
}

/**
 * Return real, curated search suggestions from website data.
 *
 * @return array Suggestion items.
 */
function mozlex_get_popular_search_hints() {
	return array(
		array( 'label' => 'Khóa thông minh A16', 'term' => 'A16' ),
		array( 'label' => 'Khóa tay gạt KS19', 'term' => 'KS19' ),
		array( 'label' => 'Face ID', 'term' => 'Face ID' ),
		array( 'label' => 'Khóa vân tay', 'term' => 'Vân tay' ),
		array( 'label' => 'Lõi Inox 304', 'term' => 'Inox 304' ),
		array( 'label' => 'Khóa cửa thông phòng', 'term' => 'Khóa cửa thông phòng' ),
	);
}
