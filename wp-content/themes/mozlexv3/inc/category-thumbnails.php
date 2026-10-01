<?php
/**
 * Ảnh đại diện cho Danh mục sản phẩm (product_category).
 * Cho phép sửa ảnh đại diện ngay trong admin Thêm/Sửa danh mục.
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Enqueue media cho trang term
add_action( 'admin_enqueue_scripts', function( $hook ) {
    // Chỉ load ở trang edit-tags của product_category
    $screen = get_current_screen();
    if ( ! $screen || $screen->taxonomy !== 'product_category' ) return;
    wp_enqueue_media();
} );

// Thêm field khi Thêm mới danh mục
add_action( 'product_category_add_form_fields', function() {
    ?>
    <div class="form-field term-thumbnail-wrap">
        <label><?php esc_html_e( 'Ảnh đại diện', 'mozlex' ); ?></label>
        <div id="product_category_thumbnail" style="float:left; margin-right:10px;">
            <img src="<?php echo esc_url( function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src() : '' ); ?>" width="60" height="60" style="display:block; border:1px solid #c3c4c7; background:#fff;" />
        </div>
        <div style="line-height:60px;">
            <input type="hidden" id="product_category_thumbnail_id" name="product_category_thumbnail_id" />
            <button type="button" class="upload_image_button button"><?php esc_html_e( 'Chọn ảnh', 'mozlex' ); ?></button>
            <button type="button" class="remove_image_button button" style="display:none;"><?php esc_html_e( 'Xóa', 'mozlex' ); ?></button>
        </div>
        <div style="clear:both;"></div>
        <p class="description"><?php esc_html_e( 'Chọn ảnh đại diện cho danh mục — sẽ hiện ở trang chủ DANH MỤC CHI TIẾT và thumbnail danh mục.', 'mozlex' ); ?></p>
        <script>
        jQuery(function($){
            var frame;
            var $thumb = $('#product_category_thumbnail');
            var $input = $('#product_category_thumbnail_id');
            $('.upload_image_button').on('click', function(e){
                e.preventDefault();
                if(frame) frame.open();
                else {
                    frame = wp.media({title:'<?php echo esc_js( __( 'Chọn ảnh đại diện danh mục', 'mozlex' ) ); ?>', button:{text:'<?php echo esc_js( __( 'Dùng ảnh này', 'mozlex' ) ); ?>'}, library:{type:'image'}, multiple:false});
                    frame.on('select', function(){
                        var att = frame.state().get('selection').first().toJSON();
                        $thumb.find('img').attr('src', att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url);
                        $input.val(att.id);
                        $('.remove_image_button').show();
                    });
                    frame.open();
                }
            });
            $('.remove_image_button').on('click', function(e){
                e.preventDefault();
                $thumb.find('img').attr('src','<?php echo esc_js( function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src() : '' ); ?>');
                $input.val('');
                $(this).hide();
            });
        });
        </script>
    </div>
    <?php
} );

// Thêm field khi Sửa danh mục
add_action( 'product_category_edit_form_fields', function( $term ) {
    $thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
    $image = $thumb_id ? wp_get_attachment_thumb_url( $thumb_id ) : ( function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src() : '' );
    if ( ! $image ) $image = '';
    ?>
    <tr class="form-field term-thumbnail-wrap">
        <th scope="row"><label><?php esc_html_e( 'Ảnh đại diện', 'mozlex' ); ?></label></th>
        <td>
            <div id="product_category_thumbnail" style="float:left; margin-right:10px;">
                <img src="<?php echo esc_url( $image ); ?>" width="60" height="60" style="display:block; border:1px solid #c3c4c7; background:#fff;" />
            </div>
            <div style="line-height:60px;">
                <input type="hidden" id="product_category_thumbnail_id" name="product_category_thumbnail_id" value="<?php echo esc_attr( $thumb_id ); ?>" />
                <button type="button" class="upload_image_button button"><?php esc_html_e( 'Chọn ảnh', 'mozlex' ); ?></button>
                <button type="button" class="remove_image_button button" <?php echo $thumb_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Xóa', 'mozlex' ); ?></button>
            </div>
            <div style="clear:both;"></div>
            <p class="description"><?php esc_html_e( 'Chọn ảnh đại diện cho danh mục — sẽ hiện ở trang chủ DANH MỤC CHI TIẾT.', 'mozlex' ); ?></p>
            <script>
            jQuery(function($){
                var frame;
                var $thumb = $('#product_category_thumbnail');
                var $input = $('#product_category_thumbnail_id');
                $('.upload_image_button').on('click', function(e){
                    e.preventDefault();
                    if(frame) frame.open();
                    else {
                        frame = wp.media({title:'<?php echo esc_js( __( 'Chọn ảnh đại diện danh mục', 'mozlex' ) ); ?>', button:{text:'<?php echo esc_js( __( 'Dùng ảnh này', 'mozlex' ) ); ?>'}, library:{type:'image'}, multiple:false});
                        frame.on('select', function(){
                            var att = frame.state().get('selection').first().toJSON();
                            $thumb.find('img').attr('src', att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url);
                            $input.val(att.id);
                            $('.remove_image_button').show();
                        });
                        frame.open();
                    }
                });
                $('.remove_image_button').on('click', function(e){
                    e.preventDefault();
                    $thumb.find('img').attr('src','<?php echo esc_js( function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src() : '' ); ?>');
                    $input.val('');
                    $(this).hide();
                });
            });
            </script>
        </td>
    </tr>
    <?php
} );

// Lưu meta khi tạo/sửa
add_action( 'created_product_category', function( $term_id ) {
    if ( isset( $_POST['product_category_thumbnail_id'] ) ) {
        update_term_meta( $term_id, 'thumbnail_id', absint( $_POST['product_category_thumbnail_id'] ) );
    }
}, 10, 1 );
add_action( 'edited_product_category', function( $term_id ) {
    if ( isset( $_POST['product_category_thumbnail_id'] ) ) {
        update_term_meta( $term_id, 'thumbnail_id', absint( $_POST['product_category_thumbnail_id'] ) );
    }
}, 10, 1 );

// Cột thumbnail trong danh sách danh mục
add_filter( 'manage_edit-product_category_columns', function( $cols ) {
    $new = array();
    foreach ( $cols as $k => $v ) {
        $new[$k] = $v;
        if ( $k === 'name' ) $new['thumb'] = __( 'Ảnh', 'mozlex' );
    }
    return $new;
} );
add_filter( 'manage_product_category_custom_column', function( $out, $col, $term_id ) {
    if ( $col === 'thumb' ) {
        $thumb_id = get_term_meta( $term_id, 'thumbnail_id', true );
        if ( $thumb_id ) {
            $out = wp_get_attachment_image( $thumb_id, array(40,40), false, array('style'=>'border:1px solid #c3c4c7; background:#fff;') );
        } else {
            $out = '<span style="color:#8c8f94;">—</span>';
        }
    }
    return $out;
}, 10, 3 );

// Helper lấy URL ảnh danh mục
function mozlex_category_thumbnail_url( $term_id, $size = 'mozlex-category' ) {
    $thumb_id = get_term_meta( $term_id, 'thumbnail_id', true );
    if ( $thumb_id ) {
        $url = wp_get_attachment_image_url( $thumb_id, $size );
        if ( $url ) return $url;
    }
    return '';
}
