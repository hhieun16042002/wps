<?php
/**
 * Single project template (ductri_project) — Mozlex V3.
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header();

while ( have_posts() ) : the_post();
	$p_id = get_the_ID();
	$meta = static function ( $key ) use ( $p_id ) { return get_post_meta( $p_id, '_dt226_' . $key, true ); };
	$badge = $meta( 'badge' ) ?: 'Công trình thực tế';
	$status = $meta( 'status' ) ?: 'Đã hoàn thành';
	$location = $meta( 'location' );
	$highlight = $meta( 'highlight' );
	$details = $meta( 'details' );
	$img_url = get_the_post_thumbnail_url( $p_id, 'full' ) ?: $meta( 'image' );
	if ( ! $img_url ) {
		$img_url = 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1400&auto=format&fit=crop&q=80';
	}
	$hotline = mozlex_opt( 'hotline', '0355514686' );
?>

<article <?php post_class( 'v3-single-project' ); ?>>
	<div class="shell" style="padding-top: 36px;">
		<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Đường dẫn', 'mozlex' ); ?>" style="margin-bottom: 20px;">
			<ol style="display:flex; gap:8px; font-size:13px; color:var(--color-secondary-text);">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:var(--color-secondary-text); text-decoration:none;">Trang chủ</a></li>
				<li>/</li>
				<li><a href="<?php echo esc_url( home_url( '/du-an/' ) ); ?>" style="color:var(--color-secondary-text); text-decoration:none;">Dự án</a></li>
				<li>/</li>
				<li style="color:var(--color-primary); font-weight:600;"><?php the_title(); ?></li>
			</ol>
		</nav>

		<div class="v3-project-hero" style="position:relative; border-radius:12px; overflow:hidden; margin-bottom:40px; background:#000;">
			<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php the_title_attribute(); ?>" style="width:100%; height:clamp(360px, 45vw, 560px); object-fit:cover; opacity:0.85;">
			<div style="position:absolute; inset:0; background:linear-gradient(180deg, transparent 40%, rgba(0,0,0,0.85) 100%);"></div>
			<div style="position:absolute; bottom:0; left:0; right:0; padding:clamp(24px, 4vw, 48px); color:#fff;">
				<div style="display:flex; gap:8px; margin-bottom:12px;">
					<span style="background:var(--color-accent); color:#fff; font-size:11px; font-weight:700; padding:4px 10px; border-radius:4px;"><?php echo esc_html( $badge ); ?></span>
					<span style="background:rgba(255,255,255,0.2); color:#fff; font-size:11px; font-weight:600; padding:4px 10px; border-radius:4px;"><?php echo esc_html( $status ); ?></span>
				</div>
				<h1 style="font-family:var(--font-heading); font-size:clamp(1.8rem, 3.8vw, 42px); font-weight:800; line-height:1.2; color:#fff; margin:0 0 10px;"><?php the_title(); ?></h1>
				<?php if ( $location ) : ?>
					<p style="margin:0; font-size:15px; color:rgba(255,255,255,0.85); display:flex; align-items:center; gap:6px;">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
						<span><?php echo esc_html( $location ); ?></span>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<div style="display:grid; grid-template-columns:1.3fr 0.7fr; gap: clamp(32px, 5vw, 60px); align-items:start; margin-bottom:60px;">
			<div>
				<h2 style="font-family:var(--font-heading); font-size:24px; font-weight:700; margin:0 0 16px;">Tổng quan dự án &amp; giải pháp triển khai</h2>
				<div class="project-description-content" style="font-size:16px; line-height:1.8; color:var(--color-secondary-text);">
					<?php the_content(); ?>
				</div>

				<?php if ( $highlight ) : ?>
					<div style="margin-top:28px; padding:20px; background:var(--color-bg); border-left:4px solid var(--color-accent); border-radius:4px;">
						<strong style="color:var(--color-primary); font-size:15px; display:block; margin-bottom:4px;">Điểm nổi bật của công trình:</strong>
						<span style="font-size:14px; color:var(--color-secondary-text);"><?php echo esc_html( $highlight ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<aside style="background:var(--color-bg); border:1px solid var(--color-border); border-radius:8px; padding:28px;">
				<h3 style="font-family:var(--font-heading); font-size:18px; font-weight:700; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid var(--color-border);">Thông số công trình</h3>
				<?php if ( $details ) : ?>
					<ul style="margin:0 0 24px; padding:0; list-style:none; display:flex; flex-direction:column; gap:10px; font-size:14px;">
						<?php foreach ( preg_split( '/\r?\n/', (string) $details ) as $line ) : if ( ! trim( $line ) ) continue; $parts = explode( '|', $line, 2 ); ?>
							<li style="display:flex; justify-content:space-between; gap:12px; border-bottom:1px dashed var(--color-border); padding-bottom:8px;">
								<span style="color:var(--color-secondary-text);"><?php echo esc_html( trim( $parts[0] ) ); ?>:</span>
								<strong style="color:var(--color-text); text-align:right;"><?php echo esc_html( trim( $parts[1] ?? '' ) ); ?></strong>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div style="display:flex; flex-direction:column; gap:10px;">
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $hotline ) ); ?>" class="btn btn-primary" style="width:100%; justify-content:center;">
						<span>Tư vấn dự án tương tự</span>
					</a>
					<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="btn btn-outline" style="width:100%; justify-content:center;">
						<span>Yêu cầu khảo sát công trình</span>
					</a>
				</div>
			</aside>
		</div>

		<!-- PREVIOUS / NEXT PROJECT -->
		<div style="display:flex; justify-content:space-between; align-items:center; gap:20px; padding:24px 0; border-top:1px solid var(--color-border); margin-bottom:60px;">
			<div>
				<?php $prev_post = get_previous_post(); if ( $prev_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="text-link" style="font-size:14px; font-weight:600;">
						&larr; <?php echo esc_html( wp_trim_words( get_the_title( $prev_post->ID ), 5 ) ); ?>
					</a>
				<?php endif; ?>
			</div>
			<div>
				<?php $next_post = get_next_post(); if ( $next_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="text-link" style="font-size:14px; font-weight:600;">
						<?php echo esc_html( wp_trim_words( get_the_title( $next_post->ID ), 5 ) ); ?> &rarr;
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</article>

<?php endwhile; ?>

<?php get_footer(); ?>
