<?php
/**
 * Single post template (Bài viết tin tức / Cẩm nang kỹ thuật).
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header();

while ( have_posts() ) :
	the_post();
	$categories = get_the_category();
	$main_cat   = ! empty( $categories ) ? $categories[0] : null;
	$read_time  = max( 1, ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) );
?>

<article class="single-post-article" id="post-<?php the_ID(); ?>">
	<header class="single-post-hero">
		<div class="shell">
			<nav class="breadcrumb-trail" aria-label="<?php esc_attr_e( 'Đường dẫn bài viết', 'mozlex' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Trang chủ', 'mozlex' ); ?></a>
				<span class="sep">/</span>
				<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>"><?php esc_html_e( 'Tin tức & Cẩm nang', 'mozlex' ); ?></a>
				<?php if ( $main_cat ) : ?>
					<span class="sep">/</span>
					<a href="<?php echo esc_url( get_category_link( $main_cat->term_id ) ); ?>"><?php echo esc_html( $main_cat->name ); ?></a>
				<?php endif; ?>
			</nav>

			<?php if ( $main_cat ) : ?>
				<div class="post-category-badge">
					<span><?php echo esc_html( $main_cat->name ); ?></span>
				</div>
			<?php endif; ?>

			<h1 class="single-post-title"><?php the_title(); ?></h1>

			<div class="single-post-meta">
				<span class="meta-item meta-author">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
					<strong>Đức Trí 226</strong>
				</span>
				<span class="meta-dot">&bull;</span>
				<span class="meta-item meta-date">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
				</span>
				<span class="meta-dot">&bull;</span>
				<span class="meta-item meta-reading">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
					<span>Khoảng <?php echo esc_html( (string) $read_time ); ?> phút đọc</span>
				</span>
			</div>
		</div>
	</header>

	<div class="shell single-post-layout">
		<main class="single-post-main">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="single-post-cover">
					<?php the_post_thumbnail( 'large', array( 'class' => 'post-hero-img' ) ); ?>
				</figure>
			<?php endif; ?>

			<?php if ( has_excerpt() ) : ?>
				<div class="post-lead-excerpt">
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
				</div>
			<?php endif; ?>

			<div class="post-content-body">
				<?php the_content(); ?>
			</div>

			<!-- Tác giả & Cam kết chất lượng -->
			<div class="post-author-box">
				<div class="author-avatar-col">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-dt226.png' ); ?>" alt="Đức Trí 226" width="64" height="64" class="author-img">
				</div>
				<div class="author-info-col">
					<h3 class="author-name">Ban Biên Tập & Kỹ Thuật Đức Trí 226</h3>
					<p class="author-bio">Đức Trí 226 là đơn vị phân phối chính hãng thiết bị khóa thông minh Mozlex, tổng thầu cung cấp giải pháp an ninh, vật liệu xây dựng và cơ điện cho các công trình nhà ở, biệt thự và dự án thương mại trên toàn quốc.</p>
				</div>
			</div>

			<!-- CTA Dịch vụ cuối bài viết -->
			<div class="post-service-cta">
				<div class="cta-inner">
					<span class="cta-badge">TƯ VẤN & BÁO GIÁ MIỄN PHÍ</span>
					<h3 class="cta-title">Cần tư vấn giải pháp hoặc thi công lắp đặt cho công trình của bạn?</h3>
					<p class="cta-desc">Đội ngũ kỹ thuật viên Đức Trí 226 sẵn sàng khảo sát thực địa, tư vấn giải pháp kỹ thuật phù hợp nhất và cung cấp bảng dự toán chi tiết không phát sinh.</p>
					<div class="cta-btns">
						<a class="btn btn-cta-call" href="tel:0355514686">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
							<span>Hotline kỹ thuật: 0355514686</span>
						</a>
						<a class="btn btn-cta-zalo" href="https://zalo.me/0355514686" target="_blank" rel="noopener">
							<span>Nhắn tin tư vấn qua Zalo</span>
						</a>
					</div>
				</div>
			</div>

			<!-- Bài viết liên quan -->
			<?php
			$related_args = array(
				'post_type'      => 'post',
				'posts_per_page' => 3,
				'post__not_in'   => array( get_the_ID() ),
				'orderby'        => 'rand',
				'no_found_rows'  => true,
			);
			if ( $main_cat ) {
				$related_args['cat'] = $main_cat->term_id;
			}
			$related = new WP_Query( $related_args );
			if ( $related->have_posts() ) :
			?>
				<section class="post-related-section">
					<h2 class="related-title">Bài viết liên quan</h2>
					<div class="related-grid">
						<?php while ( $related->have_posts() ) : $related->the_post(); ?>
							<article class="related-card">
								<a href="<?php the_permalink(); ?>" class="related-card-link">
									<?php if ( has_post_thumbnail() ) : ?>
										<div class="related-thumb">
											<?php the_post_thumbnail( 'medium' ); ?>
										</div>
									<?php else : ?>
										<div class="related-thumb related-thumb--placeholder">
											<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
										</div>
									<?php endif; ?>
									<div class="related-body">
										<time class="related-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
										<h3 class="related-heading"><?php the_title(); ?></h3>
									</div>
								</a>
							</article>
						<?php endwhile; wp_reset_postdata(); ?>
					</div>
				</section>
			<?php endif; ?>
		</main>

		<!-- Sidebar bài viết -->
		<aside class="single-post-sidebar">
			<div class="sidebar-widget widget-author-quick">
				<h3 class="widget-title">Đức Trí 226</h3>
				<p class="widget-desc">Nhà phân phối ủy quyền chính hãng thương hiệu khóa cao cấp Mozlex tại Việt Nam.</p>
				<ul class="widget-contact-list">
					<li>
						<strong>Hotline:</strong>
						<a href="tel:0355514686">0355514686</a>
					</li>
					<li>
						<strong>Email:</strong>
						<span>congtytnhhductri226@gmail.com</span>
					</li>
					<li>
						<strong>Địa chỉ:</strong>
						<span>Số 26 ngõ 24 Phan Văn Trường, Dịch Vọng Hậu, Cầu Giấy, Hà Nội</span>
					</li>
				</ul>
				<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="btn btn-sidebar-contact">Liên hệ tư vấn</a>
			</div>

			<div class="sidebar-widget widget-featured-products">
				<h3 class="widget-title">Sản phẩm tiêu biểu</h3>
				<?php
				$sidebar_prods = new WP_Query( array(
					'post_type'      => 'product',
					'posts_per_page' => 3,
					'no_found_rows'  => true,
				) );
				if ( $sidebar_prods->have_posts() ) :
					echo '<div class="sidebar-prods-list">';
					while ( $sidebar_prods->have_posts() ) : $sidebar_prods->the_post();
				?>
						<a href="<?php the_permalink(); ?>" class="sidebar-prod-item">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="sidebar-prod-thumb"><?php the_post_thumbnail( 'thumbnail' ); ?></div>
							<?php endif; ?>
							<div class="sidebar-prod-info">
								<h4 class="sidebar-prod-title"><?php the_title(); ?></h4>
								<span class="sidebar-prod-view">Xem chi tiết &rarr;</span>
							</div>
						</a>
				<?php
					endwhile;
					wp_reset_postdata();
					echo '</div>';
				endif;
				?>
			</div>
		</aside>
	</div>
</article>

<?php
endwhile;
get_footer();
