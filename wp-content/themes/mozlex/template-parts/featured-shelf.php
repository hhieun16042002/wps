<?php
/**
 * Kệ 3D cho khối nổi bật — cảm hứng AshenPress: kệ gỗ sồi, hộp sản phẩm
 * đứng như sách vải, hover nâng + tooltip, click sang trang sản phẩm,
 * kéo ngang xoay nhẹ. Dữ liệu là sản phẩm thật của shop (không phải sách mẫu).
 *
 * Nhận qua set_query_var( 'mozlex_featured_posts', $featured->posts ).
 * Lưới product-card vẫn render (ẩn khi kệ chạy OK) làm fallback khi
 * mất mạng CDN / không có WebGL / tắt JS — SEO và screen-reader giữ nguyên.
 *
 * @package mozlex
 */

declare( strict_types=1 );

$posts = get_query_var( 'mozlex_featured_posts', array() );
if ( empty( $posts ) ) {
	return;
}

$items = array();
foreach ( $posts as $p ) {
	if ( ! $p instanceof WP_Post ) {
		continue;
	}
	$pid   = $p->ID;
	$img   = get_the_post_thumbnail_url( $pid, 'mozlex-card' )
		?: get_the_post_thumbnail_url( $pid, 'large' )
		?: get_the_post_thumbnail_url( $pid, 'full' );
	$cats  = wp_get_post_terms( $pid, 'product_category' );
	/* Thông số preview: ưu tiên bảng kỹ thuật do admin nhập, thiếu thì
	 * ghép từ các trường sản phẩm sẵn có (chất liệu, màu, cửa, kích thước...). */
	$spec_rows = array();
	foreach ( (array) mozlex_technical_specs( $pid ) as $row ) {
		if ( is_array( $row ) && count( $row ) >= 2 && ( $row[0] || $row[1] ) ) {
			$spec_rows[] = array( (string) $row[0], (string) $row[1] );
		}
	}
	$meta_labels = array(
		'mozlex_material'   => 'Chất liệu',
		'mozlex_color'      => 'Màu sắc',
		'mozlex_door'       => 'Cửa phù hợp',
		'mozlex_dimensions' => 'Kích thước',
		'mozlex_capacity'   => 'Dung lượng',
		'mozlex_core_type'  => 'Củ khóa',
		'mozlex_battery'    => 'Nguồn',
	);
	foreach ( $meta_labels as $mkey => $mlabel ) {
		$mval = trim( (string) get_post_meta( $pid, $mkey, true ) );
		if ( '' !== $mval ) {
			$spec_rows[] = array( $mlabel, $mval );
		}
		if ( count( $spec_rows ) >= 6 ) {
			break;
		}
	}
	$items[] = array(
		'title'    => get_post_meta( $pid, 'mozlex_model', true ) ?: get_the_title( $p ),
		'url'      => get_permalink( $p ),
		'img'      => $img ? esc_url_raw( $img ) : '',
		'price'    => mozlex_price_text( $pid ),
		'cat'      => $cats ? $cats[0]->name : '',
		'specs'    => array_slice( $spec_rows, 0, 6 ),
		'features' => array_slice( mozlex_product_features( $pid ), 0, 4 ),
	);
}
if ( empty( $items ) ) {
	return;
}

$count = count( $items );
$per   = $count <= 4 ? $count : ( $count <= 12 ? (int) ceil( $count / 2 ) : (int) ceil( $count / 3 ) );
$rows  = max( 1, (int) ceil( $count / $per ) );
?>
<div class="featured-shelf-mount" id="mozlex-shelf" style="--shelf-rows:<?php echo esc_attr( (string) $rows ); ?>" hidden>
	<canvas class="featured-shelf-canvas" id="mozlex-shelf-canvas" aria-hidden="true"></canvas>
	<div class="featured-shelf-tip" id="mozlex-shelf-tip" hidden></div>
	<aside class="featured-shelf-preview" id="mozlex-shelf-preview" aria-label="<?php esc_attr_e( 'Xem nhanh sản phẩm', 'mozlex' ); ?>" hidden>
		<button type="button" class="preview-close" id="mozlex-shelf-close" aria-label="<?php esc_attr_e( 'Đóng xem nhanh', 'mozlex' ); ?>">×</button>
		<img class="preview-img" id="mozlex-shelf-pimg" alt="">
		<p class="preview-cat" id="mozlex-shelf-pcat"></p>
		<h3 class="preview-title" id="mozlex-shelf-ptitle"></h3>
		<p class="preview-price" id="mozlex-shelf-pprice"></p>
		<dl class="preview-specs" id="mozlex-shelf-pspecs"></dl>
		<p class="preview-features" id="mozlex-shelf-pfeatures"></p>
		<a class="preview-cta" id="mozlex-shelf-pcta" href="#"><?php esc_html_e( 'Xem chi tiết sản phẩm', 'mozlex' ); ?> <span aria-hidden="true">→</span></a>
	</aside>
	<p class="featured-shelf-hint" aria-hidden="true"><?php esc_html_e( 'Di chuột để xem — bấm 1 cái xem nhanh, bấm nữa vào chi tiết — kéo ngang để xoay kệ', 'mozlex' ); ?></p>
</div>
<script type="application/json" id="mozlex-shelf-data"><?php echo wp_json_encode( $items ); ?></script>
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/css/featured-shelf.css' ); ?>?ver=<?php echo esc_attr( MOZLEX_VERSION ); ?>">
<script type="importmap" id="mozlex-shelf-importmap">
{ "imports": {
	"three": "https://unpkg.com/three@0.181.0/build/three.module.js",
	"three/addons/": "https://unpkg.com/three@0.181.0/examples/jsm/"
} }
</script>
<script type="module" src="<?php echo esc_url( get_template_directory_uri() . '/assets/js/featured-shelf.js' ); ?>?ver=<?php echo esc_attr( MOZLEX_VERSION ); ?>"></script>
<ul class="featured-shelf-sr">
	<?php foreach ( $items as $it ) : ?>
		<li><a href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $it['title'] ); ?></a></li>
	<?php endforeach; ?>
</ul>
