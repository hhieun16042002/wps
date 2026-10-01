<?php
/**
 * Fallback index template.
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header(); ?>
<section class="archive-head">
	<div class="shell">
		<h1 class="page-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
	</div>
</section>
<section class="shell archive-body">
	<?php if ( have_posts() ) : ?>
		<div class="product-grid grid-4">
			<?php while ( have_posts() ) : the_post();
				if ( 'product' === get_post_type() ) {
					get_template_part( 'template-parts/product-card' );
				} else {
					echo '<article class="post-card"><h2 class="card-title"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2></article>';
				}
			endwhile; ?>
		</div>
	<?php endif; ?>
</section>
<?php get_footer(); ?>
