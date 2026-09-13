<?php
/**
 * Trang chủ — luxury catalogue.
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header();

// Featured: chỉ hiện khóa thông minh, custom được ở Mozlex → Cấu hình
$featured_title = mozlex_opt( 'featured_title', 'Những sản phẩm đáng chú ý' );
if ( ! $featured_title ) $featured_title = 'Những sản phẩm đáng chú ý';
$featured_cat   = mozlex_opt( 'featured_category', 'khoa-cua-thong-minh' );
if ( ! $featured_cat ) $featured_cat = 'khoa-cua-thong-minh';
$featured_count = (int) mozlex_opt( 'featured_count', '8' );
if ( $featured_count < 1 || $featured_count > 24 ) $featured_count = 8;

$exclude_slugs = array( 'c1060t', 'd7', 'ng109', 'a112' ); // theo yêu cầu xóa C1060T, D7, NG109, A112
$exclude_ids = array();
foreach ( $exclude_slugs as $ex_slug ) {
	$ex = get_page_by_path( $ex_slug, OBJECT, 'product' );
	if ( $ex ) $exclude_ids[] = (int) $ex->ID;
}
$featured = new WP_Query( array(
	'post_type'           => 'product',
	'posts_per_page'      => $featured_count,
	'post__not_in'        => $exclude_ids,
	'tax_query'           => array(
		array(
			'taxonomy' => 'product_category',
			'field'    => 'slug',
			'terms'    => $featured_cat,
		),
	),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );
// Fallback: nếu danh mục rỗng thì thử sản phẩm ghim featured
if ( ! $featured->have_posts() ) {
	$featured = new WP_Query( array(
		'post_type'           => 'product',
		'posts_per_page'      => $featured_count,
		'meta_key'            => 'mozlex_featured',
		'meta_value'          => '1',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );
}

			$categories = get_terms( array(
				'taxonomy'   => 'product_category',
				'hide_empty' => true,
				'parent'     => 0,
			) );

// Fallback: nếu chưa có featured products thì lấy 4 sản phẩm mới nhất.
$hero_products = array();
if ( $featured->have_posts() ) {
	$hero_products = array_slice( $featured->posts, 0, 4 );
}
$banner_image = mozlex_opt( 'banner_image' );
$banner_eyebrow = mozlex_opt( 'banner_eyebrow', 'ĐƠN VỊ TƯ VẤN • CUNG CẤP • LẮP ĐẶT' );
$banner_title = mozlex_opt( 'banner_title', 'TƯ VẤN – CUNG CẤP – LẮP ĐẶT THIẾT BỊ' );
$banner_decorative = mozlex_opt( 'banner_decorative', 'Giải pháp toàn diện' );
$banner_subtitle = mozlex_opt( 'banner_subtitle', 'Thiết bị toàn diện cho công trình, doanh nghiệp và gia đình. Không chỉ cung cấp thiết bị — chúng tôi cung cấp giải pháp.' );
$banner_cta_text = mozlex_opt( 'banner_cta_text', 'NHẬN TƯ VẤN' );
$banner_cta_url = mozlex_opt( 'banner_cta_url', home_url( '/lien-he/' ) );
$banner_cta2_text = mozlex_opt( 'banner_cta2_text', 'XEM SẢN PHẨM' );
$banner_cta2_url = mozlex_opt( 'banner_cta2_url', home_url( '/san-pham/' ) );
$hero_bg = $banner_image ?: ( ! empty( $hero_products ) && get_the_post_thumbnail_url( $hero_products[0]->ID, 'full' ) ? get_the_post_thumbnail_url( $hero_products[0]->ID, 'full' ) : '' );

// Hero Slider: ưu tiên CPT hero_slide, fallback sang banner đơn cũ.
$hero_slides_q = new WP_Query( array(
	'post_type'      => 'hero_slide',
	'posts_per_page' => 10,
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
	'post_status'    => 'publish',
	'no_found_rows'  => true,
) );
?>

<?php if ( $hero_slides_q->have_posts() ) : ?>
<!-- HERO SLIDER — 3s auto, custom qua Admin → Banner Slider -->
<section class="hero hero-slider" data-hero-slider data-interval="3000" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Banner', 'mozlex' ); ?>">
	<div class="hero-slider-track">
		<?php
		$slide_idx = 0;
		while ( $hero_slides_q->have_posts() ) : $hero_slides_q->the_post();
			$sid          = get_the_ID();
			$slide_title  = get_the_title();
			$slide_img    = get_the_post_thumbnail_url( $sid, 'full' ) ?: $hero_bg;
			$slide_eyebrow= get_post_meta( $sid, 'mozlex_slide_eyebrow', true ) ?: $banner_eyebrow;
			$slide_deco   = get_post_meta( $sid, 'mozlex_slide_decorative', true );
			$slide_sub    = get_post_meta( $sid, 'mozlex_slide_subtitle', true );
			$slide_cta    = get_post_meta( $sid, 'mozlex_slide_cta_text', true );
			$slide_cta_url= get_post_meta( $sid, 'mozlex_slide_cta_url', true );
			$slide_cta2   = get_post_meta( $sid, 'mozlex_slide_cta2_text', true );
			$slide_cta2_url = get_post_meta( $sid, 'mozlex_slide_cta2_url', true );

		?>
		<div class="hero-slide<?php echo 0 === $slide_idx ? ' is-active' : ''; ?>" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( ( $slide_idx + 1 ) . ' / ' . $hero_slides_q->post_count ); ?>"<?php echo 0 === $slide_idx ? '' : ' aria-hidden="true"'; ?>>
			<?php if ( $slide_img ) : ?>
				<div class="hero-bg" style="background-image:url('<?php echo esc_url( $slide_img ); ?>')"></div>
			<?php endif; ?>
			<div class="hero-overlay"></div>
			<div class="shell hero-inner">
				<?php if ( $slide_eyebrow ) : ?><p class="eyebrow"><?php echo esc_html( $slide_eyebrow ); ?></p><?php endif; ?>
				<h2 class="hero-title"><?php echo esc_html( $slide_title ); ?></h2>
				<?php if ( $slide_deco ) : ?><p class="hero-decorative"><?php echo esc_html( $slide_deco ); ?></p><?php endif; ?>
				<?php if ( $slide_sub ) : ?><p class="hero-sub"><?php echo esc_html( $slide_sub ); ?></p><?php endif; ?>
				<?php if ( $slide_cta || $slide_cta2 ) : ?>
				<div class="hero-cta">
					<?php if ( $slide_cta && $slide_cta_url ) : ?><a class="btn btn-ink" href="<?php echo esc_url( $slide_cta_url ); ?>" style="text-transform:uppercase"><?php echo esc_html( $slide_cta ); ?></a><?php endif; ?>
					<?php if ( $slide_cta2 && $slide_cta2_url ) : ?><a class="btn btn-outline" href="<?php echo esc_url( $slide_cta2_url ); ?>" style="text-transform:uppercase"><?php echo esc_html( $slide_cta2 ); ?></a><?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php $slide_idx++; endwhile; wp_reset_postdata(); ?>
	</div>
	<?php if ( $hero_slides_q->post_count > 1 ) : ?>
	<button type="button" class="hero-nav hero-prev" aria-label="<?php esc_attr_e( 'Slide trước', 'mozlex' ); ?>">&#8249;</button>
	<button type="button" class="hero-nav hero-next" aria-label="<?php esc_attr_e( 'Slide sau', 'mozlex' ); ?>">&#8250;</button>
	<div class="hero-dots" role="tablist" aria-label="<?php esc_attr_e( 'Chọn slide', 'mozlex' ); ?>">
		<?php for ( $d = 0; $d < $hero_slides_q->post_count; $d++ ) : ?>
			<button type="button" role="tab" class="hero-dot<?php echo 0 === $d ? ' is-active' : ''; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d', 'mozlex' ), $d + 1 ) ); ?>" aria-selected="<?php echo 0 === $d ? 'true' : 'false'; ?>" data-slide="<?php echo esc_attr( (string) $d ); ?>"></button>
		<?php endfor; ?>
	</div>
	<?php endif; ?>
</section>
<?php else : ?>
<!-- HERO — fallback single banner (khi chưa tạo slide) -->
<section class="hero" aria-labelledby="hero-title">
	<?php if ( $hero_bg ) : ?>
		<div class="hero-bg" style="background-image:url('<?php echo esc_url( $hero_bg ); ?>')"></div>
	<?php endif; ?>
	<div class="hero-overlay"></div>
	<div class="shell hero-inner">
		<p class="eyebrow reveal"><?php echo esc_html( $banner_eyebrow ); ?></p>
		<h1 id="hero-title" class="hero-title reveal">
			<?php echo esc_html( $banner_title ); ?>
		</h1>
		<?php if ( $banner_decorative ) : ?>
			<p class="hero-decorative reveal"><?php echo esc_html( $banner_decorative ); ?></p>
		<?php endif; ?>
		<p class="hero-sub reveal"><?php echo esc_html( $banner_subtitle ); ?></p>
		<div class="hero-cta reveal">
			<a class="btn btn-ink" href="<?php echo esc_url( $banner_cta_url ); ?>" style="text-transform:uppercase"><?php echo esc_html( $banner_cta_text ); ?></a>
			<a class="btn btn-outline" href="<?php echo esc_url( $banner_cta2_url ); ?>" style="text-transform:uppercase"><?php echo esc_html( $banner_cta2_text ); ?></a>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- INTRO — Brand name cursive + headline -->
<section class="home-section intro-section" aria-label="<?php esc_attr_e( 'Giới thiệu', 'mozlex' ); ?>">
	<div class="shell">
		<div class="intro-content">
			<p class="sub-title"><?php esc_html_e( 'MOZLEX', 'mozlex' ); ?></p>
			<h2><span class="text-large"><?php esc_html_e( 'KHÓA CAO CẤP', 'mozlex' ); ?></span></h2>

			<div style="margin:28px auto 0; display:flex; flex-direction:column; align-items:center; gap:14px;">
				<div style="display:flex; align-items:center; gap:20px; justify-content:center; flex-wrap:wrap;">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-dt226.png' ); ?>" alt="Đức Trí 226" width="220" height="60" loading="lazy" decoding="async" style="height:56px; width:auto; object-fit:contain; filter:drop-shadow(0 2px 8px rgba(0,0,0,0.06));">
					<img src="https://mozlex.vn/wp-content/uploads/2026/04/Anh-san-pham-co-logo-9-1.png" alt="Mozlex" width="220" height="60" loading="lazy" decoding="async" style="height:56px; width:auto; object-fit:contain; filter:drop-shadow(0 2px 8px rgba(0,0,0,0.06)); background:#fff; padding:4px; border-radius:6px;">
				</div>
				<p style="margin:0; font-size:0.95rem; color:var(--primary); font-weight:600; letter-spacing:0.04em; text-align:center;">Là nhà phân phối chính hãng được <strong>Mozlex</strong> tin tưởng, Đức Trí 226 cam kết chính hãng</p>
			</div>
		</div>
	</div>
</section>

<?php if ( '1' === mozlex_opt( 'show_danhmuc', '1' ) ) : ?>
<!-- 3 NHÁNH LỚN — Đức Trí 226: Xây dựng / Thương mại / Công nghệ -->
<section class="home-section" aria-labelledby="branches-title" style="background:#fff;">
	<div class="shell">
		<header class="section-header" style="text-align:center; margin-bottom:32px;">
			<p class="eyebrow">Đức Trí 226 - 3 nhánh</p>
			<h2 class="section-title" id="branches-title">XÂY DỰNG • THƯƠNG MẠI • CÔNG NGHỆ</h2>
			<div class="sefico-divider" style="margin:12px auto 0;"></div>
		</header>
				<div class="category-boxes category-boxes--danhmuc" style="grid-template-columns: repeat(3, 1fr);">
			<?php
			$branch_slugs = array( 'xay-dung', 'thuong-mai', 'cong-nghe' );
			$branch_names = array( 'xay-dung' => array( 'XÂY DỰNG', 'Xây dựng', 'Khóa cửa • Cửa sắt vân gỗ • Cửa chống cháy' ), 'thuong-mai' => array( 'THƯƠNG MẠI', 'Thương mại', 'Sơn • Dầu nhớt • Ắc quy' ), 'cong-nghe' => array( 'CÔNG NGHỆ', 'Công nghệ', 'Camera • Kiểm soát ra vào • Tự động' ) );
			$branch_terms = array();
			foreach ( $branch_slugs as $bs ) { $t = get_term_by( 'slug', $bs, 'product_category' ); if ( $t && ! is_wp_error( $t ) ) $branch_terms[] = $t; }
			foreach ( $branch_terms as $bcat ) :
				$binfo = $branch_names[ $bcat->slug ] ?? array( mb_strtoupper( $bcat->name, 'UTF-8' ), $bcat->name, $bcat->description ?: $bcat->count . ' sản phẩm' );
				// Ảnh: ưu tiên ảnh đại diện danh mục do admin chọn, fallback sản phẩm đầu, rồi AI placeholder
				$bimg = function_exists('mozlex_category_thumbnail_url') ? mozlex_category_thumbnail_url( (int) $bcat->term_id, 'mozlex-category' ) : '';
				if ( ! $bimg ) {
					$bq = new WP_Query( array( 'post_type' => 'product', 'posts_per_page' => 1, 'tax_query' => array( array( 'taxonomy' => 'product_category', 'field' => 'slug', 'terms' => $bcat->slug, 'include_children' => true ) ), 'no_found_rows' => true ) );
					if ( $bq->have_posts() && get_the_post_thumbnail_url( $bq->posts[0]->ID, 'mozlex-category' ) ) $bimg = get_the_post_thumbnail_url( $bq->posts[0]->ID, 'mozlex-category' );
					wp_reset_postdata();
				}
				if ( ! $bimg ) {
					// AI tạm: dùng placeholder theo nhánh
					$bimg = 'https://images.unsplash.com/photo-' . ( 'xay-dung' === $bcat->slug ? '1507089947368-34c0a0a8684b' : ( 'thuong-mai' === $bcat->slug ? '1441986300917-646a00fda6b7' : '1498049794561-474d27645ed9' ) ) . '?w=900&auto=format&fit=crop&q=80';
				}
				$childs = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => false, 'parent' => (int) $bcat->term_id ) );
				$child_count = is_array( $childs ) && ! is_wp_error( $childs ) ? count( $childs ) : 0;
			?>
			<a class="category-box" href="<?php echo esc_url( get_term_link( $bcat ) ); ?>" style="background-image:url('<?php echo esc_url( $bimg ); ?>'); min-height:320px;">
				<img class="category-box-img" src="<?php echo esc_url( $bimg ); ?>" alt="<?php echo esc_attr( $binfo[0] ); ?>" loading="lazy">
				<div class="category-box-overlay" style="background:rgba(0,0,0,0.55);"></div>
				<div class="category-box-content" style="text-align:center;">
					<h3><?php echo esc_html( $binfo[0] ); ?></h3>
					<p><?php echo esc_html( $binfo[2] ); ?></p>
					<span class="btn btn-sm btn-outline" style="text-transform:uppercase">Xem nhánh <i class="icon-angle-right"></i></span>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
<?php endif; ?>

<?php if ( '1' === mozlex_opt( 'show_giaiphap', '1' ) ) : ?>
<!-- GIẢI PHÁP CHO MỌI CÔNG TRÌNH -->
<section class="home-section" aria-labelledby="solution-title" style="background:#fff;">
	<div class="shell">
		<header class="section-header" style="text-align:center;">
			<p class="eyebrow">Giải pháp</p>
			<h2 class="section-title" id="solution-title">GIẢI PHÁP CHO MỌI CÔNG TRÌNH</h2>
			<p class="section-intro" style="margin:12px auto 0; max-width:60ch;">Nhu cầu → Giải pháp → Thiết bị → Lắp đặt → Bảo trì — từ nhà ở đến tòa nhà.</p>
			<div class="sefico-divider" style="margin:12px auto 0;"></div>
		</header>
		<div class="solution-grid" style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-top:32px;">
			<?php
			$sol_q = new WP_Query( array( 'post_type' => 'solution', 'posts_per_page' => 9, 'orderby' => 'menu_order', 'order' => 'ASC', 'post_status' => 'publish' ) );
			if ( $sol_q->have_posts() ) {
				while ( $sol_q->have_posts() ) { $sol_q->the_post();
					$title = get_the_title();
					$excerpt = get_the_excerpt() ?: wp_trim_words( get_the_content(), 18 );
					echo '<div class="solution-card" style="background:var(--warm-white); border:1px solid rgba(0,0,0,0.06); border-radius:10px; padding:18px;"><h3 style="font-size:0.82rem; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:#000; margin:0 0 6px;">'.esc_html($title).'</h3><p style="font-size:0.85rem; color:rgba(53,53,53,0.75); margin:0 0 8px;">'.esc_html($excerpt).'</p><span style="font-size:0.72rem; color:var(--primary); font-weight:600;">Nhu cầu → Thiết bị → Lắp đặt → Bảo trì →</span></div>';
				}
				wp_reset_postdata();
			} else {
				$solutions = array(
					array('Nhà ở / biệt thự','Tư vấn khóa & HVAC cho không gian sống'),
					array('Chung cư','An ninh & kiểm soát ra vào đồng bộ'),
					array('Văn phòng','Giải pháp tự động & tiết kiệm năng lượng'),
					array('Khách sạn','Khóa thẻ từ & điều hòa trung tâm'),
					array('Nhà hàng','Bếp & thông gió chuyên nghiệp'),
					array('Cửa hàng','Camera AI & kiểm soát cửa'),
					array('Nhà xưởng','Tủ điện & thiết bị công nghiệp'),
					array('Tòa nhà','VRV/VRF & access control tập trung'),
					array('Công trình thương mại','Tích hợp hệ thống toàn diện'),
				);
				foreach ($solutions as $s) {
					echo '<div class="solution-card" style="background:var(--warm-white); border:1px solid rgba(0,0,0,0.06); border-radius:10px; padding:18px;"><h3 style="font-size:0.82rem; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:#000; margin:0 0 6px;">'.esc_html($s[0]).'</h3><p style="font-size:0.85rem; color:rgba(53,53,53,0.75); margin:0 0 8px;">'.esc_html($s[1]).'</p><span style="font-size:0.72rem; color:var(--primary); font-weight:600;">Nhu cầu → Thiết bị → Lắp đặt → Bảo trì →</span></div>';
				}
			}
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( '1' === mozlex_opt( 'show_dichvu', '1' ) ) : ?>
<!-- LĨNH VỰC CỦA ĐỨC TRÍ — 3 mảng (Xây dựng / Thương mại / Công nghệ) — ô màu -->
<section class="home-section alt-bg sefico-section" aria-labelledby="sefico-title">
	<div class="shell">
		<header class="sefico-head reveal">
			<h2 class="sefico-title" id="sefico-title">LĨNH VỰC HOẠT ĐỘNG CỦA ĐỨC TRÍ</h2>
			<div class="sefico-divider" aria-hidden="true"></div>
		</header>
		<div class="sefico-grid" style="grid-template-columns:1fr;">
			<div class="sefico-services">
				<?php
				$svc_q = new WP_Query( array( 'post_type' => 'service', 'posts_per_page' => 3, 'orderby' => 'menu_order', 'order' => 'ASC', 'post_status' => 'publish' ) );
				$fallback_icons = array(
					'<svg viewBox="0 0 64 64" fill="none" aria-hidden="true"><rect x="10" y="18" width="44" height="30" rx="4" stroke="#000" stroke-width="2"/><path d="M14 26h10v12H14zM26 26h12v12H26zM40 26h10v12H40z" stroke="#000" stroke-width="1.6"/><path d="M18 14l6-6h12l6 6" stroke="#000" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
					'<svg viewBox="0 0 64 64" fill="none" aria-hidden="true"><rect x="12" y="14" width="40" height="28" rx="6" stroke="#000" stroke-width="2"/><path d="M20 22h14M20 28h20M20 34h14" stroke="#000" stroke-width="1.8" stroke-linecap="round"/><circle cx="44" cy="38" r="6" stroke="#c9a381" stroke-width="1.8"/><path d="M44 35v3l2 2" stroke="#c9a381" stroke-width="1.6" stroke-linecap="round"/></svg>',
					'<svg viewBox="0 0 64 64" fill="none" aria-hidden="true"><path d="M12 32h40M32 12v40" stroke="#000" stroke-width="1.8" stroke-linecap="round"/><circle cx="32" cy="32" r="10" stroke="#c9a381" stroke-width="1.8"/><path d="M32 26v6l4 4" stroke="#c9a381" stroke-width="1.6" stroke-linecap="round"/></svg>',
				);
				if ( $svc_q->have_posts() ) {
					$i=0;
					while ( $svc_q->have_posts() ) { $svc_q->the_post();
						$icon = has_post_thumbnail() ? get_the_post_thumbnail( get_the_ID(), 'thumbnail', array( 'style' => 'width:48px;height:48px;object-fit:contain;' ) ) : $fallback_icons[$i % count($fallback_icons)];
						echo '<div class="sefico-card reveal" style="transition-delay:'.($i*80).'ms"><div class="sefico-icon">'.$icon.'</div><div class="sefico-card-body"><h3>'.esc_html( get_the_title() ).'</h3><p>'.esc_html( get_the_excerpt() ?: wp_trim_words( get_the_content(), 22 ) ).'</p></div></div>';
						$i++;
					}
					wp_reset_postdata();
				} else {
				?>
				<div class="sefico-card reveal" style="transition-delay:0ms">
					<div class="sefico-icon"><?php echo $fallback_icons[0]; ?></div>
					<div class="sefico-card-body">
						<h3>XÂY DỰNG</h3>
						<p>Khóa cửa • Cửa sắt vân gỗ • Cửa chống cháy — tư vấn, cung cấp và lắp đặt trọn gói cho mọi công trình.</p>
					</div>
				</div>
				<div class="sefico-card reveal" style="transition-delay:80ms">
					<div class="sefico-icon"><?php echo $fallback_icons[1]; ?></div>
					<div class="sefico-card-body">
						<h3>THƯƠNG MẠI</h3>
						<p>Sơn • Dầu nhớt • Phụ kiện — phân phối chính hãng, CO/CQ đầy đủ, giao hàng đúng tiến độ.</p>
					</div>
				</div>
				<div class="sefico-card reveal" style="transition-delay:160ms">
					<div class="sefico-icon"><?php echo $fallback_icons[2]; ?></div>
					<div class="sefico-card-body">
						<h3>CÔNG NGHỆ</h3>
						<p>Camera an ninh • Kiểm soát ra vào • Thiết bị tự động — giải pháp thông minh, an toàn, tiết kiệm.</p>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ABOUT US + VIDEO — logo to bằng chữ Đức Trí 226 + Về chúng tôi (2 dòng) -->
<section class="home-section" aria-label="<?php esc_attr_e( 'Về chúng tôi', 'mozlex' ); ?>">
	<div class="shell about-grid">
		<div class="about-text">
			<div class="about-logo-row" style="display:flex; align-items:center; gap:16px; margin-bottom:18px; flex-direction:row-reverse; justify-content:flex-end;">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-dt226.png' ); ?>" alt="Đức Trí 226" class="about-logo" width="400" height="400" loading="lazy" decoding="async" style="height:84px; width:auto; flex-shrink:0; object-fit:contain; filter:drop-shadow(0 2px 6px rgba(0,0,0,0.08));">
				<div style="text-align:right;">
					<p class="sub-title" style="margin:0; line-height:1.1;"><?php esc_html_e( 'ĐỨC TRÍ 226', 'mozlex' ); ?></p>
					<h2 style="margin:0; line-height:1.1;"><?php esc_html_e( 'VỀ CHÚNG TÔI', 'mozlex' ); ?></h2>
				</div>
			</div>
			<p><?php esc_html_e( 'Đức Trí 226 tự hào là nhà phân phối chính hãng của Mozlex, mang đến cho khách hàng những sản phẩm khóa thông minh, khóa tay gạt từ các thương hiệu hàng đầu thế giới.', 'mozlex' ); ?></p>
			<p><?php esc_html_e( 'Chúng tôi cam kết mang đến sản phẩm chính hãng, chất lượng vượt trội cùng dịch vụ tư vấn, lắp đặt và bảo hành chuyên nghiệp.', 'mozlex' ); ?></p>
			<a class="btn btn-ink" href="<?php echo esc_url( home_url( '/ve-mozlex/' ) ); ?>" style="text-transform:uppercase"><?php esc_html_e( 'XEM THÊM', 'mozlex' ); ?> <span class="arrow">&rarr;</span></a>
		</div>
		<div class="about-video">
			<div class="video-placeholder">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
			</div>
		</div>
	</div>
</section>

<!-- STATS COUNTERS — Dark parallax -->
<section class="home-section dark stats-section" aria-label="<?php esc_attr_e( 'Thống kê', 'mozlex' ); ?>">
	<div class="stats-bg" style="background-image:url('<?php echo esc_url( get_template_directory_uri() . '/assets/img/testimonials-bg.jpg' ); ?>')"></div>
	<div class="stats-overlay"></div>
	<div class="shell">
		<div class="stats-grid">
			<div class="stat-item">
				<span class="stat-icon">
					<svg viewBox="0 0 40 40" fill="none" stroke="#c9a381" stroke-width="1.5"><rect x="4" y="8" width="32" height="24" rx="2"/><path d="M4 14h32M12 8V4M28 8V4"/></svg>
				</span>
				<p class="stat-number" data-count="13" data-suffix="+">0</p>
				<p class="stat-label">Năm kinh nghiệm</p>
			</div>
			<div class="stat-item">
				<span class="stat-icon">
					<svg viewBox="0 0 40 40" fill="none" stroke="#c9a381" stroke-width="1.5"><rect x="6" y="4" width="28" height="32" rx="2"/><path d="M14 12h12M14 20h12M14 28h8"/></svg>
				</span>
				<p class="stat-number" data-count="200" data-suffix="+">0</p>
				<p class="stat-label"><?php esc_html_e( 'Sản phẩm cao cấp', 'mozlex' ); ?></p>
			</div>
			<div class="stat-item">
				<span class="stat-icon">
					<svg viewBox="0 0 40 40" fill="none" stroke="#c9a381" stroke-width="1.5"><circle cx="20" cy="16" r="6"/><path d="M20 22c-8 0-14 4-14 8v2h28v-2c0-4-6-8-14-8z"/><path d="M30 10l4-4M30 6h4M34 6v4"/></svg>
				</span>
				<p class="stat-number" data-count="34" data-suffix="">0</p>
				<p class="stat-label"><?php esc_html_e( 'Tỉnh thành phủ sóng', 'mozlex' ); ?></p>
			</div>
			<div class="stat-item">
				<span class="stat-icon">
					<svg viewBox="0 0 40 40" fill="none" stroke="#c9a381" stroke-width="1.5"><path d="M20 6l4 8 8 1-6 6 1 8-8-4-8 4 1-8-6-6 8-1z"/><path d="M12 14l3 3 5-5" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
				<p class="stat-number" data-count="50" data-suffix="+">0</p>
				<p class="stat-label"><?php esc_html_e( 'Dịch vụ chất lượng'); ?></p>
			</div>
		</div>
	</div>
</section>

<?php if ( '1' === mozlex_opt( 'show_featured', '1' ) ) : ?>
<!-- FEATURED PRODUCTS — chỉ khóa thông minh, custom ở Mozlex → Cấu hình -->
<section class="home-section" aria-labelledby="featured-title">
	<div class="shell">
		<header class="section-header section-header-row">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Khóa thông minh', 'mozlex' ); ?></p>
				<h2 class="section-title" id="featured-title"><?php echo esc_html( $featured_title ); ?></h2>
			</div>
			<?php
			$featured_link = get_post_type_archive_link( 'product' );
			if ( ! empty( $featured_cat ) ) {
				$term = get_term_by( 'slug', $featured_cat, 'product_category' );
				if ( $term && ! is_wp_error( $term ) ) $featured_link = get_term_link( $term );
			}
			?>
			<a class="text-link" href="<?php echo esc_url( $featured_link ); ?>"><?php esc_html_e( 'Tất cả sản phẩm', 'mozlex' ); ?> <span class="arrow">&rarr;</span></a>
		</header>

		<div class="product-grid grid-4">
			<?php
			while ( $featured->have_posts() ) :
				$featured->the_post();
				get_template_part( 'template-parts/product-card' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- CONSULT WIZARD TEASER -->
<section class="home-section" aria-labelledby="wizard-title">
	<div class="shell wizard-teaser">
		<div>
			<p class="eyebrow"><?php esc_html_e( 'Consultation wizard', 'mozlex' ); ?></p>
			<h2 class="section-title" id="wizard-title"><?php esc_html_e( 'Tìm đúng mẫu khóa cho đúng cánh cửa', 'mozlex' ); ?></h2>
			<p class="section-intro"><?php esc_html_e( '5 bước giúp bạn chọn được mẫu khóa phù hợp với loại cửa và nhu cầu của mình.', 'mozlex' ); ?></p>
			<a class="btn btn-ink" href="<?php echo esc_url( home_url( '/tu-van/' ) ); ?>" style="text-transform:uppercase"><?php esc_html_e( 'BẮT ĐẦU TƯ VẤN', 'mozlex' ); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>