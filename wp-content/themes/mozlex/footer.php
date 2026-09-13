<?php
/**
 * Footer — 3 cột chuyên nghiệp + bản đồ
 *
 * @package mozlex
 */

declare( strict_types=1 );

$hotline  = mozlex_opt( 'hotline' );
$zalo     = mozlex_opt( 'zalo' );
$email    = mozlex_opt( 'email', get_option( 'admin_email' ) );
$address  = mozlex_opt( 'address' );
$company  = mozlex_opt( 'company_name', 'Công ty TNHH xây dựng thương mại và công nghệ Đức Trí 226' );
$mst      = mozlex_opt( 'mst', '0111015957' );
$bank     = mozlex_opt( 'bank_account', '1355514686 - Ngân hàng Techcombank chi nhánh Thăng Long' );
?>
</main><!-- #main -->

<footer class="site-footer footer-pro">
	<div class="shell footer-pro-grid">
		<div class="footer-pro-col">
			<h3 class="footer-pro-heading">ABOUT US</h3>
			<ul class="footer-pro-list">
				<li><a href="<?php echo esc_url( home_url( '/ve-mozlex/' ) ); ?>">Về chúng tôi</a></li>
				<li><a href="<?php echo esc_url( home_url( '/bao-hanh/' ) ); ?>">Chính sách bảo hành và đổi trả</a></li>
				<li><a href="<?php echo esc_url( home_url( '/dieu-khoan-bao-mat/' ) ); ?>">Điều khoản và bảo mật</a></li>
				<li><a href="<?php echo esc_url( home_url( '/mua-hang-thanh-toan/' ) ); ?>">Mua hàng và thanh toán</a></li>
			</ul>
		</div>
		<div class="footer-pro-col">
			<h3 class="footer-pro-heading">INFORMATION</h3>
			<ul class="footer-pro-list footer-pro-info">
				<li class="footer-pro-company"><?php echo esc_html( $company ); ?> <span style="display:inline-block; margin-left:8px; padding:3px 8px; background:#C97C5D; color:#fff; border-radius:999px; font-size:0.68rem; letter-spacing:0.06em; text-transform:uppercase; font-weight:700; vertical-align:middle;">Phân phối chính hãng</span></li>
				<li><span class="footer-pro-label">Hotline</span> <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $hotline ) ); ?>"><?php echo esc_html( $hotline ); ?></a> <span style="opacity:0.6;">/</span> <a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>" class="footer-pro-email"><?php echo esc_html( antispambot( $email ) ); ?></a></li>
				<li><span class="footer-pro-label">Địa chỉ</span> <?php echo esc_html( $address ?: '26 ngõ 24 Phan Văn Trường, Cầu Giấy, HN' ); ?></li>
			</ul>
		</div>
		<div class="footer-pro-col">
			<h3 class="footer-pro-heading">CONNECT</h3>
			<ul class="footer-pro-list footer-pro-connect">
				<li><a href="<?php echo esc_url( mozlex_opt( 'facebook', 'https://www.facebook.com/mozlex' ) ); ?>" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg> Facebook</a></li>
				<li><a href="<?php echo esc_url( mozlex_opt( 'youtube', '#' ) ); ?>" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12c0-1.1-.1-2.2-.3-3.3-.2-1-1-1.8-2-2C18.6 6.3 12 6.3 12 6.3s-6.6 0-7.7.4c-1 .2-1.8 1-2 2C2.1 9.8 2 10.9 2 12s.1 2.2.3 3.3c.2 1 1 1.8 2 2 1.1.4 7.7.4 7.7.4s6.6 0 7.7-.4c1-.2 1.8-1 2-2 .2-1.1.3-2.2.3-3.3zM10 15.5v-7l6 3.5-6 3.5z"/></svg> Youtube</a></li>
				<li><a href="https://zalo.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $zalo ?: $hotline ) ); ?>" target="_blank" rel="noopener"><span style="display:inline-grid; place-items:center; width:20px; height:20px; background:#0068FF; border-radius:4px; flex-shrink:0;"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" aria-hidden="true"><rect width="24" height="24" rx="6" fill="#0068FF"/><text x="12" y="15.5" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="8" font-weight="800" fill="#fff">Zalo</text></svg></span> Chat với tư vấn viên</a></li>
			</ul>
		</div>
	</div>
	<!-- <div class="shell" style="text-align:center; padding:12px 0; border-top:1px solid rgba(255,255,255,0.08); margin-top:16px;">
		<a href="<?php echo esc_url( home_url( '/?r3d=catalogue-mozlex-2023' ) ); ?>" style="color:#fff; text-decoration:none; font-size:0.86rem; margin:0 8px;">← Catalogue trước</a>
		<a href="<?php echo esc_url( home_url( '/?r3d=catalogue-mozlex-2023&r3d_page=2' ) ); ?>" style="color:#fff; text-decoration:none; font-size:0.86rem; margin:0 8px;">Catalogue sau →</a>
	</div> -->
	<div class="shell footer-pro-map-wrap">
		<div class="footer-pro-map">
			<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.7895730058494!2d105.7875581!3d21.0411041!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab3418ca0afb%3A0x667f6c41a863a5b9!2zMjYgTmcuIDI0IFAuIFBoYW4gVsSDbiBUcsaw4budbmcsIEPhuqd1IEdp4bqleSwgSMOgIE7hu5lpLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1788359118923!5m2!1svi!2s" width="100%" height="300" style="border:0; display:block;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" title="Bản đồ 26 ngõ 24 Phan Văn Trường"></iframe>
		</div>
	</div>
	<div class="shell footer-pro-bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $company ); ?>. Mọi quyền được bảo lưu.</p>
	</div>
