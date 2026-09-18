<?php
/**
 * Site header — DT226 brand trái, nav giữa, search dropdown premium.
 *
 * @package mozlex
 */

declare( strict_types=1 );

use function Mozlex\c;

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( is_front_page() ? 'header-transparent' : '' ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Tới nội dung chính', 'mozlex' ); ?></a>

<div class="top-bar" role="banner" aria-label="<?php esc_attr_e( 'Thông báo', 'mozlex' ); ?>">
	<div class="shell top-bar-inner">
		<span class="top-bar-text"><span class="top-bar-star" aria-hidden="true">★</span> <?php esc_html_e( 'Đức Trí 226 - Xây uy tín - Dựng niềm tin', 'mozlex' ); ?></span>
		<div class="top-bar-meta">
			<span class="top-bar-item top-bar-support"><span class="top-bar-dot" aria-hidden="true"></span> <?php esc_html_e( 'Hỗ trợ 24/7', 'mozlex' ); ?></span>
			<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', mozlex_opt( 'hotline', '0355514686' ) ) ); ?>" class="top-bar-link">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
				<span><?php echo esc_html( mozlex_opt( 'hotline', '0355514686' ) ); ?></span>
			</a>
		</div>
	</div>
</div>

<header class="site-header" id="site-header" data-elevate>
	<div class="shell header-inner">
		<a class="brand brand-dt226" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Đức Trí 226">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-dt226.png' ); ?>" alt="Đức Trí 226" class="brand-logo" width="120" height="52" loading="eager" decoding="async">
		</a>

		<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Menu chính', 'mozlex' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'nav-list',
				'depth'          => 0,
				'fallback_cb'    => 'mozlex_default_nav',
			) );
			?>
		</nav>

		<div class="header-actions">
			<div class="header-search" id="header-search" data-search-root>
				<button type="button" class="search-toggle" id="search-toggle"
					aria-expanded="false" aria-controls="header-search-panel" aria-label="<?php esc_attr_e( 'Tìm kiếm sản phẩm', 'mozlex' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
					<span class="sr-only"><?php esc_html_e( 'Tìm kiếm', 'mozlex' ); ?></span>
				</button>
				<div class="search-dropdown" id="header-search-panel" hidden>
					<form role="search" method="get" class="search-dropdown-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-autocomplete>
						<label class="sr-only" for="s-field"><?php esc_html_e( 'Từ khóa tìm kiếm', 'mozlex' ); ?></label>
						<div class="search-input-wrap">
							<svg class="search-input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
							<input type="search" id="s-field" name="s"
								placeholder="<?php esc_attr_e( 'Tìm model, tính năng hoặc danh mục…', 'mozlex' ); ?>"
								data-suggest-target="#suggest-box" autocomplete="off">
							<span class="search-spinner" aria-hidden="true" hidden></span>
							<button type="button" class="search-clear-btn" aria-label="<?php esc_attr_e( 'Xóa từ khóa', 'mozlex' ); ?>" hidden>&times;</button>
						</div>
						<input type="hidden" name="post_type" value="product">
						<button type="submit" class="btn btn-ink btn-search-submit"><?php esc_html_e( 'Tìm', 'mozlex' ); ?></button>
					</form>
					<?php $search_cats = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => true, 'number' => 6 ) ); if ( $search_cats && ! is_wp_error( $search_cats ) ) : ?>
						<div class="search-filter-pills" role="group" aria-label="<?php esc_attr_e( 'Lọc theo danh mục', 'mozlex' ); ?>">
							<button type="button" class="search-cat-pill is-active" data-search-cat=""><?php esc_html_e( 'Tất cả', 'mozlex' ); ?></button>
							<?php foreach ( $search_cats as $sc ) : ?>
								<button type="button" class="search-cat-pill" data-search-cat="<?php echo esc_attr( $sc->slug ); ?>"><?php echo esc_html( $sc->name ); ?></button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<p id="search-status" class="sr-only" role="status"></p>
					<div class="search-suggest-panel" id="suggest-panel" hidden>
						<div class="suggest-cats-wrap" id="suggest-cats" hidden></div>
						<ul class="search-suggest-list" id="suggest-box" aria-label="<?php esc_attr_e( 'Gợi ý sản phẩm', 'mozlex' ); ?>"></ul>
						<div class="suggest-footer" id="suggest-footer" hidden></div>
					</div>
					<div class="suggest-hints">
						<span class="suggest-hints-label"><?php esc_html_e( 'Gợi ý:', 'mozlex' ); ?></span>
						<?php
						$quick_hints = function_exists( 'mozlex_get_popular_search_hints' ) ? mozlex_get_popular_search_hints() : array();
						foreach ( $quick_hints as $h ) :
							$h_term = is_array( $h ) ? $h['term'] : $h;
							$h_lbl  = is_array( $h ) ? $h['label'] : $h;
						?>
							<a href="<?php echo esc_url( add_query_arg( array( 's' => $h_term, 'post_type' => 'product' ), home_url( '/' ) ) ); ?>"><?php echo esc_html( $h_lbl ); ?></a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<?php $header_hotline = mozlex_opt( 'hotline' ); ?>
			<?php if ( $header_hotline ) : ?>
				<a class="header-hotline-btn" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $header_hotline ) ); ?>" aria-label="Hotline <?php echo esc_attr( $header_hotline ); ?>">
					<span class="hotline-icon" aria-hidden="true">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
					</span>
					<span class="hotline-text"><?php echo esc_html( $header_hotline ); ?></span>
				</a>
			<?php else : ?>
				<a class="btn btn-line header-cta" href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>"><?php esc_html_e( 'Liên hệ', 'mozlex' ); ?></a>
			<?php endif; ?>
			<button type="button" class="nav-toggle" data-nav-toggle aria-expanded="false" aria-controls="mobile-drawer">
				<span class="nav-toggle-bar"></span><span class="nav-toggle-bar"></span><span class="nav-toggle-bar"></span>
				<span class="sr-only"><?php esc_html_e( 'Menu', 'mozlex' ); ?></span>
			</button>
		</div>
	</div>
