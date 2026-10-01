<?php
/**
 * Form liên hệ: admin-post handler + nonce + honeypot.
 * Lưu submission thành post type nội bộ và gửi email (không có gì hard-code).
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_mozlex_contact', 'mozlex_handle_contact' );
add_action( 'admin_post_nopriv_mozlex_contact', 'mozlex_handle_contact' );

function mozlex_handle_contact() {
	// Rate limit: 5 requests / 10 phút / IP
	$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	$key = 'mozlex_rl_' . md5( $ip );
	if ( get_transient( $key ) && (int) get_transient( $key ) >= 5 ) {
		wp_die( esc_html__( 'Bạn gửi quá nhanh, vui lòng thử lại sau 10 phút.', 'mozlex' ), '', array( 'response' => 429 ) );
	}

	$fail = function ( $message ) {
		$ref = wp_get_referer();
		// Chặn open redirect — chỉ cho phép host nội bộ
		if ( $ref ) {
			$host = parse_url( $ref, PHP_URL_HOST );
			$home_host = parse_url( home_url(), PHP_URL_HOST );
			if ( $host && $home_host && $host !== $home_host && ! str_ends_with( $host, '.trycloudflare.com' ) ) {
				$ref = home_url( '/lien-he/' );
			}
		}
		wp_safe_redirect( add_query_arg(
			array( 'contact' => 'error', 'reason' => rawurlencode( $message ) ),
			$ref ?: home_url( '/lien-he/' )
		) );
		exit;
	};

	if ( ! isset( $_POST['mozlex_contact_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mozlex_contact_nonce'] ), 'mozlex_contact' ) ) {
		$fail( __( 'Phiên không hợp lệ, vui lòng thử lại.', 'mozlex' ) );
	}

	// Honeypot — bot điền hidden field.
	if ( ! empty( $_POST['company'] ) ) {
		$fail( __( 'Yêu cầu bị từ chối.', 'mozlex' ) );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	// Chặn Email header injection — xóa \r \n
	$name    = preg_replace( '/[\r\n]+/', ' ', $name );
	$name    = trim( preg_replace( '/\s+/', ' ', $name ) );
	$phone   = isset( $_POST['phone'] ) ? preg_replace( '/[^0-9+\.\-\s]/', '', wp_unslash( $_POST['phone'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$area    = isset( $_POST['area'] ) ? sanitize_text_field( wp_unslash( $_POST['area'] ) ) : '';
	$product = isset( $_POST['product'] ) ? sanitize_text_field( wp_unslash( $_POST['product'] ) ) : '';
	$needs   = isset( $_POST['needs'] ) ? sanitize_textarea_field( wp_unslash( $_POST['needs'] ) ) : '';
	// Giới hạn độ dài needs tránh spam DB
	$needs   = mb_substr( $needs, 0, 2000 );

	if ( ! $name || mb_strlen( $name ) > 100 ) {
		$fail( __( 'Vui lòng nhập họ tên.', 'mozlex' ) );
	}
	if ( ! $phone || ! preg_match( '/^(\+?84|0)[0-9\s\.\-]{8,12}$/', $phone ) ) {
		$fail( __( 'Số điện thoại không hợp lệ.', 'mozlex' ) );
	}
	if ( ! $email || ! is_email( $email ) ) {
		$fail( __( 'Email không hợp lệ.', 'mozlex' ) );
	}

	// Tăng counter rate limit sau khi validate OK
	$cnt = (int) get_transient( $key );
	set_transient( $key, $cnt + 1, 10 * MINUTE_IN_SECONDS );

	$clean_referer = esc_url_raw( wp_get_referer() ?: home_url( '/lien-he/' ) );
	$post_id = wp_insert_post( array(
		'post_type'    => 'mozlead',
		'post_status'  => 'publish',
		'post_title'   => sprintf( '%s — %s (%s)', $name, $phone, current_time( 'mysql' ) ),
		'meta_input'   => array(
			'mozlead_name'      => $name,
			'mozlead_phone'     => $phone,
			'mozlead_email'     => $email,
			'mozlead_area'      => $area,
			'mozlead_product'   => $product,
			'mozlead_needs'     => $needs,
			'mozlead_page'      => $clean_referer,
		),
	) );

	$email = mozlex_opt( 'email', get_option( 'admin_email' ) );
	$admin_email = mozlex_opt( 'email', get_option( 'admin_email' ) );
	if ( $post_id && is_email( $admin_email ) ) {
		wp_mail(
			$admin_email,
			sprintf( '[Mozlex] Yêu cầu tư vấn từ %s', $name ),
			sprintf(
				"Họ tên: %s\nĐiện thoại: %s\nEmail: %s\nKhu vực: %s\nSản phẩm quan tâm: %s\nNhu cầu: %s\nGửi từ: %s",
				$name, $phone, $email, $area, $product, $needs, wp_get_referer()
			),
			array( 'Reply-To: ' . $name . ' <' . $email . '>' )
		);
		// Gửi email xác nhận cho khách (chuyên nghiệp)
		if ( is_email( $email ) ) {
			wp_mail(
				$email,
				'[Mozlex] Đã nhận yêu cầu tư vấn của bạn',
				sprintf(
					"Chào %s,\n\nCảm ơn bạn đã gửi yêu cầu tư vấn%s%s.\nChúng tôi đã nhận được thông tin và sẽ liên hệ lại trong 30 phút (giờ hành chính).\n\n— Đội ngũ Mozlex / Đức Trí 226\nHotline: %s\n",
					$name,
					$product ? ' về ' . $product : '',
					$area ? ' tại ' . $area : '',
					mozlex_opt( 'hotline', '0355514686' )
				)
			);
		}
	}

	// Validate redirect host
	$ref_host = parse_url( $clean_referer, PHP_URL_HOST );
	$home_host = parse_url( home_url(), PHP_URL_HOST );
	if ( $ref_host && $home_host && $ref_host !== $home_host && ! str_ends_with( $ref_host, '.trycloudflare.com' ) ) {
		$clean_referer = home_url( '/lien-he/' );
	}
	$redirect = $post_id
		? add_query_arg( 'contact', 'success', $clean_referer )
		: add_query_arg( array( 'contact' => 'error', 'reason' => 'db' ), $clean_referer );

	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Submission CPT — private, chỉ admin xem được.
 */
add_action( 'init', function () {
	register_post_type( 'mozlead', array(
		'labels'       => array(
			'name'          => __( 'Yêu cầu tư vấn', 'mozlex' ),
			'singular_name' => __( 'Yêu cầu tư vấn', 'mozlex' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => 'edit.php?post_type=product',
		'capability_type'     => 'post',
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'        => true,
		'supports'            => array( 'title' ),
		'register_meta_box_cb' => function ( $post ) {
			add_meta_box( 'mozlead_data', __( 'Chi tiết', 'mozlex' ), function () use ( $post ) {
				foreach ( array( 'mozlead_name' => 'Họ tên', 'mozlead_phone' => 'Điện thoại', 'mozlead_email' => 'Email', 'mozlead_area' => 'Khu vực', 'mozlead_product' => 'Sản phẩm quan tâm', 'mozlead_needs' => 'Nhu cầu', 'mozlead_page' => 'Gửi từ trang' ) as $key => $label ) {
					printf( '<p><strong>%s:</strong> %s</p>', esc_html( $label ), esc_html( get_post_meta( $post->ID, $key, true ) ) );
				}
			}, null, 'normal', 'high' );
		},
	) );
} );
