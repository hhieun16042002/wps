<?php
/**
 * Custom Post Type + Taxonomies cho catalogue Mozlex.
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'mozlex_register_cpt', 5 );
function mozlex_register_cpt() {
	register_post_type( 'product', array(
		'labels' => array(
			'name'          => __( 'Sản phẩm', 'mozlex' ),
			'singular_name' => __( 'Sản phẩm', 'mozlex' ),
			'add_new_item'  => __( 'Thêm sản phẩm mới', 'mozlex' ),
			'edit_item'     => __( 'Sửa sản phẩm', 'mozlex' ),
			'menu_name'     => __( 'Sản phẩm Mozlex', 'mozlex' ),
		),
		'public'        => true,
		'has_archive'   => 'san-pham',
		'rewrite'       => array(
			'slug'       => 'san-pham',
			'with_front' => false,
		),
		'menu_icon'     => 'dashicons-lock',
		'menu_position' => 20,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'  => true,
	) );

	register_taxonomy( 'product_category', 'product', array(
		'labels' => array(
			'name'          => __( 'Danh mục sản phẩm', 'mozlex' ),
			'singular_name' => __( 'Danh mục', 'mozlex' ),
			'menu_name'     => __( 'Danh mục', 'mozlex' ),
			'add_new_item'  => __( 'Thêm danh mục mới', 'mozlex' ),
			'edit_item'     => __( 'Sửa danh mục', 'mozlex' ),
			'search_items'  => __( 'Tìm danh mục', 'mozlex' ),
			'all_items'     => __( 'Tất cả danh mục', 'mozlex' ),
		),
		'hierarchical'      => true,
		'rewrite'           => array( 'slug' => 'nhom-san-pham', 'with_front' => false, 'hierarchical' => false ),
		'show_in_rest'      => true,
		'show_admin_column' => true,
	) );

	register_post_type( 'solution', array(
		'labels' => array(
			'name'          => __( 'Giải pháp', 'mozlex' ),
			'singular_name' => __( 'Giải pháp', 'mozlex' ),
			'add_new_item'  => __( 'Thêm giải pháp', 'mozlex' ),
			'edit_item'     => __( 'Sửa giải pháp', 'mozlex' ),
			'menu_name'     => __( 'Giải pháp', 'mozlex' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => 'edit.php?post_type=product',
		'menu_icon'     => 'dashicons-admin-multisite',
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'  => true,
		'has_archive'   => false,
	) );

	register_post_type( 'service', array(
		'labels' => array(
			'name'          => __( 'Dịch vụ', 'mozlex' ),
			'singular_name' => __( 'Dịch vụ', 'mozlex' ),
			'add_new_item'  => __( 'Thêm dịch vụ', 'mozlex' ),
			'edit_item'     => __( 'Sửa dịch vụ', 'mozlex' ),
			'menu_name'     => __( 'Dịch vụ', 'mozlex' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => 'edit.php?post_type=product',
		'menu_icon'     => 'dashicons-hammer',
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'  => true,
		'has_archive'   => false,
	) );

	register_post_type( 'hero_slide', array(
		'labels' => array(
			'name'          => __( 'Banner Slider', 'mozlex' ),
			'singular_name' => __( 'Slide', 'mozlex' ),
			'add_new_item'  => __( 'Thêm slide', 'mozlex' ),
			'edit_item'     => __( 'Sửa slide', 'mozlex' ),
			'menu_name'     => __( 'Banner Slider', 'mozlex' ),
			'all_items'     => __( 'Tất cả slides', 'mozlex' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'menu_icon'     => 'dashicons-images-alt2',
		'menu_position' => 21,
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
		'show_in_rest'  => true,
		'has_archive'   => false,
	) );

	$flat_taxonomies = array(
		'unlock_method' => __( 'Phương thức mở khóa', 'mozlex' ),
		'application'   => __( 'Loại cửa', 'mozlex' ),
		'price_range'   => __( 'Mức giá', 'mozlex' ),
		'color'         => __( 'Màu sắc', 'mozlex' ),
		'feature'       => __( 'Tính năng', 'mozlex' ),
	);

	foreach ( $flat_taxonomies as $slug => $label ) {
		register_taxonomy( $slug, 'product', array(
			'labels'            => array(
				'name'          => $label,
				'singular_name' => $label,
				'menu_name'     => $label,
			),
			'hierarchical'      => false,
			'rewrite'           => array( 'slug' => $slug, 'with_front' => false ),
			'show_in_rest'      => true,
			'show_admin_column' => false,
		) );
	}
}

/**
 * Tạo sẵn các taxonomy term chuẩn khi kích hoạt theme.
 */