</footer>

<?php if ( $hotline && $zalo && wp_is_mobile() ) : ?>
<nav class="mobile-cta" aria-label="<?php esc_attr_e( 'Liên hệ nhanh', 'mozlex' ); ?>">
	<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $hotline ) ); ?>" class="mobile-cta-call">&#9742; <?php esc_html_e( 'Gọi ngay', 'mozlex' ); ?></a>
	<a href="https://zalo.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $zalo ) ); ?>" class="mobile-cta-zalo" rel="noopener">ZALO</a>
</nav>
<?php endif; ?>

<!-- Zalo popup cố định cuối màn hình — dễ bấm, cấu hình trong Mozlex → Zalo/Hotline -->
<?php
$zalo_num = preg_replace( '/[^0-9]/', '', $zalo ?: $hotline );
$hotline_tel = preg_replace( '/[^0-9+]/', '', $hotline );
if ( $zalo_num || $hotline_tel ) :
?>
<div class="floating-contact" aria-label="<?php esc_attr_e( 'Liên hệ nhanh', 'mozlex' ); ?>">
	<?php if ( $zalo_num ) : ?>
	<a href="https://zalo.me/<?php echo esc_attr( $zalo_num ); ?>" target="_blank" rel="noopener" class="floating-btn floating-zalo" aria-label="Zalo">
		<span class="floating-icon" style="background:#fff; color:#0068FF; font-weight:800; font-size:0.72rem; letter-spacing:-0.02em; font-family:Inter,system-ui,sans-serif;">
			<svg viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true" style="display:block;">
				<text x="12" y="16" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="9" font-weight="800" fill="#0068FF" letter-spacing="-0.3">Zalo</text>
			</svg>
		</span>
		<span class="floating-label">Zalo</span>
	</a>
	<?php endif; ?>
	<?php if ( $hotline_tel ) : ?>
	<a href="tel:<?php echo esc_attr( $hotline_tel ); ?>" class="floating-btn floating-phone" aria-label="Gọi hotline">
		<span class="floating-icon">☎</span>
		<span class="floating-label"><?php echo esc_html( $hotline ); ?></span>
	</a>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
