<?php
/**
 * Automated Test Suite cho WP Product Crawler & Importer
 *
 * Chạy trực tiếp qua Docker CLI:
 * php run-tests.php
 *
 * @package WP_Product_Crawler
 */

define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/../../../../wp-load.php';

echo "========================================================\n";
echo "   BẮT ĐẦU KIỂM THỬ AUTOMATED TEST SUITE: CRAWLER PLUGIN\n";
echo "========================================================\n\n";

$total_tests  = 0;
$passed_tests = 0;

function assert_test( string $name, bool $condition, string $detail = '' ) {
	global $total_tests, $passed_tests;
	$total_tests++;
	if ( $condition ) {
		$passed_tests++;
		echo " [PASS] " . $name . "\n";
	} else {
		echo " [FAIL] " . $name . ( $detail ? " -> " . $detail : "" ) . "\n";
	}
}

// -----------------------------------------------------------------------------
// PHẦN 1: KIỂM THỬ BẢO MẬT SSRF & URL VALIDATION
// -----------------------------------------------------------------------------
echo "\n--- [PHẦN 1: BẢO MẬT SSRF & URL VALIDATION] ---\n";

// Test 1.1: Chặn localhost
$res = WP_Crawler_Security::validate_url( 'http://localhost/wp-admin' );
assert_test( 'Chặn truy cập hostname "localhost"', is_wp_error( $res ) );

// Test 1.2: Chặn 127.0.0.1
$res = WP_Crawler_Security::validate_url( 'http://127.0.0.1:8080/secret' );
assert_test( 'Chặn IPv4 Loopback 127.0.0.1', is_wp_error( $res ) );

// Test 1.3: Chặn dải IP riêng tư 10.0.0.0/8
$res = WP_Crawler_Security::validate_url( 'http://10.1.2.3/internal-api' );
assert_test( 'Chặn mạng riêng tư Class A 10.0.0.0/8', is_wp_error( $res ) );

// Test 1.4: Chặn dải IP riêng tư 172.16.0.0/12
$res = WP_Crawler_Security::validate_url( 'http://172.20.0.10/' );
assert_test( 'Chặn mạng riêng tư Class B 172.16.0.0/12', is_wp_error( $res ) );

// Test 1.5: Chặn dải IP riêng tư 192.168.0.0/16
$res = WP_Crawler_Security::validate_url( 'http://192.168.1.1/router' );
assert_test( 'Chặn mạng riêng tư Class C 192.168.0.0/16', is_wp_error( $res ) );

// Test 1.6: Chặn Cloud Metadata IP (169.254.169.254)
$res = WP_Crawler_Security::validate_url( 'http://169.254.169.254/latest/meta-data/' );
assert_test( 'Chặn Cloud Metadata Endpoint 169.254.169.254', is_wp_error( $res ) );

// Test 1.7: Chặn giao thức nguy hiểm (file://, ftp://, gopher://)
$res = WP_Crawler_Security::validate_url( 'file:///etc/passwd' );
assert_test( 'Chặn giao thức file://', is_wp_error( $res ) );

// Test 1.8: Chấp nhận URL công khai hợp lệ
$res = WP_Crawler_Security::validate_url( 'https://httpbin.org/get' );
assert_test( 'Cho phép URL Internet công khai hợp lệ', true === $res );

// -----------------------------------------------------------------------------
// PHẦN 2: KIỂM THỬ TRÍCH XUẤT DỮ LIỆU SẢN PHẨM (PRODUCT EXTRACTOR)
// -----------------------------------------------------------------------------
echo "\n--- [PHẦN 2: BÓC TÁCH DỮ LIỆU SẢN PHẨM & THÔNG SỐ] ---\n";

// Mẫu 1: JSON-LD Product
$json_ld_html = '<!DOCTYPE html>
<html>
<head>
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "Khóa Vân Tay Thông Minh Mozlex Pro X1",
  "image": "https://example.com/images/x1.jpg",
  "description": "Dòng khóa cao cấp tiêu chuẩn Châu Âu với cảm biến vân tay FPC Thụy Điển.",
  "sku": "MZL-PRO-X1",
  "brand": {
    "@type": "Brand",
    "name": "Mozlex"
  },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "VND",
    "price": 8500000,
    "availability": "https://schema.org/InStock"
  },
  "additionalProperty": [
    { "@type": "PropertyValue", "name": "Điện áp", "value": "220V" },
    { "@type": "PropertyValue", "name": "Chất liệu", "value": "Hợp kim kẽm nguyên khối" }
  ]
}
</script>
</head>
<body><h1>Khóa Vân Tay Thông Minh</h1></body>
</html>';