</header>

<div class="mobile-drawer" id="mobile-drawer" hidden>
	<div class="drawer-backdrop" data-drawer-close aria-hidden="true"></div>
	<div class="drawer-panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu điều hướng', 'mozlex' ); ?>">
		<div class="drawer-head">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="drawer-brand">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-dt226.png' ); ?>" alt="Đức Trí 226" class="drawer-logo" width="36" height="36">
			</a>
			<button type="button" class="drawer-close" data-drawer-close aria-label="<?php esc_attr_e( 'Đóng menu', 'mozlex' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
			</button>
		</div>
		<div class="drawer-search">
			<form role="search" method="get" class="drawer-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="sr-only" for="drawer-s"><?php esc_html_e( 'Tìm kiếm sản phẩm', 'mozlex' ); ?></label>
				<div class="drawer-search-wrap">
					<svg class="drawer-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
					<input type="search" id="drawer-s" name="s" placeholder="<?php esc_attr_e( 'Tìm model, khóa thông minh…', 'mozlex' ); ?>" autocomplete="off">
					<input type="hidden" name="post_type" value="product">
					<button type="submit" class="drawer-search-btn"><?php esc_html_e( 'Tìm', 'mozlex' ); ?></button>
				</div>
			</form>
		</div>
		<!-- 3 Tabs cho menu di động: Menu | Danh mục | Liên hệ -->
		<div class="drawer-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Tabs menu di động', 'mozlex' ); ?>">
			<button type="button" class="drawer-tab-btn is-active" role="tab" aria-selected="true" data-drawer-tab="menu">
				<span><?php esc_html_e( 'Menu', 'mozlex' ); ?></span>
			</button>
			<button type="button" class="drawer-tab-btn" role="tab" aria-selected="false" data-drawer-tab="categories">
				<span><?php esc_html_e( 'Danh mục', 'mozlex' ); ?></span>
			</button>
			<button type="button" class="drawer-tab-btn" role="tab" aria-selected="false" data-drawer-tab="contact">
				<span><?php esc_html_e( 'Liên hệ', 'mozlex' ); ?></span>
			</button>
		</div>

		<!-- Panel 1: Menu chính -->
		<div class="drawer-tab-panel is-active" data-drawer-panel="menu">
			<nav class="drawer-nav-wrap" aria-label="<?php esc_attr_e( 'Menu di động', 'mozlex' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'drawer-list',
					'depth'          => 0,
					'fallback_cb'    => 'mozlex_default_nav',
				) );
				?>
			</nav>
			<div class="drawer-contact-mini" style="padding:14px 20px; border-top:1px solid rgba(255,255,255,0.08);">
				<div class="drawer-contact-actions">
					<a class="btn btn-drawer-call" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', mozlex_opt( 'hotline', '0355514686' ) ) ); ?>">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
						<span><?php echo esc_html( mozlex_opt( 'hotline', '0355514686' ) ); ?></span>
					</a>
					<?php if ( mozlex_opt( 'zalo' ) || mozlex_opt( 'hotline' ) ) : ?>
						<a class="btn btn-drawer-zalo" href="<?php echo esc_url( 'https://zalo.me/' . preg_replace( '/[^0-9]/', '', mozlex_opt( 'zalo' ) ?: mozlex_opt( 'hotline' ) ) ); ?>" target="_blank" rel="noopener">
							<span>Zalo</span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Panel 2: Danh mục sản phẩm -->
		<div class="drawer-tab-panel" data-drawer-panel="categories" hidden>
			<nav class="drawer-nav-wrap" aria-label="<?php esc_attr_e( 'Danh mục sản phẩm', 'mozlex' ); ?>">
				<ul class="drawer-list drawer-cat-list">
					<?php
					$drawer_cats = get_terms( array(
						'taxonomy'   => 'product_category',
						'hide_empty' => false,
						'orderby'    => 'count',
						'order'      => 'DESC',
					) );
					if ( ! is_wp_error( $drawer_cats ) && ! empty( $drawer_cats ) ) :
						foreach ( $drawer_cats as $dcat ) :
							if ( $dcat->count == 0 && ! in_array( $dcat->slug, array( 'khoa-cua-thong-minh', 'khoa-cua-thong-phong', 'cua-chong-chay', 'camera-an-ninh' ), true ) ) {
								continue;
							}
							?>
							<li>
								<a href="<?php echo esc_url( get_term_link( $dcat ) ); ?>" class="drawer-cat-link" style="display:flex; align-items:center; justify-content:space-between;">
									<span><?php echo esc_html( $dcat->name ); ?></span>
									<?php if ( $dcat->count > 0 ) : ?>
										<span style="font-size:0.75rem; background:rgba(201,163,129,0.18); color:var(--primary,#c9a381); padding:2px 8px; border-radius:999px;"><?php echo esc_html( (string) $dcat->count ); ?></span>
									<?php endif; ?>
								</a>
							</li>
							<?php
						endforeach;
					endif;
					?>
				</ul>
			</nav>
		</div>

		<!-- Panel 3: Liên hệ & Hỗ trợ -->
		<div class="drawer-tab-panel" data-drawer-panel="contact" hidden>
			<div class="drawer-contact" style="border-top:none; padding:16px 20px;">
				<div class="drawer-contact-actions">
					<a class="btn btn-drawer-call" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', mozlex_opt( 'hotline', '0355514686' ) ) ); ?>">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
						<span>Gọi ngay: <?php echo esc_html( mozlex_opt( 'hotline', '0355514686' ) ); ?></span>
					</a>
					<?php if ( mozlex_opt( 'zalo' ) || mozlex_opt( 'hotline' ) ) : ?>
						<a class="btn btn-drawer-zalo" href="<?php echo esc_url( 'https://zalo.me/' . preg_replace( '/[^0-9]/', '', mozlex_opt( 'zalo' ) ?: mozlex_opt( 'hotline' ) ) ); ?>" target="_blank" rel="noopener">
							<span>Nhắn tin Zalo</span>
						</a>
					<?php endif; ?>
				</div>
				<div style="margin-top:16px; font-size:0.86rem; color:rgba(255,255,255,0.72); line-height:1.6;">
					<p style="margin:0 0 10px 0;"><strong style="color:#fff;">🏢 Văn phòng & Showroom:</strong><br><?php echo esc_html( mozlex_opt( 'address', 'Số 26 ngõ 24 Phan Văn Trường, Dịch Vọng Hậu, Cầu Giấy, Hà Nội' ) ); ?></p>
					<?php if ( mozlex_opt( 'email' ) ) : ?>
						<p style="margin:0 0 10px 0;"><strong style="color:#fff;">✉️ Email:</strong> <a href="mailto:<?php echo esc_attr( antispambot( mozlex_opt( 'email' ) ) ); ?>" style="color:var(--primary,#c9a381);"><?php echo esc_html( antispambot( mozlex_opt( 'email' ) ) ); ?></a></p>
					<?php endif; ?>
					<p style="margin:0 0 10px 0;"><strong style="color:#fff;">⏱ Giờ làm việc:</strong><br>8:00 – 18:00 (Thứ 2 – Thứ 7)</p>
					<p style="margin:0; font-size:0.8rem; color:rgba(255,255,255,0.45);"><?php echo esc_html( mozlex_opt( 'company_name', 'Công ty TNHH xây dựng thương mại và công nghệ Đức Trí 226' ) ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>

<main id="main" class="site-main">
