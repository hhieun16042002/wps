<?php
/**
 * Meta box sản phẩm — Model, giá, thông số, gallery, so sánh.
 * Tự viết không phụ thuộc ACF để giữ site nhẹ.
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'mozlex_product_data',
		__( 'Dữ liệu Mozlex', 'mozlex' ),
		'mozlex_render_product_metabox',
		'product',
		'normal',
		'high'
	);
	add_meta_box(
		'mozlex_hero_slide_data',
		__( 'Nội dung slide (ghi chữ trên ảnh)', 'mozlex' ),
		'mozlex_render_hero_slide_metabox',
		'hero_slide',
		'normal',
		'high'
	);
} );

function mozlex_render_product_metabox( $post ) {
	wp_nonce_field( 'mozlex_save_meta', 'mozlex_meta_nonce' );
	echo '<div style="background:#f0f6fc; border:1px solid #c3c4c7; padding:10px 12px; border-radius:6px; margin-bottom:14px; font-size:0.9em;"><strong>Hướng dẫn nhanh (dễ):</strong> Muốn bán <em>Sơn</em> — vào <a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=product_category&post_type=product' ) ) . '" target="_blank">Sản phẩm → Nhóm sản phẩm</a> → <strong>Thêm mới</strong> “Sơn” (Thư mục cha: — Không có —) → thêm “Sơn đỏ”, “Sơn đen” (Thư mục cha: Sơn). Khi <strong>Thêm sản phẩm</strong>, chọn danh mục ở cột phải, đặt Model/Mã, Ảnh đại diện, Gallery — tên & danh mục tự hiện ở trang chủ & menu, không cần code.</div>';

	$fields = array(
		'mozlex_model'        => array( __( 'Model / Mã SP (VD: A16, Sơn Đỏ 5L)', 'mozlex' ), 'text' ),
		'mozlex_price'        => array( __( 'Giá (VNĐ — bỏ trống nếu ẩn giá)', 'mozlex' ), 'number' ),
		'mozlex_price_status' => array( 'Trạng thái giá', array(
			''           => __( 'Không hiển thị', 'mozlex' ),
			'an-gia'     => __( 'Hiển thị "Liên hệ tư vấn"', 'mozlex' ),
			'tu-van'     => __( 'Giá tham khảo — nhận tư vấn', 'mozlex' ),
		) ),
		'mozlex_material'     => array( __( 'Chất liệu / Loại (VD: Đồng, Sơn nước, Thép)', 'mozlex' ), 'text' ),
		'mozlex_color'        => array( __( 'Màu sắc (VD: Vàng 24K, Đỏ, Đen, Xanh)', 'mozlex' ), 'text' ),
		'mozlex_door'         => array( __( 'Quy cách / Độ dày (VD: 38-50mm, 5L, 18L)', 'mozlex' ), 'text' ),
		'mozlex_core_type'    => array( __( 'Loại / Củ (VD: 5845, Sơn lót, Sơn bóng)', 'mozlex' ), 'text' ),
		'mozlex_dimensions'   => array( __( 'Kích thước (VD: 143x83x53mm, 5L)', 'mozlex' ), 'text' ),
		'mozlex_capacity'     => array( __( 'Dung lượng / Định mức (VD: 100 vân tay, 20m²/L)', 'mozlex' ), 'text' ),
		'mozlex_battery'      => array( __( 'Nguồn / Bảo quản (VD: Pin, Khô ráo)', 'mozlex' ), 'text' ),
		'mozlex_unlock'       => array( __( 'Thuộc tính khác (ghi đè, phân cách bằng dấu ",")', 'mozlex' ), 'text' ),
	);

	echo '<div class="mozlex-metagrid">';
	foreach ( $fields as $key => $def ) {
		list( $label, $type ) = $def;
		$value                = get_post_meta( $post->ID, $key, true );
		echo '<p class="mozlex-metafield"><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label>';
		if ( is_array( $type ) ) {
			echo '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '">';
			foreach ( $type as $val => $text ) {
				echo '<option value="' . esc_attr( $val ) . '" ' . selected( $value, $val, false ) . '>' . esc_html( $text ) . '</option>';
			}
			echo '</select>';
		} elseif ( 'number' === $type ) {
			echo '<input type="number" class="widefat" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" min="0" step="1000">';
		} else {
			echo '<input type="text" class="widefat" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
		}
		echo '</p>';
	}
	echo '</div>';

	$specs = get_post_meta( $post->ID, 'mozlex_tech_specs', true );
	$lines = '';
	if ( is_array( $specs ) ) {
		foreach ( $specs as $row ) {
			$lines .= implode( '|', $row ) . "\n";
		}
	} else {
		$lines = (string) $specs;
	}

	echo '<p><label for="mozlex_tech_specs"><strong>' . esc_html__( 'Thông số kỹ thuật (mỗi dòng: Nhãn|Giá trị)', 'mozlex' ) . '</strong></label>' .
		'<textarea rows="8" style="width:100%" id="mozlex_tech_specs" name="mozlex_tech_specs">' . esc_textarea( $lines ) . '</textarea></p>';

	// Gallery — picker dễ chỉnh, giống mozlex (thumbnail mờ, hover đậm)
	$gallery_val = get_post_meta( $post->ID, 'mozlex_gallery', true );
	$gallery_ids = array_filter( array_map( 'absint', explode( ',', (string) $gallery_val ) ) );
	echo '<div class="mozlex-gallery-field" style="margin:16px 0; padding:14px; background:#fff; border:1px solid #ccd0d4; border-radius:6px;">';
	echo '<p style="margin:0 0 8px;"><strong>' . esc_html__( 'Gallery sản phẩm — ảnh slide (giống Mozlex: ảnh dưới mờ, lướt tới đậm)', 'mozlex' ) . '</strong><br><span style="color:#646970; font-size:0.85em;">' . esc_html__( 'Ảnh đại diện (Featured image) bên phải là ảnh chính. Gallery dưới là các ảnh slide/thumbnail — tất cả ảnh sẽ hiện mờ, hover/lướt tới sẽ đậm lên như mozlex.vn. Kéo thả để sắp xếp (thứ tự ID).', 'mozlex' ) . '</span></p>';
	echo '<div id="mozlex-gallery-preview" style="display:flex; flex-wrap:wrap; gap:8px; min-height:60px; margin-bottom:10px; padding:8px; background:#f6f7f7; border:1px dashed #c3c4c7; border-radius:4px;">';
	if ( $gallery_ids ) {
		foreach ( $gallery_ids as $gid ) {
			$thumb = wp_get_attachment_image_url( $gid, 'thumbnail' );
			if ( $thumb ) echo '<span class="mozlex-gallery-thumb" data-id="' . esc_attr( $gid ) . '" style="position:relative; display:inline-block; width:70px; height:70px; border:1px solid #c3c4c7; border-radius:4px; overflow:hidden; background:#fff;"><img src="' . esc_url( $thumb ) . '" style="width:100%; height:100%; object-fit:cover;"><button type="button" class="mozlex-gallery-remove" data-id="' . esc_attr( $gid ) . '" style="position:absolute; top:2px; right:2px; width:18px; height:18px; background:#d63638; color:#fff; border:none; border-radius:50%; cursor:pointer; font-size:11px; line-height:18px; text-align:center;">×</button></span>';
		}
	} else {
		echo '<span style="color:#8c8f94; font-size:0.9em; padding:12px;">' . esc_html__( 'Chưa có ảnh gallery — bấm "Chọn ảnh" để thêm (giữ Ctrl để chọn nhiều).', 'mozlex' ) . '</span>';
	}
	echo '</div>';
	echo '<input type="hidden" id="mozlex_gallery" name="mozlex_gallery" value="' . esc_attr( $gallery_val ) . '">';
	echo '<p style="margin:0; display:flex; gap:8px;"><button type="button" class="button button-primary" id="mozlex-gallery-pick">' . esc_html__( 'Chọn ảnh gallery', 'mozlex' ) . '</button> <button type="button" class="button" id="mozlex-gallery-clear">' . esc_html__( 'Xóa tất cả', 'mozlex' ) . '</button> <span style="color:#646970; font-size:0.85em; margin-left:8px;">ID: <code id="mozlex-gallery-ids" style="font-size:0.9em;">' . esc_html( $gallery_val ?: '—' ) . '</code></span></p>';
	echo '</div>';

	echo '<p><label><input type="checkbox" name="mozlex_featured" value="1" ' . checked( get_post_meta( $post->ID, 'mozlex_featured', true ), '1', false ) . '> ' .
		esc_html__( 'Sản phẩm nổi bật ở trang chủ ("Những thiết kế đáng chú ý")', 'mozlex' ) . '</label></p>';
	// Inline JS cho gallery picker — dùng wp.media (đã enqueue ở admin_enqueue_scripts)
	echo '<script>(function(){var input=document.getElementById("mozlex_gallery"),preview=document.getElementById("mozlex-gallery-preview"),pick=document.getElementById("mozlex-gallery-pick"),clear=document.getElementById("mozlex-gallery-clear"),idsEl=document.getElementById("mozlex-gallery-ids"),frame;function refresh(ids){if(!input||!preview) return;input.value=ids.join(",");if(idsEl) idsEl.textContent=ids.length?ids.join(","):"—";if(!ids.length){preview.innerHTML="<span style=\"color:#8c8f94; font-size:0.9em; padding:12px;\">Chưa có ảnh gallery — bấm \"Chọn ảnh\" để thêm (giữ Ctrl để chọn nhiều).</span>"; return;}preview.innerHTML="";ids.forEach(function(id){var url="";try{var att=wp.media.attachment(id); if(att){url=att.get("sizes")&&att.get("sizes").thumbnail?att.get("sizes").thumbnail.url:att.get("url");} }catch(e){} var span=document.createElement("span");span.className="mozlex-gallery-thumb";span.setAttribute("data-id",id);span.style.cssText="position:relative; display:inline-block; width:70px; height:70px; border:1px solid #c3c4c7; border-radius:4px; overflow:hidden; background:#fff;";span.innerHTML="<img src=\""+(url||"")+"\" style=\"width:100%; height:100%; object-fit:cover;\"><button type=\"button\" class=\"mozlex-gallery-remove\" data-id=\""+id+"\" style=\"position:absolute; top:2px; right:2px; width:18px; height:18px; background:#d63638; color:#fff; border:none; border-radius:50%; cursor:pointer; font-size:11px; line-height:18px; text-align:center;\">×</button>";preview.appendChild(span);});}function getIds(){return (input&&input.value?input.value.split(",").map(function(s){return parseInt(s,10);}).filter(Boolean):[]);}if(pick){pick.addEventListener("click",function(e){e.preventDefault();if(frame) frame.open();else{frame=wp.media({title:"Chọn ảnh gallery — giữ Ctrl để chọn nhiều",button:{text:"Thêm vào gallery"},library:{type:"image"},multiple:true});frame.on("select",function(){var sel=frame.state().get("selection").toJSON();var ids=getIds();sel.forEach(function(att){if(ids.indexOf(att.id)===-1) ids.push(att.id);});refresh(ids);});frame.open();}});}if(clear){clear.addEventListener("click",function(e){e.preventDefault();refresh([]);});}if(preview){preview.addEventListener("click",function(e){var btn=e.target.closest(".mozlex-gallery-remove");if(!btn) return;var id=parseInt(btn.getAttribute("data-id"),10);var ids=getIds().filter(function(x){return x!==id;});refresh(ids);});} })();</script>';
}

add_action( 'save_post_product', function ( $post_id ) {
	if ( ! isset( $_POST['mozlex_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mozlex_meta_nonce'] ), 'mozlex_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text_keys = array(
		'mozlex_model', 'mozlex_material', 'mozlex_color', 'mozlex_door',
		'mozlex_core_type', 'mozlex_dimensions', 'mozlex_capacity',
		'mozlex_battery', 'mozlex_price_status', 'mozlex_unlock',
	);
	foreach ( $text_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
	// Gallery — chỉ cho phép ID số, tránh stored XSS / SSRF (tăng lên 20 để lấy hết ảnh mozlex)
	if ( isset( $_POST['mozlex_gallery'] ) ) {
		$raw = wp_unslash( $_POST['mozlex_gallery'] );
		$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
		// Giới hạn 20 ảnh (mozlex có sản phẩm 14 ảnh), mỗi ID phải là attachment hợp lệ
		$ids = array_slice( $ids, 0, 20 );
		$valid = array();
		foreach ( $ids as $id ) {
			if ( wp_attachment_is_image( $id ) ) $valid[] = $id;
		}
		update_post_meta( $post_id, 'mozlex_gallery', implode( ',', $valid ) );
	}

	if ( isset( $_POST['mozlex_price'] ) ) {
		update_post_meta( $post_id, 'mozlex_price', (float) sanitize_text_field( wp_unslash( $_POST['mozlex_price'] ) ) );
	}

	update_post_meta( $post_id, 'mozlex_featured', isset( $_POST['mozlex_featured'] ) ? '1' : '' );

	// Tech specs: các dòng "Nhãn|Giá trị".
	if ( isset( $_POST['mozlex_tech_specs'] ) ) {
		$rows = array();
		foreach ( preg_split( '/\r\n|\r|\n/', wp_unslash( $_POST['mozlex_tech_specs'] ) ) as $line ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per-cell below.
			if ( false === strpos( $line, '|' ) ) {
				continue;
			}
			list( $label, $value ) = array_map( 'trim', explode( '|', $line, 2 ) );
			if ( $label && $value ) {
				$rows[] = array( sanitize_text_field( $label ), sanitize_text_field( $value ) );
			}
		}
		update_post_meta( $post_id, 'mozlex_tech_specs', $rows );
	}
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;
	$screen = get_current_screen();
	if ( ! $screen || 'product' !== $screen->post_type ) return;
	wp_enqueue_media();
} );

/* ---------- Hero Slide metabox ---------- */
function mozlex_render_hero_slide_metabox( $post ) {
	wp_nonce_field( 'mozlex_save_slide', 'mozlex_slide_nonce' );
	$eyebrow   = get_post_meta( $post->ID, 'mozlex_slide_eyebrow', true );
	$decorative= get_post_meta( $post->ID, 'mozlex_slide_decorative', true );
	$subtitle  = get_post_meta( $post->ID, 'mozlex_slide_subtitle', true );
	$cta_text  = get_post_meta( $post->ID, 'mozlex_slide_cta_text', true );
	$cta_url   = get_post_meta( $post->ID, 'mozlex_slide_cta_url', true );
	$cta2_text = get_post_meta( $post->ID, 'mozlex_slide_cta2_text', true );
	$cta2_url  = get_post_meta( $post->ID, 'mozlex_slide_cta2_url', true );
	?>
	<p><em><?php esc_html_e( 'Ảnh nền: dùng Ảnh đại diện (Featured image) bên phải. Tiêu đề slide = Tiêu đề bài viết phía trên. Kéo thả Thứ tự (Order) để sắp xếp slide.', 'mozlex' ); ?></em></p>
	<p><label for="mozlex_slide_eyebrow"><strong><?php esc_html_e( 'Dòng nhỏ trên tiêu đề (eyebrow)', 'mozlex' ); ?></strong></label>
		<input type="text" class="widefat" id="mozlex_slide_eyebrow" name="mozlex_slide_eyebrow" value="<?php echo esc_attr( $eyebrow ); ?>" placeholder="VD: ĐƠN VỊ TƯ VẤN • CUNG CẤP • LẮP ĐẶT"></p>
	<p><label for="mozlex_slide_decorative"><strong><?php esc_html_e( 'Dòng trang trí (cursive, dưới tiêu đề)', 'mozlex' ); ?></strong></label>
		<input type="text" class="widefat" id="mozlex_slide_decorative" name="mozlex_slide_decorative" value="<?php echo esc_attr( $decorative ); ?>" placeholder="VD: Giải pháp toàn diện"></p>
	<p><label for="mozlex_slide_subtitle"><strong><?php esc_html_e( 'Mô tả ngắn (subtitle)', 'mozlex' ); ?></strong></label>
		<textarea rows="2" class="widefat" id="mozlex_slide_subtitle" name="mozlex_slide_subtitle" placeholder="Mô tả ngắn dưới tiêu đề"><?php echo esc_textarea( $subtitle ); ?></textarea></p>
	<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
		<p><label for="mozlex_slide_cta_text"><strong><?php esc_html_e( 'Nút chính — Chữ', 'mozlex' ); ?></strong></label>
			<input type="text" class="widefat" id="mozlex_slide_cta_text" name="mozlex_slide_cta_text" value="<?php echo esc_attr( $cta_text ); ?>" placeholder="NHẬN TƯ VẤN"></p>
		<p><label for="mozlex_slide_cta_url"><strong><?php esc_html_e( 'Nút chính — Link', 'mozlex' ); ?></strong></label>
			<input type="text" class="widefat" id="mozlex_slide_cta_url" name="mozlex_slide_cta_url" value="<?php echo esc_attr( $cta_url ); ?>" placeholder="/lien-he/"></p>
	</div>
	<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
		<p><label for="mozlex_slide_cta2_text"><strong><?php esc_html_e( 'Nút phụ — Chữ', 'mozlex' ); ?></strong></label>
			<input type="text" class="widefat" id="mozlex_slide_cta2_text" name="mozlex_slide_cta2_text" value="<?php echo esc_attr( $cta2_text ); ?>" placeholder="XEM SẢN PHẨM"></p>
		<p><label for="mozlex_slide_cta2_url"><strong><?php esc_html_e( 'Nút phụ — Link', 'mozlex' ); ?></strong></label>
			<input type="text" class="widefat" id="mozlex_slide_cta2_url" name="mozlex_slide_cta2_url" value="<?php echo esc_attr( $cta2_url ); ?>" placeholder="/san-pham/"></p>
	</div>
	<p style="color:#666; font-size:0.85em;"><?php esc_html_e( 'Để trống nút thì nút đó sẽ ẩn. Thứ tự slide = Menu Order (Thuộc tính trang → Thứ tự).', 'mozlex' ); ?></p>
	<?php
}

