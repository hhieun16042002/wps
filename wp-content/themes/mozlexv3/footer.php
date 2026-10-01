<?php
/**
 * Modern Corporate Footer — Mozlex V3 (Đức Trí 226).
 *
 * @package mozlex
 */

declare( strict_types=1 );

$hotline  = mozlex_opt( 'hotline', '0355514686' );
$zalo     = mozlex_opt( 'zalo' );
$email    = mozlex_opt( 'email', get_option( 'admin_email' ) );
$address  = mozlex_opt( 'address', 'Số 26 ngõ 24 Phan Văn Trường, Dịch Vọng Hậu, Cầu Giấy, Hà Nội' );
$company  = mozlex_opt( 'company_name', 'Công ty TNHH xây dựng thương mại và công nghệ Đức Trí 226' );
$mst      = mozlex_opt( 'mst', '0111015957' );
$bank     = mozlex_opt( 'bank_account', '1355514686 - Ngân hàng Techcombank chi nhánh Thăng Long' );
$hotline_clean = preg_replace( '/[^0-9+]/', '', $hotline );
?>
</main><!-- #main -->

<footer class="site-footer v3-corporate-footer" style="background:#24201d; color:#faf7f2; border-top:1px solid #38322c; padding-top:64px;">
	<div class="shell">
		<!-- Top Branding row -->
		<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:32px; flex-wrap:wrap; padding-bottom:48px; border-bottom:1px solid rgba(255,255,255,0.08);">
			<div style="max-width:440px;">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:inline-flex; align-items:center; gap:12px; text-decoration:none; margin-bottom:14px;">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-dt226.png' ); ?>" alt="Đức Trí 226" width="130" height="50" style="object-fit:contain;">
					<span style="font-family:var(--font-heading); font-size:18px; font-weight:800; color:#fff; letter-spacing:-0.02em;">ĐỨC TRÍ 226</span>
				</a>
				<p style="font-size:14px; line-height:1.65; color:rgba(255,255,255,0.65); margin:0;">
					Đơn vị tư vấn, cung cấp và thi công trọn gói trong 3 lĩnh vực cốt lõi: Xây dựng, Thương mại và Công nghệ. Tổng đại lý phân phối chính thức khóa thông minh Mozlex tại Việt Nam.
				</p>
			</div>
			<div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
				<a href="tel:<?php echo esc_attr( $hotline_clean ); ?>" class="btn btn-outline" style="color:#ffffff !important; border-color:#333;">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
					<span>Hotline: <?php echo esc_html( $hotline ); ?></span>
				</a>
				<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="btn btn-accent">
					<span>Liên hệ hợp tác</span> &rarr;
				</a>
			</div>
		</div>

		<!-- 4 Columns Grid -->
		<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:40px; padding:48px 0; border-bottom:1px solid rgba(255,255,255,0.08);">
			<!-- Col 1: KHÁM PHÁ -->
			<div>
				<h3 style="font-family:var(--font-heading); font-size:14px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:var(--color-accent); margin:0 0 18px;">KHÁM PHÁ</h3>
				<ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px; font-size:14px;">
					<li><a href="<?php echo esc_url( home_url( '/ve-mozlex/' ) ); ?>" style="color:rgba(255,255,255,0.7); text-decoration:none; transition:color 160ms;">Về chúng tôi</a></li>
					<li><a href="<?php echo esc_url( home_url( '/san-pham/' ) ); ?>" style="color:rgba(255,255,255,0.7); text-decoration:none; transition:color 160ms;">Sản phẩm chính hãng</a></li>
					<li><a href="<?php echo esc_url( home_url( '/linh-vuc-hoat-dong/' ) ); ?>" style="color:rgba(255,255,255,0.7); text-decoration:none; transition:color 160ms;">Lĩnh vực hoạt động</a></li>
					<li><a href="<?php echo esc_url( home_url( '/du-an/' ) ); ?>" style="color:rgba(255,255,255,0.7); text-decoration:none; transition:color 160ms;">Dự án &amp; Công trình</a></li>
					<li><a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" style="color:rgba(255,255,255,0.7); text-decoration:none; transition:color 160ms;">Tin tức &amp; Kiến thức</a></li>
				</ul>
			</div>

			<!-- Col 2: HỖ TRỢ -->
			<div>
				<h3 style="font-family:var(--font-heading); font-size:14px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:var(--color-accent); margin:0 0 18px;">HỖ TRỢ</h3>
				<ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px; font-size:14px;">
					<li><a href="<?php echo esc_url( home_url( '/bao-hanh/' ) ); ?>" style="color:rgba(255,255,255,0.7); text-decoration:none; transition:color 160ms;">Chính sách bảo hành &amp; đổi trả</a></li>
					<li><a href="<?php echo esc_url( home_url( '/dieu-khoan-bao-mat/' ) ); ?>" style="color:rgba(255,255,255,0.7); text-decoration:none; transition:color 160ms;">Điều khoản và bảo mật</a></li>
					<li><a href="<?php echo esc_url( home_url( '/mua-hang-thanh-toan/' ) ); ?>" style="color:rgba(255,255,255,0.7); text-decoration:none; transition:color 160ms;">Mua hàng và thanh toán</a></li>
					<li><a href="<?php echo esc_url( home_url( '/tu-van/' ) ); ?>" style="color:rgba(255,255,255,0.7); text-decoration:none; transition:color 160ms;">Trợ lý kỹ thuật chọn khóa</a></li>
				</ul>
			</div>

			<!-- Col 3: THÔNG TIN -->
			<div>
				<h3 style="font-family:var(--font-heading); font-size:14px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:var(--color-accent); margin:0 0 18px;">THÔNG TIN</h3>
				<ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px; font-size:13px; color:rgba(255,255,255,0.7); line-height:1.5;">
					<li style="color:#ffffff; font-weight:600;"><?php echo esc_html( $company ); ?></li>
					<li><strong>Địa chỉ:</strong> <?php echo esc_html( $address ); ?></li>
					<li><strong>Hotline:</strong> <a href="tel:<?php echo esc_attr( $hotline_clean ); ?>" style="color:#ffffff;"><?php echo esc_html( $hotline ); ?></a></li>
					<?php if ( $email ) : ?>
						<li><strong>Email:</strong> <a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>" style="color:#ffffff;"><?php echo esc_html( antispambot( $email ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( $mst ) : ?>
						<li><strong>MST:</strong> <?php echo esc_html( $mst ); ?></li>
					<?php endif; ?>
				</ul>
			</div>

			<!-- Col 4: SOCIAL -->
			<div>
				<h3 style="font-family:var(--font-heading); font-size:14px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:var(--color-accent); margin:0 0 18px;">SOCIAL</h3>
				<ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:12px; font-size:14px;">
					<li>
						<a href="<?php echo esc_url( mozlex_opt( 'facebook', 'https://www.facebook.com/mozlex' ) ); ?>" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; gap:8px; color:rgba(255,255,255,0.7); text-decoration:none;">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
							<span>Facebook</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( mozlex_opt( 'youtube', '#' ) ); ?>" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; gap:8px; color:rgba(255,255,255,0.7); text-decoration:none;">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-1.1-.1-2.2-.3-3.3-.2-1-1-1.8-2-2C18.6 6.3 12 6.3 12 6.3s-6.6 0-7.7.4c-1 .2-1.8 1-2 2C2.1 9.8 2 10.9 2 12s.1 2.2.3 3.3c.2 1 1 1.8 2 2 1.1.4 7.7.4 7.7.4s6.6 0 7.7-.4c1-.2 1.8-1 2-2 .2-1.1.3-2.2.3-3.3zM10 15.5v-7l6 3.5-6 3.5z"/></svg>
							<span>Youtube</span>
						</a>
					</li>
					<li>
						<a href="https://zalo.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $zalo ?: $hotline ) ); ?>" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; gap:8px; color:rgba(255,255,255,0.7); text-decoration:none;">
							<span style="display:inline-grid; place-items:center; width:18px; height:18px; background:#0068FF; border-radius:3px; font-weight:800; font-size:9px; color:#fff;">Z</span>
							<span>Chat qua Zalo</span>
						</a>
					</li>
				</ul>
			</div>
		</div>

		<!-- Map Section -->
		<div style="padding:32px 0 24px;">
			<div style="border-radius:8px; overflow:hidden; border:1px solid #222;">
				<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.7895730058494!2d105.7875581!3d21.0411041!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab3418ca0afb%3A0x667f6c41a863a5b9!2zMjYgTmcuIDI0IFAuIFBoYW4gVsSDbiBUcsaw4budbmcsIEPhuqd1IEdp4bqleSwgSMOgIE7hu5lpLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1788359118923!5m2!1svi!2s" width="100%" height="240" style="border:0; display:block;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" title="Bản đồ văn phòng Đức Trí 226"></iframe>
			</div>
		</div>

		<!-- Bottom Copyright Bar -->
		<div style="padding:24px 0 36px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; font-size:13px; color:rgba(255,255,255,0.5);">
			<p style="margin:0;">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $company ); ?>. Mọi quyền được bảo lưu.</p>
			<p style="margin:0;">Thiết kế &amp; Phát triển: Mozlex V3 Architecture</p>
		</div>
	</div>
