<?php
/**
 * Trang chủ — Mozlex V3 (Đức Trí 226).
 * Kiến tạo giá trị từ Xây dựng đến Công nghệ.
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header();

// Featured products query
$featured_title = mozlex_opt( 'featured_title', 'SẢN PHẨM TIÊU BIỂU' ) ?: 'SẢN PHẨM TIÊU BIỂU';
$featured_cat   = mozlex_opt( 'featured_category', '' );
$featured_count = (int) mozlex_opt( 'featured_count', '8' );
if ( $featured_count < 1 || $featured_count > 24 ) $featured_count = 8;

$featured_args = array(
	'post_type'           => 'product',
	'posts_per_page'      => $featured_count,
	'post_status'         => 'publish',
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
	'orderby'             => 'date',
	'order'               => 'DESC',
);

if ( ! empty( $featured_cat ) ) {
	$featured_args['tax_query'] = array(
		array(
			'taxonomy' => 'product_category',
			'field'    => 'slug',
			'terms'    => $featured_cat,
		),
	);
}

$featured_query = new WP_Query( $featured_args );

// Fallback if empty category
if ( ! $featured_query->have_posts() ) {
	unset( $featured_args['tax_query'] );
	$featured_query = new WP_Query( $featured_args );
}

// Real product categories for dynamic filter
$product_categories = get_terms( array(
	'taxonomy'   => 'product_category',
	'hide_empty' => true,
	'parent'     => 0,
	'number'     => 8,
) );

// Real projects query
$projects_query = new WP_Query( array(
	'post_type'      => 'ductri_project',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'no_found_rows'  => true,
) );

// Real news posts query (1 large + 3 small = 4 posts)
$news_query = new WP_Query( array(
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
	'no_found_rows'  => true,
) );

// Highlight Mozlex Lock for Showcase
$showcase_product = new WP_Query( array(
	'post_type'      => 'product',
	'posts_per_page' => 1,
	'post_status'    => 'publish',
	'tax_query'      => array(
		array(
			'taxonomy' => 'product_category',
			'field'    => 'slug',
			'terms'    => array( 'khoa-thong-minh', 'khoa' ),
		),
	),
	'no_found_rows'  => true,
) );

if ( ! $showcase_product->have_posts() ) {
	$showcase_product = new WP_Query( array(
		'post_type'      => 'product',
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	) );
}

$hotline_raw = mozlex_opt( 'hotline', '0355514686' );
$hotline_clean = preg_replace( '/[^0-9+]/', '', $hotline_raw );
?>

<!-- 01. HERO SECTION -->
<section class="v3-hero" aria-labelledby="v3-hero-headline">
	<div class="shell">
		<div class="v3-hero-grid">
			<div class="v3-hero-content">
				<div class="v3-hero-badge">
					<span class="v3-badge-dot" aria-hidden="true"></span>
					<span>ĐỨC TRÍ 226 • XÂY DỰNG • THƯƠNG MẠI • CÔNG NGHỆ</span>
				</div>
				<h1 id="v3-hero-headline" class="v3-hero-title">
					KIẾN TẠO GIÁ TRỊ<br>
					TỪ XÂY DỰNG<br>
					<span class="text-accent">ĐẾN CÔNG NGHỆ</span>
				</h1>
				<p class="v3-hero-subhead">
					Đức Trí 226 cung cấp các sản phẩm, dịch vụ và giải pháp trong lĩnh vực xây dựng, thương mại và công nghệ. Khẳng định uy tín qua từng công trình và sự hài lòng của đối tác.
				</p>
				<div class="v3-hero-actions">
					<a href="<?php echo esc_url( home_url( '/san-pham/' ) ); ?>" class="btn btn-primary">
						<span>KHÁM PHÁ GIẢI PHÁP</span>
						<span class="arrow" aria-hidden="true">&rarr;</span>
					</a>
					<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="btn btn-outline">
						<span>NHẬN TƯ VẤN</span>
					</a>
				</div>
				<div class="v3-hero-pillars-hint">
					<span class="hint-label">Lĩnh vực hoạt động:</span>
					<a href="<?php echo esc_url( home_url( '/linh-vuc-hoat-dong/?nhanh=xay-dung' ) ); ?>" class="hint-tag">XÂY DỰNG</a>
					<a href="<?php echo esc_url( home_url( '/linh-vuc-hoat-dong/?nhanh=thuong-mai' ) ); ?>" class="hint-tag">THƯƠNG MẠI</a>
					<a href="<?php echo esc_url( home_url( '/linh-vuc-hoat-dong/?nhanh=cong-nghe' ) ); ?>" class="hint-tag">CÔNG NGHỆ</a>
				</div>
			</div>

			<div class="v3-hero-visual">
				<div class="v3-hero-card-featured">
					<div class="v3-hero-image-wrap">
						<?php
						$hero_thumb = 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1000&auto=format&fit=crop&q=80';
						if ( $showcase_product->have_posts() && has_post_thumbnail( $showcase_product->posts[0]->ID ) ) {
							$hero_thumb = get_the_post_thumbnail_url( $showcase_product->posts[0]->ID, 'large' );
						}
						?>
						<img src="<?php echo esc_url( $hero_thumb ); ?>" alt="Đức Trí 226 - Giải pháp toàn diện" class="v3-hero-img" loading="eager" fetchpriority="high">
						<div class="v3-hero-overlay"></div>
					</div>
					<div class="v3-hero-card-meta">
						<div class="v3-meta-left">
							<span class="v3-meta-tag">MOZLEX OFFICIAL</span>
							<h3 class="v3-meta-title">Khóa Thông Minh &amp; Kiểm Soát Cửa</h3>
							<p class="v3-meta-desc">Chuẩn an ninh Châu Âu, sinh trắc học Face ID &amp; Vân tay FPC</p>
						</div>
						<div class="v3-meta-right">
							<a href="<?php echo esc_url( home_url( '/san-pham/' ) ); ?>" class="btn btn-sm btn-accent" aria-label="Xem sản phẩm">
								<span>Xem</span> &rarr;
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 02. THREE BUSINESS AREAS (3 LĨNH VỰC) -->
<section class="v3-section v3-areas-section" aria-labelledby="v3-areas-title">
	<div class="shell">
		<header class="section-header">
			<span class="eyebrow">HỆ SINH THÁI DỊCH VỤ</span>
			<h2 id="v3-areas-title" class="section-title">BA LĨNH VỰC CỐT LÕI</h2>
			<p class="section-intro">
				Nền tảng năng lực đa ngành vững chắc, từ kỹ thuật thi công cơ điện hoàn thiện đến thương mại phân phối và công nghệ thông minh.
			</p>
		</header>

		<div class="v3-areas-grid">
			<!-- Area 01: Xây dựng -->
			<article class="v3-area-card">
				<div class="v3-area-img-wrap">
					<img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=900&auto=format&fit=crop&q=80" alt="Lĩnh vực Xây dựng Đức Trí 226" loading="lazy">
					<div class="v3-area-overlay"></div>
				</div>
				<div class="v3-area-content">
					<div class="v3-area-num">01</div>
					<h3 class="v3-area-name">XÂY DỰNG</h3>
					<p class="v3-area-desc">Thi công và cung cấp sản phẩm phục vụ công trình. Cải tạo, hoàn thiện hệ thống cửa, vách ngăn và cơ điện.</p>
					<a href="<?php echo esc_url( home_url( '/linh-vuc-hoat-dong/?nhanh=xay-dung' ) ); ?>" class="v3-area-link" aria-label="Khám phá mảng xây dựng">
						<span>Chi tiết giải pháp</span>
						<span class="arrow">&rarr;</span>
					</a>
				</div>
			</article>

			<!-- Area 02: Thương mại -->
			<article class="v3-area-card">
				<div class="v3-area-img-wrap">
					<img src="https://images.unsplash.com/photo-1441986300917-646a00fda6b7?w=900&auto=format&fit=crop&q=80" alt="Lĩnh vực Thương mại Đức Trí 226" loading="lazy">
					<div class="v3-area-overlay"></div>
				</div>
				<div class="v3-area-content">
					<div class="v3-area-num">02</div>
					<h3 class="v3-area-name">THƯƠNG MẠI</h3>
					<p class="v3-area-desc">Cung cấp và phân phối sản phẩm chính hãng. Chuỗi cung ứng vật tư, thiết bị phụ trợ đạt chuẩn CO/CQ.</p>
					<a href="<?php echo esc_url( home_url( '/linh-vuc-hoat-dong/?nhanh=thuong-mai' ) ); ?>" class="v3-area-link" aria-label="Khám phá mảng thương mại">
						<span>Chi tiết giải pháp</span>
						<span class="arrow">&rarr;</span>
					</a>
				</div>
			</article>

			<!-- Area 03: Công nghệ -->
			<article class="v3-area-card">
				<div class="v3-area-img-wrap">
					<img src="https://images.unsplash.com/photo-1518770660439-4636190af475?w=900&auto=format&fit=crop&q=80" alt="Lĩnh vực Công nghệ Đức Trí 226" loading="lazy">
					<div class="v3-area-overlay"></div>
				</div>
				<div class="v3-area-content">
					<div class="v3-area-num">03</div>
					<h3 class="v3-area-name">CÔNG NGHỆ</h3>
					<p class="v3-area-desc">Thiết bị an ninh, kiểm soát và giải pháp thông minh. Tổng đại lý khóa cao cấp Mozlex, kiểm soát truy cập và IoT.</p>
					<a href="<?php echo esc_url( home_url( '/linh-vuc-hoat-dong/?nhanh=cong-nghe' ) ); ?>" class="v3-area-link" aria-label="Khám phá mảng công nghệ">
						<span>Chi tiết giải pháp</span>
						<span class="arrow">&rarr;</span>
					</a>
				</div>
			</article>
		</div>
	</div>
</section>

<!-- 03. ABOUT SECTION & PHILOSOPHY -->
<section class="v3-section v3-about-section" aria-labelledby="v3-about-title">
	<div class="shell">
		<div class="v3-about-grid">
			<div class="v3-about-statement">
				<span class="eyebrow">TRIẾT LÝ HÀNH ĐỘNG</span>
				<h2 id="v3-about-title" class="v3-about-heading">
					KHÔNG CHỈ CUNG CẤP SẢN PHẨM.<br>
					<span class="text-accent">CHÚNG TÔI CUNG CẤP GIẢI PHÁP.</span>
				</h2>
				<p class="v3-about-lead">
					Tại Đức Trí 226, mỗi sản phẩm được cung cấp đều đi kèm với sự tư vấn sâu sắc về kỹ thuật, sự am hiểu về cấu trúc công trình và tinh thần phụng sự cao nhất. Chúng tôi chịu trách nhiệm trên từng mét vuông thi công và từng thiết bị vận hành.
				</p>
				<div class="v3-about-actions">
					<a href="<?php echo esc_url( home_url( '/ve-mozlex/' ) ); ?>" class="btn btn-primary">
						<span>VỀ CHÚNG TÔI</span> &rarr;
					</a>
					<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="btn btn-outline">
						<span>LIÊN HỆ HỢP TÁC</span>
					</a>
				</div>
			</div>

			<div class="v3-about-values">
				<div class="v3-value-card">
					<div class="v3-value-num">01</div>
					<h3 class="v3-value-title">ĐÚNG CAM KẾT</h3>
					<p class="v3-value-desc">Bảo đảm chính xác mọi điều khoản hợp đồng về chủng loại vật tư, nguồn gốc xuất xứ và giải pháp đề xuất.</p>
				</div>
				<div class="v3-value-card">
					<div class="v3-value-num">02</div>
					<h3 class="v3-value-title">CHẤT LƯỢNG</h3>
					<p class="v3-value-desc">100% thiết bị chính hãng, tiêu chuẩn kiểm định nghiêm ngặt, kiểm tra độ bền trước khi bàn giao thực địa.</p>
				</div>
				<div class="v3-value-card">
					<div class="v3-value-num">03</div>
					<h3 class="v3-value-title">TIẾN ĐỘ</h3>
					<p class="v3-value-desc">Tổ chức thi công khoa học, điều phối linh hoạt nhằm bám sát và bảo đảm tiến độ tổng thể của chủ đầu tư.</p>
				</div>
				<div class="v3-value-card">
					<div class="v3-value-num">04</div>
					<h3 class="v3-value-title">TRÁCH NHIỆM</h3>
					<p class="v3-value-desc">Đồng hành trọn đời sản phẩm. Trung tâm kỹ thuật hỗ trợ 24/7, xử lý sự cố nhanh chóng tận công trình.</p>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 04. STATS (REAL DATA BASED) -->
<section class="v3-stats-section" aria-label="Thống kê năng lực">
	<div class="shell">
		<div class="v3-stats-grid">
			<div class="v3-stat-block">
				<div class="v3-stat-number">100%</div>
				<div class="v3-stat-label">SẢN PHẨM CHÍNH HÃNG</div>
				<div class="v3-stat-sub">Đầy đủ chứng nhận CO/CQ &amp; kiểm định</div>
			</div>
			<div class="v3-stat-block">
				<div class="v3-stat-number">36+</div>
				<div class="v3-stat-label">THÁNG BẢO HÀNH</div>
				<div class="v3-stat-sub">Đổi mới linh kiện chính hãng tận nơi</div>
			</div>
			<div class="v3-stat-block">
				<div class="v3-stat-number">24/7</div>
				<div class="v3-stat-label">HỖ TRỢ KỸ THUẬT</div>
				<div class="v3-stat-sub">Hotline trực tiếp từ đội ngũ kỹ sư</div>
			</div>
			<div class="v3-stat-block">
				<div class="v3-stat-number">6+</div>
				<div class="v3-stat-label">DỰ ÁN TIÊU BIỂU</div>
				<div class="v3-stat-sub">Biệt thự cao cấp, tòa nhà &amp; văn phòng</div>
			</div>
		</div>
	</div>
</section>

<!-- 05. SẢN PHẨM TIÊU BIỂU -->
<section class="v3-section v3-products-section" aria-labelledby="v3-products-title">
	<div class="shell">
		<header class="section-header section-header-row">
			<div>
				<span class="eyebrow">CATALOGUE CHÍNH HÃNG</span>
				<h2 id="v3-products-title" class="section-title">SẢN PHẨM TIÊU BIỂU</h2>
			</div>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="btn btn-outline btn-sm">
				<span>XEM TẤT CẢ SẢN PHẨM</span> &rarr;
			</a>
		</header>

		<!-- Category Filter Pills (Real categories) -->
		<?php if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) : ?>
			<div class="v3-product-filters" role="tablist" aria-label="Bộ lọc danh mục">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="v3-filter-pill is-active">
					TẤT CẢ
				</a>
				<?php foreach ( $product_categories as $pcat ) : ?>
					<a href="<?php echo esc_url( get_term_link( $pcat ) ); ?>" class="v3-filter-pill">
						<?php echo esc_html( mb_strtoupper( $pcat->name, 'UTF-8' ) ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="product-grid grid-4">
			<?php
			if ( $featured_query->have_posts() ) :
				while ( $featured_query->have_posts() ) :
					$featured_query->the_post();
					get_template_part( 'template-parts/product-card' );
				endwhile;
				wp_reset_postdata();
			else :
				echo '<p class="section-intro">Hiện chưa có sản phẩm nổi bật nào được chọn.</p>';
			endif;
			?>
		</div>
	</div>
</section>

<!-- 06. MOZLEX SHOWCASE (PREMIUM BLACK SECTION) -->
<section class="v3-showcase-section" aria-labelledby="v3-showcase-title">
	<div class="shell">
		<div class="v3-showcase-grid">
			<div class="v3-showcase-text">
				<div class="v3-showcase-eyebrow">
					<span class="showcase-accent-badge">ĐỐI TÁC CHIẾN LƯỢC</span>
					<span>MOZLEX ARCHITECTURAL HARDWARE</span>
				</div>
				<h2 id="v3-showcase-title" class="v3-showcase-heading">
					MOZLEX<br>
					KHÓA THÔNG MINH<br>
					<span class="text-accent">CHO KHÔNG GIAN HIỆN ĐẠI</span>
				</h2>
				<p class="v3-showcase-desc">
					Đức Trí 226 là nhà phân phối chính thức dòng sản phẩm khóa kiến trúc cao cấp Mozlex tại Việt Nam. Thiết kế tinh giản, vật liệu Inox 304 nguyên khối mạ Titan PVD cùng công nghệ sinh trắc học chuẩn xác.
				</p>

				<!-- Real Feature Labels -->
				<div class="v3-showcase-features" aria-label="Các phương thức xác thực">
					<div class="v3-feat-chip">
						<span class="feat-dot"></span>
						<strong>VÂN TAY</strong>
						<small>Cảm biến FPC &lt;0.3s</small>
					</div>
					<div class="v3-feat-chip">
						<span class="feat-dot"></span>
						<strong>FACE ID</strong>
						<small>Nhận diện 3D chống giả</small>
					</div>
					<div class="v3-feat-chip">
						<span class="feat-dot"></span>
						<strong>MẬT MÃ</strong>
						<small>Mã số ảo chống nhìn trộm</small>
					</div>
					<div class="v3-feat-chip">
						<span class="feat-dot"></span>
						<strong>THẺ TỪ</strong>
						<small>Mã hóa tần số cao RFID</small>
					</div>
					<div class="v3-feat-chip">
						<span class="feat-dot"></span>
						<strong>CHÌA KHÓA CƠ</strong>
						<small>Ruột khóa chống sao chép</small>
					</div>
				</div>

				<div class="v3-showcase-cta">
					<a href="<?php echo esc_url( home_url( '/san-pham/' ) ); ?>" class="btn btn-accent">
						<span>KHÁM PHÁ CATALOGUE MOZLEX</span> &rarr;
					</a>
					<a href="tel:<?php echo esc_attr( $hotline_clean ); ?>" class="btn btn-outline">
						<span>HOTLINE: <?php echo esc_html( $hotline_raw ); ?></span>
					</a>
				</div>
			</div>

			<div class="v3-showcase-media">
				<div class="v3-showcase-media-box">
					<?php
					$showcase_img = 'https://mozlex.vn/wp-content/uploads/2026/04/Anh-san-pham-co-logo-9-1.png';
					if ( $showcase_product->have_posts() && has_post_thumbnail( $showcase_product->posts[0]->ID ) ) {
						$showcase_img = get_the_post_thumbnail_url( $showcase_product->posts[0]->ID, 'large' );
					}
					?>
					<img src="<?php echo esc_url( $showcase_img ); ?>" alt="Khóa thông minh Mozlex chính hãng" class="v3-showcase-hero-img" loading="lazy">
					<div class="v3-showcase-badge-floating">
						<span class="badge-title">Tiêu Chuẩn Châu Âu</span>
						<span class="badge-sub">Bảo hành 36 tháng chính hãng</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- 07. PROJECT SECTION (CÔNG TRÌNH TIÊU BIỂU) -->
<section class="v3-section v3-projects-section" aria-labelledby="v3-projects-title">
	<div class="shell">
		<header class="section-header section-header-row">
			<div>
				<span class="eyebrow">NĂNG LỰC THỰC TẾ</span>
				<h2 id="v3-projects-title" class="section-title">CÔNG TRÌNH TIÊU BIỂU</h2>
			</div>
			<a href="<?php echo esc_url( home_url( '/du-an/' ) ); ?>" class="btn btn-outline btn-sm">
				<span>XEM TẤT CẢ DỰ ÁN</span> &rarr;
			</a>
		</header>

		<?php if ( $projects_query->have_posts() ) : ?>
			<div class="v3-projects-grid">
				<?php
				$p_idx = 0;
				while ( $projects_query->have_posts() ) :
					$projects_query->the_post();
					$p_id = get_the_ID();
					$p_idx++;
					$is_lead = ( 1 === $p_idx );
					$loc = get_post_meta( $p_id, '_dt226_location', true );
					$badge = get_post_meta( $p_id, '_dt226_badge', true ) ?: 'Dự án thực tế';
					$status = get_post_meta( $p_id, '_dt226_status', true ) ?: 'Đã hoàn thành';
					$img_url = get_the_post_thumbnail_url( $p_id, 'large' ) ?: get_post_meta( $p_id, '_dt226_image', true );
					if ( ! $img_url ) {
						$img_url = 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=900&auto=format&fit=crop&q=80';
					}
				?>
					<article class="v3-project-card <?php echo $is_lead ? 'v3-project-lead' : 'v3-project-standard'; ?>">
						<div class="v3-project-thumb-wrap">
							<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
							<div class="v3-project-badges">
								<span class="badge-cat"><?php echo esc_html( $badge ); ?></span>
								<span class="badge-status"><?php echo esc_html( $status ); ?></span>
							</div>
						</div>
						<div class="v3-project-body">
							<?php if ( $loc ) : ?>
								<p class="v3-project-location">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
									<span><?php echo esc_html( $loc ); ?></span>
								</p>
							<?php endif; ?>
							<h3 class="v3-project-heading"><?php the_title(); ?></h3>
							<p class="v3-project-snippet"><?php echo esc_html( wp_trim_words( get_the_excerpt() ?: get_the_content(), 20 ) ); ?></p>
							<a href="<?php echo esc_url( home_url( '/du-an/' ) ); ?>" class="text-link">
								<span>Xem hồ sơ công trình</span> &rarr;
							</a>
						</div>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		<?php else : ?>
			<p class="section-intro">Đang cập nhật danh sách công trình tiêu biểu.</p>
		<?php endif; ?>
	</div>
</section>

<!-- 08. WHY ĐỨC TRÍ 226 (VÌ SAO ĐỨC TRÍ 226) -->
<section class="v3-section v3-why-section" aria-labelledby="v3-why-title">
	<div class="shell">
		<header class="section-header" style="text-align: center;">
			<span class="eyebrow">CAM KẾT THƯƠNG HIỆU</span>
			<h2 id="v3-why-title" class="section-title">VÌ SAO ĐỨC TRÍ 226?</h2>
			<p class="section-intro" style="margin: 0 auto;">
				Chúng tôi định hình tiêu chuẩn phục vụ qua 4 giá trị cốt lõi, loại bỏ sự phức tạp và mang đến sự yên tâm tuyệt đối.
			</p>
		</header>

		<div class="v3-why-grid">
			<div class="v3-why-box">
				<div class="v3-why-num">01</div>
				<h3 class="v3-why-name">ĐÚNG CAM KẾT</h3>
				<p class="v3-why-desc">
					Mọi thiết bị và dịch vụ bàn giao đều chính xác 100% theo bản vẽ, chủng loại hợp đồng và tiêu chuẩn kỹ thuật thỏa thuận.
				</p>
			</div>
			<div class="v3-why-box">
				<div class="v3-why-num">02</div>
				<h3 class="v3-why-name">CHẤT LƯỢNG</h3>
				<p class="v3-why-desc">
					Nhập khẩu và cung ứng trực tiếp, linh kiện cơ khí chuẩn xác, không sử dụng hàng trôi nổi kém chất lượng.
				</p>
			</div>
			<div class="v3-why-box">
				<div class="v3-why-num">03</div>
				<h3 class="v3-why-name">TIẾN ĐỘ</h3>
				<p class="v3-why-desc">
					Tác phong công nghiệp, đội ngũ kỹ thuật giàu kinh nghiệm, đáp ứng chuẩn chỉ mốc thời gian bàn giao dự án.
				</p>
			</div>
			<div class="v3-why-box">
				<div class="v3-why-num">04</div>
				<h3 class="v3-why-name">TRÁCH NHIỆM</h3>
				<p class="v3-why-desc">
					Chính sách hậu mãi rõ ràng, bảo hành dài hạn, sẵn sàng hỗ trợ kỹ thuật và bảo trì định kỳ sau bán hàng.
				</p>
			</div>
		</div>
	</div>
</section>

<!-- 09. WORKFLOW (TỪ NHU CẦU ĐẾN GIẢI PHÁP) -->
<section class="v3-section v3-workflow-section" aria-labelledby="v3-workflow-title">
	<div class="shell">
		<header class="section-header" style="text-align: center;">
			<span class="eyebrow">QUY TRÌNH CHUYÊN NGHIỆP</span>
			<h2 id="v3-workflow-title" class="section-title">TỪ NHU CẦU ĐẾN GIẢI PHÁP</h2>
			<p class="section-intro" style="margin: 0 auto;">
				Năm bước triển khai chuẩn hóa mang lại sự minh bạch, an toàn và hiệu quả tối ưu cho đối tác.
			</p>
		</header>

		<div class="v3-workflow-timeline">
			<div class="v3-workflow-step">
				<div class="v3-step-number">01</div>
				<div class="v3-step-dot" aria-hidden="true"></div>
				<h3 class="v3-step-title">TIẾP NHẬN</h3>
				<p class="v3-step-desc">Tiếp nhận thông tin, lắng nghe yêu cầu thực tế và mục tiêu công trình.</p>
			</div>
			<div class="v3-workflow-step">
				<div class="v3-step-number">02</div>
				<div class="v3-step-dot" aria-hidden="true"></div>
				<h3 class="v3-step-title">TƯ VẤN</h3>
				<p class="v3-step-desc">Khảo sát đố cửa, kết cấu hạ tầng và đề xuất phương án tối ưu chi phí.</p>
			</div>
			<div class="v3-workflow-step">
				<div class="v3-step-number">03</div>
				<div class="v3-step-dot" aria-hidden="true"></div>
				<h3 class="v3-step-title">BÁO GIÁ</h3>
				<p class="v3-step-desc">Lập dự toán chi tiết, minh bạch vật tư, thiết bị và các cam kết bảo hành.</p>
			</div>
			<div class="v3-workflow-step">
				<div class="v3-step-number">04</div>
				<div class="v3-step-dot" aria-hidden="true"></div>
				<h3 class="v3-step-title">TRIỂN KHAI</h3>
				<p class="v3-step-desc">Thi công lắp đặt chuẩn kỹ thuật, căn chỉnh thẩm mỹ và bàn giao thực địa.</p>
			</div>
			<div class="v3-workflow-step">
				<div class="v3-step-number">05</div>
				<div class="v3-step-dot" aria-hidden="true"></div>
				<h3 class="v3-step-title">ĐỒNG HÀNH</h3>
				<p class="v3-step-desc">Bảo hành 24/7, hướng dẫn sử dụng chi tiết và bảo trì kỹ thuật định kỳ.</p>
			</div>
		</div>
	</div>
</section>

<!-- 10. NEWS (TIN TỨC & KIẾN THỨC) -->
<section class="v3-section v3-news-section" aria-labelledby="v3-news-title">
	<div class="shell">
		<header class="section-header section-header-row">
			<div>
				<span class="eyebrow">CHUYÊN MỤC CHUYÊN GIA</span>
				<h2 id="v3-news-title" class="section-title">TIN TỨC &amp; KIẾN THỨC</h2>
			</div>
			<a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" class="btn btn-outline btn-sm">
				<span>XEM TẤT CẢ BÀI VIẾT</span> &rarr;
			</a>
		</header>

		<?php if ( $news_query->have_posts() ) : ?>
			<div class="v3-news-grid">
				<?php
				$n_idx = 0;
				while ( $news_query->have_posts() ) :
					$news_query->the_post();
					$n_idx++;
					$is_lead_news = ( 1 === $n_idx );
					$post_cats = get_the_category();
					$cat_name = ! empty( $post_cats ) ? $post_cats[0]->name : 'Tin tức';
				?>
					<article class="v3-news-card <?php echo $is_lead_news ? 'v3-news-lead' : 'v3-news-standard'; ?>">
						<a href="<?php the_permalink(); ?>" class="v3-news-thumb-link">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( $is_lead_news ? 'large' : 'medium_large', array( 'class' => 'v3-news-img', 'loading' => 'lazy' ) ); ?>
							<?php else : ?>
								<div class="v3-news-placeholder"><span>ĐỨC TRÍ 226</span></div>
							<?php endif; ?>
						</a>
						<div class="v3-news-body">
							<div class="v3-news-meta">
								<span class="v3-news-cat"><?php echo esc_html( $cat_name ); ?></span>
								<time class="v3-news-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
							</div>
							<h3 class="v3-news-heading">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							<?php if ( $is_lead_news ) : ?>
								<p class="v3-news-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt() ?: get_the_content(), 28 ) ); ?></p>
							<?php endif; ?>
							<a href="<?php the_permalink(); ?>" class="text-link">
								<span>Đọc bài viết</span> &rarr;
							</a>
						</div>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		<?php else : ?>
			<p class="section-intro">Đang cập nhật các bài viết kiến thức mới nhất.</p>
		<?php endif; ?>
	</div>
</section>

<!-- 11. CONTACT CTA (PREMIUM BLACK SECTION) -->
<section class="v3-contact-cta-section" aria-labelledby="v3-cta-title">
	<div class="shell">
		<div class="v3-cta-box">
			<div class="v3-cta-content">
				<span class="eyebrow" style="color: var(--color-accent) !important;">KẾT NỐI HỢP TÁC</span>
				<h2 id="v3-cta-title" class="v3-cta-heading">
					BẠN ĐANG CÓ<br>
					<span class="text-accent">MỘT DỰ ÁN?</span>
				</h2>
				<p class="v3-cta-text">
					Hãy để Đức Trí 226 đồng hành cùng bạn từ giai đoạn khảo sát, lên dự toán đến thi công hoàn thiện trọn gói.
				</p>
				<div class="v3-cta-info">
					<div class="info-row">
						<strong>Hotline:</strong>
						<a href="tel:<?php echo esc_attr( $hotline_clean ); ?>"><?php echo esc_html( $hotline_raw ); ?></a>
					</div>
					<div class="info-row">
						<strong>Văn phòng:</strong>
						<span><?php echo esc_html( mozlex_opt( 'address', 'Số 26 ngõ 24 Phan Văn Trường, Cầu Giấy, Hà Nội' ) ); ?></span>
					</div>
				</div>
			</div>
			<div class="v3-cta-buttons">
				<a href="tel:<?php echo esc_attr( $hotline_clean ); ?>" class="btn btn-accent btn-lg">
					<span>GỌI NGAY: <?php echo esc_html( $hotline_raw ); ?></span>
				</a>
				<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="btn btn-outline btn-lg">
					<span>NHẬN TƯ VẤN BÁO GIÁ</span> &rarr;
				</a>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>