<?php
/**
 * Admin page sửa Thông số kỹ thuật từng sản phẩm
 *
 * @package mozlex
 */
if ( ! defined('ABSPATH') ) exit;

add_action('admin_menu', function(){
    add_submenu_page(
        'edit.php?post_type=product',
        'Sửa thông số kỹ thuật',
        'Thông số kỹ thuật',
        'edit_posts',
        'mozlex-specs',
        'mozlex_render_specs_page'
    );
});

function mozlex_render_specs_page(){
    if ( ! current_user_can('edit_posts') ) return;
    // Save
    if ( isset($_POST['mozlex_specs_nonce']) && wp_verify_nonce($_POST['mozlex_specs_nonce'], 'mozlex_specs_save') ) {
        $updated=0;
        foreach( (array)($_POST['specs'] ?? []) as $post_id => $fields ){
            $post_id = (int)$post_id;
            if ( ! get_post($post_id) ) continue;
            foreach( ['mozlex_material','mozlex_color','mozlex_door','mozlex_core_type','mozlex_capacity','mozlex_battery','mozlex_dimensions'] as $key ){
                $val = isset($fields[$key]) ? sanitize_text_field($fields[$key]) : '';
                update_post_meta($post_id, $key, $val);
            }
            // Custom specs rows
            $rows = [];
            if ( isset($fields['custom']) && is_array($fields['custom']) ) {
                foreach( $fields['custom'] as $row ){
                    $k = isset($row['k']) ? sanitize_text_field($row['k']) : '';
                    $v = isset($row['v']) ? sanitize_text_field($row['v']) : '';
                    if ( $k !== '' || $v !== '' ) $rows[] = [$k,$v];
                }
            }
            update_post_meta($post_id, 'mozlex_specs', $rows);
            $updated++;
        }
        echo '<div class="notice notice-success"><p>Đã lưu '.$updated.' sản phẩm.</p></div>';
    }
    $q=new WP_Query(['post_type'=>'product','posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC','post_status'=>'publish']);
    ?>
    <div class="wrap">
        <h1>Sửa thông số kỹ thuật từng sản phẩm</h1>
        <p>Chỉnh <em>Chất liệu, Màu sắc, Độ dày cửa, Loại lõi, Dung lượng, Nguồn/pin, Kích thước</em> và các dòng <em>Tùy chỉnh</em> (sẽ hiện trong bảng <strong>Thông số kỹ thuật</strong> ở trang chi tiết). Để trống sẽ hiện <em>Liên hệ tư vấn</em>.</p>
        <form method="post">
            <?php wp_nonce_field('mozlex_specs_save','mozlex_specs_nonce'); ?>
            <table class="widefat striped" style="margin-top:12px;">
                <thead>
                    <tr>
                        <th style="width:220px;">Sản phẩm</th>
                        <th>Chất liệu</th>
                        <th>Màu sắc</th>
                        <th>Độ dày cửa</th>
                        <th>Loại lõi</th>
                        <th>Dung lượng</th>
                        <th>Nguồn/pin</th>
                        <th>Kích thước</th>
                        <th style="width:260px;">Tùy chỉnh (thêm dòng)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($q->posts as $p): 
                    $id=$p->ID;
                    $custom=get_post_meta($id,'mozlex_specs',true);
                    if(!is_array($custom)) $custom=[];
                ?>
                    <tr>
                        <td><strong><?php echo esc_html($p->post_title); ?></strong><br><small><?php echo esc_html($p->post_name); ?></small></td>
                        <td><input type="text" name="specs[<?php echo $id; ?>][mozlex_material]" value="<?php echo esc_attr(get_post_meta($id,'mozlex_material',true)); ?>" style="width:120px;"></td>
                        <td><input type="text" name="specs[<?php echo $id; ?>][mozlex_color]" value="<?php echo esc_attr(get_post_meta($id,'mozlex_color',true)); ?>" style="width:100px;"></td>
                        <td><input type="text" name="specs[<?php echo $id; ?>][mozlex_door]" value="<?php echo esc_attr(get_post_meta($id,'mozlex_door',true)); ?>" style="width:110px;"></td>
                        <td><input type="text" name="specs[<?php echo $id; ?>][mozlex_core_type]" value="<?php echo esc_attr(get_post_meta($id,'mozlex_core_type',true)); ?>" style="width:110px;"></td>
                        <td><input type="text" name="specs[<?php echo $id; ?>][mozlex_capacity]" value="<?php echo esc_attr(get_post_meta($id,'mozlex_capacity',true)); ?>" style="width:110px;"></td>
                        <td><input type="text" name="specs[<?php echo $id; ?>][mozlex_battery]" value="<?php echo esc_attr(get_post_meta($id,'mozlex_battery',true)); ?>" style="width:110px;"></td>
                        <td><input type="text" name="specs[<?php echo $id; ?>][mozlex_dimensions]" value="<?php echo esc_attr(get_post_meta($id,'mozlex_dimensions',true)); ?>" style="width:110px;"></td>
                        <td>
                            <div class="mozlex-custom-rows" data-post="<?php echo $id; ?>">
                                <?php
                                $rows = $custom;
                                if(empty($rows)) $rows=[['','']];
                                foreach($rows as $i=>$row):
                                    $k=$row[0]??''; $v=$row[1]??'';
                                ?>
                                <div style="display:flex; gap:4px; margin-bottom:4px;">
                                    <input type="text" name="specs[<?php echo $id; ?>][custom][<?php echo $i; ?>][k]" value="<?php echo esc_attr($k); ?>" placeholder="Tên" style="width:90px;">
                                    <input type="text" name="specs[<?php echo $id; ?>][custom][<?php echo $i; ?>][v]" value="<?php echo esc_attr($v); ?>" placeholder="Giá trị" style="width:110px;">
                                </div>
                                <?php endforeach; ?>
                                <button type="button" class="button mozlex-add-row" data-post="<?php echo $id; ?>">+ Thêm dòng</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php submit_button('Lưu tất cả'); ?>
        </form>
    </div>
    <script>
    document.querySelectorAll('.mozlex-add-row').forEach(btn=>{
        btn.addEventListener('click',()=>{
            const wrap=btn.closest('.mozlex-custom-rows');
            const idx=wrap.querySelectorAll('div').length;
            const post=btn.getAttribute('data-post');
            const div=document.createElement('div');
            div.style.cssText='display:flex; gap:4px; margin-bottom:4px;';
            div.innerHTML=`<input type="text" name="specs[${post}][custom][${idx}][k]" placeholder="Tên" style="width:90px;"><input type="text" name="specs[${post}][custom][${idx}][v]" placeholder="Giá trị" style="width:110px;">`;
            btn.before(div);
        });
    });
    </script>
    <?php
}
