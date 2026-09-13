<?php
/**
 * Generic page template (blog list, các page chưa dùng template riêng).
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header(); ?>
<section class="archive-head">
	<div class="shell">
		<?php mozlex_breadcrumb(); ?>
		<h1 class="page-title"><?php the_title(); ?></h1>
	</div>
</section>

<?php if ( is_home() ) : // Blog index. ?>
	<section class="shell archive-body">
		<?php if ( have_posts() ) : ?>
			<div class="post-list">
				<?php while ( have_posts() ) : the_post(); ?>
					<article class="post-card">
						<h2 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="post-meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
						<div class="post-excerpt"><?php the_excerpt(); ?></div>
					</article>
				<?php endwhile; ?>
			</div>
			<nav class="pagination"><?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => '&larr;', 'next_text' => '&rarr;' ) ); ?></nav>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'Chưa có bài viết nào.', 'mozlex' ); ?></p>
		<?php endif; ?>
	</section>

<?php elseif ( is_search() ) : ?>
	<section class="shell archive-body">
		<?php if ( have_posts() ) : ?>
			<p class="result-count"><?php printf( esc_html__( 'Kết quả cho "%s"', 'mozlex' ), esc_html( get_search_query() ) ); ?></p>
			<div class="product-grid grid-4">
				<?php while ( have_posts() ) : the_post();
					if ( 'product' === get_post_type() ) {
						get_template_part( 'template-parts/product-card' );
					} else {
						echo '<article class="post-card"><h2 class="card-title"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2><div class="post-excerpt">' . wp_kses_post( get_the_excerpt() ) . '</div></article>';
					}
				endwhile; ?>
			</div>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'Không tìm thấy kết quả. Hãy thử model như "A16" hoặc tính năng như "Face ID".', 'mozlex' ); ?></p>
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="mega-search inline">
				<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
					placeholder="<?php esc_attr_e( 'Tìm model, tính năng hoặc ứng dụng…', 'mozlex' ); ?>">
				<button type="submit" class="btn btn-ink"><?php esc_html_e( 'Tìm', 'mozlex' ); ?></button>
			</form>
		<?php endif; ?>
	</section>
<?php else : ?>
	<section class="editorial-content shell-narrow">
		<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
	</section>
<?php endif; ?>

<?php get_footer(); ?>