$extracted_json = WP_Product_Extractor::extract( $json_ld_html, 'https://example.com/product/x1' );

assert_test( 'Trích xuất Tên sản phẩm từ JSON-LD', 'Khóa Vân Tay Thông Minh Mozlex Pro X1' === $extracted_json['name'] );
assert_test( 'Trích xuất SKU/Model từ JSON-LD', 'MZL-PRO-X1' === $extracted_json['sku'] );
assert_test( 'Trích xuất Giá bán từ JSON-LD', 8500000.0 === (float) $extracted_json['price'] );
assert_test( 'Trích xuất Hãng từ JSON-LD', 'Mozlex' === $extracted_json['brand'] );
assert_test( 'Bảo toàn Thông số kỹ thuật dạng Cấu trúc (Specifications)', isset( $extracted_json['specifications']['Điện áp'] ) && '220V' === $extracted_json['specifications']['Điện áp'] );
assert_test( 'Chuyển đổi Thông số kỹ thuật sang mozlex_tech_specs', count( $extracted_json['tech_specs_rows'] ) === 2 && 'Chất liệu' === $extracted_json['tech_specs_rows'][1][0] );

// Mẫu 2: HTML Semantic & Bảng thông số kỹ thuật (HTML DOM Fallback)
$dom_html = '<!DOCTYPE html>
<html>
<head><title>Sơn Phủ Nano Cao Cấp 5L</title></head>
<body>
  <h1 class="product-title">Sơn Phủ Nano Cao Cấp 5L</h1>
  <div class="product-sku">Mã SP: SN-5L-PRO</div>
  <div class="product-price">1.450.000 ₫</div>
  <div class="product-description"><p>Sơn phủ bóng cao cấp bảo vệ bề mặt chống thấm vượt trội.</p></div>
  <div class="product-specs">
    <table class="table-specifications">
      <tr><th>Dung tích</th><td>5 Lít</td></tr>
      <tr><th>Độ phủ</th><td>12-14 m²/lít/lớp</td></tr>
      <tr><th>Thời gian khô</th><td>2 giờ</td></tr>
    </table>
  </div>
  <div class="product-gallery">
    <img src="/img/paint-main.jpg" class="product-image" data-large_image="https://example.com/img/paint-large.jpg">
  </div>
</body>
</html>';

$extracted_dom = WP_Product_Extractor::extract( $dom_html, 'https://example.com/san-pham/son-nano-5l' );

assert_test( 'Trích xuất Tên từ thẻ H1 DOM', 'Sơn Phủ Nano Cao Cấp 5L' === $extracted_dom['name'] );
assert_test( 'Trích xuất SKU từ text DOM', 'SN-5L-PRO' === $extracted_dom['sku'] );
assert_test( 'Chuyển đổi tiền tệ Việt Nam (1.450.000 ₫) sang số thực 1450000', 1450000.0 === (float) $extracted_dom['price'] );
assert_test( 'Bóc tách 3 thông số kỹ thuật từ bảng HTML', count( $extracted_dom['specifications'] ) === 3 && '5 Lít' === $extracted_dom['specifications']['Dung tích'] );
assert_test( 'Trích xuất ảnh lớn nhất data-large_image', 'https://example.com/img/paint-large.jpg' === $extracted_dom['main_image'] );

// Kiểm tra bóc tách mô tả chi tiết phong phú (Full Description) từ Elementor / WooCommerce
$elementor_html = '<!DOCTYPE html>
<html>
<body>
  <div class="elementor-widget-woocommerce-product-short-description">
    <div class="woocommerce-product-details__short-description">
      Mẫu: 4 cánh lệch. Vật liệu: Thép trắng mạ điện.
    </div>
  </div>
  <div class="elementor-widget-woocommerce-product-content">
    <div class="elementor-widget-container">
      <h2>Mô tả chi tiết sản phẩm</h2>
      <p>Cửa thép vân gỗ cao cấp sản xuất theo dây chuyền tiêu chuẩn Châu Âu.</p>
      <ul>
        <li>Chống cháy, cách âm, cách nhiệt</li>
        <li>Chống cong vênh mối mọt</li>
      </ul>
    </div>
  </div>
