<?php
/** Search results use the existing WordPress query and shared product cards. */
get_header();
global $wp_query;
?>
<section class="archive-head"><div class="shell">
	<?php mozlex_breadcrumb(); ?>
	<h1 class="page-title">Kết quả tìm kiếm: “<?php echo esc_html( get_search_query( false ) ); ?>”</h1>
	<p class="search-results-count">Tìm thấy <strong><?php echo esc_html( number_format_i18n( $wp_query->found_posts ) ); ?></strong> sản phẩm phù hợp</p>
	<?php
	$matched_cats = function_exists( 'mozlex_search_categories' ) ? mozlex_search_categories( get_search_query(), 4 ) : array();
	if ( ! empty( $matched_cats ) ) :
	?>
		<div class="search-related-categories" aria-label="<?php esc_attr_e( 'Danh mục liên quan', 'mozlex' ); ?>">
			<span class="search-related-label"><?php esc_html_e( 'Danh mục gợi ý:', 'mozlex' ); ?></span>
			<?php foreach ( $matched_cats as $mc ) : ?>
				<a class="search-related-pill" href="<?php echo esc_url( $mc['url'] ); ?>">
					<?php echo esc_html( $mc['name'] ); ?> <span class="pill-count">(<?php echo (int) $mc['count']; ?>)</span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php get_search_form(); ?>
</div></section>
<section class="archive-body shell">
	<?php if ( have_posts() ) : ?>
		<div class="product-grid grid-4">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php if ( 'product' === get_post_type() ) : get_template_part( 'template-parts/product-card' ); else : ?>
			<article class="search-content-card"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article>
			<?php endif; ?>
		<?php endwhile; ?>
		</div>
		<?php the_posts_pagination( array( 'prev_text' => 'Trang trước', 'next_text' => 'Trang sau' ) ); ?>
	<?php else : ?>
		<div class="empty-state search-empty-state">
			<div class="empty-icon" aria-hidden="true">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
			</div>
			<h2>Không tìm thấy sản phẩm cho “<?php echo esc_html( get_search_query() ); ?>”</h2>
			<div class="search-empty-advice">
				<p><strong>Gợi ý tìm kiếm:</strong></p>
				<ul>
					<li>Kiểm tra lại chính tả hoặc thử gõ tiếng Việt có dấu / không dấu.</li>
					<li>Thử tìm kiếm với tên model ngắn hơn (ví dụ: <code>A16</code>, <code>KS19</code>, <code>F7</code>).</li>
					<li>Tìm theo tính năng hoặc vật liệu: <code>Face ID</code>, <code>vân tay</code>, <code>Inox 304</code>.</li>
				</ul>
			</div>
			<div class="search-empty-actions">
				<a class="btn btn-ink" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Xem tất cả sản phẩm</a>
				<a class="btn btn-outline-dark" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', mozlex_opt( 'hotline', '0355514686' ) ) ); ?>">Gọi tư vấn miễn phí</a>
			</div>
		</div>
		<?php $suggested = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 4, 'no_found_rows' => true ) ); ?>
		<?php if ( $suggested->have_posts() ) : ?>
			<div class="suggested-products-wrap">
				<h2 class="section-title">Sản phẩm nổi bật đề xuất</h2>
				<div class="product-grid grid-4">
					<?php while ( $suggested->have_posts() ) : $suggested->the_post(); get_template_part( 'template-parts/product-card' ); endwhile; ?>
				</div>
			</div>
		<?php endif; wp_reset_postdata(); ?>
	<?php endif; ?>
</section>
<?php get_footer(); ?>
