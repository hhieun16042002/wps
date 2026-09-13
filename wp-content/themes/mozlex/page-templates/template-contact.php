<?php
/**
 * Template Name: Liên hệ
 * Form POST → admin-post.php (inc/contact-handler.php): nonce + honeypot + server validation.
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header();

$contact_status = isset( $_GET['contact'] ) ? sanitize_key( $_GET['contact'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- chỉ hiển thị trạng thái.
$prefill_model  = isset( $_GET['san-pham'] ) ? sanitize_text_field( wp_unslash( $_GET['san-pham'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$fields = array(
	'hotline' => mozlex_opt( 'hotline' ),
	'zalo'    => mozlex_opt( 'zalo' ),
	'email'   => mozlex_opt( 'email', get_option( 'admin_email' ) ),
	'address' => mozlex_opt( 'address' ),
);
?>
<?php $contact_bg = mozlex_opt('contact_banner_image', '');
if ( ! $contact_bg || false !== strpos( $contact_bg, 'photo-1497366216548' ) ) { $contact_bg = get_template_directory_uri() . '/assets/img/banner-lien-he.jpg'; } ?>
<section class="contact-hero" style="position:relative; height:300px; display:grid; place-items:center; overflow:hidden; background:#0a2a5e;">
	<img src="<?php echo esc_url($contact_bg); ?>" alt="Liên hệ" style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0.35;">
	<div style="position:relative; z-index:1; text-align:center; color:#fff; padding:0 16px;">
		<h1 style="margin:0; font-size:clamp(1.8rem,4vw,2.6rem); font-weight:800; color:#fff;"><?php the_title(); ?></h1>
		<p style="margin:8px auto 0; max-width:60ch; color:rgba(255,255,255,0.85);">Kết nối với Đức Trí 226 — tư vấn tận tâm, phản hồi trong 30 phút.</p>
	</div>
</section>
<section class="shell" style="padding:16px 0 0;">
	<?php mozlex_breadcrumb(); ?>
</section>

<section class="shell contact-grid">
	<div class="contact-form-wrap">
		<?php if ( 'success' === $contact_status ) : ?>
			<div class="form-state success" role="status">
				<p><strong><?php esc_html_e( 'Đã gửi yêu cầu thành công.', 'mozlex' ); ?></strong></p>
				<p><?php esc_html_e( 'Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.', 'mozlex' ); ?></p>
			</div>
		<?php elseif ( 'error' === $contact_status ) : ?>
			<div class="form-state error" role="alert">
				<p><?php echo esc_html( isset( $_GET['reason'] ) ? urldecode( sanitize_text_field( wp_unslash( $_GET['reason'] ) ) ) : __( 'Có lỗi xảy ra, vui lòng thử lại.', 'mozlex' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?></p>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="contact-form contact-form--pro" novalidate>
			<input type="hidden" name="action" value="mozlex_contact">
			<?php wp_nonce_field( 'mozlex_contact', 'mozlex_contact_nonce' ); ?>
			<p class="hp-field" aria-hidden="true"><label>Company <input type="text" name="company" tabindex="-1" autocomplete="off"></label></p>

			<div class="contact-form-grid">
				<label>
					<span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 12a4 4 0 100-8 4 4 0 000 8z"/><path d="M4 20c0-4 3.5-6 8-6s8 2 8 6"/></svg> Họ tên <abbr title="<?php esc_attr_e( 'bắt buộc', 'mozlex' ); ?>">*</abbr></span>
					<input type="text" name="name" required maxlength="100" pattern="[^\d]{2,}" placeholder="Nguyễn Văn A" title="<?php esc_attr_e( 'Vui lòng nhập họ tên', 'mozlex' ); ?>">
				</label>
				<label>
					<span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M22 16.9v3a2 2 0 01-2.2 2A19.8 19.8 0 013 8.2 2 2 0 015 6h3a1 1 0 011 .7l1.2 4a1 1 0 01-.3 1L8.5 13a16 16 0 006 6l1.3-1.4a1 1 0 011-.3l4 1.2a1 1 0 01.7 1z"/></svg> Số điện thoại *</span>
					<input type="tel" name="phone" required inputmode="tel" autocomplete="tel" placeholder="0901 234 567" pattern="(\+?84|0)[0-9\s\.\-]{8,12}" title="<?php esc_attr_e( 'VD: 0901234567', 'mozlex' ); ?>">
				</label>
			</div>
			<div class="contact-form-grid">
				<label>
					<span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="M4 9h16M9 4v16"/></svg> Email <abbr title="<?php esc_attr_e( 'bắt buộc', 'mozlex' ); ?>">*</abbr></span>
					<input type="email" name="email" required maxlength="120" placeholder="ban@congty.com" autocomplete="email">
				</label>
				<label>
					<span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 21s7-5 7-11a7 7 0 00-14 0c0 6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg> Khu vực</span>
					<input type="text" name="area" maxlength="100" placeholder="VD: Hà Nội, TP.HCM">
				</label>
			</div>
			<label class="contact-product-wrap" style="position:relative;">
				<span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 2l7 4v8l-7 4-7-4V6z"/><path d="M12 12v8"/><path d="M5 6l7 4 7-4"/></svg> Sản phẩm quan tâm <small style="font-weight:400; text-transform:none; letter-spacing:0; color:#6b6b6b;">— gõ để gợi ý, click để xem danh sách</small></span>
				<input type="text" name="product" id="contact-product" value="<?php echo esc_attr( $prefill_model ); ?>" maxlength="150" placeholder="VD: DS01, KS01, A16, Khóa thông minh…" autocomplete="off" aria-autocomplete="list" aria-controls="contact-product-suggest" aria-expanded="false">
				<ul id="contact-product-suggest" class="contact-suggest-list" role="listbox" hidden></ul>
			</label>
			<label>
				<span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 01-8.5 8.5H12a8.5 8.5 0 01-8.5-8.5A8.5 8.5 0 0112 3a8.5 8.5 0 019 8.5z"/><path d="M8 12h8M12 8v8"/></svg> Nhu cầu</span>
				<textarea name="needs" rows="4" maxlength="2000" placeholder="VD: Cần tư vấn khóa cho cửa gỗ 4 cánh, độ dày 40mm, màu PVD…"></textarea>
			</label>

			<button type="submit" class="btn btn-ink contact-submit" style="width:100%; justify-content:center; padding:14px; font-size:0.88rem; letter-spacing:0.08em; text-transform:uppercase; white-space:nowrap;"><?php esc_html_e( 'GỬI YÊU CẦU — PHẢN HỒI TRONG 30 PHÚT', 'mozlex' ); ?> <span aria-hidden="true">→</span></button>
		<style>
		@media (max-width:480px){
			.contact-submit{ font-size:0.72rem !important; letter-spacing:0.04em !important; padding:13px 8px !important; }
		}
		</style>
			<p style="font-size:0.78rem; color:#6b6b6b; text-align:center; margin:8px 0 0;">Bảo mật: email & SĐT chỉ dùng để tư vấn, không chia sẻ.</p>
		</form>
		<script>
		(function(){
			var input=document.getElementById('contact-product');
			var list=document.getElementById('contact-product-suggest');
			if(!input||!list||!window.MozlexData) return;
			var timer=null, activeIdx=-1;
			function showList(show){ list.hidden=!show; input.setAttribute('aria-expanded', show?'true':'false'); }
			function esc(s){ return String(s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];}); }
			function render(items, q){
				activeIdx=-1;
				if(!items||!items.length){ list.innerHTML='<li class="contact-suggest-empty">Không tìm thấy — thử từ khóa khác</li>'; showList(true); return; }
				var qlow=(q||'').toLowerCase();
				list.innerHTML=items.slice(0,6).map(function(it){
					var name=esc(it.model||it.title);
					var idx=name.toLowerCase().indexOf(qlow);
					if(idx>=0&&qlow) name=esc((it.model||it.title).slice(0,idx))+'<mark>'+esc((it.model||it.title).slice(idx,idx+qlow.length))+'</mark>'+esc((it.model||it.title).slice(idx+qlow.length));
					var thumb=it.thumb?'<img src="'+encodeURI(it.thumb)+'" alt="" loading="lazy">':'<span class="thumb-ph">'+esc((it.model||'').slice(0,2))+'</span>';
					var cat=it.category?'<span>'+esc(it.category)+'</span>':'';
					return '<li role="option"><button type="button" class="contact-suggest-item" data-value="'+esc(it.model||it.title)+'"><span class="contact-suggest-thumb">'+thumb+'</span><span class="contact-suggest-body"><span class="contact-suggest-name">'+name+'</span><span class="contact-suggest-meta">'+cat+'</span></span></button></li>';
				}).join('');
				showList(true);
			}
			function fetchSuggest(q){
				var url=MozlexData.restUrl+'products?s='+encodeURIComponent(q||'khoa')+'&per_page=6';
				fetch(url,{headers:{'X-WP-Nonce':MozlexData.nonce}}).then(function(r){return r.json();}).then(function(data){
					var items=data.items||data; render(items,q);
				}).catch(function(){ list.innerHTML='<li class="contact-suggest-empty">Lỗi tải gợi ý</li>'; showList(true); });
			}
			input.addEventListener('focus', function(){ var q=input.value.trim(); fetchSuggest(q); });
			input.addEventListener('click', function(){ var q=input.value.trim(); fetchSuggest(q); });
			input.addEventListener('input', function(){
				clearTimeout(timer);
				var q=input.value.trim();
				if(!q){ fetchSuggest(''); return; }
				if(q.length<1){ showList(false); return; }
				timer=setTimeout(function(){ fetchSuggest(q); }, 260);
			});
			list.addEventListener('click', function(e){
				var btn=e.target.closest('.contact-suggest-item');
				if(!btn) return;
				input.value=btn.getAttribute('data-value')||'';
				showList(false); input.focus();
			});
			input.addEventListener('keydown', function(e){
				var items=list.querySelectorAll('.contact-suggest-item');
				if(!items.length||list.hidden) return;
				if(e.key==='ArrowDown'){ e.preventDefault(); activeIdx=Math.min(activeIdx+1,items.length-1); items.forEach(function(el,i){el.classList.toggle('is-active',i===activeIdx);}); }
				else if(e.key==='ArrowUp'){ e.preventDefault(); activeIdx=Math.max(activeIdx-1,0); items.forEach(function(el,i){el.classList.toggle('is-active',i===activeIdx);}); }
				else if(e.key==='Enter'&&activeIdx>=0){ e.preventDefault(); items[activeIdx].click(); }
				else if(e.key==='Escape'){ showList(false); }
			});
			document.addEventListener('click', function(e){ if(!e.target.closest('.contact-product-wrap')) showList(false); });
		})();
		</script>
	</div>

	<aside class="contact-info" aria-label="<?php esc_attr_e( 'Thông tin liên hệ', 'mozlex' ); ?>">
		<h2 class="section-title sm"><?php esc_html_e( 'Liên hệ trực tiếp', 'mozlex' ); ?></h2>
		<?php $company_name = mozlex_opt( 'company_name' ); if ( $company_name ) : ?>
			<p style="font-weight:700; margin-bottom:12px; color:var(--dark);"><?php echo esc_html( $company_name ); ?></p>
		<?php endif; ?>
		<dl class="info-list">
			<?php if ( $fields['hotline'] ) : ?>
				<dt>SĐT</dt><dd><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $fields['hotline'] ) ); ?>"><?php echo esc_html( $fields['hotline'] ); ?></a></dd>
			<?php endif; ?>
			<?php if ( $fields['zalo'] ) : ?>
				<dt>Zalo</dt><dd><a href="https://zalo.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $fields['zalo'] ) ); ?>" rel="noopener"><?php echo esc_html( $fields['zalo'] ); ?></a></dd>
			<?php endif; ?>
			<?php if ( $fields['email'] ) : ?>
				<dt>Email</dt><dd><a href="mailto:<?php echo esc_attr( antispambot( $fields['email'] ) ); ?>"><?php echo esc_html( antispambot( $fields['email'] ) ); ?></a></dd>
			<?php endif; ?>
			<?php if ( $fields['address'] ) : ?>
				<dt>Địa chỉ</dt><dd><?php echo esc_html( $fields['address'] ); ?></dd>
			<?php endif; ?>
			<?php $mst = mozlex_opt( 'mst' ); if ( $mst ) : ?>
				<dt>MST</dt><dd><?php echo esc_html( $mst ); ?></dd>
			<?php endif; ?>
			<?php $bank = mozlex_opt( 'bank_account' ); if ( $bank ) : ?>
				<dt>Tài khoản</dt><dd style="font-size:0.9rem;"><?php echo esc_html( $bank ); ?></dd>
			<?php endif; ?>
		</dl>
		<div class="contact-map" style="margin-top:20px; border-radius:12px; overflow:hidden; border:1px solid rgba(0,0,0,0.08);">
			<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3723.7895730058494!2d105.7875581!3d21.0411041!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab3418ca0afb%3A0x667f6c41a863a5b9!2zMjYgTmcuIDI0IFAuIFBoYW4gVsSDbiBUcsaw4budbmcsIEPhuqd1IEdp4bqleSwgSMOgIE7hu5lpLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1788359118923!5m2!1svi!2s" width="100%" height="350" style="border:0; display:block;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" title="Bản đồ 26 ngõ 24 Phan Văn Trường"></iframe>
		</div>
		<?php the_content(); ?>
	</aside>
</section>
<?php get_footer(); ?>
