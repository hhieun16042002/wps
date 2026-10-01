<?php
/**
 * News / Blog index template (/tin-tuc/).
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header(); ?>

<section class="news-archive-hero">
	<div class="shell">
		<nav class="breadcrumb-trail" aria-label="<?php esc_attr_e( 'Đường dẫn', 'mozlex' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Trang chủ', 'mozlex' ); ?></a>
			<span class="sep">/</span>
			<span><?php esc_html_e( 'Tin tức & Cẩm nang', 'mozlex' ); ?></span>
		</nav>
		<p class="news-eyebrow">KIẾN THỨC & HOẠT ĐỘNG</p>
		<h1 class="news-main-title">TIN TỨC & CẨM NANG CHUYÊN GIA</h1>
		<p class="news-main-sub">Kiến thức kỹ thuật, giải pháp an ninh công trình, tư vấn lựa chọn thiết bị và hướng dẫn sử dụng từ chuyên gia Đức Trí 226.</p>

		<!-- Category Filter Pills -->
		<?php
		$blog_cats = get_categories( array( 'hide_empty' => true ) );
		if ( ! empty( $blog_cats ) ) :
		?>
			<div class="news-cat-filter">
				<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" class="news-filter-pill is-active">Tất cả bài viết</a>
				<?php foreach ( $blog_cats as $bcat ) : ?>
					<a href="<?php echo esc_url( get_category_link( $bcat->term_id ) ); ?>" class="news-filter-pill">
						<?php echo esc_html( $bcat->name ); ?> (<?php echo esc_html( (string) $bcat->count ); ?>)
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="shell news-archive-body">
	<?php
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
	$news_q = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => 9,
		'paged'          => $paged,
	) );
	if ( $news_q->have_posts() ) :
	?>
		<div class="news-grid">
			<?php
			while ( $news_q->have_posts() ) :
				$news_q->the_post();
				$cats = get_the_category();
				$cname = ! empty( $cats ) ? $cats[0]->name : 'Kiến thức';
				$read_time = max( 1, ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) );
			?>
				<article class="news-card">
					<a href="<?php the_permalink(); ?>" class="news-card-thumb-link" aria-label="<?php the_title_attribute(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium_large', array( 'class' => 'news-card-img', 'loading' => 'lazy' ) ); ?>
						<?php else : ?>
							<div class="news-card-img-placeholder">
								<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
							</div>
						<?php endif; ?>
						<span class="news-card-cat-badge"><?php echo esc_html( $cname ); ?></span>
					</a>
					<div class="news-card-content">
						<div class="news-card-meta">
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
							<span class="meta-dot">&bull;</span>
							<span><?php echo esc_html( (string) $read_time ); ?> phút đọc</span>
						</div>
						<h2 class="news-card-title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>
						<p class="news-card-excerpt">
							<?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?>
						</p>
						<a href="<?php the_permalink(); ?>" class="news-card-more">
							<span>Đọc chi tiết</span>
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<nav class="pagination-wrapper" aria-label="<?php esc_attr_e( 'Phân trang tin tức', 'mozlex' ); ?>">
			<?php
			$big = 999999999;
			echo paginate_links( array(
				'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'    => '?paged=%#%',
				'current'   => max( 1, $paged ),
				'total'     => $news_q->max_num_pages,
				'prev_text' => '&larr; Trang trước',
				'next_text' => 'Trang sau &rarr;',
			) );
			?>
		</nav>
	<?php else : ?>
		<div class="empty-news-state">
			<p><?php esc_html_e( 'Hiện chưa có bài viết nào.', 'mozlex' ); ?></p>
		</div>
	<?php endif; ?>
</section>

<?php get_footer(); ?>
