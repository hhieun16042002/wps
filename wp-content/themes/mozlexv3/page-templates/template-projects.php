<?php
/**
 * Template Name: Dự Án Tiêu Biểu
 *
 * @package mozlex
 */

declare( strict_types=1 );

$projects = new WP_Query( array(
	'post_type'      => 'ductri_project',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'no_found_rows'  => true,
) );

get_header(); ?>

<section class="v3-hero" style="padding: clamp(48px, 6vw, 80px) 0 clamp(32px, 4vw, 56px);">
	<div class="shell">
		<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Đường dẫn', 'mozlex' ); ?>" style="margin-bottom: 20px;">
			<ol style="display:flex; gap:8px; font-size:13px; color:var(--color-secondary-text);">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:var(--color-secondary-text); text-decoration:none;"><?php esc_html_e( 'Trang chủ', 'mozlex' ); ?></a></li>
				<li>/</li>
				<li style="color:var(--color-primary); font-weight:600;"><?php esc_html_e( 'Dự án tiêu biểu', 'mozlex' ); ?></li>
			</ol>
		</nav>
		<span class="eyebrow">HỒ SƠ THỰC TẾ</span>
		<h1 class="page-title" style="margin: 0 0 16px;">DỰ ÁN &amp; CÔNG TRÌNH TIÊU BIỂU</h1>
		<p class="section-intro">
			Mỗi công trình là một minh chứng sống động cho chất lượng thiết bị, năng lực thi công cơ khí chính xác và trách nhiệm hậu mãi tận tâm của Đức Trí 226.
		</p>

		<div class="v3-product-filters" style="margin-top: 32px;" role="tablist" aria-label="Lọc theo loại hình">
			<button type="button" class="v3-filter-pill is-active" data-filter="all">Tất cả dự án (<?php echo (int) $projects->post_count; ?>)</button>
			<button type="button" class="v3-filter-pill" data-filter="biet-thu">Biệt thự &amp; Nhà phố</button>
			<button type="button" class="v3-filter-pill" data-filter="toa-nha">Tòa nhà &amp; Văn phòng</button>
			<button type="button" class="v3-filter-pill" data-filter="khach-san">Khách sạn &amp; Nghỉ dưỡng</button>
		</div>
	</div>
</section>

<section class="shell" style="padding-block: var(--section-gap);">
	<div class="projects-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)); gap:32px;">
		<?php if ( $projects->have_posts() ) : ?>
			<?php while ( $projects->have_posts() ) : $projects->the_post();
				$id = get_the_ID();
				$meta = static function ( $key ) use ( $id ) { return get_post_meta( $id, '_dt226_' . $key, true ); };
				$cat = $meta( 'category' ) ?: 'biet-thu';
				$badge = $meta( 'badge' ) ?: 'Biệt thự cao cấp';
				$status = $meta( 'status' ) ?: 'Đã bàn giao';
				$location = $meta( 'location' );
				$highlight = $meta( 'highlight' );
				$details = $meta( 'details' );
				$img_url = get_the_post_thumbnail_url( $id, 'large' ) ?: $meta( 'image' );
				if ( ! $img_url ) {
					$img_url = 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=900&auto=format&fit=crop&q=80';
				}
			?>
				<article class="v3-project-card project-card" data-cat="<?php echo esc_attr( $cat ); ?>">
					<div class="v3-project-thumb-wrap" style="height:240px; position:relative; overflow:hidden;">
						<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" style="width:100%; height:100%; object-fit:cover;">
						<div class="v3-project-badges" style="position:absolute; top:12px; left:12px; display:flex; gap:6px;">
							<span class="badge-cat" style="background:#000; color:#fff; padding:3px 8px; font-size:11px; font-weight:700; border-radius:3px;"><?php echo esc_html( $badge ); ?></span>
							<span class="badge-status" style="background:#dcfce7; color:#166534; padding:3px 8px; font-size:11px; font-weight:700; border-radius:3px;"><?php echo esc_html( $status ); ?></span>
						</div>
					</div>
					<div class="v3-project-body" style="padding:24px; display:flex; flex-direction:column; flex-grow:1;">
						<?php if ( $location ) : ?>
							<p class="v3-project-location" style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--color-secondary-text); margin:0 0 8px;">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
								<span><?php echo esc_html( $location ); ?></span>
							</p>
						<?php endif; ?>
						<h2 class="v3-project-heading" style="font-size:19px; font-weight:700; margin:0 0 12px;"><?php the_title(); ?></h2>
						<div class="v3-project-snippet" style="font-size:14px; line-height:1.6; color:var(--color-secondary-text); margin-bottom:16px;">
							<?php the_content(); ?>
						</div>
						<?php if ( $details ) : ?>
							<ul class="project-specs" style="margin:0 0 16px; padding:0; list-style:none; font-size:13px; border-top:1px solid var(--color-border); padding-top:12px; display:flex; flex-direction:column; gap:6px;">
								<?php foreach ( preg_split( '/\r?\n/', (string) $details ) as $line ) : if ( ! trim( $line ) ) continue; $parts = explode( '|', $line, 2 ); ?>
									<li><?php if ( count( $parts ) === 2 ) : ?><strong style="color:var(--color-text);"><?php echo esc_html( trim( $parts[0] ) ); ?>:</strong> <?php echo esc_html( trim( $parts[1] ) ); ?><?php else : echo esc_html( $line ); endif; ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<div style="margin-top:auto; padding-top:14px; border-top:1px solid var(--color-border); display:flex; justify-content:space-between; align-items:center;">
							<?php if ( $highlight ) : ?>
								<span style="font-size:12px; font-weight:600; color:var(--color-accent);"><?php echo esc_html( $highlight ); ?></span>
							<?php endif; ?>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', mozlex_opt( 'hotline', '0355514686' ) ) ); ?>" class="text-link" style="font-size:13px; font-weight:600;">
								Tư vấn giải pháp &rarr;
							</a>
						</div>
					</div>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		<?php else : ?>
			<p class="section-intro">Hiện chưa có dự án nào được cập nhật.</p>
		<?php endif; ?>
	</div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var buttons = document.querySelectorAll('.v3-filter-pill[data-filter]');
	var cards = document.querySelectorAll('.v3-project-card[data-cat]');
	buttons.forEach(function (btn) {
		btn.addEventListener('click', function () {
			buttons.forEach(function (b) { b.classList.remove('is-active'); });
			btn.classList.add('is-active');
			var filter = btn.getAttribute('data-filter');
			cards.forEach(function (card) {
				if (filter === 'all' || card.getAttribute('data-cat') === filter) {
					card.style.display = '';
				} else {
					card.style.display = 'none';
				}
			});
		});
	});
});
</script>

<?php get_footer(); ?>
