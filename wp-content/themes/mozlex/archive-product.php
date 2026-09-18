<?php
/**
 * Archive sản phẩm — Professional Filter Suite with Luxury UX
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

// Search keyword in filter
$search_query = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

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
	$queried = get_queried_object();
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

$query_args = array(
	'post_type'      => 'product',
	'posts_per_page' => 16,
	'paged'          => $paged,
	'post_status'    => 'publish',
	'tax_query'      => $query_filters ?: null,
	'orderby'        => $orderby,
	'order'          => $order,
);

if ( $search_query ) {
	$query_args['s'] = $search_query;
}

$wp_query = new WP_Query( $query_args );

$current_sort = $sort ?: 'newest';

// Helper to check if filter value active.
function mozlex_is_filter_active( $tax, $slug ) {
	$raw = isset( $_GET[ $tax ] ) ? sanitize_text_field( wp_unslash( $_GET[ $tax ] ) ) : '';
	$vals = $raw ? array_map( 'sanitize_title', explode( ',', $raw ) ) : array();
	return in_array( $slug, $vals, true );
}

// Color palette mapping for swatches
$color_swatches = array(
	'den'       => array( 'name' => 'Đen huyền bí', 'hex' => '#18181b', 'border' => '#27272a' ),
	'vang'      => array( 'name' => 'Vàng kim', 'hex' => '#d4af37', 'border' => '#b45309' ),
	'pvd'       => array( 'name' => 'Titan PVD', 'hex' => '#c9a381', 'border' => '#9a6e48' ),
	'ghi'       => array( 'name' => 'Ghi xám', 'hex' => '#64748b', 'border' => '#475569' ),
	'inox'      => array( 'name' => 'Inox bạc', 'hex' => '#cbd5e1', 'border' => '#94a3b8' ),
	'dong'      => array( 'name' => 'Đồng cổ', 'hex' => '#b45309', 'border' => '#78350f' ),
	'van-go'    => array( 'name' => 'Vân gỗ', 'hex' => '#854d0e', 'border' => '#713f12' ),
	'champagne' => array( 'name' => 'Champagne', 'hex' => '#f5ecd0', 'border' => '#dcd1b3' ),
	'coffee'    => array( 'name' => 'Nâu cà phê', 'hex' => '#452b1f', 'border' => '#2b1b14' ),
);
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
// Hiển thị danh mục con khi xem danh mục lớn
if ( $is_category ) {
	$term = get_queried_object();
	$children = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => false, 'parent' => (int) $term->term_id, 'orderby' => 'name', 'order' => 'ASC' ) );
	if ( $children && ! is_wp_error( $children ) && count( $children ) ) {
		echo '<section class="shell cat-nav-section" style="margin-top:18px;"><div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;"><h2 style="margin:0; font-size:0.95rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--dark);">Danh mục con của ' . esc_html( $term->name ) . '</h2></div><ul class="cat-nav-list">';
		foreach ( $children as $child ) {
			echo '<li><a href="' . esc_url( get_term_link( $child ) ) . '" class="cat-nav-pill">' . esc_html( $child->name ) . ' <span class="cat-count">(' . (int) $child->count . ')</span></a></li>';
		}
		echo '</ul></section>';
	}
}
// Khi ở trang /san-pham/ (tất cả), hiện thanh danh mục lớn
if ( ! $is_category ) {
	$top_cats = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => false, 'parent' => 0, 'orderby' => 'name', 'number' => 12 ) );
	if ( $top_cats && ! is_wp_error( $top_cats ) && count( $top_cats ) > 1 ) {
		usort( $top_cats, function( $a, $b ){
			if ( $a->slug === 'thiet-bi-khac' && $b->slug !== 'thiet-bi-khac' ) return 1;
			if ( $b->slug === 'thiet-bi-khac' && $a->slug !== 'thiet-bi-khac' ) return -1;
			$order = array( 'cong-nghe'=>1, 'thuong-mai'=>2, 'xay-dung'=>3 );
			$oa = $order[$a->slug] ?? 99; $ob = $order[$b->slug] ?? 99;
			if ( $oa !== $ob ) return $oa <=> $ob;
			return strcmp( $a->name, $b->name );
		});
		$active_cat_param = isset( $_GET['product_category'] ) ? sanitize_title( (string) $_GET['product_category'] ) : '';
		echo '<section class="shell cat-nav-section" style="margin-top:20px;"><div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;"><h2 style="margin:0; font-size:0.92rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--dark);">Xem theo danh mục</h2></div><ul class="cat-nav-list">';
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
		<!-- Backdrop for mobile drawer -->
		<div class="filter-backdrop" data-filter-backdrop aria-hidden="true"></div>

		<!-- Filter Sidebar / Mobile Drawer -->
		<aside class="filter-sidebar" id="filter-sidebar" aria-label="<?php esc_attr_e( 'Bộ lọc sản phẩm', 'mozlex' ); ?>">
			<!-- Header -->
			<div class="filter-sidebar-head">
				<div class="filter-head-title-wrap">
					<svg class="filter-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
					<h2 class="filter-sidebar-title"><?php esc_html_e( 'Bộ lọc tìm kiếm', 'mozlex' ); ?></h2>
				</div>
				<div class="filter-head-actions">
					<button type="button" class="filter-clear-all" data-filter-reset title="Bỏ tất cả bộ lọc">
						<?php esc_html_e( 'Thiết lập lại', 'mozlex' ); ?>
					</button>
					<button type="button" class="filter-close" data-filter-close aria-label="Đóng bộ lọc">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
					</button>
				</div>
			</div>

			<!-- Filter Quick Search Box -->
			<div class="filter-search-box">
				<svg class="search-box-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
				<input type="search" name="s" form="filter-form" id="filter-keyword-input" class="filter-keyword-input" placeholder="<?php esc_attr_e( 'Tìm theo mã khóa, tên...', 'mozlex' ); ?>" value="<?php echo esc_attr( $search_query ); ?>" autocomplete="off" />
				<?php if ( $search_query ) : ?>
					<button type="button" class="search-box-clear" data-clear-search aria-label="Xóa từ khóa">&times;</button>
				<?php endif; ?>
			</div>

			<form id="filter-form" class="filter-form" method="get" data-filter-form>
				<?php
				// Keep current category param for SEO category pages.
				if ( $is_category ) {
					$queried = get_queried_object();
					echo '<input type="hidden" name="product_category" value="' . esc_attr( $queried->slug ) . '">';
				}
				?>

				<!-- 1. Nhóm sản phẩm (Category Tree) -->
				<?php if ( ! $is_category ) : 
					$cat_terms = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => false ) );
					if ( $cat_terms && ! is_wp_error( $cat_terms ) ) :
						$parents = array_filter( $cat_terms, fn($t) => (int)$t->parent === 0 );
						$children_by_parent = array();
						foreach ( $cat_terms as $t ) {
							if ( (int)$t->parent !== 0 ) {
								$children_by_parent[(int)$t->parent][] = $t;
							}
						}
						// Priority order
						usort( $parents, function( $a, $b ){
							if ( $a->slug === 'thiet-bi-khac' && $b->slug !== 'thiet-bi-khac' ) return 1;
							if ( $b->slug === 'thiet-bi-khac' && $a->slug !== 'thiet-bi-khac' ) return -1;
							$order = array( 'cong-nghe' => 1, 'thuong-mai' => 2, 'xay-dung' => 3 );
							$oa = $order[$a->slug] ?? 99; $ob = $order[$b->slug] ?? 99;
							if ( $oa !== $ob ) return $oa <=> $ob;
							return strcmp( $a->name, $b->name );
						});
				?>
					<details class="filter-group filter-group--category" open>
						<summary class="filter-group-title">
							<span class="group-title-text"><?php esc_html_e( 'Nhóm sản phẩm', 'mozlex' ); ?></span>
							<svg class="group-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
						</summary>
						<div class="filter-group-body">
							<div class="category-tree">
								<?php foreach ( $parents as $parent ) :
									$active = mozlex_is_filter_active( 'product_category', $parent->slug );
									$has_kids = !empty($children_by_parent[(int)$parent->term_id]);
								?>
									<div class="cat-tree-node<?php echo $has_kids ? ' has-children' : ''; ?>">
										<label class="filter-check filter-check--parent">
											<input type="checkbox" name="product_category" value="<?php echo esc_attr( $parent->slug ); ?>" <?php checked( $active ); ?>>
											<span class="filter-check-box">
												<svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
											</span>
											<span class="filter-check-label"><?php echo esc_html( $parent->name ); ?></span>
											<?php if ( $parent->count > 0 ) : ?>
												<span class="filter-count"><?php echo (int)$parent->count; ?></span>
											<?php endif; ?>
										</label>

										<?php if ( $has_kids ) : ?>
											<div class="cat-tree-children">
												<?php foreach ( $children_by_parent[(int)$parent->term_id] as $child ) :
													$child_active = mozlex_is_filter_active( 'product_category', $child->slug );
												?>
													<label class="filter-check filter-check--child">
														<input type="checkbox" name="product_category" value="<?php echo esc_attr( $child->slug ); ?>" <?php checked( $child_active ); ?>>
														<span class="filter-check-box">
															<svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
														</span>
														<span class="filter-check-label"><?php echo esc_html( $child->name ); ?></span>
														<?php if ( $child->count > 0 ) : ?>
															<span class="filter-count"><?php echo (int)$child->count; ?></span>
														<?php endif; ?>
													</label>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</details>
				<?php endif; endif; ?>

				<!-- 2. Phương thức mở khóa -->
				<?php
				$unlock_terms = get_terms( array( 'taxonomy' => 'unlock_method', 'hide_empty' => true ) );
				if ( $unlock_terms && ! is_wp_error( $unlock_terms ) ) :
				?>
					<details class="filter-group" open>
						<summary class="filter-group-title">
							<span class="group-title-text"><?php esc_html_e( 'Phương thức mở khóa', 'mozlex' ); ?></span>
							<svg class="group-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
						</summary>
						<div class="filter-group-body">
							<?php foreach ( $unlock_terms as $term ) :
								$active = mozlex_is_filter_active( 'unlock_method', $term->slug );
							?>
								<label class="filter-check">
									<input type="checkbox" name="unlock_method" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( $active ); ?>>
									<span class="filter-check-box">
										<svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
									</span>
									<span class="filter-check-label"><?php echo esc_html( $term->name ); ?></span>
									<span class="filter-count"><?php echo (int)$term->count; ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</details>
				<?php endif; ?>

				<!-- 3. Loại cửa / Vị trí lắp đặt -->
				<?php
				$app_terms = get_terms( array( 'taxonomy' => 'application', 'hide_empty' => true ) );
				if ( $app_terms && ! is_wp_error( $app_terms ) ) :
				?>
					<details class="filter-group" open>
						<summary class="filter-group-title">
							<span class="group-title-text"><?php esc_html_e( 'Loại cửa phù hợp', 'mozlex' ); ?></span>
							<svg class="group-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
						</summary>
						<div class="filter-group-body">
							<?php foreach ( $app_terms as $term ) :
								$active = mozlex_is_filter_active( 'application', $term->slug );
							?>
								<label class="filter-check">
									<input type="checkbox" name="application" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( $active ); ?>>
									<span class="filter-check-box">
										<svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
									</span>
									<span class="filter-check-label"><?php echo esc_html( $term->name ); ?></span>
									<span class="filter-count"><?php echo (int)$term->count; ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</details>
				<?php endif; ?>

				<!-- 4. Màu sắc (Color Swatches Luxury Style) -->
				<?php
				$color_terms = get_terms( array( 'taxonomy' => 'color', 'hide_empty' => true ) );
				if ( $color_terms && ! is_wp_error( $color_terms ) ) :
				?>
					<details class="filter-group" open>
						<summary class="filter-group-title">
							<span class="group-title-text"><?php esc_html_e( 'Màu sắc', 'mozlex' ); ?></span>
							<svg class="group-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
						</summary>
						<div class="filter-group-body">
							<div class="swatches-grid">
								<?php foreach ( $color_terms as $term ) :
									$active = mozlex_is_filter_active( 'color', $term->slug );
									$swatch_info = $color_swatches[ $term->slug ] ?? array(
										'name'   => $term->name,
										'hex'    => '#71717a',
										'border' => '#52525b',
									);
								?>
									<label class="swatch-item<?php echo $active ? ' is-active' : ''; ?>" title="<?php echo esc_attr( $term->name ); ?> (<?php echo (int)$term->count; ?>)">
										<input type="checkbox" name="color" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( $active ); ?>>
										<span class="swatch-bubble" style="background-color: <?php echo esc_attr( $swatch_info['hex'] ); ?>; border-color: <?php echo esc_attr( $swatch_info['border'] ); ?>;">
											<svg class="swatch-check" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
										</span>
										<span class="swatch-name"><?php echo esc_html( $term->name ); ?></span>
										<span class="swatch-count"><?php echo (int)$term->count; ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					</details>
				<?php endif; ?>

				<!-- 5. Tính năng nổi bật -->
				<?php
				$feature_terms = get_terms( array( 'taxonomy' => 'feature', 'hide_empty' => true, 'number' => 12 ) );
				if ( $feature_terms && ! is_wp_error( $feature_terms ) ) :
				?>
					<details class="filter-group">
						<summary class="filter-group-title">
							<span class="group-title-text"><?php esc_html_e( 'Tính năng cao cấp', 'mozlex' ); ?></span>
							<svg class="group-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
						</summary>
						<div class="filter-group-body">
							<?php foreach ( $feature_terms as $term ) :
								$active = mozlex_is_filter_active( 'feature', $term->slug );
							?>
								<label class="filter-check">
									<input type="checkbox" name="feature" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( $active ); ?>>
									<span class="filter-check-box">
										<svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
									</span>
									<span class="filter-check-label"><?php echo esc_html( $term->name ); ?></span>
									<span class="filter-count"><?php echo (int)$term->count; ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</details>
				<?php endif; ?>

				<noscript>
					<button type="submit" class="btn btn-ink" style="width:100%; margin-top:14px;"><?php esc_html_e( 'Áp dụng bộ lọc', 'mozlex' ); ?></button>
				</noscript>
			</form>

			<!-- Mobile Sticky Footer inside Drawer -->
			<div class="filter-sidebar-footer">
				<button type="button" class="btn filter-drawer-btn filter-drawer-reset" data-filter-reset>
					<?php esc_html_e( 'Thiết lập lại', 'mozlex' ); ?>
				</button>
				<button type="button" class="btn filter-drawer-btn filter-drawer-apply" data-filter-apply>
					<span><?php esc_html_e( 'Áp dụng', 'mozlex' ); ?></span>
					<span class="filter-drawer-badge" data-filter-drawer-count>(<?php echo (int)$wp_query->found_posts; ?>)</span>
				</button>
			</div>
		</aside>

		<!-- Main Catalog Content -->
		<div class="archive-main">
			<!-- Modernized Toolbar -->
			<div class="archive-toolbar">
				<div class="toolbar-left">
					<button type="button" class="btn filter-toggle-mobile" data-filter-toggle aria-expanded="false" aria-controls="filter-sidebar">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
						<span><?php esc_html_e( 'Bộ lọc', 'mozlex' ); ?></span>
						<span class="filter-badge-pill" data-filter-badge-count hidden>0</span>
					</button>

					<div class="result-count-box">
						<p class="result-count" id="result-count" aria-live="polite">
							<?php printf(
								/* translators: %s: number of products */
								__( 'Hiển thị <strong class="count-number">%s</strong> sản phẩm', 'mozlex' ),
								number_format_i18n( $wp_query->found_posts )
							); ?>
						</p>
					</div>
				</div>

				<div class="toolbar-right">
					<!-- View Switcher (Desktop): Grid 4 vs Grid 2 -->
					<div class="view-mode-toggle" aria-label="<?php esc_attr_e( 'Chế độ xem', 'mozlex' ); ?>">
						<button type="button" class="view-mode-btn is-active" data-view-mode="grid-4" title="<?php esc_attr_e( 'Lưới chuẩn (4 cột)', 'mozlex' ); ?>" aria-label="Lưới 4 cột">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
						</button>
						<button type="button" class="view-mode-btn" data-view-mode="grid-2" title="<?php esc_attr_e( 'Lưới lớn (2 cột)', 'mozlex' ); ?>" aria-label="Lưới lớn 2 cột">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="8" height="18"/><rect x="13" y="3" width="8" height="18"/></svg>
						</button>
					</div>

					<!-- Sort Dropdown -->
					<div class="sort-selector-wrap">
						<span class="sort-selector-label"><?php esc_html_e( 'Sắp xếp:', 'mozlex' ); ?></span>
						<div class="sort-select-custom">
							<select name="sort" form="filter-form" id="product-sort" class="sort-dropdown">
								<option value="" <?php selected( $current_sort, 'newest' ); ?>><?php esc_html_e( 'Mới nhất', 'mozlex' ); ?></option>
								<option value="name-az" <?php selected( $current_sort, 'name-az' ); ?>><?php esc_html_e( 'Tên A–Z', 'mozlex' ); ?></option>
							</select>
							<svg class="sort-select-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
						</div>
					</div>
				</div>
			</div>

			<!-- Active Filter Chips Bar -->
			<div class="active-filter-bar" id="active-filter-bar" hidden>
				<span class="active-filter-label"><?php esc_html_e( 'Đang lọc:', 'mozlex' ); ?></span>
				<div class="active-chips-container" id="active-chips"></div>
				<button type="button" class="active-filter-clear-btn" data-filter-reset title="Bỏ lọc">
					<?php esc_html_e( 'Xóa tất cả', 'mozlex' ); ?>
				</button>
			</div>

			<!-- Product Grid or Empty State -->
			<?php if ( $wp_query->have_posts() ) : ?>
				<div class="product-grid grid-4" id="archive-grid" data-compare-source>
					<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); get_template_part( 'template-parts/product-card' ); endwhile; ?>
				</div>

				<div class="archive-pagination-wrap" id="archive-pagination">
					<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => '&larr;', 'next_text' => '&rarr;' ) ); ?>
				</div>
			<?php else : ?>
				<div class="empty-filter-state" id="archive-empty">
					<div class="empty-state-icon">
						<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
					</div>
					<h3 class="empty-state-title"><?php esc_html_e( 'Không tìm thấy sản phẩm phù hợp', 'mozlex' ); ?></h3>
					<p class="empty-state-desc"><?php esc_html_e( 'Không có mẫu khóa nào thỏa mãn tất cả tiêu chí bạn đã chọn. Hãy thử bỏ bớt một vài bộ lọc hoặc liên hệ để được hỗ trợ chuyên sâu.', 'mozlex' ); ?></p>
					<button type="button" class="btn btn-primary empty-state-reset" data-filter-reset>
						<?php esc_html_e( 'Xóa bộ lọc & xem tất cả', 'mozlex' ); ?>
					</button>
				</div>
				<div class="product-grid grid-4" id="archive-grid" data-compare-source hidden></div>
				<div class="archive-pagination-wrap" id="archive-pagination" hidden></div>
			<?php endif; wp_reset_postdata(); ?>
		</div>
	</div>
</section>

<?php get_footer(); ?>
