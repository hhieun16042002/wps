<?php
/**
 * Archive sản phẩm — filter SEO-safe + AJAX + chips + sidebar + compare.
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header();

global $wp_query;
$is_category = is_tax( 'product_category' );

$title = $is_category ? single_term_title( '', false ) : __( 'Tất cả sản phẩm', 'mozlex' );
$intro = ( $is_category && term_description() ) ? wp_strip_all_tags( term_description() ) : '';

/**
 * Filter: chấp nhận slug tồn tại thật, hỗ trợ multi-value comma-separated.
 */
$filter_terms  = array( 'product_category', 'unlock_method', 'application', 'color', 'feature' );
$query_filters = array();
foreach ( $filter_terms as $tax ) {
	$raw = isset( $_GET[ $tax ] ) ? sanitize_text_field( wp_unslash( $_GET[ $tax ] ) ) : '';
	if ( '' === $raw ) continue;
	$vals = array_filter( array_map( 'sanitize_title', explode( ',', $raw ) ) );
	$valid = array();
	foreach ( $vals as $v ) { if ( term_exists( $v, $tax ) ) $valid[] = $v; }
	if ( $valid ) {
		$query_filters[] = array(
			'taxonomy' => $tax,
			'field'    => 'slug',
			'terms'    => $valid,
			'operator' => 'IN',
		);
	}
}

// Sort whitelist.
	$order   = 'DESC';
	$orderby = array( 'date' => 'DESC' );
	$sort    = isset( $_GET['sort'] ) ? sanitize_key( $_GET['sort'] ) : '';
	switch ( $sort ) {
		case 'name-az':
			$orderby = array( 'title' => 'ASC', 'date' => 'DESC' );
			break;
	}

if ( $is_category ) {
	$queried    = get_queried_object();
	$query_filters[] = array(
		'taxonomy' => 'product_category',
		'field'    => 'slug',
		'terms'    => $queried->slug,
	);
}
if ( count( $query_filters ) > 1 ) {
	$query_filters['relation'] = 'AND';
}

	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

	$wp_query = new WP_Query( array(
		'post_type'      => 'product',
		'posts_per_page' => 16,
		'paged'          => $paged,
		'post_status'    => 'publish',
		'tax_query'      => $query_filters ?: null,
		'orderby'        => $orderby,
		'order'          => $order,
	) );

$current_sort = $sort ?: 'newest';

// Helper to check if filter value active (supports multi).
function mozlex_is_filter_active( $tax, $slug ) {
	$raw = isset( $_GET[ $tax ] ) ? sanitize_text_field( wp_unslash( $_GET[ $tax ] ) ) : '';
	$vals = $raw ? array_map( 'sanitize_title', explode( ',', $raw ) ) : array();
	return in_array( $slug, $vals, true );
}
?>

<section class="archive-head">
	<div class="shell">
		<?php mozlex_breadcrumb(); ?>
		<h1 class="page-title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro ) : ?>
		<p class="section-intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
// Hiển thị danh mục con khi xem danh mục lớn — click vào danh mục lớn sổ danh mục con (như Sơn → Sơn đỏ/đen)
if ( $is_category ) {
	$term = get_queried_object();
	$children = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => false, 'parent' => (int) $term->term_id, 'orderby' => 'name', 'order' => 'ASC' ) );
	if ( $children && ! is_wp_error( $children ) && count( $children ) ) {
		echo '<section class="shell" style="margin-top:18px;"><div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;"><h2 style="margin:0; font-size:1rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--dark);">Danh mục con của ' . esc_html( $term->name ) . '</h2></div><ul style="display:flex; flex-wrap:wrap; gap:10px; list-style:none; margin:0; padding:0;">';
		foreach ( $children as $child ) {
			echo '<li><a href="' . esc_url( get_term_link( $child ) ) . '" style="display:inline-block; padding:8px 14px; border:1px solid rgba(0,0,0,0.1); border-radius:999px; background:#fff; color:var(--dark); text-decoration:none; font-size:0.86rem;">' . esc_html( $child->name ) . '</a></li>';
		}
		echo '</ul></section>';
	}
}
// Khi ở trang /san-pham/ (tất cả), hiện lưới danh mục lớn để dễ xem
if ( ! $is_category ) {
	$top_cats = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => false, 'parent' => 0, 'orderby' => 'name', 'number' => 12 ) );
	if ( $top_cats && ! is_wp_error( $top_cats ) && count( $top_cats ) > 1 ) {
		// Đưa Thiết bị khác xuống cuối, giữ thứ tự Công nghệ → Thương mại → Xây dựng → Thiết bị khác như yêu cầu
		usort( $top_cats, function( $a, $b ){
			if ( $a->slug === 'thiet-bi-khac' && $b->slug !== 'thiet-bi-khac' ) return 1;
			if ( $b->slug === 'thiet-bi-khac' && $a->slug !== 'thiet-bi-khac' ) return -1;
			// Giữ thứ tự ưu tiên Công nghệ, Thương mại, Xây dựng
			$order = array( 'cong-nghe'=>1, 'thuong-mai'=>2, 'xay-dung'=>3 );
			$oa = $order[$a->slug] ?? 99; $ob = $order[$b->slug] ?? 99;
			if ( $oa !== $ob ) return $oa <=> $ob;
			return strcmp( $a->name, $b->name );
		});
		$active_cat_param = isset( $_GET['product_category'] ) ? sanitize_title( (string) $_GET['product_category'] ) : '';
		echo '<section class="shell cat-nav-section" style="margin-top:20px;"><div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;"><h2 style="margin:0; font-size:0.95rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--dark);">Xem theo danh mục</h2></div><ul class="cat-nav-list">';
		$is_all_active = empty($active_cat_param);
		echo '<li><a href="' . esc_url( home_url( '/san-pham/' ) ) . '" class="cat-nav-pill' . ( $is_all_active ? ' is-active' : '' ) . '">Tất cả</a></li>';
		foreach ( $top_cats as $tc ) {
			$is_active = ( $active_cat_param === $tc->slug );
			echo '<li><a href="' . esc_url( get_term_link( $tc ) ) . '" class="cat-nav-pill' . ( $is_active ? ' is-active' : '' ) . '">' . esc_html( $tc->name ) . '</a></li>';
		}
		echo '</ul></section>';
	}
}
?>

