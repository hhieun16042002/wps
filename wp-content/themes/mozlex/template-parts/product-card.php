<?php
/**
 * Product card — dùng lại ở homepage, archive, kết quả wizard.
 *
 * @package mozlex
 */

declare( strict_types=1 );

$post_id = get_the_ID();
$model = get_post_meta( $post_id, 'mozlex_model', true ) ?: get_the_title();
$price = mozlex_price_text( $post_id );

$cats     = wp_get_post_terms( $post_id, 'product_category' );
$cat     = $cats ? $cats[0]->name : '';
$top_feat = array_slice( mozlex_product_features( $post_id ), 0, 3 );

$extra_class = get_query_var( 'mozlex_card_extra_class', '' );
$img_size = ( 'is-featured' === $extra_class ) ? 'large' : 'mozlex-card';
$img = get_the_post_thumbnail( $post_id, $img_size, array(
	'loading' => 'lazy',
	'class'   => 'card-image',
) );
?>
<article class="product-card <?php echo esc_attr( $extra_class ); ?>" data-model="<?php echo esc_attr( $model ); ?>"
	data-price="<?php echo esc_attr( (string) get_post_meta( $post_id, 'mozlex_price', true ) ); ?>"
	data-filters="<?php echo esc_attr( implode( ',', array_merge(
		wp_list_pluck( wp_get_post_terms( $post_id, 'unlock_method' ), 'slug' ),
		wp_list_pluck( wp_get_post_terms( $post_id, 'application' ), 'slug' ),
		wp_list_pluck( wp_get_post_terms( $post_id, 'color' ), 'slug' ),
		wp_list_pluck( wp_get_post_terms( $post_id, 'price_range' ), 'slug' )
	) ) ); ?>">
	<a class="card-link" href="<?php the_permalink(); ?>">
		<figure class="card-figure">
			<?php
			echo $img ?: '<div class="card-image card-image-placeholder" aria-hidden="true"><span>' . esc_html( $model ) . '</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- output escaping bên trong.
			?>
		</figure>
		<div class="card-body">
			<?php if ( $cat ) : ?>
				<p class="card-eyebrow"><?php echo esc_html( $cat ); ?></p>
			<?php endif; ?>
			<h3 class="card-title"><?php echo esc_html( $model ); ?></h3>
			<?php if ( $top_feat ) : ?>
				<p class="card-features"><?php echo esc_html( implode( ' · ', $top_feat ) ); ?></p>
			<?php endif; ?>
			<p class="card-meta">
				<span class="card-more"><?php esc_html_e( 'Xem chi tiết', 'mozlex' ); ?> <span class="arrow" aria-hidden="true">&rarr;</span></span>
			</p>
		</div>
	</a>
</article>