</body>
</html>';
$extracted_desc = WP_Product_Extractor::extract( $elementor_html, 'https://example.com/san-pham/cua-thep' );
assert_test( 'Bóc tách đầy đủ mô tả chi tiết HTML từ Elementor content widget', false !== strpos( $extracted_desc['description'], 'Mô tả chi tiết sản phẩm' ) && false !== strpos( $extracted_desc['description'], 'Chống cháy, cách âm' ) );
assert_test( 'Bóc tách đúng mô tả ngắn từ short-description container', false !== strpos( $extracted_desc['short_description'], 'Mẫu: 4 cánh lệch' ) );

// -----------------------------------------------------------------------------
// PHẦN 3: KIỂM THỬ ÁNH XẠ DANH MỤC (CATEGORY MAPPING)
// -----------------------------------------------------------------------------
echo "\n--- [PHẦN 3: ÁNH XẠ DANH MỤC (CATEGORY MAPPING)] ---\n";

$cats = WP_Category_Mapper::get_local_categories();
assert_test( 'Đọc danh sách danh mục nội bộ từ taxonomy product_category', count( $cats ) > 0 );

$suggest = WP_Category_Mapper::suggest_mapping( 'Khóa cửa điện tử thông minh' );
assert_test( 'Gợi ý danh mục tự động qua độ tương đồng chuỗi', ! empty( $suggest['term_name'] ) );

// Tạo danh mục mới kiểm thử
$new_cat_id = WP_Category_Mapper::create_local_category( 'Thiết Bị Kiểm Thử Test' );
assert_test( 'Tạo mới danh mục nội bộ thành công', is_int( $new_cat_id ) && $new_cat_id > 0 );

// Kiểm thử chuẩn hóa làm sạch danh mục nguồn (loại bỏ non-breaking space \xC2\xA0, ?, /, entity)
$dirty_cat1 = "\xC2\xA0 CỬA THÉP VÂN GỖ 4 CÁNH KING BAC / ";
$cleaned_cat1 = WP_Product_Extractor::clean_category_name( $dirty_cat1 );
assert_test( 'Làm sạch khoảng trắng đặc biệt \xC2\xA0 và ký tự / ở danh mục nguồn', 'CỬA THÉP VÂN GỖ 4 CÁNH KING BAC' === $cleaned_cat1 );

$dirty_cat2 = "Danh mục: Cửa Chống Cháy 60 Phút";
$cleaned_cat2 = WP_Product_Extractor::clean_category_name( $dirty_cat2 );
assert_test( 'Loại bỏ tiền tố "Danh mục:" khi bóc tách', 'Cửa Chống Cháy 60 Phút' === $cleaned_cat2 );

// Kiểm thử trích xuất đúng danh mục từ JSON-LD BreadcrumbList (không lấy nhầm tên sản phẩm)
$test_breadcrumb_html = '<script type="application/ld+json">
{
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "item": { "@id": "https://example.com", "name": "Trang chủ" } },
    { "@type": "ListItem", "position": 2, "item": { "@id": "https://example.com/cat", "name": "CỬA PHARAON KING BAC" } },
    { "@type": "ListItem", "position": 3, "item": { "@id": "https://example.com/prod", "name": "KING BAC PHARAON TRẤN LONG" } }
  ]
}
</script>';
$test_extracted = WP_Product_Extractor::extract( $test_breadcrumb_html, 'https://example.com/prod' );
assert_test( 'Trích xuất chính xác danh mục nguồn từ JSON-LD BreadcrumbList (CỬA PHARAON KING BAC)', 'CỬA PHARAON KING BAC' === $test_extracted['category'] );

// Kiểm thử trích xuất đúng danh mục từ thẻ HTML breadcrumb woocommerce
$test_dom_bc_html = '<nav class="woocommerce-breadcrumb"><a href="https://example.com">Trang chủ</a> / <a href="https://example.com/cua-4-canh">CỬA THÉP VÂN GỖ 4 CÁNH KING BAC</a> / CỬA THÉP 4 CÁNH MẪU 05</nav>';
$test_extracted_dom = WP_Product_Extractor::extract( $test_dom_bc_html, 'https://example.com/cua-4-canh/05' );
assert_test( 'Trích xuất chính xác danh mục nguồn từ liên kết WooCommerce Breadcrumb', 'CỬA THÉP VÂN GỖ 4 CÁNH KING BAC' === $test_extracted_dom['category'] );

