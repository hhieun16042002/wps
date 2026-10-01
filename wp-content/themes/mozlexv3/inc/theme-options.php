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

		'_section_popup'           => __( 'Popup Khuyến Mãi / Thông Báo (Promotional Popup)', 'mozlex' ),
		'promo_popup_enabled'      => __( 'Bật Popup Khuyến Mãi', 'mozlex' ),
		'promo_popup_badge'        => __( 'Nhãn nhỏ trên tiêu đề (Badge)', 'mozlex' ),
		'promo_popup_title'        => __( 'Tiêu đề Popup', 'mozlex' ),
		'promo_popup_desc'         => __( 'Nội dung mô tả ngắn', 'mozlex' ),
		'promo_popup_image'        => __( 'Ảnh banner Popup (URL)', 'mozlex' ),
		'promo_popup_btn_text'     => __( 'Chữ nút bấm chính (CTA)', 'mozlex' ),
		'promo_popup_btn_url'      => __( 'Đường dẫn nút bấm chính (CTA URL)', 'mozlex' ),
		'promo_popup_close_text'   => __( 'Chữ nút phụ / Bỏ qua', 'mozlex' ),
		'promo_popup_secondary_text'=> __( 'Dòng ghi chú nhỏ bên dưới', 'mozlex' ),
		'promo_popup_frequency'    => __( 'Tần suất hiển thị khi người dùng đóng', 'mozlex' ),
		'promo_popup_countdown_enabled' => __( 'Bật đồng hồ đếm ngược', 'mozlex' ),
		'promo_popup_countdown_label'   => __( 'Tiêu đề đồng hồ đếm ngược', 'mozlex' ),
		'promo_popup_countdown_end'     => __( 'Thời gian kết thúc đếm ngược', 'mozlex' ),
		'promo_popup_schedule_enabled' => __( 'Bật giới hạn thời gian (Lên lịch)', 'mozlex' ),
		'promo_popup_start_date'   => __( 'Ngày bắt đầu hiển thị (YYYY-MM-DD)', 'mozlex' ),
		'promo_popup_end_date'     => __( 'Ngày kết thúc hiển thị (YYYY-MM-DD)', 'mozlex' ),

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
	$checkboxes = array(
		'menu_trangchu','menu_gioithieu','menu_sanpham','menu_linhvuc','menu_tintuc','menu_lienhe',
		'show_danhmuc','show_giaiphap','show_dichvu','show_featured','show_wizard',
		'promo_popup_enabled','promo_popup_schedule_enabled','promo_popup_countdown_enabled'
	);
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
			} elseif ( in_array( $key, array( 'banner_image', 'banner_cta_url', 'banner_cta2_url', 'contact_banner_image', 'promo_popup_image', 'promo_popup_btn_url' ), true ) ) {
				$sanitizer = 'esc_url_raw';
			} elseif ( in_array( $key, array( 'banner_subtitle', 'promo_popup_desc' ), true ) ) {
				$sanitizer = 'sanitize_textarea_field';
			} elseif ( 'promo_popup_frequency' === $key ) {
				$v = sanitize_key( wp_unslash( $input[ $key ] ) );
				$sanitizer = fn( $_ ) => in_array( $v, array( '1hour', 'today', 'always' ), true ) ? $v : '1hour';
			} elseif ( in_array( $key, array( 'promo_popup_start_date', 'promo_popup_end_date' ), true ) ) {
				$v = sanitize_text_field( wp_unslash( $input[ $key ] ) );
				$sanitizer = fn( $_ ) => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $v ) ? $v : '';
			} elseif ( 'promo_popup_countdown_end' === $key ) {
				$v = sanitize_text_field( wp_unslash( $input[ $key ] ) );
				$clean[ $key ] = preg_match( '/^\d{4}-\d{2}-\d{2}(?:[T\s]\d{2}:\d{2}(?::\d{2})?)?$/', $v ) ? $v : '';
				continue;
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

	$popup_enabled    = ! empty( $opts['promo_popup_enabled'] ) && '1' === $opts['promo_popup_enabled'];
	$schedule_enabled = ! empty( $opts['promo_popup_schedule_enabled'] ) && '1' === $opts['promo_popup_schedule_enabled'];
	$start_date       = ! empty( $opts['promo_popup_start_date'] ) ? $opts['promo_popup_start_date'] : '';
	$end_date         = ! empty( $opts['promo_popup_end_date'] ) ? $opts['promo_popup_end_date'] : '';
	$now_ts           = current_time( 'timestamp' );

	$popup_status = 'inactive';
	$popup_status_label = __( 'Đang tắt', 'mozlex' );
	$popup_status_color = '#646970';

	if ( $popup_enabled ) {
		if ( $schedule_enabled ) {
			$start_ts = ! empty( $start_date ) ? strtotime( $start_date . ' 00:00:00' ) : 0;
			$end_ts   = ! empty( $end_date ) ? strtotime( $end_date . ' 23:59:59' ) : 0;

			if ( $start_ts && $now_ts < $start_ts ) {
				$popup_status = 'scheduled';
				$popup_status_label = sprintf( __( 'Đã lên lịch (bắt đầu từ %s)', 'mozlex' ), $start_date );
				$popup_status_color = '#d97706';
			} elseif ( $end_ts && $now_ts > $end_ts ) {
				$popup_status = 'expired';
				$popup_status_label = sprintf( __( 'Đã kết thúc (hết hạn ngày %s)', 'mozlex' ), $end_date );
				$popup_status_color = '#dc2626';
			} else {
				$popup_status = 'active';
				$popup_status_label = __( 'Đang hoạt động (theo lịch)', 'mozlex' );
				$popup_status_color = '#16a34a';
			}
		} else {
			$popup_status = 'active';
			$popup_status_label = __( 'Đang hoạt động (luôn bật)', 'mozlex' );
			$popup_status_color = '#16a34a';
		}
	}
	?>
	<div class="wrap mozlex-admin-wrap">
		<h1><?php esc_html_e( 'Mozlex — Cấu hình website', 'mozlex' ); ?></h1>

		<nav class="nav-tab-wrapper mozlex-nav-tabs" style="margin-top:16px; margin-bottom:20px;">
			<a href="#tab-general" class="nav-tab nav-tab-active" data-tab="tab-general">
				<span class="dashicons dashicons-admin-settings" style="vertical-align:text-bottom; margin-right:4px;"></span>
				<?php esc_html_e( '1. Cấu hình chung & Trang chủ', 'mozlex' ); ?>
			</a>
			<a href="#tab-popup" class="nav-tab" data-tab="tab-popup">
				<span class="dashicons dashicons-megaphone" style="vertical-align:text-bottom; margin-right:4px;"></span>
				<?php esc_html_e( '2. Popup Khuyến Mãi & Quảng Cáo', 'mozlex' ); ?>
				<?php if ( $popup_enabled ) : ?>
					<span class="mozlex-tab-indicator" style="display:inline-block; width:8px; height:8px; border-radius:50%; background:<?php echo esc_attr( $popup_status_color ); ?>; margin-left:4px;" title="<?php echo esc_attr( $popup_status_label ); ?>"></span>
				<?php endif; ?>
			</a>
			<a href="#tab-colors-contact" class="nav-tab" data-tab="tab-colors-contact">
				<span class="dashicons dashicons-art" style="vertical-align:text-bottom; margin-right:4px;"></span>
				<?php esc_html_e( '3. Màu sắc & Thông tin liên hệ', 'mozlex' ); ?>
			</a>
		</nav>

		<form method="post" action="options.php" id="mozlex-options-form">
			<?php settings_fields( 'mozlex_options_group' ); ?>

			<!-- TAB 1: CẤU HÌNH CHUNG & TRANG CHỦ -->
			<div id="tab-general" class="mozlex-tab-pane">
				<div class="notice notice-info" style="padding:10px 14px; margin:0 0 16px 0;"><strong>Mới:</strong> Banner giờ là slider tự chạy 3s — vào <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=hero_slide' ) ); ?>"><strong>Banner Slider</strong></a> để thêm/sửa/xóa slide, ghi chữ trên ảnh, đổi ảnh & nút. Kéo <em>Thứ tự</em> để sắp xếp. Nếu chưa tạo slide nào, banner đơn cũ bên dưới vẫn dùng làm fallback. Ảnh nên 1920×720, JPG/WebP.</div>

				<h2><?php esc_html_e( 'Banner trang chủ (Hero Fallback)', 'mozlex' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mozlex-banner_image"><?php esc_html_e( 'Ảnh nền banner (URL)', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-banner_image" name="mozlex_options[banner_image]" value="<?php echo esc_attr( $opts['banner_image'] ?? '' ); ?>" class="regular-text" style="width:420px; max-width:100%;" placeholder="https://...">
							<button type="button" class="button mozlex-media-pick" data-target="mozlex-banner_image"><?php esc_html_e( 'Chọn ảnh', 'mozlex' ); ?></button>
							<div class="mozlex-image-preview" style="margin-top:10px; <?php echo empty( $opts['banner_image'] ) ? 'display:none;' : ''; ?>">
								<img src="<?php echo esc_url( $opts['banner_image'] ?? '' ); ?>" alt="" style="max-width:480px; height:auto; border:1px solid #ddd; border-radius:6px;">
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-banner_eyebrow"><?php esc_html_e( 'Dòng nhỏ trên tiêu đề (eyebrow)', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-banner_eyebrow" name="mozlex_options[banner_eyebrow]" value="<?php echo esc_attr( $opts['banner_eyebrow'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-banner_title"><?php esc_html_e( 'Tiêu đề chính', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-banner_title" name="mozlex_options[banner_title]" value="<?php echo esc_attr( $opts['banner_title'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-banner_decorative"><?php esc_html_e( 'Dòng trang trí (cursive)', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-banner_decorative" name="mozlex_options[banner_decorative]" value="<?php echo esc_attr( $opts['banner_decorative'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-banner_subtitle"><?php esc_html_e( 'Mô tả ngắn dưới tiêu đề', 'mozlex' ); ?></label></th>
						<td><textarea id="mozlex-banner_subtitle" name="mozlex_options[banner_subtitle]" rows="2" class="large-text" style="max-width:600px;"><?php echo esc_textarea( $opts['banner_subtitle'] ?? '' ); ?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-banner_cta_text"><?php esc_html_e( 'Chữ nút CTA chính', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-banner_cta_text" name="mozlex_options[banner_cta_text]" value="<?php echo esc_attr( $opts['banner_cta_text'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-banner_cta_url"><?php esc_html_e( 'Link nút CTA chính', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-banner_cta_url" name="mozlex_options[banner_cta_url]" value="<?php echo esc_attr( $opts['banner_cta_url'] ?? '' ); ?>" class="regular-text" placeholder="/san-pham/"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-banner_cta2_text"><?php esc_html_e( 'Chữ nút CTA phụ', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-banner_cta2_text" name="mozlex_options[banner_cta2_text]" value="<?php echo esc_attr( $opts['banner_cta2_text'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-banner_cta2_url"><?php esc_html_e( 'Link nút CTA phụ', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-banner_cta2_url" name="mozlex_options[banner_cta2_url]" value="<?php echo esc_attr( $opts['banner_cta2_url'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
				</table>

				<hr>
				<h2><?php esc_html_e( 'Menu chính (bật/tắt nhanh)', 'mozlex' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php foreach ( array( 'menu_trangchu' => 'Hiện TRANG CHỦ', 'menu_gioithieu' => 'Hiện GIỚI THIỆU', 'menu_sanpham' => 'Hiện SẢN PHẨM', 'menu_linhvuc' => 'Hiện LĨNH VỰC HOẠT ĐỘNG', 'menu_tintuc' => 'Hiện TIN TỨC', 'menu_lienhe' => 'Hiện LIÊN HỆ' ) as $mkey => $mlabel ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $mlabel ); ?></th>
							<td><label><input type="checkbox" name="mozlex_options[<?php echo esc_attr( $mkey ); ?>]" value="1" <?php checked( ! isset( $opts[ $mkey ] ) || '1' === $opts[ $mkey ] ); ?>> <?php esc_html_e( 'Hiển thị', 'mozlex' ); ?></label></td>
						</tr>
					<?php endforeach; ?>
				</table>

				<hr>
				<h2><?php esc_html_e( 'Hiển thị Section Trang chủ', 'mozlex' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php foreach ( array( 'show_danhmuc' => 'Hiện DANH MỤC THIẾT BỊ', 'show_giaiphap' => 'Hiện GIẢI PHÁP CHO MỌI CÔNG TRÌNH', 'show_dichvu' => 'Hiện DỊCH VỤ CỦA ĐỨC TRÍ', 'show_featured' => 'Hiện Sản phẩm nổi bật', 'show_wizard' => 'Hiện khối Tư vấn / Consultation wizard' ) as $skey => $slabel ) : ?>
						<?php $checked = ( 'show_wizard' === $skey ) ? ( isset( $opts[ $skey ] ) && '1' === $opts[ $skey ] ) : ( ! isset( $opts[ $skey ] ) || '1' === $opts[ $skey ] ); ?>
						<tr>
							<th scope="row"><?php echo esc_html( $slabel ); ?></th>
							<td><label><input type="checkbox" name="mozlex_options[<?php echo esc_attr( $skey ); ?>]" value="1" <?php checked( $checked ); ?>> <?php esc_html_e( 'Hiển thị', 'mozlex' ); ?></label></td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<th scope="row"><label for="mozlex-featured_title"><?php esc_html_e( 'Tiêu đề khối Nổi bật', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-featured_title" name="mozlex_options[featured_title]" value="<?php echo esc_attr( $opts['featured_title'] ?? 'Những sản phẩm đáng chú ý' ); ?>" class="regular-text" style="width:320px;"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-featured_layout"><?php esc_html_e( 'Kiểu hiển thị khối nổi bật', 'mozlex' ); ?></label></th>
						<td>
							<?php $fl = $opts['featured_layout'] ?? 'shelf'; ?>
							<select id="mozlex-featured_layout" name="mozlex_options[featured_layout]" style="min-width:280px;">
								<option value="shelf" <?php selected( $fl, 'shelf' ); ?>>Kệ 3D kiểu AshenPress (khuyên dùng)</option>
								<option value="grid" <?php selected( $fl, 'grid' ); ?>>Lưới phẳng truyền thống</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-featured_mode"><?php esc_html_e( 'Chế độ Sản phẩm nổi bật', 'mozlex' ); ?></label></th>
						<td>
							<?php $fm = $opts['featured_mode'] ?? 'mixed'; ?>
							<select id="mozlex-featured_mode" name="mozlex_options[featured_mode]" style="min-width:280px;">
								<option value="auto" <?php selected( $fm, 'auto' ); ?>>Tự động — theo danh mục + số lượng</option>
								<option value="manual" <?php selected( $fm, 'manual' ); ?>>Thủ công — chỉ hiện SP pick bên dưới</option>
								<option value="mixed" <?php selected( $fm, 'mixed' ); ?>>Cả hai — ưu tiên SP pick tay, thiếu thì auto bù</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-featured_manual_ids"><?php esc_html_e( 'Danh sách SP nổi bật thủ công', 'mozlex' ); ?></label></th>
						<td>
							<textarea id="mozlex-featured_manual_ids" name="mozlex_options[featured_manual_ids]" rows="2" class="large-text code" style="max-width:600px;" placeholder="VD: 638, 562, 558, khoa-tay-gat-mozlex-ks19"><?php echo esc_textarea( $opts['featured_manual_ids'] ?? '' ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-featured_category"><?php esc_html_e( 'Danh mục hiển thị (slug)', 'mozlex' ); ?></label></th>
						<td>
							<?php $fc = $opts['featured_category'] ?? 'khoa-cua-thong-minh'; $cats = get_terms(['taxonomy'=>'product_category','hide_empty'=>false]); ?>
							<select id="mozlex-featured_category" name="mozlex_options[featured_category]" style="min-width:260px;">
								<option value=""><?php esc_html_e( '— Tất cả sản phẩm —', 'mozlex' ); ?></option>
								<?php if ( ! is_wp_error( $cats ) ) : foreach( $cats as $cat ) : ?>
								<option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $fc, $cat->slug ); ?>><?php echo esc_html( $cat->name . ' (' . $cat->slug . ')' ); ?></option>
								<?php endforeach; endif; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-featured_count"><?php esc_html_e( 'Số sản phẩm hiển thị', 'mozlex' ); ?></label></th>
						<td><input type="number" id="mozlex-featured_count" name="mozlex_options[featured_count]" value="<?php echo esc_attr( $opts['featured_count'] ?? '8' ); ?>" min="1" max="24" style="width:80px;"></td>
					</tr>
				</table>

				<hr>
				<h2><?php esc_html_e( '3 nhánh Đức Trí 226', 'mozlex' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mozlex-branch_xaydung_image"><?php esc_html_e( 'Ảnh XÂY DỰNG (URL)', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-branch_xaydung_image" name="mozlex_options[branch_xaydung_image]" value="<?php echo esc_attr( $opts['branch_xaydung_image'] ?? '' ); ?>" class="regular-text" style="width:420px; max-width:100%;">
							<button type="button" class="button mozlex-media-pick" data-target="mozlex-branch_xaydung_image"><?php esc_html_e( 'Chọn ảnh', 'mozlex' ); ?></button>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-branch_xaydung_desc"><?php esc_html_e( 'Mô tả XÂY DỰNG', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-branch_xaydung_desc" name="mozlex_options[branch_xaydung_desc]" value="<?php echo esc_attr( $opts['branch_xaydung_desc'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-branch_thuongmai_image"><?php esc_html_e( 'Ảnh THƯƠNG MẠI (URL)', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-branch_thuongmai_image" name="mozlex_options[branch_thuongmai_image]" value="<?php echo esc_attr( $opts['branch_thuongmai_image'] ?? '' ); ?>" class="regular-text" style="width:420px; max-width:100%;">
							<button type="button" class="button mozlex-media-pick" data-target="mozlex-branch_thuongmai_image"><?php esc_html_e( 'Chọn ảnh', 'mozlex' ); ?></button>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-branch_thuongmai_desc"><?php esc_html_e( 'Mô tả THƯƠNG MẠI', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-branch_thuongmai_desc" name="mozlex_options[branch_thuongmai_desc]" value="<?php echo esc_attr( $opts['branch_thuongmai_desc'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-branch_congnghe_image"><?php esc_html_e( 'Ảnh CÔNG NGHỆ (URL)', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-branch_congnghe_image" name="mozlex_options[branch_congnghe_image]" value="<?php echo esc_attr( $opts['branch_congnghe_image'] ?? '' ); ?>" class="regular-text" style="width:420px; max-width:100%;">
							<button type="button" class="button mozlex-media-pick" data-target="mozlex-branch_congnghe_image"><?php esc_html_e( 'Chọn ảnh', 'mozlex' ); ?></button>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-branch_congnghe_desc"><?php esc_html_e( 'Mô tả CÔNG NGHỆ', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-branch_congnghe_desc" name="mozlex_options[branch_congnghe_desc]" value="<?php echo esc_attr( $opts['branch_congnghe_desc'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-contact_banner_image"><?php esc_html_e( 'Ảnh nền Banner Liên hệ (URL)', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-contact_banner_image" name="mozlex_options[contact_banner_image]" value="<?php echo esc_attr( $opts['contact_banner_image'] ?? '' ); ?>" class="regular-text" style="width:420px; max-width:100%;">
							<button type="button" class="button mozlex-media-pick" data-target="mozlex-contact_banner_image"><?php esc_html_e( 'Chọn ảnh', 'mozlex' ); ?></button>
						</td>
					</tr>
				</table>
			</div>

			<!-- TAB 2: POPUP KHUYẾN MÃI & QUẢNG CÁO -->
			<div id="tab-popup" class="mozlex-tab-pane" style="display:none;">
				<div class="mozlex-popup-status-box" style="display:flex; align-items:center; justify-content:space-between; background:#fff; border:1px solid #ccd0d4; border-left:4px solid <?php echo esc_attr( $popup_status_color ); ?>; padding:14px 18px; border-radius:4px; margin-bottom:20px;">
					<div>
						<h3 style="margin:0 0 4px 0; font-size:1.05rem;"><?php esc_html_e( 'Trạng thái Popup Hiện tại:', 'mozlex' ); ?> <span style="color:<?php echo esc_attr( $popup_status_color ); ?>; font-weight:700;"><?php echo esc_html( $popup_status_label ); ?></span></h3>
						<p style="margin:0; color:#646970; font-size:0.88rem;"><?php esc_html_e( 'Popup dùng để quảng bá chiến dịch giảm giá, giới thiệu sản phẩm mới hoặc thông báo quan trọng.', 'mozlex' ); ?></p>
					</div>
					<div>
						<label class="mozlex-switch-label" style="display:inline-flex; align-items:center; cursor:pointer; gap:10px; font-weight:600; font-size:0.95rem;">
							<input type="checkbox" id="mozlex-promo_popup_enabled" name="mozlex_options[promo_popup_enabled]" value="1" <?php checked( $popup_enabled ); ?> style="width:20px; height:20px;">
							<span><?php esc_html_e( 'Kích hoạt Popup', 'mozlex' ); ?></span>
						</label>
					</div>
				</div>

				<h2><?php esc_html_e( 'Nội dung Popup Khuyến Mãi', 'mozlex' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mozlex-promo_popup_badge"><?php esc_html_e( 'Nhãn nhỏ nổi bật (Badge)', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-promo_popup_badge" name="mozlex_options[promo_popup_badge]" value="<?php echo esc_attr( $opts['promo_popup_badge'] ?? 'ƯU ĐÃI ĐẶC BIỆT' ); ?>" class="regular-text" placeholder="VD: ƯU ĐÃI ĐẶC BIỆT hoặc HOT DEAL">
							<p class="description"><?php esc_html_e( 'Dòng chữ nhỏ viền kim đồng hiển thị phía trên tiêu đề chính.', 'mozlex' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-promo_popup_title"><?php esc_html_e( 'Tiêu đề Popup', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-promo_popup_title" name="mozlex_options[promo_popup_title]" value="<?php echo esc_attr( $opts['promo_popup_title'] ?? 'Giảm tới 20% Khóa Cửa Thông Minh' ); ?>" class="large-text" style="max-width:560px;" placeholder="VD: Giảm tới 20% Khóa Cửa Thông Minh">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-promo_popup_desc"><?php esc_html_e( 'Mô tả chương trình', 'mozlex' ); ?></label></th>
						<td>
							<textarea id="mozlex-promo_popup_desc" name="mozlex_options[promo_popup_desc]" rows="3" class="large-text" style="max-width:560px;" placeholder="Nhập chi tiết ưu đãi hoặc thông báo gửi tới khách hàng..."><?php echo esc_textarea( $opts['promo_popup_desc'] ?? 'Đón đầu công nghệ an ninh với dòng khóa vân tay cao cấp Mozlex. Miễn phí lắp đặt tận nơi tại Hà Nội & bảo hành chính hãng 24 tháng.' ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-promo_popup_image"><?php esc_html_e( 'Ảnh Banner Khuyến Mãi', 'mozlex' ); ?></label></th>
						<td>
							<div style="display:flex; align-items:center; gap:8px; max-width:560px;">
								<input type="text" id="mozlex-promo_popup_image" name="mozlex_options[promo_popup_image]" value="<?php echo esc_attr( $opts['promo_popup_image'] ?? '' ); ?>" class="regular-text" style="flex:1;" placeholder="https://...">
								<button type="button" class="button button-primary mozlex-media-pick" data-target="mozlex-promo_popup_image"><?php esc_html_e( 'Chọn ảnh', 'mozlex' ); ?></button>
								<button type="button" class="button mozlex-media-clear" data-target="mozlex-promo_popup_image" style="<?php echo empty( $opts['promo_popup_image'] ) ? 'display:none;' : ''; ?>"><?php esc_html_e( 'Xóa', 'mozlex' ); ?></button>
							</div>
							<p class="description"><?php esc_html_e( 'Nên dùng ảnh tỉ lệ 4:3 hoặc vuông (tối thiểu 600×500px). Nếu không chọn ảnh, popup sẽ hiển thị dạng hộp thông báo thanh lịch.', 'mozlex' ); ?></p>
							<div id="mozlex-promo_popup_image-preview" class="mozlex-image-preview" style="margin-top:10px; <?php echo empty( $opts['promo_popup_image'] ) ? 'display:none;' : ''; ?>">
								<img src="<?php echo esc_url( $opts['promo_popup_image'] ?? '' ); ?>" alt="" style="max-width:320px; max-height:200px; object-fit:cover; border:1px solid #ddd; border-radius:6px;">
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-promo_popup_btn_text"><?php esc_html_e( 'Chữ nút CTA chính', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-promo_popup_btn_text" name="mozlex_options[promo_popup_btn_text]" value="<?php echo esc_attr( $opts['promo_popup_btn_text'] ?? 'Xem Sản Phẩm Ưu Đãi' ); ?>" class="regular-text" placeholder="Xem Sản Phẩm Ưu Đãi">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-promo_popup_btn_url"><?php esc_html_e( 'Đường dẫn nút CTA', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-promo_popup_btn_url" name="mozlex_options[promo_popup_btn_url]" value="<?php echo esc_attr( $opts['promo_popup_btn_url'] ?? '/san-pham/' ); ?>" class="regular-text" placeholder="/san-pham/">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-promo_popup_close_text"><?php esc_html_e( 'Chữ nút phụ / Bỏ qua', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-promo_popup_close_text" name="mozlex_options[promo_popup_close_text]" value="<?php echo esc_attr( $opts['promo_popup_close_text'] ?? 'Bỏ qua hôm nay' ); ?>" class="regular-text" placeholder="Bỏ qua hôm nay">
							<p class="description"><?php esc_html_e( 'Nút nhỏ bên cạnh nút chính cho phép khách đóng popup nhanh.', 'mozlex' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-promo_popup_secondary_text"><?php esc_html_e( 'Dòng lưu ý / phụ chú', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-promo_popup_secondary_text" name="mozlex_options[promo_popup_secondary_text]" value="<?php echo esc_attr( $opts['promo_popup_secondary_text'] ?? '* Áp dụng cho 50 đơn hàng đầu tiên trong tháng' ); ?>" class="large-text" style="max-width:560px;" placeholder="VD: * Áp dụng cho 50 khách hàng đầu tiên...">
						</td>
					</tr>
				</table>

				<hr>
				<h2><?php esc_html_e( 'Tần suất Hiển thị (Display Frequency)', 'mozlex' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Khi người dùng đóng popup', 'mozlex' ); ?></th>
						<td>
							<?php $freq = $opts['promo_popup_frequency'] ?? '1hour'; ?>
							<fieldset style="display:flex; flex-direction:column; gap:10px;">
								<label style="display:flex; align-items:flex-start; gap:8px;">
									<input type="radio" name="mozlex_options[promo_popup_frequency]" value="1hour" <?php checked( $freq, '1hour' ); ?> style="margin-top:2px;">
									<div>
										<strong><?php esc_html_e( 'Đóng trong 1 Giờ (Close for 1 Hour)', 'mozlex' ); ?></strong>
										<div style="color:#646970; font-size:0.86rem;"><?php esc_html_e( 'Không hiển thị lại trong vòng 1 tiếng sau khi khách đóng. Sau 1 tiếng nếu khách tải lại trang sẽ hiện lại.', 'mozlex' ); ?></div>
									</div>
								</label>
								<label style="display:flex; align-items:flex-start; gap:8px;">
									<input type="radio" name="mozlex_options[promo_popup_frequency]" value="today" <?php checked( $freq, 'today' ); ?> style="margin-top:2px;">
									<div>
										<strong><?php esc_html_e( 'Đóng cho Hôm nay (Close for Today)', 'mozlex' ); ?></strong>
										<div style="color:#646970; font-size:0.86rem;"><?php esc_html_e( 'Không hiển thị lại trong toàn bộ ngày hôm nay. Sang ngày hôm sau mới hiển thị lại.', 'mozlex' ); ?></div>
									</div>
								</label>
								<label style="display:flex; align-items:flex-start; gap:8px;">
									<input type="radio" name="mozlex_options[promo_popup_frequency]" value="always" <?php checked( $freq, 'always' ); ?> style="margin-top:2px;">
									<div>
										<strong><?php esc_html_e( 'Luôn hiển thị (Always Show)', 'mozlex' ); ?></strong>
										<div style="color:#646970; font-size:0.86rem;"><?php esc_html_e( 'Không chặn lưu vết, hiển thị lại mỗi khi mở trang hoặc phiên mới (phù hợp khi đang chạy sự kiện chớp nhoáng hoặc test).', 'mozlex' ); ?></div>
									</div>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>

				<hr>
				<h2><?php esc_html_e( 'Đồng Hồ Đếm Ngược (Countdown Timer)', 'mozlex' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Bật đồng hồ đếm ngược', 'mozlex' ); ?></th>
						<td>
							<?php $cd_enabled = ! empty( $opts['promo_popup_countdown_enabled'] ) && '1' === $opts['promo_popup_countdown_enabled']; ?>
							<label>
								<input type="checkbox" id="mozlex-promo_popup_countdown_enabled" name="mozlex_options[promo_popup_countdown_enabled]" value="1" <?php checked( $cd_enabled ); ?>>
								<strong><?php esc_html_e( 'Hiển thị đồng hồ đếm ngược Ngày : Giờ : Phút : Giây trong popup', 'mozlex' ); ?></strong>
							</label>
							<p class="description"><?php esc_html_e( 'Tạo cảm giác cấp bách, khuyến khích khách mua hàng nhanh chóng trước khi ưu đãi kết thúc.', 'mozlex' ); ?></p>
						</td>
					</tr>
					<tr class="mozlex-countdown-row" style="<?php echo ! $cd_enabled ? 'opacity:0.6;' : ''; ?>">
						<th scope="row"><label for="mozlex-promo_popup_countdown_label"><?php esc_html_e( 'Tiêu đề đồng hồ', 'mozlex' ); ?></label></th>
						<td>
							<input type="text" id="mozlex-promo_popup_countdown_label" name="mozlex_options[promo_popup_countdown_label]" value="<?php echo esc_attr( $opts['promo_popup_countdown_label'] ?? 'Ưu đãi kết thúc sau:' ); ?>" class="regular-text" placeholder="VD: Ưu đãi kết thúc sau:">
						</td>
					</tr>
					<tr class="mozlex-countdown-row" style="<?php echo ! $cd_enabled ? 'opacity:0.6;' : ''; ?>">
						<th scope="row"><label for="mozlex-promo_popup_countdown_end"><?php esc_html_e( 'Thời điểm kết thúc đếm ngược', 'mozlex' ); ?></label></th>
						<td>
							<input type="datetime-local" id="mozlex-promo_popup_countdown_end" name="mozlex_options[promo_popup_countdown_end]" value="<?php echo esc_attr( $opts['promo_popup_countdown_end'] ?? '' ); ?>" style="padding:4px 8px;">
							<p class="description"><?php esc_html_e( 'Nếu để trống, hệ thống sẽ tự động đếm ngược đến 23:59:59 của Ngày kết thúc lên lịch bên dưới.', 'mozlex' ); ?></p>
						</td>
					</tr>
				</table>

				<hr>
				<h2><?php esc_html_e( 'Lên Lịch Chiến Dịch (Promotion Scheduling)', 'mozlex' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Bật hẹn giờ chiến dịch', 'mozlex' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="mozlex-promo_popup_schedule_enabled" name="mozlex_options[promo_popup_schedule_enabled]" value="1" <?php checked( $schedule_enabled ); ?>>
								<strong><?php esc_html_e( 'Giới hạn thời gian hiển thị theo ngày bắt đầu & ngày kết thúc', 'mozlex' ); ?></strong>
							</label>
							<p class="description"><?php esc_html_e( 'Nếu tắt, popup sẽ luôn xuất hiện khi công tắc "Kích hoạt Popup" bên trên được BẬT.', 'mozlex' ); ?></p>
						</td>
					</tr>
					<tr class="mozlex-schedule-row" style="<?php echo ! $schedule_enabled ? 'opacity:0.6;' : ''; ?>">
						<th scope="row"><label for="mozlex-promo_popup_start_date"><?php esc_html_e( 'Ngày bắt đầu (Start Date)', 'mozlex' ); ?></label></th>
						<td>
							<input type="date" id="mozlex-promo_popup_start_date" name="mozlex_options[promo_popup_start_date]" value="<?php echo esc_attr( $start_date ); ?>" style="padding:4px 8px;">
							<span class="description"><?php esc_html_e( 'Bắt đầu hiển thị từ 00:00 của ngày này. Để trống nếu muốn bắt đầu ngay.', 'mozlex' ); ?></span>
						</td>
					</tr>
					<tr class="mozlex-schedule-row" style="<?php echo ! $schedule_enabled ? 'opacity:0.6;' : ''; ?>">
						<th scope="row"><label for="mozlex-promo_popup_end_date"><?php esc_html_e( 'Ngày kết thúc (End Date)', 'mozlex' ); ?></label></th>
						<td>
							<input type="date" id="mozlex-promo_popup_end_date" name="mozlex_options[promo_popup_end_date]" value="<?php echo esc_attr( $end_date ); ?>" style="padding:4px 8px;">
							<span class="description"><?php esc_html_e( 'Tự động tắt sau 23:59 của ngày này. Để trống nếu không giới hạn ngày kết thúc.', 'mozlex' ); ?></span>
						</td>
					</tr>
				</table>
			</div>

			<!-- TAB 3: MÀU SẮC & THÔNG TIN LIÊN HỆ -->
			<div id="tab-colors-contact" class="mozlex-tab-pane" style="display:none;">
				<h2><?php esc_html_e( 'Màu sắc website — đổi màu chủ đạo', 'mozlex' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php foreach ( array( 'primary_color' => 'Màu chủ đạo (nút, link, viền kim đồng #c9a381)', 'primary_hover' => 'Màu hover (#d48a49)', 'color_dark' => 'Màu tối (header/footer #0a0a0a)' ) as $ckey => $clabel ) : ?>
						<?php $val = $opts[ $ckey ] ?? ( 'primary_color' === $ckey ? '#c9a381' : ( 'primary_hover' === $ckey ? '#d48a49' : '#0a0a0a' ) ); ?>
						<tr>
							<th scope="row"><label for="mozlex-<?php echo esc_attr( $ckey ); ?>"><?php echo esc_html( $clabel ); ?></label></th>
							<td style="display:flex; align-items:center; gap:10px;">
								<input type="color" id="mozlex-<?php echo esc_attr( $ckey ); ?>-picker" value="<?php echo esc_attr( $val ); ?>" style="width:44px; height:34px; padding:2px; border:1px solid #c3c4c7; border-radius:4px; cursor:pointer;">
								<input type="text" id="mozlex-<?php echo esc_attr( $ckey ); ?>" name="mozlex_options[<?php echo esc_attr( $ckey ); ?>]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" placeholder="#c9a381" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" style="width:120px;">
								<span style="color:#646970; font-size:0.85em;">VD: #c9a381 — đổi là web tự đổi màu</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>

				<hr>
				<h2><?php esc_html_e( 'Thông tin pháp lý công ty (hiển thị footer + liên hệ)', 'mozlex' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mozlex-company_name"><?php esc_html_e( 'Tên công ty đầy đủ', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-company_name" name="mozlex_options[company_name]" value="<?php echo esc_attr( $opts['company_name'] ?? '' ); ?>" class="regular-text" style="width:500px; max-width:100%;"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-hotline"><?php esc_html_e( 'Hotline / SĐT', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-hotline" name="mozlex_options[hotline]" value="<?php echo esc_attr( $opts['hotline'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-zalo"><?php esc_html_e( 'Zalo (số điện thoại Zalo)', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-zalo" name="mozlex_options[zalo]" value="<?php echo esc_attr( $opts['zalo'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-email"><?php esc_html_e( 'Email nhận yêu cầu tư vấn', 'mozlex' ); ?></label></th>
						<td><input type="email" id="mozlex-email" name="mozlex_options[email]" value="<?php echo esc_attr( $opts['email'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-address"><?php esc_html_e( 'Địa chỉ showroom / văn phòng', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-address" name="mozlex_options[address]" value="<?php echo esc_attr( $opts['address'] ?? '' ); ?>" class="large-text" style="max-width:600px;"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-mst"><?php esc_html_e( 'Mã số thuế (MST)', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-mst" name="mozlex_options[mst]" value="<?php echo esc_attr( $opts['mst'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-bank_account"><?php esc_html_e( 'Số tài khoản + Ngân hàng', 'mozlex' ); ?></label></th>
						<td><input type="text" id="mozlex-bank_account" name="mozlex_options[bank_account]" value="<?php echo esc_attr( $opts['bank_account'] ?? '' ); ?>" class="regular-text" style="width:500px; max-width:100%;"></td>
					</tr>
				</table>

				<hr>
				<h2><?php esc_html_e( 'Mạng xã hội', 'mozlex' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mozlex-facebook"><?php esc_html_e( 'Facebook URL', 'mozlex' ); ?></label></th>
						<td><input type="url" id="mozlex-facebook" name="mozlex_options[facebook]" value="<?php echo esc_attr( $opts['facebook'] ?? '' ); ?>" class="regular-text" style="width:420px; max-width:100%;"></td>
					</tr>
					<tr>
						<th scope="row"><label for="mozlex-youtube"><?php esc_html_e( 'YouTube URL', 'mozlex' ); ?></label></th>
						<td><input type="url" id="mozlex-youtube" name="mozlex_options[youtube]" value="<?php echo esc_attr( $opts['youtube'] ?? '' ); ?>" class="regular-text" style="width:420px; max-width:100%;"></td>
					</tr>
				</table>
			</div>

			<div style="margin-top:24px; padding-top:16px; border-top:1px solid #c3c4c7;">
				<?php submit_button( __( 'Lưu tất cả thay đổi', 'mozlex' ), 'primary large' ); ?>
			</div>
		</form>
	</div>

	<script>
	// Tab switching with URL hash & persistence
	(function () {
		var tabs = document.querySelectorAll('.mozlex-nav-tabs .nav-tab');
		var panes = document.querySelectorAll('.mozlex-tab-pane');
		if (!tabs.length || !panes.length) return;

		function switchTab(tabId) {
			tabs.forEach(function (t) {
				t.classList.toggle('nav-tab-active', t.getAttribute('data-tab') === tabId);
			});
			panes.forEach(function (p) {
				p.style.display = (p.id === tabId) ? 'block' : 'none';
			});
			if (history.replaceState) {
				history.replaceState(null, null, '#' + tabId);
			}
			try { sessionStorage.setItem('mozlex_active_tab', tabId); } catch(e){}
		}

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function (e) {
				e.preventDefault();
				switchTab(tab.getAttribute('data-tab'));
			});
		});

		var initialTab = window.location.hash ? window.location.hash.replace('#', '') : '';
		if (!initialTab) {
			try { initialTab = sessionStorage.getItem('mozlex_active_tab'); } catch(e){}
		}
		if (initialTab && document.getElementById(initialTab)) {
			switchTab(initialTab);
		}
	})();

	// Media library picker with live image preview & clear
	(function () {
		var btns = document.querySelectorAll('.mozlex-media-pick');
		var clearBtns = document.querySelectorAll('.mozlex-media-clear');
		if (!window.wp || !wp.media) return;

		var frame;
		var activeTargetInput = null;

		btns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				activeTargetInput = document.getElementById(btn.getAttribute('data-target'));
				if (!frame) {
					frame = wp.media({
						title: 'Chọn ảnh từ thư viện Media',
						button: { text: 'Dùng ảnh này' },
						library: { type: 'image' },
						multiple: false
					});
					frame.on('select', function () {
						var attachment = frame.state().get('selection').first().toJSON();
						if (activeTargetInput) {
							activeTargetInput.value = attachment.url;
							var prevId = activeTargetInput.id + '-preview';
							var prev = document.getElementById(prevId) || activeTargetInput.closest('td').querySelector('.mozlex-image-preview');
							if (prev) {
								prev.style.display = 'block';
								var img = prev.querySelector('img');
								if (img) img.src = attachment.url;
							}
							var clr = activeTargetInput.closest('td').querySelector('.mozlex-media-clear');
							if (clr) clr.style.display = 'inline-block';
						}
					});
				}
				frame.open();
			});
		});

		clearBtns.forEach(function (clr) {
			clr.addEventListener('click', function () {
				var target = document.getElementById(clr.getAttribute('data-target'));
				if (target) target.value = '';
				var prev = target ? (document.getElementById(target.id + '-preview') || target.closest('td').querySelector('.mozlex-image-preview')) : null;
				if (prev) prev.style.display = 'none';
				clr.style.display = 'none';
			});
		});
	})();

	// Dynamic schedule rows opacity
	(function () {
		var schedToggle = document.getElementById('mozlex-promo_popup_schedule_enabled');
		var rows = document.querySelectorAll('.mozlex-schedule-row');
		if (!schedToggle || !rows.length) return;

		schedToggle.addEventListener('change', function () {
			rows.forEach(function (r) {
				r.style.opacity = schedToggle.checked ? '1' : '0.6';
			});
		});
	})();

	// Dynamic countdown rows opacity
	(function () {
		var cdToggle = document.getElementById('mozlex-promo_popup_countdown_enabled');
		var rows = document.querySelectorAll('.mozlex-countdown-row');
		if (!cdToggle || !rows.length) return;

		cdToggle.addEventListener('change', function () {
			rows.forEach(function (r) {
				r.style.opacity = cdToggle.checked ? '1' : '0.6';
			});
		});
	})();

	// Color pickers sync
	(function () {
		['primary_color', 'primary_hover', 'color_dark'].forEach(function (key) {
			var picker = document.getElementById('mozlex-' + key + '-picker');
			var text   = document.getElementById('mozlex-' + key);
			if (!picker || !text) return;
			picker.addEventListener('input', function () { text.value = picker.value; });
			text.addEventListener('input', function () {
				var v = text.value.trim();
				if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(v)) picker.value = v;
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
