<?php
/**
 * Product detail — Premium, Balanced, SEO & Mobile-first.
 *
 * @package mozlex
 */
declare( strict_types=1 );
get_header();
while ( have_posts() ) : the_post();
	$model_meta  = get_post_meta( get_the_ID(), 'mozlex_model', true );
	$model       = $model_meta ?: get_the_title();
	$model_short = $model;
	if ( $model_meta && preg_match( '/([A-Z0-9\-]+)$/', $model_meta, $m ) ) {
		$model_short = $m[1];
	}
	$cats  = wp_get_post_terms( get_the_ID(), 'product_category' );
	$cat   = $cats ? $cats[0] : null;
	$cat_name = $cat ? $cat->name : '';
	$cat_link = $cat ? get_term_link( $cat ) : '';
	$price = mozlex_price_text();
	$feat  = mozlex_product_features();
	$specs = mozlex_technical_specs();
	$gallery_ids = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( get_the_ID(), 'mozlex_gallery', true ) ) ) );
	if ( get_post_thumbnail_id() ) array_unshift( $gallery_ids, (int) get_post_thumbnail_id() );
	$gallery_ids = array_values( array_unique( $gallery_ids ) );
	$raw_content = get_the_content();
	$bullets_html = '';
	$desc_remaining = $raw_content;
	// Clone y hệt mozlex.vn: nếu có block "THÔNG TIN CHI TIẾT" (h2 + ul) thì lấy cả h2+ul làm bullets, giữ lại intro + video trong mô tả
	if ( preg_match( '/<h2[^>]*>.*?THÔNG TIN CHI TIẾT.*?<\/h2>\s*<ul[^>]*>.*?<\/ul>/is', $raw_content, $m ) ) {
		$bullets_html = $m[0];
		$desc_remaining = trim( str_replace( $m[0], '', $raw_content ) );
	} elseif ( preg_match( '/<ul[^>]*>.*?<\/ul>/is', $raw_content, $m ) ) {
		$bullets_html = $m[0];
		$desc_remaining = trim( str_replace( $m[0], '', $raw_content ) );
	}
	if ( ! $bullets_html ) {
		$fallback = array();
		foreach ( array( 'Kiểu cách: Phân thể', 'Chất liệu: Đồng nguyên khối', 'Mạ titan PVD', 'Lõi đồng 10 bi', 'Lõi inox 304', 'Bảo hành 36 tháng' ) as $b ) $fallback[] = '<li>' . esc_html( $b ) . '</li>';
		$bullets_html = '<ul>' . implode( '', $fallback ) . '</ul>';
	}
	$share_url = get_permalink();
	$share_img = $gallery_ids ? wp_get_attachment_image_url( $gallery_ids[0], 'full' ) : '';
	$hotline   = preg_replace( '/[^0-9+]/', '', mozlex_opt( 'hotline', '0355514686' ) );
	$zalo_opt  = mozlex_opt( 'zalo' );
	$zalo_num  = preg_replace( '/[^0-9]/', '', $zalo_opt ?: $hotline );
	$zalo_url  = 'https://zalo.me/' . $zalo_num;