</footer>

<!-- Floating Contact (Zalo & Hotline) -->
<?php
$zalo_num = preg_replace( '/[^0-9]/', '', $zalo ?: $hotline );
if ( $zalo_num || $hotline_clean ) :
?>
<div class="floating-contact" aria-label="<?php esc_attr_e( 'Liên hệ nhanh', 'mozlex' ); ?>" style="position:fixed; bottom:24px; right:24px; z-index:110; display:flex; flex-direction:column; gap:10px;">
	<?php if ( $zalo_num ) : ?>
		<a href="https://zalo.me/<?php echo esc_attr( $zalo_num ); ?>" target="_blank" rel="noopener" class="floating-btn" style="display:flex; align-items:center; gap:8px; background:#0068FF; color:#fff; text-decoration:none; padding:10px 16px; border-radius:9999px; box-shadow:0 4px 16px rgba(0,104,255,0.4); font-size:13px; font-weight:700;">
			<span style="display:inline-grid; place-items:center; width:20px; height:20px; background:#fff; color:#0068FF; border-radius:50%; font-size:11px; font-weight:800;">Z</span>
			<span>Zalo Chat</span>
		</a>
	<?php endif; ?>
	<?php if ( $hotline_clean ) : ?>
		<a href="tel:<?php echo esc_attr( $hotline_clean ); ?>" class="floating-btn" style="display:flex; align-items:center; gap:8px; background:#111; color:#fff; text-decoration:none; padding:10px 16px; border-radius:9999px; box-shadow:0 4px 16px rgba(0,0,0,0.3); font-size:13px; font-weight:700; border:1px solid #333;">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
			<span><?php echo esc_html( $hotline ); ?></span>
		</a>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