// Kiểm thử tự động tạo và gán đúng danh mục như website nguồn
$door_cat_name = 'CỬA THÉP VÂN GỖ 4 CÁNH KING BAC';
$suggest_door = WP_Category_Mapper::suggest_mapping( $door_cat_name );
assert_test( 'Tự động tạo danh mục với tên chính xác từ website nguồn', $door_cat_name === $suggest_door['term_name'] && $suggest_door['term_id'] > 0 );

// Kiểm thử gọi lại lần 2 không tạo trùng lặp term
$suggest_door_2 = WP_Category_Mapper::suggest_mapping( $door_cat_name );
assert_test( 'Tái sử dụng term_id đã có, không tạo trùng lặp danh mục', $suggest_door['term_id'] === $suggest_door_2['term_id'] );

// -----------------------------------------------------------------------------
// PHẦN 4: KIỂM THỬ PHÁT HIỆN TRÙNG LẶP & IMPORT
// -----------------------------------------------------------------------------
echo "\n--- [PHẦN 4: DUPLICATE DETECTION & PRODUCT IMPORT] ---\n";

// Tạo sản phẩm thử nghiệm lần 1
$test_prod = array(
	'source_url'        => 'https://example.com/products/test-door-lock-2026',
	'name'              => 'Khóa Thử Nghiệm Tự Động Test 2026',
	'sku'               => 'TEST-LOCK-999',
	'model'             => 'TEST-LOCK-999',
	'price'             => 5200000.0,
	'description'       => '<p>Mô tả chi tiết sản phẩm thử nghiệm crawler.</p>',
	'short_description' => 'Mô tả ngắn thử nghiệm.',
	'category'          => 'Thiết Bị Kiểm Thử Test',
	'main_image'        => '',
	'gallery_images'    => array(),
	'specifications'    => array( 'Vật liệu' => 'Đồng thau', 'Bảo hành' => '36 tháng' ),
	'tech_specs_rows'   => array( array( 'Vật liệu', 'Đồng thau' ), array( 'Bảo hành', '36 tháng' ) ),
);

// Kiểm tra trùng trước khi import: phải là NEW
$dup_before = WP_Duplicate_Detector::check( $test_prod );
assert_test( 'Phát hiện sản phẩm chưa tồn tại là "NEW"', 'NEW' === $dup_before['status'] );

// Tiến hành Import sản phẩm mới (không chỉ định status -> mặc định là publish)
$import_res = WP_Product_Importer::import( $test_prod, array(
	'import_mode'        => 'create_update',
	'mapped_category_id' => $new_cat_id,
) );
assert_test( 'Nhập sản phẩm mới vào WordPress thành công', true === $import_res['success'] && 'created' === $import_res['action'] );
$created_post_id = $import_res['post_id'];

// Kiểm tra dữ liệu trong WordPress Post & Meta
$post_obj = get_post( $created_post_id );
assert_test( 'Sản phẩm mặc định được lưu ở trạng thái "publish" (Đang hiển thị)', 'publish' === $post_obj->post_status );
assert_test( 'Lưu đúng Model/SKU vào meta mozlex_model', 'TEST-LOCK-999' === get_post_meta( $created_post_id, 'mozlex_model', true ) );
assert_test( 'Lưu đúng Giá vào meta mozlex_price', 5200000.0 === (float) get_post_meta( $created_post_id, 'mozlex_price', true ) );
$saved_specs = get_post_meta( $created_post_id, 'mozlex_tech_specs', true );
assert_test( 'Lưu đúng Thông số kỹ thuật vào meta mozlex_tech_specs', is_array( $saved_specs ) && count( $saved_specs ) === 2 );

// Kiểm tra trùng lặp lần 2: phải là DUPLICATE
$dup_after = WP_Duplicate_Detector::check( $test_prod );
assert_test( 'Phát hiện sản phẩm đã nhập là "DUPLICATE"', 'DUPLICATE' === $dup_after['status'] && $dup_after['post_id'] === $created_post_id );

// Kiểm tra cơ chế tự động bỏ qua khi quét lại: Import lại sản phẩm trùng lặp phải trả về 'skipped'
$dup_import_res = WP_Product_Importer::import( $test_prod, array(
	'import_mode'        => 'create_update',
	'mapped_category_id' => $new_cat_id,
) );
assert_test( 'Tự động bỏ qua không import lại sản phẩm đã trùng lặp (DUPLICATE)', true === $dup_import_res['success'] && 'skipped' === $dup_import_res['action'] );