add_action( 'after_switch_theme', 'mozlex_seed_terms' );
function mozlex_seed_terms() {
	$terms = array(
		'unlock_method' => array(
			'face-id' => 'Face ID',
			'van-tay' => 'Vân tay',
			'mat-ma'  => 'Mật mã',
			'the-tu'  => 'Thẻ từ',
			'chia-co' => 'Chìa cơ',
		),
		'application'   => array(
			'cuua-go-nhua'    => 'Cửa gỗ – nhựa',
			'cua-nhom-xingfa' => 'Cửa nhôm Xingfa',
			'cua-kinh'        => 'Cửa kính',
			'cua-cong'        => 'Cửa cổng',
		),
		'price_range'   => array(
			'duoi-5-trieu'      => 'Dưới 5 triệu',
			'tu-5-10-trieu'     => '5 – 10 triệu',
			'tren-10-trieu'     => 'Trên 10 triệu',
			'lien-he-tu-van'    => 'Liên hệ tư vấn',
		),
		'color'         => array(
			'den'        => 'Đen',
			'pvd'        => 'PVD',
			'ghi'        => 'Ghi',
			'inox'       => 'Inox',
			'champagne'  => 'Champagne',
			'coffee'     => 'Coffee',
			'van-go'     => 'Vân gỗ',
			'dong'       => 'Đồng',
		),
		'feature'       => array(
			'face-id'                  => 'Face ID',
			'fingerprint'              => 'Fingerprint',
			'pin-code'                 => 'PIN',
			'rfid-card'                => 'RFID Card',
			'mechanical-key'           => 'Mechanical Key',
			'video-call'               => 'Video Call',
			'app-tuya'                 => 'TUYA',
			'app-icsee-home'           => 'iCSee Home',
			'app-ttlock'               => 'TTLOCK',
			'vietnamese-voice'         => 'Vietnamese Voice',
			'anti-intrusion-alert'     => 'Anti-intrusion Alert',
			'virtual-password'         => 'Virtual Password',
			'opening-history'          => 'Opening History',
			'temporary-password'       => 'Temporary Password',
			'inox-304-core'            => 'Stainless Steel 304 Core',
			'5-safety-bolts'           => '5 Safety Bolts',
			'2-safety-bolts'           => '2 Safety Bolts',
			'low-battery-alert'        => 'Low Battery Alert',
			'emergency-usb'            => 'Emergency USB',
			'pull-push-mechanism'      => 'Pull & Push Mechanism',
			'fpc-360-sensor'           => 'FPC 360 Sensor',
		),
	);

	foreach ( $terms as $tax => $map ) {
		foreach ( $map as $slug => $name ) {
			if ( ! term_exists( $slug, $tax ) ) {
				wp_insert_term( $name, $tax, array( 'slug' => $slug ) );
			}
		}
	}

	// Nhóm sản phẩm chính — đa ngành, Khóa chỉ là 1 nhóm.
	$cats = array(
		'khoa'               => 'Khóa & phụ kiện',
		'khoa-thong-minh'    => 'Khóa thông minh',
		'khoa-tay-gat'       => 'Khóa tay gạt',
		'khoa-keo-day'       => 'Khóa kéo đẩy',
		'phu-kien-linh-kien' => 'Phụ kiện & linh kiện',
		'dieu-hoa-hvac'      => 'Điều hòa & HVAC',
		'camera-an-ninh'     => 'Camera & An ninh',
		'kiem-soat-ra-vao'   => 'Kiểm soát ra vào',
		'thiet-bi-tu-dong'   => 'Thiết bị tự động',
		'thiet-bi-dien'      => 'Thiết bị điện & điều khiển',
		'thiet-bi-khac'      => 'Thiết bị khác',
	);
	foreach ( $cats as $slug => $name ) {
		if ( ! term_exists( $slug, 'product_category' ) ) {
			wp_insert_term( $name, 'product_category', array( 'slug' => $slug ) );
		}
	}
	// Gán parent cho các nhóm khóa con để tạo hierarchy Khóa → con
	$parent = get_term_by( 'slug', 'khoa', 'product_category' );
	if ( $parent ) {
		foreach ( array( 'khoa-thong-minh','khoa-tay-gat','khoa-keo-day','phu-kien-linh-kien' ) as $child_slug ) {
			$child = get_term_by( 'slug', $child_slug, 'product_category' );
			if ( $child && (int) $child->parent !== (int) $parent->term_id ) {
				wp_update_term( $child->term_id, 'product_category', array( 'parent' => $parent->term_id ) );
			}
		}
	}

	// Seed Giải pháp & Dịch vụ mẫu nếu chưa có
	if ( ! get_posts( array( 'post_type' => 'solution', 'numberposts' => 1 ) ) ) {
		$solutions = array(
			array( 'Nhà ở / biệt thự', 'Tư vấn khóa & HVAC cho không gian sống', 'Tư vấn khóa & HVAC cho không gian sống, an ninh và tiện nghi cho gia đình.' ),
			array( 'Chung cư', 'An ninh & kiểm soát ra vào đồng bộ', 'Giải pháp đồng bộ cho căn hộ, hành lang và tầng hầm.' ),
			array( 'Văn phòng', 'Giải pháp tự động & tiết kiệm năng lượng', 'Tự động hóa cửa, điều hòa và kiểm soát truy cập văn phòng.' ),
			array( 'Khách sạn', 'Khóa thẻ từ & điều hòa trung tâm', 'Thẻ từ, VRV và quản lý phòng thông minh.' ),
			array( 'Nhà hàng', 'Bếp & thông gió chuyên nghiệp', 'Hút mùi, thông gió và an ninh bếp.' ),
			array( 'Cửa hàng', 'Camera AI & kiểm soát cửa', 'Giám sát, báo động và kiểm soát ra vào.' ),
			array( 'Nhà xưởng', 'Tủ điện & thiết bị công nghiệp', 'Điện, tự động hóa và an toàn lao động.' ),
			array( 'Tòa nhà', 'VRV/VRF & access control tập trung', 'Quản lý tập trung cho tòa nhà cao tầng.' ),
			array( 'Công trình thương mại', 'Tích hợp hệ thống toàn diện', 'Tích hợp khóa, HVAC, camera và điện.' ),
		);
		foreach ( $solutions as $i => $s ) {
			wp_insert_post( array( 'post_type' => 'solution', 'post_title' => $s[0], 'post_content' => $s[2], 'post_excerpt' => $s[1], 'post_status' => 'publish', 'menu_order' => $i ) );
		}
	}
	if ( ! get_posts( array( 'post_type' => 'service', 'numberposts' => 1 ) ) ) {
		$services = array(
			array( 'TƯ VẤN GIẢI PHÁP', 'Tư vấn kỹ thuật, khảo sát công trình và đề xuất phương án thiết bị tối ưu cho từng nhu cầu.' ),
			array( 'CUNG CẤP THIẾT BỊ', 'Cung cấp thiết bị chính hãng, đầy đủ CO/CQ, giá cạnh tranh và giao hàng đúng tiến độ.' ),
			array( 'THIẾT KẾ HỆ THỐNG', 'Thiết kế bản vẽ, tính toán công suất và tích hợp hệ thống cho công trình.' ),
			array( 'THI CÔNG & LẮP ĐẶT', 'Thi công chuyên nghiệp, đấu nối, chạy thử và bàn giao đúng kỹ thuật.' ),
			array( 'BẢO HÀNH', 'Bảo hành 36 tháng, đổi mới 6 tháng khi lỗi NSX, hỗ trợ kỹ thuật nhanh.' ),
			array( 'BẢO TRÌ & NÂNG CẤP', 'Bảo trì định kỳ, vệ sinh, nâng cấp hệ thống để duy trì hiệu suất tối ưu.' ),
		);
		foreach ( $services as $i => $s ) {
			wp_insert_post( array( 'post_type' => 'service', 'post_title' => $s[0], 'post_content' => $s[1], 'post_status' => 'publish', 'menu_order' => $i ) );
		}
	}
	// Seed Banner Slider mẫu nếu chưa có (để admin thấy ngay cách custom)
	if ( ! get_posts( array( 'post_type' => 'hero_slide', 'numberposts' => 1 ) ) ) {
		$demo_slides = array(
			array(
				'title'     => 'TƯ VẤN – CUNG CẤP – LẮP ĐẶT THIẾT BỊ',
				'eyebrow'   => 'ĐƠN VỊ TƯ VẤN • CUNG CẤP • LẮP ĐẶT',
				'decorative'=> 'Giải pháp toàn diện',
				'subtitle'  => 'Thiết bị toàn diện cho công trình, doanh nghiệp và gia đình. Không chỉ cung cấp thiết bị — chúng tôi cung cấp giải pháp.',
				'cta'       => 'NHẬN TƯ VẤN',
				'cta_url'   => '/lien-he/',
				'cta2'      => 'XEM SẢN PHẨM',
				'cta2_url'  => '/san-pham/',
			),
			array(
				'title'     => 'KHÓA THÔNG MINH CAO CẤP',
				'eyebrow'   => 'MOZLEX • CHÍNH HÃNG • BỀN BỈ',
				'decorative'=> 'An toàn – Sang trọng',
				'subtitle'  => 'An toàn, sang trọng — giải pháp tối ưu cho biệt thự, chung cư và văn phòng.',
				'cta'       => 'KHÁM PHÁ NGAY',
				'cta_url'   => '/nhom-san-pham/khoa-thong-minh/',
				'cta2'      => 'TƯ VẤN MIỄN PHÍ',
				'cta2_url'  => '/lien-he/',
			),
			array(
				'title'     => 'ĐIỀU HÒA & HVAC CHUYÊN NGHIỆP',
				'eyebrow'   => 'VRV • VRF • DÂN DỤNG • TRUNG TÂM',
				'decorative'=> 'Tiết kiệm – Bền bỉ',
				'subtitle'  => 'Thiết kế, cung cấp và thi công hệ thống điều hòa cho mọi quy mô công trình.',
				'cta'       => 'XEM GIẢI PHÁP',
				'cta_url'   => '/nhom-san-pham/dieu-hoa-hvac/',
				'cta2'      => 'LIÊN HỆ BÁO GIÁ',
				'cta2_url'  => '/lien-he/',
			),
		);
		foreach ( $demo_slides as $i => $s ) {
			$pid = wp_insert_post( array( 'post_type' => 'hero_slide', 'post_title' => $s['title'], 'post_status' => 'publish', 'menu_order' => $i ) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				update_post_meta( $pid, 'mozlex_slide_eyebrow', $s['eyebrow'] );
				update_post_meta( $pid, 'mozlex_slide_decorative', $s['decorative'] );
				update_post_meta( $pid, 'mozlex_slide_subtitle', $s['subtitle'] );
				update_post_meta( $pid, 'mozlex_slide_cta_text', $s['cta'] );
				update_post_meta( $pid, 'mozlex_slide_cta_url', $s['cta_url'] );
				update_post_meta( $pid, 'mozlex_slide_cta2_text', $s['cta2'] );
				update_post_meta( $pid, 'mozlex_slide_cta2_url', $s['cta2_url'] );
			}
		}
	}
}

/**
 * Tự động đồng bộ Rewrite Rules nếu đường dẫn /san-pham/ bị thiếu trên máy chủ mới.
 */
add_action( 'init', function () {
	$rules = get_option( 'rewrite_rules' );
	if ( is_array( $rules ) && ! isset( $rules['san-pham/([^/]+)(?:/([0-9]+))?/?$'] ) ) {
		flush_rewrite_rules( false );
	}
}, 99 );