?>
<article <?php post_class( 'product-single' ); ?> itemscope itemtype="https://schema.org/Product">
	<div class="shell">
		<nav class="product-breadcrumb" aria-label="Breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a>
			<span aria-hidden="true">/</span>
			<?php if ( $cat ) : ?><a href="<?php echo esc_url( $cat_link ); ?>"><?php echo esc_html( $cat_name ); ?></a><span aria-hidden="true">/</span><?php endif; ?>
			<span aria-current="page"><?php echo esc_html( get_the_title() ); ?></span>
		</nav>

		<div class="product-hero">
			<div class="product-hero-grid">
				<!-- LEFT: Gallery -->
				<div class="product-gallery" data-gallery-root>
					<figure class="product-main" data-gallery-slider data-count="<?php echo count( $gallery_ids ); ?>">
						<?php if ( $gallery_ids ) :
							$gallery_data = [];
							foreach ( $gallery_ids as $gid ) $gallery_data[] = ['src'=>wp_get_attachment_image_url($gid,'large'),'full'=>wp_get_attachment_image_url($gid,'full'),'alt'=> sprintf( '%s — hình %d', $model_short, count($gallery_data)+1 )];
						?>
						<div class="product-main-wrap" data-gallery-main>
							<?php echo wp_get_attachment_image( $gallery_ids[0], 'large', false, [
								'class'=>'product-main-img',
								'itemprop'=>'image',
								'fetchpriority'=>'high',
								'alt'=> sprintf( '%s — sản phẩm Mozlex', $model_short ),
								'id'=>'gallery-main-img',
							] ); ?>
							<button type="button" class="product-zoom gallery-zoom" aria-label="Phóng to"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></button>
							<?php if ( count($gallery_ids) > 1 ) : ?>
								<button type="button" class="product-arrow gallery-prev product-prev" aria-label="Ảnh trước">‹</button>
								<button type="button" class="product-arrow gallery-next product-next" aria-label="Ảnh tiếp">›</button>
							<?php endif; ?>
						</div>
						<script type="application/json" id="gallery-data"><?php echo wp_json_encode( $gallery_data ); ?></script>
						<?php else : ?>
							<div class="product-main-placeholder" role="img" aria-label="<?php echo esc_attr($model_short); ?>"><span><?php echo esc_html($model_short); ?></span></div>
						<?php endif; ?>
					</figure>
					<?php if ( count($gallery_ids) > 1 ) : ?>
					<div class="product-thumbs-wrap">
						<ul class="product-thumbs" data-gallery data-lightbox role="list">
							<?php foreach ( $gallery_ids as $i => $id ) : ?>
							<li><button type="button" data-lightbox-item data-full="<?php echo esc_url( wp_get_attachment_image_url($id,'full') ); ?>" data-caption="<?php echo esc_attr(sprintf('%s — hình %d',$model_short,$i+1)); ?>" class="<?php echo 0===$i?'is-active':''; ?>" aria-label="Xem ảnh <?php echo $i+1; ?>"><?php echo wp_get_attachment_image($id,'medium',false,['loading'=> $i<4?'eager':'lazy','alt'=>sprintf('%s — hình %d',$model_short,$i+1)]); ?></button></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
				</div>

				<!-- RIGHT: Info -->
				<div class="product-info">
					<p class="product-eyebrow"><?php echo esc_html( mb_strtoupper($cat_name ?: 'Khóa cao cấp','UTF-8') ); ?></p>
					<h1 class="product-title" itemprop="name"><?php echo esc_html( get_the_title() ); ?></h1>
					<?php if ( $model_meta ) : ?>
					<p class="product-model">Model: <strong><?php echo esc_html( $model_meta ); ?></strong></p>
					<?php endif; ?>

					<div class="product-bullets">
						<?php echo $bullets_html; ?>
					</div>

					<div class="product-ctas">
						<a class="btn btn-ink product-cta-primary" href="<?php echo esc_url( home_url('/lien-he/?san-pham='.rawurlencode($model_short)) ); ?>">Nhận báo giá &amp; Tư vấn</a>
						<a class="btn btn-outline-dark" href="tel:<?php echo esc_attr($hotline); ?>">Gọi ngay</a>
						<a class="btn btn-zalo" href="<?php echo esc_url($zalo_url); ?>" target="_blank" rel="noopener">Zalo</a>
					</div>
					<p class="product-cta-note">Tư vấn miễn phí • Báo giá trong 2 giờ • Lắp đặt tận nơi</p>

					<div class="product-meta">
						<?php if ( $cat ) : ?><p><span>Danh mục:</span> <a href="<?php echo esc_url($cat_link); ?>"><?php echo esc_html($cat_name); ?></a></p><?php endif; ?>
						<?php $tags = wp_get_post_terms(get_the_ID(),'feature'); if(!$tags||is_wp_error($tags)) $tags=wp_get_post_terms(get_the_ID(),'product_category'); if($tags && !is_wp_error($tags)): ?>
						<p><span>Thẻ:</span> <?php foreach(array_slice($tags,0,5) as $t): ?><a href="<?php echo esc_url(get_term_link($t)); ?>"><?php echo esc_html($t->name); ?></a><?php echo $t!==end($tags)?', ':''; ?><?php endforeach; ?></p>
						<?php endif; ?>
						<div class="product-share">
							<span>Chia sẻ:</span>
							<?php
							$enc_url=rawurlencode($share_url);
							?>
							<a href="https://www.facebook.com/sharer.php?u=<?php echo esc_attr($enc_url); ?>" target="_blank" rel="noopener" class="share-btn share-fb" aria-label="Chia sẻ lên Facebook">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
								<span>Facebook</span>
							</a>
							<a href="https://zalo.me/share?url=<?php echo esc_attr($enc_url); ?>" target="_blank" rel="noopener" class="share-btn share-zalo" aria-label="Chia sẻ qua Zalo">
								<span class="zalo-dot">Z</span>
								<span>Zalo</span>
							</a>
							<button type="button" class="share-btn share-copy" data-copy-url="<?php echo esc_url($share_url); ?>" aria-label="Sao chép liên kết">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
								<span class="share-copy-text">Sao chép link</span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<nav class="product-section-nav shell" data-product-tabs aria-label="Thông tin sản phẩm">
		<a href="#product-description">Đặc điểm nổi bật</a>
		<a href="#product-specifications">Thông số kỹ thuật</a>
		<a href="#product-policies">Giao hàng &amp; Bảo hành</a>
	</nav>
	<div class="product-mobile-actions" hidden data-product-actions aria-label="<?php esc_attr_e( 'Tư vấn sản phẩm', 'mozlex' ); ?>">
		<div class="product-mobile-info">
			<span class="product-mobile-title"><?php echo esc_html( get_the_title() ); ?></span>
			<span class="product-mobile-price"><?php echo esc_html( $price ?: __( 'Giá: Liên hệ', 'mozlex' ) ); ?></span>
		</div>
		<div class="product-mobile-btns">
			<a class="btn btn-zalo" href="<?php echo esc_url( $zalo_url ); ?>" target="_blank" rel="noopener">Zalo</a>
			<a class="btn btn-ink" href="tel:<?php echo esc_attr( $hotline ); ?>">Gọi tư vấn</a>
		</div>
	</div>

	<div id="product-description" data-product-panel>
	<section class="product-desc-section">
		<div class="shell shell-narrow">
			<div class="product-desc-inner">
				<?php echo trim( strip_tags( $desc_remaining ) ) ? apply_filters( 'the_content', $desc_remaining ) : $bullets_html; ?>
			</div>
		</div>
	</section>

	<?php if ( $feat ) : ?>
	<section class="product-highlights">
		<div class="shell">
			<h2 class="section-title-sm">Tính năng nổi bật</h2>
			<ul class="feature-badges"><?php foreach($feat as $f): ?><li class="feature-badge"><?php echo esc_html($f); ?></li><?php endforeach; ?></ul>
		</div>
	</section>
	<?php endif; ?>

	</div><!-- #product-description -->

	<section class="product-specs" id="product-specifications" data-product-panel>
		<div class="shell">
			<h2 class="section-title-sm">Thông số kỹ thuật</h2>
			<table class="spec-table">
				<tbody>
					<tr><th>Model</th><td><?php echo esc_html($model_short); ?></td></tr>
					<?php foreach([ 'Chất liệu'=>get_post_meta(get_the_ID(),'mozlex_material',true), 'Màu sắc'=>get_post_meta(get_the_ID(),'mozlex_color',true), 'Độ dày cửa'=>get_post_meta(get_the_ID(),'mozlex_door',true), 'Loại lõi'=>get_post_meta(get_the_ID(),'mozlex_core_type',true), 'Dung lượng'=>get_post_meta(get_the_ID(),'mozlex_capacity',true), 'Nguồn/pin'=>get_post_meta(get_the_ID(),'mozlex_battery',true), 'Kích thước'=>get_post_meta(get_the_ID(),'mozlex_dimensions',true) ] as $label=>$val): ?>
					<tr><th><?php echo esc_html($label); ?></th><td><?php echo esc_html($val ?: 'Liên hệ tư vấn'); ?></td></tr>
					<?php endforeach; foreach($specs as $row): ?><tr><th><?php echo esc_html($row[0]); ?></th><td><?php echo esc_html($row[1]); ?></td></tr><?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>

	<section class="product-policies shell" id="product-policies" data-product-panel>
		<h2 class="section-title-sm">Giao hàng &amp; Bảo hành</h2>
		<p>Xem chính sách áp dụng hoặc liên hệ để xác nhận thời gian giao hàng, lắp đặt và bảo hành cho sản phẩm này.</p>
		<a class="text-link" href="<?php echo esc_url( home_url( '/bao-hanh/' ) ); ?>">Chính sách bảo hành</a>
		<a class="text-link" href="<?php echo esc_url( home_url( '/mua-hang-thanh-toan/' ) ); ?>">Mua hàng &amp; thanh toán</a>
	</section>
	<?php if($cats):
		$related=new WP_Query(['post_type'=>'product','posts_per_page'=>4,'post__not_in'=>[get_the_ID()],'tax_query'=>[['taxonomy'=>'product_category','field'=>'term_id','terms'=>$cats[0]->term_id]],'no_found_rows'=>true]);
		if($related->have_posts()): ?>
	<section class="product-related">
		<div class="shell">
			<h2 class="section-title-sm">Sản phẩm cùng nhóm</h2>
			<div class="product-grid grid-4">
				<?php while($related->have_posts()): $related->the_post(); get_template_part('template-parts/product-card'); endwhile; ?>
			</div>
		</div>
	</section>
	<?php endif; wp_reset_postdata(); endif; ?>
</article>
<?php endwhile; ?>

<?php get_footer(); ?>
