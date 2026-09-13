<?php
/**
 * Template Name: Bảo hành
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header();

$w_months = (int) mozlex_opt( 'warranty_months', 36 );
$r_months = (int) mozlex_opt( 'replace_months', 6 );
?>
<section class="archive-head">
	<div class="shell">
		<?php mozlex_breadcrumb(); ?>
		<h1 class="page-title"><?php the_title(); ?></h1>
	</div>
</section>

<section class="editorial-content shell">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<?php the_content(); ?>
	<?php endwhile; endif; ?>
	<div class="cta-strip">
		<a class="btn btn-ink" href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>"><?php esc_html_e( 'Yêu cầu bảo hành / tư vấn', 'mozlex' ); ?></a>
	</div>
</section>
<?php get_footer(); ?>