add_action( 'save_post_hero_slide', function ( $post_id ) {
	if ( ! isset( $_POST['mozlex_slide_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mozlex_slide_nonce'] ), 'mozlex_save_slide' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	$keys = array( 'mozlex_slide_eyebrow','mozlex_slide_decorative','mozlex_slide_subtitle','mozlex_slide_cta_text','mozlex_slide_cta_url','mozlex_slide_cta2_text','mozlex_slide_cta2_url' );
	foreach ( $keys as $k ) {
		if ( isset( $_POST[ $k ] ) ) {
			$val = wp_unslash( $_POST[ $k ] );
			if ( str_ends_with( $k, '_url' ) ) $val = esc_url_raw( $val );
			elseif ( 'mozlex_slide_subtitle' === $k ) $val = sanitize_textarea_field( $val );
			else $val = sanitize_text_field( $val );
			update_post_meta( $post_id, $k, $val );
		}
	}
} );

/**
 * Chỉ số tiền hỗ trợ admin column.
 */
add_filter( 'manage_product_posts_columns', function ( $cols ) {
	$cols['model'] = __( 'Model', 'mozlex' );
	$cols['price'] = __( 'Giá', 'mozlex' );
	return $cols;
}, 5 );

add_action( 'manage_product_posts_custom_column', function ( $col, $post_id ) {
	if ( 'model' === $col ) {
		echo esc_html( get_post_meta( $post_id, 'mozlex_model', true ) ?: '—' );
	}
	if ( 'price' === $col ) {
		echo esc_html( mozlex_price_text( $post_id ) ?: '—' );
	}
}, 10, 2 );
