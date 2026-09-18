<?php
/**
 * Trang cài đặt tập trung: hotline/Zalo/email/địa chỉ/warranty/social.
 * Không hard-code vào template.
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_options_page(
		__( 'Mozlex — Cấu hình', 'mozlex' ),
		__( 'Mozlex', 'mozlex' ),
		'manage_options',
		'mozlex-options',
		'mozlex_render_options_page'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'mozlex_options_group', 'mozlex_options', array(
		'type'              => 'array',
		'sanitize_callback' => 'mozlex_sanitize_options',
	) );
} );

function mozlex_option_fields() {
	return array(
		'_section_banner'  => __( 'Banner trang chủ (Hero)', 'mozlex' ),
		'banner_image'     => __( 'Ảnh nền banner (URL)', 'mozlex' ),
		'banner_eyebrow'   => __( 'Dòng nhỏ trên tiêu đề (eyebrow)', 'mozlex' ),
		'banner_title'     => __( 'Tiêu đề chính', 'mozlex' ),
		'banner_decorative'=> __( 'Dòng trang trí (cursive)', 'mozlex' ),
		'banner_subtitle'  => __( 'Mô tả ngắn dưới tiêu đề', 'mozlex' ),
		'banner_cta_text'  => __( 'Chữ nút CTA chính', 'mozlex' ),
		'banner_cta_url'   => __( 'Link nút CTA chính', 'mozlex' ),
		'banner_cta2_text' => __( 'Chữ nút CTA phụ', 'mozlex' ),
		'banner_cta2_url'  => __( 'Link nút CTA phụ', 'mozlex' ),

		'_section_menu'    => __( 'Menu chính (bật/tắt nhanh — chi tiết hơn vào Giao diện → Menu)', 'mozlex' ),
		'menu_trangchu'    => __( 'Hiện TRANG CHỦ', 'mozlex' ),
		'menu_gioithieu'   => __( 'Hiện GIỚI THIỆU', 'mozlex' ),
		'menu_sanpham'     => __( 'Hiện SẢN PHẨM', 'mozlex' ),
		'menu_linhvuc'     => __( 'Hiện LĨNH VỰC HOẠT ĐỘNG', 'mozlex' ),
		'menu_tintuc'      => __( 'Hiện TIN TỨC', 'mozlex' ),
		'menu_lienhe'      => __( 'Hiện LIÊN HỆ', 'mozlex' ),

		'_section_home'    => __( 'Hiển thị section trang chủ (bật/tắt)', 'mozlex' ),
		'show_danhmuc'     => __( 'Hiện DANH MỤC THIẾT BỊ', 'mozlex' ),
		'show_giaiphap'    => __( 'Hiện GIẢI PHÁP CHO MỌI CÔNG TRÌNH', 'mozlex' ),
		'show_dichvu'      => __( 'Hiện DỊCH VỤ CỦA ĐỨC TRÍ', 'mozlex' ),
		'show_featured'    => __( 'Hiện Sản phẩm nổi bật', 'mozlex' ),
		'show_wizard'      => __( 'Hiện khối Tư vấn / Consultation wizard (trang chủ)', 'mozlex' ),
		'featured_title'   => __( 'Tiêu đề khối Sản phẩm nổi bật', 'mozlex' ),
		'featured_layout'  => __( 'Kiểu hiển thị khối nổi bật (Lưới / Kệ 3D)', 'mozlex' ),
		'featured_mode'    => __( 'Chế độ Sản phẩm nổi bật (tự động / thủ công)', 'mozlex' ),
		'featured_category'=> __( 'Danh mục hiển thị (slug)', 'mozlex' ),
		'featured_count'   => __( 'Số sản phẩm hiển thị', 'mozlex' ),
		'featured_manual_ids' => __( 'Danh sách SP nổi bật thủ công (ID hoặc slug, cách nhau dấu phẩy)', 'mozlex' ),
		'_section_branches'=> __( '3 nhánh Đức Trí 226 (Xây dựng/Thương mại/Công nghệ) — ảnh & mô tả', 'mozlex' ),
		'branch_xaydung_image' => __( 'Ảnh XÂY DỰNG (URL)', 'mozlex' ),
		'branch_xaydung_desc'  => __( 'Mô tả XÂY DỰNG', 'mozlex' ),
		'branch_thuongmai_image'=> __( 'Ảnh THƯƠNG MẠI (URL)', 'mozlex' ),
		'branch_thuongmai_desc' => __( 'Mô tả THƯƠNG MẠI', 'mozlex' ),
		'branch_congnghe_image' => __( 'Ảnh CÔNG NGHỆ (URL)', 'mozlex' ),
		'branch_congnghe_desc'  => __( 'Mô tả CÔNG NGHỆ', 'mozlex' ),
		'_section_contact' => __( 'Banner Liên hệ', 'mozlex' ),
		'contact_banner_image' => __( 'Ảnh nền Banner Liên hệ (URL)', 'mozlex' ),

		'_section_colors'  => __( 'Màu sắc website — đổi màu chủ đạo (gõ mã hex hoặc chọn màu)', 'mozlex' ),
		'primary_color'    => __( 'Màu chủ đạo (nút, link, viền cam #c9a381)', 'mozlex' ),
		'primary_hover'    => __( 'Màu hover (#d48a49)', 'mozlex' ),
		'color_dark'       => __( 'Màu tối (header/footer #0a0a0a)', 'mozlex' ),

		'_section_company' => __( 'Thông tin pháp lý công ty (hiển thị footer + liên hệ)', 'mozlex' ),
		'company_name'     => __( 'Tên công ty đầy đủ', 'mozlex' ),
		'hotline'          => __( 'Hotline / SĐT', 'mozlex' ),
		'zalo'             => __( 'Zalo (số điện thoại Zalo)', 'mozlex' ),
		'email'            => __( 'Email nhận yêu cầu tư vấn', 'mozlex' ),
		'address'          => __( 'Địa chỉ showroom / văn phòng', 'mozlex' ),
		'mst'              => __( 'Mã số thuế (MST)', 'mozlex' ),
		'bank_account'     => __( 'Số tài khoản + Ngân hàng', 'mozlex' ),

		'_section_social'  => __( 'Mạng xã hội', 'mozlex' ),
		'facebook'         => __( 'Facebook URL', 'mozlex' ),
		'youtube'          => __( 'YouTube URL', 'mozlex' ),


	);
}

function mozlex_sanitize_options( $input ) {
	$clean = array();
	$checkboxes = array( 'menu_trangchu','menu_gioithieu','menu_sanpham','menu_linhvuc','menu_tintuc','menu_lienhe','show_danhmuc','show_giaiphap','show_dichvu','show_featured','show_wizard' );
	foreach ( mozlex_option_fields() as $key => $label ) {
		if ( str_starts_with( $key, '_' ) ) {
			continue;
		}
		if ( in_array( $key, $checkboxes, true ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? '1' : '0';
			continue;
		}
		if ( isset( $input[ $key ] ) ) {
			if ( in_array( $key, array( 'warranty_months', 'replace_months', 'featured_count' ), true ) ) {
				$sanitizer = fn( $v ) => (string) absint( $v );
			} elseif ( in_array( $key, array( 'banner_image', 'banner_cta_url', 'banner_cta2_url' ), true ) ) {
				$sanitizer = 'esc_url_raw';
			} elseif ( 'banner_subtitle' === $key ) {
				$sanitizer = 'sanitize_textarea_field';
			} elseif ( 'featured_mode' === $key ) {
				$v = sanitize_key( wp_unslash( $input[ $key ] ) );
				$sanitizer = fn( $_ ) => in_array( $v, array( 'auto', 'manual', 'mixed' ), true ) ? $v : 'mixed';
			} elseif ( 'featured_layout' === $key ) {
				$v = sanitize_key( wp_unslash( $input[ $key ] ) );
				$sanitizer = fn( $_ ) => in_array( $v, array( 'grid', 'shelf' ), true ) ? $v : 'shelf';
			} elseif ( in_array( $key, array( 'primary_color', 'primary_hover', 'color_dark' ), true ) ) {
				$val = sanitize_hex_color( wp_unslash( $input[ $key ] ) );
				$clean[ $key ] = $val ? $val : '';
				continue;
			} else {
				$sanitizer = 'sanitize_text_field';
			}
			$clean[ $key ] = call_user_func( $sanitizer, wp_unslash( $input[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}
	}
	return $clean;
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'settings_page_mozlex-options' !== $hook ) return;
	wp_enqueue_media();
} );

function mozlex_render_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$opts = get_option( 'mozlex_options', array() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Mozlex — Cấu hình website', 'mozlex' ); ?></h1>
		<div class="notice notice-info" style="padding:10px 14px; margin:12px 0;"><strong>Mới:</strong> Banner giờ là slider tự chạy 3s — vào <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=hero_slide' ) ); ?>"><strong>Banner Slider</strong></a> để thêm/sửa/xóa slide, ghi chữ trên ảnh, đổi ảnh & nút. Kéo <em>Thứ tự</em> để sắp xếp. Nếu chưa tạo slide nào, banner đơn cũ bên dưới vẫn dùng làm fallback. Ảnh nên 1920×720, JPG/WebP.</div>
		<form method="post" action="options.php">
			<?php settings_fields( 'mozlex_options_group' ); ?>
			<?php foreach ( mozlex_option_fields() as $key => $label ) : ?>
				<?php if ( str_starts_with( $key, '_' ) ) : ?>
					<hr><h2><?php echo esc_html( $label ); ?></h2>
					<table class="form-table" role="presentation">
				<?php elseif ( in_array( $key, ['banner_image','branch_xaydung_image','branch_thuongmai_image','branch_congnghe_image','contact_banner_image'], true ) ) : ?>
					<tr>
						<th scope="row"><label for="mozlex-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<input type="text" id="mozlex-<?php echo esc_attr( $key ); ?>" name="mozlex_options[<?php echo esc_attr( $key ); ?>]"
								value="<?php echo esc_attr( isset( $opts[ $key ] ) ? $opts[ $key ] : '' ); ?>" class="regular-text" style="width:420px; max-width:100%;" placeholder="https://...">
							<button type="button" class="button mozlex-media-pick" data-target="mozlex-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Chọn ảnh', 'mozlex' ); ?></button>
							<?php if ( ! empty( $opts[ $key ] ) ) : ?>
								<div style="margin-top:10px;"><img src="<?php echo esc_url( $opts[ $key ] ); ?>" alt="" style="max-width:480px; height:auto; border:1px solid #ddd; border-radius:6px;"></div>
							<?php endif; ?>
						</td>
					</tr>
				<?php elseif ( 'banner_subtitle' === $key ) : ?>
					<tr>
						<th scope="row"><label for="mozlex-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<textarea id="mozlex-<?php echo esc_attr( $key ); ?>" name="mozlex_options[<?php echo esc_attr( $key ); ?>]" rows="2" class="large-text" style="max-width:600px;"><?php echo esc_textarea( isset( $opts[ $key ] ) ? $opts[ $key ] : '' ); ?></textarea>
						</td>
					</tr>
				<?php elseif ( in_array( $key, array( 'menu_trangchu','menu_gioithieu','menu_sanpham','menu_linhvuc','menu_tintuc','menu_lienhe','show_danhmuc','show_giaiphap','show_dichvu','show_featured','show_wizard' ), true ) ) : ?>
					<?php $checked = ( 'show_wizard' === $key ) ? ( isset( $opts[ $key ] ) && '1' === $opts[ $key ] ) : ( ! isset( $opts[ $key ] ) || '1' === $opts[ $key ] ); ?>
					<tr>
						<th scope="row"><?php echo esc_html( $label ); ?></th>
						<td><label><input type="checkbox" name="mozlex_options[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $checked ); ?>> <?php esc_html_e( 'Hiển thị', 'mozlex' ); ?></label></td>
					</tr>
				<?php elseif ( 'featured_layout' === $key ) : ?>
					<?php $val = isset( $opts[ $key ] ) ? $opts[ $key ] : 'shelf'; ?>
					<tr>
						<th scope="row"><label for="mozlex-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<select id="mozlex-<?php echo esc_attr( $key ); ?>" name="mozlex_options[<?php echo esc_attr( $key ); ?>]" style="min-width:280px;">
								<option value="shelf" <?php selected( $val, 'shelf' ); ?>>Kệ 3D kiểu AshenPress (khuyên dùng)</option>
								<option value="grid" <?php selected( $val, 'grid' ); ?>>Lưới phẳng truyền thống</option>
							</select>
							<p class="description">Kệ 3D tự rớt về lưới khi mất mạng CDN hoặc máy khách không có WebGL.</p>
						</td>
					</tr>
				<?php elseif ( 'featured_mode' === $key ) : ?>
					<?php $val = isset( $opts[ $key ] ) ? $opts[ $key ] : 'mixed'; ?>
					<tr>
						<th scope="row"><label for="mozlex-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<select id="mozlex-<?php echo esc_attr( $key ); ?>" name="mozlex_options[<?php echo esc_attr( $key ); ?>]" style="min-width:280px;">
								<option value="auto" <?php selected( $val, 'auto' ); ?>>Tự động — theo danh mục + số lượng</option>
								<option value="manual" <?php selected( $val, 'manual' ); ?>>Thủ công — chỉ hiện SP Bạn pick bên dưới</option>
								<option value="mixed" <?php selected( $val, 'mixed' ); ?>>Cả hai — ưu tiên SP pick tay, thiếu thì auto bù (khuyên dùng)</option>
							</select>
							<p class="description">Muốn 4 → 8 → 16 ô thì sửa “Số sản phẩm hiển thị” bên dưới. Muốn pick tay thì chọn “Cả hai” hoặc “Thủ công” rồi dán ID/slug vào ô bên dưới.</p>
						</td>
					</tr>
				<?php elseif ( 'featured_manual_ids' === $key ) : ?>
					<tr>
						<th scope="row"><label for="mozlex-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<textarea id="mozlex-<?php echo esc_attr( $key ); ?>" name="mozlex_options[<?php echo esc_attr( $key ); ?>]" rows="2" class="large-text code" style="max-width:600px;" placeholder="VD: 638, 562, 558, khoa-tay-gat-mozlex-ks19"><?php echo esc_textarea( isset( $opts[ $key ] ) ? $opts[ $key ] : '' ); ?></textarea>
							<p class="description">Cách lấy ID: vào <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>">Sản phẩm Mozlex</a> → di chuột vào tên SP → nhìn góc trái dưới trình duyệt sẽ thấy <code>post=123</code> — đó là ID. Hoặc mở SP ra, URL có <code>/wp-admin/post.php?post=123</code>. Cũng chấp nhận slug (phần cuối link, VD <code>khoa-tay-gat-mozlex-ks19</code>). Thứ tự Bạn dán = thứ tự hiện ở trang chủ.</p>
						</td>
					</tr>
				<?php elseif ( 'featured_category' === $key ) : ?>
					<?php $val = isset( $opts[ $key ] ) ? $opts[ $key ] : 'khoa-cua-thong-minh'; $cats = get_terms(['taxonomy'=>'product_category','hide_empty'=>false]); ?>
					<tr>
						<th scope="row"><label for="mozlex-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<select id="mozlex-<?php echo esc_attr( $key ); ?>" name="mozlex_options[<?php echo esc_attr( $key ); ?>]" style="min-width:260px;">
								<option value=""><?php esc_html_e( '— Tất cả sản phẩm —', 'mozlex' ); ?></option>
								<?php foreach( $cats as $cat ) : ?>
								<option value="<?php echo esc_attr($cat->slug); ?>" <?php selected($val, $cat->slug); ?>><?php echo esc_html($cat->name.' ('.$cat->slug.')'); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description">Chọn danh mục chỉ hiện khóa thông minh (mặc định: khoa-cua-thong-minh). Đổi ở đây trang chủ tự đổi.</p>
						</td>
					</tr>
				<?php elseif ( 'featured_count' === $key ) : ?>
					<tr>
						<th scope="row"><label for="mozlex-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td><input type="number" id="mozlex-<?php echo esc_attr( $key ); ?>" name="mozlex_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( isset($opts[$key]) ? $opts[$key] : '8' ); ?>" min="1" max="24" style="width:80px;"> <span class="description">Số lượng (mặc định 8)</span></td>
					</tr>
				<?php elseif ( 'featured_title' === $key ) : ?>
					<tr>
						<th scope="row"><label for="mozlex-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td><input type="text" id="mozlex-<?php echo esc_attr( $key ); ?>" name="mozlex_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( isset($opts[$key]) ? $opts[$key] : 'Những sản phẩm đáng chú ý' ); ?>" class="regular-text" style="width:320px;" placeholder="Những sản phẩm đáng chú ý"> <span class="description">Đổi tiêu đề khối</span></td>
					</tr>
				<?php elseif ( in_array( $key, array( 'primary_color','primary_hover','color_dark' ), true ) ) : ?>
					<?php $val = isset( $opts[ $key ] ) ? $opts[ $key ] : ( 'primary_color' === $key ? '#c9a381' : ( 'primary_hover' === $key ? '#d48a49' : '#0a0a0a' ) ); ?>
					<tr>
						<th scope="row"><label for="mozlex-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td style="display:flex; align-items:center; gap:10px;">
							<input type="color" id="mozlex-<?php echo esc_attr( $key ); ?>-picker" value="<?php echo esc_attr( $val ); ?>" style="width:44px; height:34px; padding:2px; border:1px solid #c3c4c7; border-radius:4px; cursor:pointer;">
							<input type="text" id="mozlex-<?php echo esc_attr( $key ); ?>" name="mozlex_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" placeholder="#c9a381" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" style="width:120px;">
							<span style="color:#646970; font-size:0.85em;">VD: #c9a381 — đổi là web tự đổi màu</span>
						</td>
					</tr>
				<?php else : ?>
					<tr>
						<th scope="row"><label for="mozlex-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<input type="text" id="mozlex-<?php echo esc_attr( $key ); ?>" name="mozlex_options[<?php echo esc_attr( $key ); ?>]"
								value="<?php echo esc_attr( isset( $opts[ $key ] ) ? $opts[ $key ] : '' ); ?>" class="regular-text" placeholder="<?php echo esc_attr( 'banner_cta_url' === $key ? '/khoa-thong-minh/' : '' ); ?>">
						</td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<script>
	(function(){
		var btns=document.querySelectorAll('.mozlex-media-pick');
		if(!btns.length||!window.wp||!wp.media) return;
		var frame, targetInput;
		btns.forEach(function(btn){
			btn.addEventListener('click', function(){
				targetInput=document.getElementById(btn.getAttribute('data-target'));
				if(frame) frame.open();
				else {
					frame=wp.media({title:'Chọn ảnh', button:{text:'Dùng ảnh này'}, library:{type:'image'}, multiple:false});
					frame.on('select', function(){
						var att=frame.state().get('selection').first().toJSON();
						if(targetInput) targetInput.value=att.url;
					});
					frame.open();
				}
			});
		});
	})();
	// Đồng bộ color picker <-> text input (gõ mã hex là web đổi màu)
	(function(){
		['primary_color','primary_hover','color_dark'].forEach(function(key){
			var picker=document.getElementById('mozlex-'+key+'-picker');
			var text=document.getElementById('mozlex-'+key);
			if(!picker||!text) return;
			picker.addEventListener('input', function(){ text.value=picker.value; });
			text.addEventListener('input', function(){
				var v=text.value.trim();
				if(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(v)) picker.value=v;
			});
		});
	})();
	</script>
	<?php
}

// Xuất CSS biến màu ra front-end — đổi mã hex là web tự đổi màu ngay
add_action( 'wp_head', function () {
	$opts = get_option( 'mozlex_options', array() );
	$primary = isset( $opts['primary_color'] ) && $opts['primary_color'] ? $opts['primary_color'] : '';
	$hover   = isset( $opts['primary_hover'] ) && $opts['primary_hover'] ? $opts['primary_hover'] : '';
	$dark    = isset( $opts['color_dark'] ) && $opts['color_dark'] ? $opts['color_dark'] : '';
	if ( ! $primary && ! $hover && ! $dark ) return;
	echo "<style id=\"mozlex-colors\">:root{";
	if ( $primary ) echo "--primary:" . esc_html( $primary ) . ";";
	if ( $hover )   echo "--primary-hover:" . esc_html( $hover ) . ";";
	if ( $dark )    echo "--dark:" . esc_html( $dark ) . ";--black:" . esc_html( $dark ) . ";";
	echo "}</style>\n";
}, 20 );
