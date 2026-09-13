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
		<span class="top-bar-text"><span aria-hidden="true">★</span> <?php esc_html_e( 'ĐƠN VỊ PHÂN PHỐI CHÍNH HÃNG MOZLEX', 'mozlex' ); ?></span>
		<?php if ( ! is_front_page() ) : ?>
		<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', mozlex_opt( 'hotline', '0355514686' ) ) ); ?>" class="top-bar-link"><?php echo esc_html( mozlex_opt( 'hotline', '0355514686' ) ); ?> &nbsp;|&nbsp; <?php esc_html_e( 'Tư vấn miễn phí', 'mozlex' ); ?></a>
		<?php endif; ?>
	</div>
</div>

<header class="site-header" id="site-header" data-elevate>
	<div class="shell header-inner">
		<a class="brand brand-dt226" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="DT 226 — Trang chủ">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-dt226.png' ); ?>" alt="DT 226" class="brand-logo" width="400" height="400" loading="eager" decoding="async">
		</a>

		<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Menu chính', 'mozlex' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'nav-list',
				'depth'          => 1,
				'fallback_cb'    => 'mozlex_default_nav',
			) );
			?>
		</nav>

		<div class="header-actions">
			<div class="header-search" id="header-search" data-search-root>
				<button type="button" class="search-toggle" id="search-toggle"
					aria-expanded="false" aria-controls="header-search-panel" aria-label="<?php esc_attr_e( 'Tìm kiếm sản phẩm', 'mozlex' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
					<span class="sr-only"><?php esc_html_e( 'Tìm kiếm', 'mozlex' ); ?></span>
				</button>
				<div class="search-dropdown" id="header-search-panel" hidden>
					<form role="search" method="get" class="search-dropdown-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-autocomplete>
						<label class="sr-only" for="s-field"><?php esc_html_e( 'Từ khóa tìm kiếm', 'mozlex' ); ?></label>
						<input type="search" id="s-field" name="s"
							placeholder="<?php esc_attr_e( 'Tìm model, tính năng hoặc danh mục…', 'mozlex' ); ?>"
							data-suggest-target="#suggest-box" autocomplete="off">
						<input type="hidden" name="post_type" value="product">
						<button type="submit" class="btn btn-ink btn-sm"><?php esc_html_e( 'Tìm', 'mozlex' ); ?></button>
					</form>
					<?php $search_cats = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => true, 'number' => 6 ) ); if ( $search_cats && ! is_wp_error( $search_cats ) ) : ?>
						<div class="search-filter-pills" role="group" aria-label="<?php esc_attr_e( 'Lọc theo danh mục', 'mozlex' ); ?>">
							<button type="button" class="search-cat-pill is-active" data-search-cat=""><?php esc_html_e( 'Tất cả', 'mozlex' ); ?></button>
							<?php foreach ( $search_cats as $sc ) : ?>
								<button type="button" class="search-cat-pill" data-search-cat="<?php echo esc_attr( $sc->slug ); ?>"><?php echo esc_html( $sc->name ); ?></button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<ul class="search-suggest-list" id="suggest-box" role="listbox" aria-label="<?php esc_attr_e( 'Gợi ý sản phẩm', 'mozlex' ); ?>"></ul>
					<p class="suggest-hints">
						<?php foreach ( array( 'A16', 'A8', 'GF300', 'Khóa cơ', 'Face ID', 'PVD' ) as $hint ) : ?>
							<a href="<?php echo esc_url( add_query_arg( array( 's' => $hint, 'post_type' => 'product' ), home_url( '/' ) ) ); ?>"><?php echo esc_html( $hint ); ?></a>
						<?php endforeach; ?>
					</p>
				</div>
			</div>
			<?php $header_hotline = mozlex_opt( 'hotline' ); ?>
			<?php if ( $header_hotline ) : ?>
				<a class="btn btn-line header-cta" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $header_hotline ) ); ?>"><?php echo esc_html( $header_hotline ); ?></a>
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
	<div class="drawer-panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'mozlex' ); ?>">
		<div class="drawer-head">
			<a href="<?php echo esc_url(home_url('/')); ?>" style="display:flex; align-items:center; gap:8px; text-decoration:none; color:inherit;">
				<img src="<?php echo esc_url(get_template_directory_uri().'/assets/img/logo-dt226.png'); ?>" alt="DT 226" style="height:32px; width:auto; object-fit:contain;">
			</a>
			<button type="button" class="drawer-close" data-drawer-close>&times;<span class="sr-only">Đóng</span></button>
		</div>
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'drawer-list',
			'depth'          => 1,
			'fallback_cb'    => 'mozlex_default_nav',
		) );
		?>
	</div>
</div>

<main id="main" class="site-main">
