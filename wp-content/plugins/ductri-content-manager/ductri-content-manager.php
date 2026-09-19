<?php
/**
 * Plugin Name: Đức Trí 226 — Quản lý Nội Dung & Trạng Thái Hiển Thị
 * Description: Bật/tắt hiển thị sản phẩm, tin tức, dự án và danh mục ngay trong bảng quản trị WordPress.
 * Version: 1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dt226_project_fields() {
	return array(
		'category' => 'Nhóm dự án', 'badge' => 'Nhãn loại công trình',
		'status' => 'Tiến độ (ví dụ: Đã bàn giao)', 'location' => 'Địa điểm',
		'highlight' => 'Điểm nổi bật', 'details' => 'Thông tin chi tiết — mỗi dòng: Nhãn | Nội dung',
		'image' => 'Ảnh từ liên kết (dùng khi chưa chọn Ảnh đại diện)', 'image_alt' => 'Mô tả ảnh',
	);
}
function dt226_sanitize_category( $value ) {
	return in_array( $value, array( 'biet-thu', 'toa-nha', 'khach-san' ), true ) ? $value : 'biet-thu';
}
function dt226_register_projects() {
	register_post_type( 'ductri_project', array(
		'labels' => array( 'name' => 'Dự án', 'singular_name' => 'Dự án', 'add_new_item' => 'Thêm dự án', 'edit_item' => 'Chỉnh sửa dự án', 'all_items' => 'Tất cả dự án', 'not_found' => 'Chưa có dự án' ),
		'public' => false, 'show_ui' => true, 'show_in_menu' => true, 'show_in_rest' => true,
		'menu_icon' => 'dashicons-building', 'menu_position' => 21, 'map_meta_cap' => true,
		'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'revisions', 'custom-fields' ),
	) );
	foreach ( dt226_project_fields() as $key => $label ) {
		$sanitize = 'sanitize_text_field';
		if ( 'details' === $key ) $sanitize = 'sanitize_textarea_field';
		if ( 'image' === $key ) $sanitize = 'esc_url_raw';
		if ( 'category' === $key ) $sanitize = 'dt226_sanitize_category';
		register_post_meta( 'ductri_project', '_dt226_' . $key, array(
			'type' => 'string', 'single' => true, 'show_in_rest' => true,
			'sanitize_callback' => $sanitize,
			'auth_callback' => static function ( $allowed, $meta_key, $post_id ) { return current_user_can( 'edit_post', $post_id ); },
		) );
	}
}
add_action( 'init', 'dt226_register_projects' );
// Use the familiar editor with image picker and labelled project fields.
add_filter( 'use_block_editor_for_post_type', static function ( $use, $type ) { return 'ductri_project' === $type ? false : $use; }, 10, 2 );

function dt226_seed_projects() {
	dt226_register_projects();
	if ( get_option( 'dt226_projects_migrated' ) ) return;
	$rows = json_decode( file_get_contents( __DIR__ . '/projects.json' ), true );
	if ( ! is_array( $rows ) || count( $rows ) !== 6 ) wp_die( 'Dữ liệu dự án không hợp lệ.' );
	foreach ( $rows as $i => $row ) {
		$existing = get_posts( array( 'post_type' => 'ductri_project', 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future', 'trash' ), 'meta_key' => '_dt226_legacy_id', 'meta_value' => (string) ( $i + 1 ), 'fields' => 'ids', 'posts_per_page' => 1 ) );
		if ( $existing ) continue;
		$meta = array( '_dt226_legacy_id' => (string) ( $i + 1 ) );
		foreach ( $row['meta'] as $key => $value ) $meta['_dt226_' . $key] = $value;
		$id = wp_insert_post( array( 'post_type' => 'ductri_project', 'post_title' => $row['title'], 'post_content' => $row['content'], 'post_status' => 'publish', 'menu_order' => $row['menu_order'], 'meta_input' => $meta ), true );
		if ( is_wp_error( $id ) ) wp_die( esc_html( $id->get_error_message() ) );
	}
	update_option( 'dt226_projects_migrated', 1, false );
}
register_activation_hook( __FILE__, 'dt226_seed_projects' );

add_filter( 'template_include', static function ( $template ) {
	if ( is_page_template( 'page-templates/template-projects.php' ) && function_exists( 'mozlex_opt' ) ) return __DIR__ . '/projects-template.php';
	return $template;
}, 99 );

add_action( 'add_meta_boxes_ductri_project', static function () {
	add_meta_box( 'dt226-project-info', 'Thông tin công trình', 'dt226_project_box', 'ductri_project', 'normal', 'high' );
} );
function dt226_project_box( $post ) {
	wp_nonce_field( 'dt226_save_project', 'dt226_project_nonce' );
	echo '<p>Nhập phần mô tả ở khung nội dung phía trên. Chọn <strong>Ảnh đại diện</strong> để tải hoặc thay ảnh. Trong <strong>Thuộc tính</strong>, số thứ tự nhỏ sẽ hiển thị trước.</p>';
	foreach ( dt226_project_fields() as $key => $label ) {
		$value = get_post_meta( $post->ID, '_dt226_' . $key, true );
		echo '<p><label for="dt226-' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		if ( 'category' === $key ) {
			echo '<select id="dt226-category" name="dt226[category]">';
			foreach ( array( 'biet-thu' => 'Biệt thự & Nhà phố', 'toa-nha' => 'Tòa nhà & Văn phòng', 'khach-san' => 'Khách sạn & Nghỉ dưỡng' ) as $slug => $name ) echo '<option value="' . esc_attr( $slug ) . '" ' . selected( $value, $slug, false ) . '>' . esc_html( $name ) . '</option>';
			echo '</select>';
		} elseif ( 'details' === $key ) {
			echo '<textarea class="widefat" rows="5" id="dt226-details" name="dt226[details]">' . esc_textarea( $value ) . '</textarea>';
		} else {
			echo '<input class="widefat" type="' . ( 'image' === $key ? 'url' : 'text' ) . '" id="dt226-' . esc_attr( $key ) . '" name="dt226[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '">';
		}
		echo '</p>';
	}
}
add_action( 'save_post_ductri_project', static function ( $id ) {
	if ( wp_is_post_revision( $id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) return;
	if ( ! current_user_can( 'edit_post', $id ) || ! isset( $_POST['dt226_project_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt226_project_nonce'] ) ), 'dt226_save_project' ) ) return;
	$values = isset( $_POST['dt226'] ) && is_array( $_POST['dt226'] ) ? wp_unslash( $_POST['dt226'] ) : array();
	foreach ( dt226_project_fields() as $key => $label ) {
		if ( isset( $values[$key] ) && is_string( $values[$key] ) ) update_post_meta( $id, '_dt226_' . $key, $values[$key] );
	}
} );

// --- 7. QUẢN LÝ TRẠNG THÁI BẬT / ẨN (SẢN PHẨM, TIN TỨC, DỰ ÁN, DANH MỤC) ---

function dt226_visibility_post_types() {
	return array( 'post', 'ductri_project', 'product' );
}

function dt226_visibility_taxonomies() {
	return array( 'category', 'product_category' );
}

// 7.1. Cột Bật/Tắt cho Post Types (Sản phẩm, Tin tức, Dự án)
foreach ( dt226_visibility_post_types() as $type ) {
	add_filter( 'manage_' . $type . '_posts_columns', static function ( $columns ) {
		$columns['dt226_visibility'] = 'Bật / Tắt';
		return $columns;
	} );
	add_action( 'manage_' . $type . '_posts_custom_column', 'dt226_visibility_column', 10, 2 );
}

function dt226_visibility_column( $column, $id ) {
	if ( 'dt226_visibility' !== $column ) return;
	$status = get_post_status( $id );
	if ( ! in_array( $status, array( 'publish', 'draft' ), true ) ) {
		echo '<span class="description">Tùy chỉnh trong sửa bài</span>';
		return;
	}
	$on = 'publish' === $status;
	$title = get_the_title( $id );

	echo '<div class="dt226-switch-cell">';
	echo '<span class="dt226-pill ' . ( $on ? 'dt226-pill-on' : 'dt226-pill-off' ) . '">';
	echo '<span class="dt226-led" aria-hidden="true"></span>';
	echo '<span class="dt226-pill-text">' . ( $on ? 'Đang hiển thị' : 'Đang tạm ẩn' ) . '</span>';
	echo '</span>';

	if ( current_user_can( 'edit_post', $id ) && ( $on || current_user_can( get_post_type_object( get_post_type( $id ) )->cap->publish_posts ) ) ) {
		printf(
			'<button type="button" class="dt226-btn-action dt226-toggle %s" data-id="%d" data-status="%s" data-nonce="%s" role="switch" aria-checked="%s" aria-label="%s" title="%s">%s</button>',
			$on ? 'is-turning-off' : 'is-turning-on',
			(int) $id,
			$on ? 'draft' : 'publish',
			esc_attr( wp_create_nonce( 'dt226_toggle_' . $id . '_' . $status ) ),
			$on ? 'true' : 'false',
			esc_attr( ( $on ? 'Tạm ẩn: ' : 'Hiển thị: ' ) . $title ),
			esc_attr( $on ? 'Bấm để tạm ẩn khỏi website' : 'Bấm để hiển thị công khai' ),
			$on ? 'Tắt hiển thị' : 'Bật hiển thị'
		);
	}
	echo '</div>';
	echo '<noscript><p>Dùng Sửa nhanh → Trạng thái để thay đổi.</p></noscript>';
}

// 7.2. Quản lý Bật/Ẩn cho Taxonomies (Danh mục Sản phẩm & Danh mục Tin tức)
add_action( 'init', static function () {
	foreach ( dt226_visibility_taxonomies() as $tax ) {
		register_term_meta( $tax, '_dt226_term_hidden', array(
			'type'              => 'boolean',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'auth_callback'     => static function () {
				return current_user_can( 'manage_categories' );
			},
		) );
	}
} );

foreach ( dt226_visibility_taxonomies() as $tax ) {
	add_action( $tax . '_add_form_fields', 'dt226_taxonomy_add_fields' );
	add_action( $tax . '_edit_form_fields', 'dt226_taxonomy_edit_fields', 10, 2 );
	add_action( 'created_' . $tax, 'dt226_save_term_visibility' );
	add_action( 'edited_' . $tax, 'dt226_save_term_visibility' );
	add_filter( 'manage_edit-' . $tax . '_columns', static function ( $columns ) {
		$columns['dt226_visibility'] = 'Bật / Tắt';
		return $columns;
	} );
	add_filter( 'manage_' . $tax . '_custom_column', 'dt226_taxonomy_column_content', 10, 3 );
}

function dt226_taxonomy_add_fields() {
	?>
	<div class="form-field term-visibility-wrap">
		<label for="dt226_term_hidden"><strong>Trạng thái hiển thị</strong></label>
		<select name="dt226_term_hidden" id="dt226_term_hidden">
			<option value="0" selected>Bật (Hiển thị công khai trên website)</option>
			<option value="1">Ẩn (Tạm ẩn khỏi menu, widget và trang chủ)</option>
		</select>
		<p class="description">Khi ẩn, danh mục này sẽ tạm thời không xuất hiện ngoài trang web nhưng vẫn giữ nguyên dữ liệu trong quản trị.</p>
	</div>
	<?php
}

function dt226_taxonomy_edit_fields( $term ) {
	$hidden = (int) get_term_meta( $term->term_id, '_dt226_term_hidden', true );
	?>
	<tr class="form-field term-visibility-wrap">
		<th scope="row"><label for="dt226_term_hidden">Trạng thái hiển thị</label></th>
		<td>
			<select name="dt226_term_hidden" id="dt226_term_hidden" class="postform">
				<option value="0" <?php selected( $hidden, 0 ); ?>>Bật (Hiển thị công khai trên website)</option>
				<option value="1" <?php selected( $hidden, 1 ); ?>>Ẩn (Tạm ẩn khỏi menu, widget và trang chủ)</option>
			</select>
			<p class="description">Khi chọn Ẩn, khách vãng lai sẽ không thấy danh mục này trong menu, bộ lọc và các danh sách ngoài frontend.</p>
		</td>
	</tr>
	<?php
}

function dt226_save_term_visibility( $term_id ) {
	if ( ! current_user_can( 'manage_categories' ) ) return;
	if ( isset( $_POST['dt226_term_hidden'] ) ) {
		$hidden = ( '1' === (string) $_POST['dt226_term_hidden'] ) ? 1 : 0;
		update_term_meta( $term_id, '_dt226_term_hidden', $hidden );
	}
}

function dt226_taxonomy_column_content( $content, $column_name, $term_id ) {
	if ( 'dt226_visibility' !== $column_name ) return $content;
	$hidden = (int) get_term_meta( $term_id, '_dt226_term_hidden', true );
	$is_active = ( 1 !== $hidden );
	$term = get_term( $term_id );
	if ( ! $term || is_wp_error( $term ) ) return $content;

	$out = '<div class="dt226-switch-cell">';
	$out .= '<span class="dt226-pill ' . ( $is_active ? 'dt226-pill-on' : 'dt226-pill-off' ) . '">';
	$out .= '<span class="dt226-led" aria-hidden="true"></span>';
	$out .= '<span class="dt226-pill-text">' . ( $is_active ? 'Đang hiển thị' : 'Đang tạm ẩn' ) . '</span>';
	$out .= '</span>';

	if ( current_user_can( 'manage_categories' ) ) {
		$nonce = wp_create_nonce( 'dt226_term_toggle_' . $term_id );
		$target = $is_active ? '1' : '0';
		$label = $is_active ? 'Tắt hiển thị' : 'Bật hiển thị';
		$action_class = $is_active ? 'is-turning-off' : 'is-turning-on';
		$out .= sprintf(
			'<button type="button" class="dt226-btn-action dt226-term-toggle %s" data-id="%d" data-taxonomy="%s" data-hidden="%s" data-nonce="%s" role="switch" aria-checked="%s" aria-label="%s: %s" title="%s">%s</button>',
			esc_attr( $action_class ),
			(int) $term_id,
			esc_attr( $term->taxonomy ),
			esc_attr( $target ),
			esc_attr( $nonce ),
			$is_active ? 'true' : 'false',
			esc_attr( $label ),
			esc_attr( $term->name ),
			esc_attr( $is_active ? 'Bấm để tạm ẩn khỏi website' : 'Bấm để hiển thị công khai' ),
			esc_html( $label )
		);
	}
	$out .= '</div>';
	return $out;
}

// 7.3. Action xử lý Toggle cho Term (Danh mục)
add_action( 'admin_post_dt226_term_toggle', static function () {
	if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) wp_die( 'Phương thức không hợp lệ.', '', array( 'response' => 405 ) );
	$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
	$term = get_term( $term_id );
	if ( ! $term || is_wp_error( $term ) || ! in_array( $term->taxonomy, dt226_visibility_taxonomies(), true ) || ! current_user_can( 'manage_categories' ) ) {
		wp_die( 'Bạn không có quyền chỉnh sửa danh mục.', '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'dt226_term_toggle_' . $term_id );
	$hidden = ( isset( $_POST['hidden'] ) && '1' === (string) $_POST['hidden'] ) ? 1 : 0;
	update_term_meta( $term_id, '_dt226_term_hidden', $hidden );

	$post_type = ( 'product_category' === $term->taxonomy ) ? 'product' : 'post';
	$redirect_url = add_query_arg( array(
		'taxonomy'           => $term->taxonomy,
		'post_type'          => $post_type,
		'dt226_term_updated' => $hidden ? 'hidden' : 'visible',
	), admin_url( 'edit-tags.php' ) );

	wp_safe_redirect( $redirect_url );
	exit;
} );

// 7.4. Action xử lý Toggle cho Post Type (Sản phẩm, Tin tức, Dự án)
add_action( 'admin_post_dt226_toggle', static function () {
	if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) wp_die( 'Phương thức không hợp lệ.', '', array( 'response' => 405 ) );
	$id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$post = get_post( $id );
	if ( ! $post || ! in_array( $post->post_type, dt226_visibility_post_types(), true ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_die( 'Bạn không có quyền chỉnh sửa nội dung này.', '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'dt226_toggle_' . $id . '_' . $post->post_status );
	$target = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';
	if ( ! in_array( $post->post_status, array( 'publish', 'draft' ), true ) || ( 'publish' === $post->post_status ? 'draft' : 'publish' ) !== $target ) {
		wp_die( 'Trạng thái đã thay đổi. Vui lòng tải lại danh sách.' );
	}
	if ( 'publish' === $target && ! current_user_can( get_post_type_object( $post->post_type )->cap->publish_posts ) ) {
		wp_die( 'Bạn không có quyền xuất bản.', '', array( 'response' => 403 ) );
	}
	$update = array( 'ID' => $id, 'post_status' => $target );
	if ( 'publish' === $target && $post->post_date_gmt > current_time( 'mysql', true ) ) {
		$update['post_date'] = current_time( 'mysql' );
		$update['post_date_gmt'] = current_time( 'mysql', true );
	}
	$result = wp_update_post( $update, true );
	if ( is_wp_error( $result ) ) wp_die( esc_html( $result->get_error_message() ) );
	wp_safe_redirect( add_query_arg( array( 'post_type' => $post->post_type, 'dt226_updated' => $target ), admin_url( 'edit.php' ) ) );
	exit;
} );

// 7.5. Lọc danh mục ẩn ngoài Frontend (Ẩn khỏi Menu, Widget, Query)
add_filter( 'get_terms_args', static function ( $args, $taxonomies ) {
	if ( is_admin() ) return $args;
	$supported = dt226_visibility_taxonomies();
	$taxes = (array) $taxonomies;
	$intersect = array_intersect( $taxes, $supported );
	if ( empty( $intersect ) && ! in_array( '', $taxes, true ) ) return $args;

	$meta_query = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array();
	$meta_query[] = array(
		'relation' => 'OR',
		array(
			'key'     => '_dt226_term_hidden',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => '_dt226_term_hidden',
			'value'   => '1',
			'compare' => '!=',
		),
	);
	$args['meta_query'] = $meta_query;
	return $args;
}, 10, 2 );

// 7.6. Chặn khách vãng lai truy cập trực tiếp vào trang archive của danh mục bị ẩn
add_action( 'template_redirect', static function () {
	if ( is_admin() || current_user_can( 'manage_categories' ) ) return;
	if ( is_tax( 'product_category' ) || is_category() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->term_id ) ) {
			$hidden = (int) get_term_meta( $term->term_id, '_dt226_term_hidden', true );
			if ( 1 === $hidden ) {
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				nocache_headers();
				$template = get_404_template();
				if ( $template ) {
					include $template;
				}
				exit;
			}
		}
	}
} );

// 7.7. Nạp JS, CSS & Thông báo Admin
add_action( 'admin_head', static function () {
	echo '<style>
		.column-dt226_visibility {
			width: 130px !important;
			text-align: center !important;
			vertical-align: middle !important;
		}
		.dt226-switch-cell {
			display: inline-flex;
			flex-direction: column;
			align-items: center;
			gap: 6px;
			padding: 4px 0;
			min-width: 110px;
		}
		.dt226-pill {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 3px 10px;
			border-radius: 9999px;
			font-size: 11px;
			font-weight: 600;
			line-height: 1.4;
			letter-spacing: 0.2px;
			transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
		}
		.dt226-pill-on {
			background: #ecfdf5;
			color: #065f46;
			border: 1px solid #a7f3d0;
		}
		.dt226-pill-off {
			background: #f1f5f9;
			color: #475569;
			border: 1px solid #cbd5e1;
		}
		.dt226-led {
			width: 7px;
			height: 7px;
			border-radius: 50%;
			display: inline-block;
			flex-shrink: 0;
		}
		.dt226-pill-on .dt226-led {
			background: #10b981;
			box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
			animation: dt226-pulse 2.2s infinite;
		}
		.dt226-pill-off .dt226-led {
			background: #94a3b8;
		}
		@keyframes dt226-pulse {
			0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
			70% { transform: scale(1); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0); }
			100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
		}
		.dt226-btn-action {
			cursor: pointer;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 4px;
			padding: 4px 12px;
			border-radius: 6px;
			font-size: 11px;
			font-weight: 600;
			line-height: 1.3;
			border: 1px solid #cbd5e1;
			background: #ffffff;
			color: #334155;
			transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
			box-shadow: 0 1px 2px rgba(0,0,0,0.04);
		}
		.dt226-btn-action:hover {
			background: #f8fafc;
			border-color: #94a3b8;
			color: #0f172a;
			transform: translateY(-1px);
			box-shadow: 0 2px 4px rgba(0,0,0,0.08);
		}
		.dt226-btn-action.is-turning-on {
			background: #0f172a;
			border-color: #0f172a;
			color: #ffffff;
		}
		.dt226-btn-action.is-turning-on:hover {
			background: #1e293b;
			border-color: #1e293b;
			color: #ffffff;
		}
		.dt226-btn-action.is-turning-off {
			background: #ffffff;
			border-color: #e2e8f0;
			color: #64748b;
		}
		.dt226-btn-action.is-turning-off:hover {
			background: #fef2f2;
			border-color: #fecaca;
			color: #b91c1c;
		}
		.dt226-btn-action:focus {
			outline: 2px solid #3b82f6;
			outline-offset: 1px;
		}
		.dt226-btn-action.updating-message {
			opacity: 0.6;
			cursor: wait;
			pointer-events: none;
		}
	</style>';
} );

add_action( 'admin_enqueue_scripts', static function ( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen ) return;
	$is_post_list = 'edit.php' === $hook && in_array( $screen->post_type, dt226_visibility_post_types(), true );
	$is_term_list = 'edit-tags.php' === $hook && in_array( $screen->taxonomy, dt226_visibility_taxonomies(), true );
	if ( ! $is_post_list && ! $is_term_list ) return;

	wp_enqueue_script( 'dt226-content-admin', plugins_url( 'admin.js', __FILE__ ), array(), '1.2.0', true );
	wp_localize_script( 'dt226-content-admin', 'DT226Admin', array( 'url' => admin_url( 'admin-post.php' ) ) );
} );

add_action( 'admin_notices', static function () {
	$screen = get_current_screen();
	if ( ! $screen ) return;

	if ( 'edit' === $screen->base && in_array( $screen->post_type, dt226_visibility_post_types(), true ) ) {
		$pt_label = 'nội dung';
		if ( 'product' === $screen->post_type ) $pt_label = 'sản phẩm';
		elseif ( 'post' === $screen->post_type ) $pt_label = 'tin tức / bài viết';
		elseif ( 'ductri_project' === $screen->post_type ) $pt_label = 'dự án';
		echo '<div class="notice notice-info is-dismissible"><p><strong>Quản lý hiển thị ' . esc_html( $pt_label ) . ':</strong> Bấm <strong>Tắt</strong> để tạm ẩn khỏi website (chuyển sang Bản nháp), bấm <strong>Bật</strong> để hiển thị công khai trở lại.</p></div>';
	}

	if ( 'edit-tags' === $screen->base && in_array( $screen->taxonomy, dt226_visibility_taxonomies(), true ) ) {
		echo '<div class="notice notice-info is-dismissible"><p><strong>Quản lý hiển thị danh mục:</strong> Bấm <strong>Ẩn</strong> để tạm ẩn danh mục khỏi menu, widget và bộ lọc ngoài website; bấm <strong>Bật</strong> để hiển thị lại ngay lập tức.</p></div>';
	}
} );
