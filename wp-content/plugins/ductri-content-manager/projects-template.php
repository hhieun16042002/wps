<?php
/**
 * Template Name: Dự Án Tiêu Biểu
 *
 * @package mozlex
 */

declare( strict_types=1 );

$projects = new WP_Query( array( 'post_type' => 'ductri_project', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ), 'no_found_rows' => true ) );
get_header(); ?>

<section class="projects-hero">
	<div class="shell">
		<nav class="breadcrumb-trail" aria-label="<?php esc_attr_e( 'Đường dẫn', 'mozlex' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Trang chủ', 'mozlex' ); ?></a>
			<span class="sep">/</span>
			<span><?php esc_html_e( 'Dự án tiêu biểu', 'mozlex' ); ?></span>
		</nav>
		<p class="projects-eyebrow">NĂNG LỰC THỰC CHIẾN</p>
		<h1 class="projects-main-title">DỰ ÁN & CÔNG TRÌNH TIÊU BIỂU</h1>
		<p class="projects-main-sub">Mỗi công trình là một minh chứng sống động cho chất lượng thiết bị, năng lực thi công cơ khí chính xác và trách nhiệm hậu mãi tận tâm của Đức Trí 226.</p>

		<div class="project-filter-bar">
			<button type="button" class="proj-filter-btn is-active" aria-pressed="true" data-filter="all">Tất cả dự án (<?php echo (int) $projects->post_count; ?>)</button>
			<button type="button" class="proj-filter-btn" aria-pressed="false" data-filter="biet-thu">Biệt thự & Nhà phố</button>
			<button type="button" class="proj-filter-btn" aria-pressed="false" data-filter="toa-nha">Tòa nhà & Văn phòng</button>
			<button type="button" class="proj-filter-btn" aria-pressed="false" data-filter="khach-san">Khách sạn & Nghỉ dưỡng</button>
		</div>
	</div>
</section>

<section class="shell projects-body">
	<div class="projects-grid">
		<?php while ( $projects->have_posts() ) : $projects->the_post();
			$id = get_the_ID();
			$meta = static function ( $key ) use ( $id ) { return get_post_meta( $id, '_dt226_' . $key, true ); };
		?>
		<article class="project-card" data-cat="<?php echo esc_attr( $meta( 'category' ) ); ?>">
			<div class="project-thumb-wrap">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'large', array( 'class' => 'project-img', 'loading' => 'lazy', 'alt' => get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true ) ?: get_the_title() ) ); ?>
				<?php elseif ( $meta( 'image' ) ) : ?>
					<img src="<?php echo esc_url( $meta( 'image' ) ); ?>" alt="<?php echo esc_attr( $meta( 'image_alt' ) ?: get_the_title() ); ?>" class="project-img" loading="lazy">
				<?php endif; ?>
				<?php if ( $meta( 'badge' ) ) : ?><span class="project-cat-badge"><?php echo esc_html( $meta( 'badge' ) ); ?></span><?php endif; ?>
				<?php if ( $meta( 'status' ) ) : ?><span class="project-status-badge"><?php echo esc_html( $meta( 'status' ) ); ?></span><?php endif; ?>
			</div>
			<div class="project-content">
				<?php if ( $meta( 'location' ) ) : ?><div class="project-location"><span><?php echo esc_html( $meta( 'location' ) ); ?></span></div><?php endif; ?>
				<h2 class="project-title"><?php the_title(); ?></h2>
				<div class="project-desc"><?php the_content(); ?></div>
				<ul class="project-specs">
					<?php foreach ( preg_split( '/\r?\n/', (string) $meta( 'details' ) ) as $line ) : if ( ! trim( $line ) ) continue; $parts = explode( '|', $line, 2 ); ?>
					<li><?php if ( count( $parts ) === 2 ) : ?><strong><?php echo esc_html( trim( $parts[0] ) ); ?>:</strong> <?php echo esc_html( trim( $parts[1] ) ); ?><?php else : echo esc_html( $line ); endif; ?></li>
					<?php endforeach; ?>
				</ul>
				<div class="project-footer">
					<span class="project-highlight"><?php echo esc_html( $meta( 'highlight' ) ); ?></span>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', mozlex_opt( 'hotline', '0355514686' ) ) ); ?>" class="project-link">Tư vấn giải pháp tương tự &rarr;</a>
				</div>
			</div>
		</article>
		<?php endwhile; wp_reset_postdata(); ?>
		<?php if ( ! $projects->post_count ) : ?><p>Hiện chưa có dự án được hiển thị.</p><?php endif; ?>
	</div>

	<!-- CTA Khảo sát dự án -->
	<div class="projects-cta-box">
		<div class="cta-banner-content">
			<span class="cta-pill">CAM KẾT CHẤT LƯỢNG TIẾN ĐỘ</span>
			<h2 class="cta-head">Bạn đang chuẩn bị hoàn thiện công trình hoặc muốn nâng cấp hệ thống an ninh?</h2>
			<p class="cta-sub">Đội ngũ kỹ sư và thợ chuyên trách của Đức Trí 226 cam kết mang mẫu thiết bị đến tận nơi khảo sát, đo đạc đố cửa và lập phương án giải pháp tối ưu nhất cho bạn hoàn toàn miễn phí.</p>
			<div class="cta-actions">
				<a href="tel:0355514686" class="btn btn-cta-primary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
					<span>Hotline khảo sát: 0355514686</span>
				</a>
				<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="btn btn-cta-outline">
					<span>Gửi yêu cầu báo giá dự toán</span>
				</a>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var buttons = document.querySelectorAll('.proj-filter-btn');
	var cards = document.querySelectorAll('.project-card');
	buttons.forEach(function(btn) {
		btn.addEventListener('click', function() {
			buttons.forEach(function(b) { b.classList.remove('is-active'); });
			btn.classList.add('is-active');
			buttons.forEach(function(b) { b.setAttribute('aria-pressed', String(b === btn)); });
			var filter = btn.getAttribute('data-filter');
			cards.forEach(function(c) {
				if (filter === 'all' || c.getAttribute('data-cat') === filter) {
					c.style.display = '';
				} else {
					c.style.display = 'none';
				}
			});
		});
	});
});
</script>

<?php get_footer(); ?>