// Kiểm tra cập nhật giá: phải là UPDATED
$test_prod_updated = $test_prod;
$test_prod_updated['price'] = 5800000.0;
$dup_updated = WP_Duplicate_Detector::check( $test_prod_updated );
assert_test( 'Phát hiện sản phẩm có thay đổi giá là "UPDATED"', 'UPDATED' === $dup_updated['status'] );

// Import cập nhật sản phẩm khi giá thay đổi
$update_res = WP_Product_Importer::import( $test_prod_updated, array(
	'import_mode'        => 'create_update',
	'status'             => 'publish',
	'mapped_category_id' => $new_cat_id,
) );
assert_test( 'Cập nhật sản phẩm cũ khi có thay đổi giá thành công (action: updated)', true === $update_res['success'] && 'updated' === $update_res['action'] );
assert_test( 'Giá mới được cập nhật trong database', 5800000.0 === (float) get_post_meta( $created_post_id, 'mozlex_price', true ) );

// -----------------------------------------------------------------------------
// PHẦN 5: KIỂM THỬ QUẢN LÝ JOB, STRUCTURED LOGS & PROCESS CONTROLS
// -----------------------------------------------------------------------------
echo "\n--- [PHẦN 5: QUẢN LÝ JOB, STRUCTURED LOGS & PROCESS CONTROLS] ---\n";

$job_mgr = WP_Crawler_Job_Manager::get_instance();
$job_id  = $job_mgr->create_job( 'https://example.com/catalogue', 'listing', 'create_update', array() );
assert_test( 'Tạo Job ghi nhận vào cơ sở dữ liệu', $job_id > 0 );

$job_mgr->log( $job_id, 'INFO', 'Đã quét 15 sản phẩm mẫu.' );
$job_mgr->log( $job_id, 'WARN', 'Sản phẩm SP-02 không có giá niêm yết.' );

$logs = $job_mgr->get_job_logs( $job_id );
assert_test( 'Ghi và đọc Structured Logs của Job', count( $logs ) >= 2 );

// Test Tạm dừng (Pause) và Tiếp tục (Resume)
$job_mgr->pause_job( $job_id );
$job_paused = $job_mgr->get_job( $job_id );
assert_test( 'Tạm dừng Job chuyển trạng thái thành "paused"', 'paused' === $job_paused->status );

$job_mgr->resume_job( $job_id );
$job_resumed = $job_mgr->get_job( $job_id );
assert_test( 'Tiếp tục Job chuyển trạng thái thành "importing"', 'importing' === $job_resumed->status );

// -----------------------------------------------------------------------------
// PHẦN 6: KIỂM THỬ CRASH RECOVERY & CANCEL BẢO TOÀN SẢN PHẨM ĐÃ NHẬP
// -----------------------------------------------------------------------------
echo "\n--- [PHẦN 6: CRASH RECOVERY & CANCEL BẢO TOÀN SẢN PHẨM] ---\n";

// Thêm items vào Job để mô phỏng phiên làm việc dở dang
$item1_id = $job_mgr->add_item( $job_id, 'https://example.com/p1', 'imported', $test_prod, 'Sản phẩm đã xong #1', 'TEST-LOCK-999', 5800000 );
$job_mgr->update_item( $item1_id, array( 'post_id' => $created_post_id ) );

$item2_id = $job_mgr->add_item( $job_id, 'https://example.com/p2', 'extracted', array( 'name' => 'Sản phẩm chưa nhập #2' ), 'Sản phẩm chưa nhập #2', 'SKU-002', 1200000 );

// Kiểm tra Crash Recovery nhận diện đúng phiên
$active_session = $job_mgr->get_active_session();
assert_test( 'Nhận diện đúng phiên dở dang qua get_active_session()', null !== $active_session && (int) $active_session['job']->id === $job_id );
assert_test( 'Thống kê phiên dở dang: Có 1 sản phẩm đã imported', 1 === $active_session['stats']['imported'] );
assert_test( 'Thống kê phiên dở dang: Có 1 sản phẩm đang pending', 1 === $active_session['stats']['pending'] );

// Thực hiện Hủy bỏ (Cancel) phiên
$job_mgr->cancel_job( $job_id );

$cancelled_job = $job_mgr->get_job( $job_id );
$item1_after   = $job_mgr->get_item( $item1_id );
$item2_after   = $job_mgr->get_item( $item2_id );