<section class="archive-body shell" aria-label="<?php esc_attr_e( 'Danh sách sản phẩm', 'mozlex' ); ?>">
	<div class="archive-layout" data-filter-root>
		<!-- Sidebar -->
		<aside class="filter-sidebar" id="filter-sidebar" aria-label="<?php esc_attr_e( 'Bộ lọc sản phẩm', 'mozlex' ); ?>">
			<div class="filter-sidebar-head">
				<h2 class="filter-sidebar-title"><?php esc_html_e( 'Bộ lọc', 'mozlex' ); ?></h2>
				<button type="button" class="filter-close" data-filter-close aria-label="Đóng bộ lọc">×</button>
				<button type="button" class="filter-clear-all" data-filter-reset><?php esc_html_e( 'Xóa tất cả', 'mozlex' ); ?></button>
			</div>

			<div class="active-chips" id="active-chips" hidden></div>

			<form id="filter-form" class="filter-form" method="get" data-filter-form>
				<?php
				// Keep current category param for SEO category pages.
				if ( $is_category ) {
					$queried = get_queried_object();
					echo '<input type="hidden" name="product_category" value="' . esc_attr( $queried->slug ) . '">';
				}
				?>
				<?php
				$filter_config = array(
					'product_category' => __( 'Nhóm sản phẩm', 'mozlex' ),
					'unlock_method'    => __( 'Phương thức mở khóa', 'mozlex' ),
					'application'      => __( 'Loại cửa', 'mozlex' ),
					'color'            => __( 'Màu sắc', 'mozlex' ),
				);
				// Add feature as collapsible last
				$feature_terms = get_terms( array( 'taxonomy' => 'feature', 'hide_empty' => true, 'number' => 12 ) );
				// Bỏ filter giá, giữ các filter còn lại
				foreach ( $filter_config as $tax => $label ) :
					$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => ( 'product_category' === $tax ? false : true ) ) );
					if ( is_wp_error( $terms ) || ! $terms ) continue;
					// Skip product_category on category archive (already fixed)
					if ( $is_category && 'product_category' === $tax ) continue;
				?>
					<details class="filter-group" open>
						<summary class="filter-group-title"><?php echo esc_html( $label ); ?></summary>
						<div class="filter-group-body">
							<?php
							if ( 'product_category' === $tax ) {
								// Hiển thị phân cấp: cha (Công nghệ/Thương mại/Xây dựng/Thiết bị khác) + con đẩy phải 1 xíu
								$parents = array_filter( $terms, fn($t)=> (int)$t->parent===0 );
								$children_by_parent = [];
								foreach ( $terms as $t ) if ( (int)$t->parent!==0 ) $children_by_parent[(int)$t->parent][]=$t;
								// Sắp xếp cha: Công nghệ, Thương mại, Xây dựng, Thiết bị khác
								usort( $parents, function($a,$b){
									if($a->slug==='thiet-bi-khac' && $b->slug!=='thiet-bi-khac') return 1;
									if($b->slug==='thiet-bi-khac' && $a->slug!=='thiet-bi-khac') return -1;
									$order=['cong-nghe'=>1,'thuong-mai'=>2,'xay-dung'=>3];
									$oa=$order[$a->slug]??99; $ob=$order[$b->slug]??99;
									if($oa!==$ob) return $oa<=>$ob;
									return strcmp($a->name,$b->name);
								});
								foreach ( $parents as $parent ) :
									$active = mozlex_is_filter_active( $tax, $parent->slug );
							?>
								<label class="filter-check" style="font-weight:700;">
									<input type="checkbox" name="<?php echo esc_attr( $tax ); ?>" value="<?php echo esc_attr( $parent->slug ); ?>" <?php checked( $active ); ?>>
								<span class="filter-check-label"><?php echo esc_html( $parent->name ); ?></span>
							</label>
								<?php if ( !empty($children_by_parent[(int)$parent->term_id]) ) : ?>
									<div style="margin-left:16px; border-left:1px solid rgba(0,0,0,0.06); padding-left:10px; display:grid; gap:6px; margin-bottom:6px;">
									<?php foreach ( $children_by_parent[(int)$parent->term_id] as $child ) : $active = mozlex_is_filter_active( $tax, $child->slug ); ?>
										<label class="filter-check">
											<input type="checkbox" name="<?php echo esc_attr( $tax ); ?>" value="<?php echo esc_attr( $child->slug ); ?>" <?php checked( $active ); ?>>
										<span class="filter-check-label"><?php echo esc_html( $child->name ); ?></span>
									</label>
									<?php endforeach; ?>
									</div>
								<?php endif; ?>
							<?php
								endforeach;
								// Các term mồ côi (không cha) nếu có
								foreach ( $terms as $term ) if ( (int)$term->parent===0 && !in_array($term,$parents,true) ) {
									$active = mozlex_is_filter_active( $tax, $term->slug );
									echo '<label class="filter-check"><input type="checkbox" name="'.esc_attr($tax).'" value="'.esc_attr($term->slug).'" '.checked($active,false,false).'><span class="filter-check-label">'.esc_html($term->name).'</span></label>';
								}
							} else {
								foreach ( $terms as $term ) : $active = mozlex_is_filter_active( $tax, $term->slug );
							?>
								<label class="filter-check">
									<input type="checkbox" name="<?php echo esc_attr( $tax ); ?>" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( $active ); ?>>
								<span class="filter-check-label"><?php echo esc_html( $term->name ); ?></span>
							</label>
						<?php endforeach; } ?>
						</div>
					</details>
				<?php endforeach; ?>

				<?php if ( $feature_terms && ! is_wp_error( $feature_terms ) ) : ?>
					<details class="filter-group">
						<summary class="filter-group-title"><?php esc_html_e( 'Tính năng', 'mozlex' ); ?></summary>
						<div class="filter-group-body">
							<?php foreach ( $feature_terms as $term ) : $active = mozlex_is_filter_active( 'feature', $term->slug ); ?>
								<label class="filter-check">
									<input type="checkbox" name="feature" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( $active ); ?>>
								<span class="filter-check-label"><?php echo esc_html( $term->name ); ?></span>
							</label>
							<?php endforeach; ?>
						</div>
					</details>
				<?php endif; ?>

				<noscript><button type="submit" class="btn btn-ink">Áp dụng bộ lọc</button></noscript>
			</form>
		</aside>

		<div class="archive-main">
			<div class="archive-toolbar">
				<!-- Sort: bỏ giá, chỉ giữ mới nhất / tên -->
				<div class="filter-sort-row">
					<label class="filter-field">
						<span class="filter-label"><?php esc_html_e( 'Sắp xếp', 'mozlex' ); ?></span>
						<select name="sort" form="filter-form" id="product-sort">
							<option value="" <?php selected( $current_sort, 'newest' ); ?>><?php esc_html_e( 'Mới nhất', 'mozlex' ); ?></option>
							<option value="name-az" <?php selected( $current_sort, 'name-az' ); ?>><?php esc_html_e( 'Tên A–Z', 'mozlex' ); ?></option>
													</select>
					</label>
				</div>
				<p class="result-count" id="result-count" aria-live="polite">
					<?php printf( esc_html( _n( '%s sản phẩm', '%s sản phẩm', $wp_query->found_posts, 'mozlex' ) ), number_format_i18n( $wp_query->found_posts ) ); ?>
				</p>
				<button type="button" class="btn btn-ink filter-toggle-mobile" data-filter-toggle aria-expanded="false" aria-controls="filter-sidebar">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 6h18M7 12h10M10 18h4"/></svg>
					<?php esc_html_e( 'Bộ lọc', 'mozlex' ); ?>
				</button>
			</div>

			<div id="active-chips-mobile" class="active-chips" hidden></div>

			<?php if ( $wp_query->have_posts() ) : ?>
				<div class="product-grid grid-4" id="archive-grid" data-compare-source>
					<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); get_template_part( 'template-parts/product-card' ); endwhile; ?>
				</div>

				<div class="archive-pagination-wrap" id="archive-pagination">
					<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => '&larr;', 'next_text' => '&rarr;' ) ); ?>
				</div>
			<?php else : ?>
				<p class="empty-note" id="archive-empty"><?php esc_html_e( 'Không có sản phẩm phù hợp bộ lọc hiện tại. Vui lòng thử lại với ít tiêu chí hơn hoặc liên hệ tư vấn trực tiếp.', 'mozlex' ); ?></p>
				<div class="product-grid grid-4" id="archive-grid" data-compare-source hidden></div>
				<div class="archive-pagination-wrap" id="archive-pagination" hidden></div>
			<?php endif; wp_reset_postdata(); ?>
		</div>
	</div>
</section>

<?php get_footer(); ?>