assert_test( 'Job chuyển trạng thái thành "cancelled"', 'cancelled' === $cancelled_job->status );
assert_test( 'Sản phẩm đã nhập thành công GIỮ NGUYÊN trạng thái "imported"', 'imported' === $item1_after->status && (int) $item1_after->post_id === $created_post_id );
assert_test( 'Bài viết trên WordPress vẫn TỒN TẠI NGUYÊN VẸN trên website sau khi Cancel', null !== get_post( $created_post_id ) && 'publish' === get_post_status( $created_post_id ) );
assert_test( 'Sản phẩm phía sau trong hàng đợi bị hủy thành "cancelled"', 'cancelled' === $item2_after->status );

// -----------------------------------------------------------------------------
// PHẦN 7: KIỂM THỬ THAO TÁC XÓA SẢN PHẨM KHỎI DANH SÁCH (DELETE ITEMS)
// -----------------------------------------------------------------------------
echo "\n--- [PHẦN 7: THAO TÁC XÓA SẢN PHẨM KHỎI DANH SÁCH] ---\n";

// Tạo các item mẫu để test xóa
$item_del1 = $job_mgr->add_item( $job_id, 'https://example.com/del1', 'pending', array(), 'SP cần xóa #1', 'DEL-001', 100000 );
$item_del2 = $job_mgr->add_item( $job_id, 'https://example.com/del2', 'pending', array(), 'SP cần xóa #2', 'DEL-002', 200000 );
$item_del3 = $job_mgr->add_item( $job_id, 'https://example.com/del3', 'pending', array(), 'SP cần xóa #3', 'DEL-003', 300000 );

// Test Xóa 1 sản phẩm đơn lẻ (Single Delete)
$del_single_res = $job_mgr->delete_item( $item_del1 );
assert_test( 'Xóa thành công 1 sản phẩm đơn lẻ khỏi cơ sở dữ liệu', true === $del_single_res );
assert_test( 'Sản phẩm đã xóa không còn tồn tại trong wp_crawler_items', null === $job_mgr->get_item( $item_del1 ) );

// Test Xóa hàng loạt sản phẩm (Bulk Delete)
$bulk_del_count = $job_mgr->delete_items( array( $item_del2, $item_del3 ) );
assert_test( 'Xóa hàng loạt (Bulk Delete) trả về đúng số lượng 2 sản phẩm', 2 === $bulk_del_count );
assert_test( 'Các sản phẩm trong danh sách bulk delete đã bị xóa sạch', null === $job_mgr->get_item( $item_del2 ) && null === $job_mgr->get_item( $item_del3 ) );

// Test Xóa sản phẩm kèm xóa bài viết liên kết trên WordPress (delete_post = true)
$temp_post_id = wp_insert_post( array(
	'post_title'  => 'Sản phẩm thử nghiệm xóa kèm bài viết',
	'post_type'   => 'product',
	'post_status' => 'publish',
) );
$item_with_post = $job_mgr->add_item( $job_id, 'https://example.com/del-post', 'imported', array(), 'SP có bài viết', 'DEL-POST', 500000 );
$job_mgr->update_item( $item_with_post, array( 'post_id' => $temp_post_id ) );

$del_with_post_res = $job_mgr->delete_item( $item_with_post, true );
assert_test( 'Xóa item kèm tùy chọn delete_post = true thành công', true === $del_with_post_res );
assert_test( 'Bài viết liên kết trên WordPress đã được xóa hoàn toàn', null === get_post( $temp_post_id ) );

// Dọn dẹp dữ liệu kiểm thử
wp_delete_post( $created_post_id, true );
wp_delete_term( $new_cat_id, 'product_category' );

// -----------------------------------------------------------------------------
// KẾT LUẬN TỔNG THỂ
// -----------------------------------------------------------------------------
echo "\n========================================================\n";
echo sprintf( "   KẾT QUẢ KIỂM THỬ: %d/%d TEST CASES ĐÃ ĐẠT (%.1f%%)\n", $passed_tests, $total_tests, ( $passed_tests / $total_tests ) * 100 );
echo "========================================================\n";

if ( $passed_tests === $total_tests ) {
	echo ">>> TẤT CẢ CÁC BÀI TEST ĐỀU VƯỢT QUA HOÀN HẢO! <<<\n";
	exit( 0 );
} else {
	echo ">>> CÓ TEST CASE BỊ THẤT BẠI! <<<\n";
	exit( 1 );
}
