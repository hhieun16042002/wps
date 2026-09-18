<?php
require_once '/var/www/html/wp-load.php';

// 1. Tạo hoặc lấy categories
$cats_data = [
    'giai-phap-cong-trinh' => 'Giải Pháp Công Trình',
    'cam-nang-ky-thuat'    => 'Cẩm Nang Kỹ Thuật',
    'bao-tri-dich-vu'      => 'Bảo Trì & Dịch Vụ',
];

$cat_ids = [];
foreach ($cats_data as $slug => $name) {
    $term = get_term_by('slug', $slug, 'category');
    if (!$term) {
        $res = wp_insert_term($name, 'category', ['slug' => $slug]);
        if (!is_wp_error($res)) {
            $cat_ids[$slug] = $res['term_id'];
        }
    } else {
        $cat_ids[$slug] = $term->term_id;
    }
}

// 2. Định nghĩa 3 bài viết chất lượng cao
$articles = [
    [
        'title' => 'Giải pháp an ninh & kiểm soát ra vào cho tòa nhà văn phòng 2026: Tối ưu chi phí và nâng tầm quản trị',
        'slug'  => 'giai-phap-an-ninh-kiem-soat-ra-vao-toa-nha-van-phong',
        'cat'   => 'giai-phap-cong-trinh',
        'excerpt' => 'Phân tích kiến trúc kiểm soát an ninh đa tầng cho tòa nhà thương mại và văn phòng hiện đại. Cách tích hợp khóa cửa từ, barrier, kiểm soát thang máy và camera AI giúp tối ưu đến 45% chi phí vận hành.',
        'content' => '
<p class="lead">Trong kỷ nguyên quản trị thông minh, hệ thống kiểm soát ra vào (Access Control) không đơn thuần là những chiếc khóa cửa đóng mở cơ học, mà đã trở thành "trục xương sống" đảm bảo an ninh, bảo mật thông tin và đo lường hiệu suất vận hành của toàn bộ tòa nhà doanh nghiệp.</p>

<div class="article-highlight-box">
	<h4>Tóm tắt các điểm then chốt:</h4>
	<ul>
		<li>Kiến trúc an ninh 3 lớp: Vành đai ngoài (Barrier/Cổng xoay) &rarr; Thang máy phân tầng &rarr; Khóa từ cửa phòng ban.</li>
		<li>Chuyển dịch công nghệ từ thẻ từ truyền thống sang nhận diện Face ID 3D và sinh trắc học không chạm.</li>
		<li>Tiết kiệm tới 45% chi phí thuê nhân sự tuần tra, bảo vệ trực điểm cố định.</li>
		<li>Giải pháp tích hợp đồng bộ từ đơn vị tổng thầu Đức Trí 226.</li>
	</ul>
</div>

<h2>1. Thực trạng quản lý an ninh tại các tòa nhà văn phòng hiện nay</h2>
<p>Nhiều tòa nhà văn phòng, trụ sở công ty quy mô từ 100 đến 1.000 nhân sự vẫn đang đối mặt với các thách thức lớn:</p>
<ul>
	<li><strong>Thất lạc và mượn thẻ lẫn nhau:</strong> Tình trạng nhân viên mượn thẻ chấm công hộ hoặc làm rơi thẻ tạo kẽ hở lớn cho người lạ thâm nhập khu vực nội bộ.</li>
	<li><strong>Ùn tắc giờ cao điểm:</strong> Tốc độ nhận diện thẻ chậm hoặc đầu đọc cơ học cũ kỹ khiến dòng người xếp hàng dài tại sảnh thang máy mỗi buổi sáng.</li>
	<li><strong>Chi phí nhân sự bảo vệ cao:</strong> Phải bố trí nhiều chốt bảo vệ kiểm tra giấy tờ thủ công nhưng vẫn khó kiểm soát triệt để khách vãng lai.</li>
</ul>

<h2>2. Mô hình phân tầng an ninh 3 lớp đạt chuẩn quốc tế</h2>
<p>Đức Trí 226 đã nghiên cứu và triển khai thành công mô hình an ninh 3 tầng cho nhiều dự án văn phòng trọng điểm:</p>

<h3>Lớp 1: Kiểm soát sảnh chính & Cổng phân làn Flap Barrier</h3>
<p>Tại sảnh tiếp đón, hệ thống cửa phân làn tốc độ cao kết hợp camera nhận diện khuôn mặt Face ID động (Dynamic Face Recognition) cho phép nhận diện nhân sự với khoảng cách lên tới 2 mét, tốc độ xử lý dưới 0.2 giây mà người dùng không cần dừng lại.</p>

<h3>Lớp 2: Phân quyền thang máy thông minh</h3>
<p>Nhân sự hoặc khách chỉ được phép bấm tầng thang máy tương ứng với phòng ban làm việc được cấp quyền. Khách vãng lai nhận mã QR Code tạm thời có thời hạn, hạn chế tối đa rủi ro đi lạc vào khu vực cơ mật.</p>

<h3>Lớp 3: Khóa thông minh phòng ban & Khu vực trọng yếu</h3>
<p>Các phòng Giám đốc, phòng Tài chính - Kế toán, phòng Server máy chủ được trang bị khóa điện tử cao cấp Mozlex với tiêu chuẩn bảo mật đa yếu tố (Face ID + Vân tay tĩnh mạch + Mã số ảo chống nhìn trộm). Mọi nhật ký mở cửa (ai mở, thời gian nào) đều được ghi nhận theo thời gian thực về máy chủ.</p>

<table class="article-table">
	<thead>
		<tr>
			<th>Hạng mục so sánh</th>
			<th>Hệ thống truyền thống</th>
			<th>Giải pháp thông minh Đức Trí 226</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Phương thức xác thực</td>
			<td>Chìa khóa cơ / Thẻ nhựa từ</td>
			<td>Face ID 3D / Vân tay FPC / Smartphone</td>
		</tr>
		<tr>
			<td>Khả năng gian lận</td>
			<td>Dễ cho mượn thẻ, đánh chìa</td>
			<td>Tuyệt đối chống giả mạo sinh trắc học</td>
		</tr>
		<tr>
			<td>Báo cáo chấm công</td>
			<td>Xuất thủ công, dễ thất lạc</td>
			<td>Đồng bộ tự động phần mềm ERP/HRM</td>
		</tr>
		<tr>
			<td>Thời gian xử lý</td>
			<td>1.5 - 3.0 giây/người</td>
			<td>&lt; 0.3 giây/người (Luồng đi liên tục)</td>
		</tr>
	</tbody>
</table>

<h2>3. Quy trình tư vấn và triển khai chìa khóa trao tay của Đức Trí 226</h2>
<p>Với vai trò là nhà phân phối chính hãng Mozlex và tổng thầu cơ điện an ninh, Đức Trí 226 cam kết:</p>
<ol>
	<li><strong>Khảo sát thực địa miễn phí:</strong> Đo đạc luồng giao thông sảnh, tính toán tải trọng cửa kính và lưu lượng nhân sự.</li>
	<li><strong>Bản vẽ giải pháp chi tiết:</strong> Sơ đồ đấu nối dây âm sàn, phương án nguồn lưu điện UPS dự phòng khi mất điện lưới.</li>
	<li><strong>Thi công chuẩn chỉ:</strong> Kỹ thuật viên tay nghề cao thi công ngoài giờ hành chính, không gây ảnh hưởng đến hoạt động của văn phòng.</li>
	<li><strong>Bảo hành & bảo trì 36 tháng:</strong> Hỗ trợ kỹ thuật 24/7, có mặt trong vòng 60 phút tại khu vực nội thành Hà Nội khi có sự cố.</li>
</ol>
'
    ],
    [
        'title' => 'Nâng cấp khóa thông minh cho cửa gỗ biệt thự & chung cư: Quy trình kỹ thuật không làm hỏng cửa',
        'slug'  => 'quy-trinh-nang-cap-khoa-thong-minh-cua-go-biet-thu-chung-cu',
        'cat'   => 'cam-nang-ky-thuat',
        'excerpt' => 'Hướng dẫn chi tiết từ thợ lành nghề Đức Trí 226: Cách đo đố cửa dày 38-50mm, chọn ruột khóa tiêu chuẩn 6068, kỹ thuật khoét mộng chính xác và giải pháp dùng tấm ốp PVD che kín 100% vết khóa cơ cũ.',
        'content' => '
<p class="lead">Nhiều gia chủ sở hữu những bộ cửa gỗ tự nhiên đắt giá (gỗ Lim, Gõ Đỏ, Hương, Sồi) thường rất đắn đo khi muốn đổi từ khóa cơ cũ sang khóa điện tử thông minh: <em>"Liệu khoét cửa có làm nứt gỗ không? Khóa mới có che hết được các lỗ khoan của khóa cũ hay không?"</em> Bài viết này sẽ giải đáp chi tiết góc nhìn kỹ thuật từ các chuyên gia Đức Trí 226.</p>

<h2>1. Điều kiện tiên quyết của đố cửa gỗ trước khi lắp khóa thông minh</h2>
<p>Khác với cửa nhôm kính hay cửa sắt vân gỗ, cửa gỗ tự nhiên có độ co ngót theo thời tiết và trọng lượng cánh rất lớn. Để lắp đặt khóa thông minh an toàn, bộ cửa cần đáp ứng 2 tiêu chí vàng:</p>

<ul>
	<li><strong>Độ dày đố cửa (Door Thickness):</strong> Tối thiểu từ <strong>38mm đến 55mm</strong>. Đối với các dòng khóa đại sảnh Mozlex cỡ lớn (Face ID), độ dày lý tưởng là từ 40mm trở lên.</li>
	<li><strong>Độ rộng đố cửa (Stile Width):</strong> Tối thiểu từ <strong>95mm đến 110mm</strong> (tính từ mép cửa đến đường chỉ trang trí hoặc kính). Đố cửa đủ rộng đảm bảo ruột khóa (Mortise) nằm lọt lòng trong thân gỗ mà không cắt đứt liên kết mộng gỗ.</li>
</ul>

<div class="article-warning-box">
	<strong>CẢNH BÁO KỸ THUẬT:</strong> Tuyệt đối không để thợ nghiệp dư dùng máy khoan tay khoét mộng tự do. Gỗ lim hoặc gõ đỏ rất già và giòn, nếu mũi khoan rung lắc hoặc quá nhiệt sẽ gây nứt dọc thớ gỗ, phá hỏng cánh cửa trị giá hàng chục triệu đồng!
</div>

<h2>2. Kỹ thuật "che khuyết điểm" khóa cơ cũ bằng tấm ốp PVD chuyên dụng</h2>
<p>Một trong những vấn đề lớn nhất khi thay khóa cơ cũ (khóa tay gạt tròn, khóa củ tròn hoặc khóa phân thể) là các lỗ khoan bắt ốc cũ nằm lệch so với thân khóa mới.</p>
<p>Tại Đức Trí 226, giải pháp độc quyền của chúng tôi là sử dụng <strong>Tấm ốp thẩm mỹ đồng chất liệu mạ PVD cao cấp</strong>:</p>
<ol>
	<li>Tấm ốp có độ dày 2mm, màu sắc mạ vàng bóng hoặc xám titan đồng bộ 100% với màu khóa Mozlex.</li>
	<li>Tấm ốp được cắt CNC chính xác ôm trọn toàn bộ lỗ khoan cũ, đồng thời gia cố thêm độ cứng vững cho mặt ngoài cánh cửa.</li>
	<li>Sau khi hoàn thiện, cửa trông hoàn toàn liền lạc, sang trọng như một bộ cửa được thiết kế mới nguyên bản từ xưởng.</li>
</ol>

<h2>3. 5 Bước thi công lắp đặt chuẩn mực của thợ Đức Trí 226</h2>
<p>Mỗi ca lắp đặt tại nhà khách hàng được thực hiện theo đúng quy chuẩn 5 bước nghiêm ngặt:</p>
<ul>
	<li><strong>Bước 1:</strong> Sử dụng dưỡng giấy chuyên dụng dán lên mép cửa để định vị tim ruột khóa và các lỗ bu-lông xuyên thân.</li>
	<li><strong>Bước 2:</strong> Dùng máy phay rãnh gỗ chuyên dụng (Mortising Jig) với cữ đo hành trình chuẩn xác từng milimet, giúp thành khoét vuông vức, phẳng mịn.</li>
	<li><strong>Bước 3:</strong> Vệ sinh sạch mùn cưa bên trong hốc mộng, tra dung dịch chống ẩm bảo vệ cốt gỗ trước khi lắp ruột khóa inox 304 tiêu chuẩn 6068.</li>
	<li><strong>Bước 4:</strong> Bắt ốc vít lục giác chống rung, đấu nối giắc cắm chống đứt gãy và cài đặt gioăng cao su chống nước xâm nhập.</li>
	<li><strong>Bước 5:</strong> Căn chỉnh miệng đón khóa (Strike Plate) sao cho khi khép cửa, gioăng cao su nén nhẹ êm ái, chốt khóa nhảy trơn tru không bị kẹt hay quá tải mô-tơ.</li>
</ul>

<h2>4. Cam kết bảo hành và hỗ trợ kỹ thuật tại nhà</h2>
<p>Đức Trí 226 cam kết với mọi khách hàng lắp đặt:</p>
<ul>
	<li>Thi công thẩm mỹ, không nứt gỗ, không làm bẩn không gian sống.</li>
	<li>Đổi mới 1:1 trong vòng 6 tháng nếu có lỗi từ nhà sản xuất.</li>
	<li>Bảo hành chính hãng 36 tháng, kiểm tra vệ sinh miễn phí năm đầu tiên.</li>
</ul>
'
    ],
    [
        'title' => 'Bảo dưỡng khóa cửa điện tử định kỳ: 5 nguyên tắc vàng giúp thiết bị bền bỉ hơn 10 năm',
        'slug'  => '5-nguyen-tac-vang-bao-duong-khoa-cua-dien-tu-tren-10-nam',
        'cat'   => 'bao-tri-dich-vu',
        'excerpt' => 'Vì sao khóa điện tử cần bảo dưỡng hàng năm? Hướng dẫn chọn pin AA chống rò rỉ axit, vệ sinh cảm biến vân tay/mặt kính Face ID đúng cách và chính sách bảo trì miễn phí năm đầu từ Đức Trí 226.',
        'content' => '
<p class="lead">Khóa cửa điện tử thông minh là thiết bị vi điện tử kết hợp cơ khí chính xác, chịu tác động trực tiếp của môi trường khí hậu nhiệt đới gió mùa tại Việt Nam (nắng gắt mùa hè, nồm ẩm mùa xuân). Thực hiện đúng 5 nguyên tắc bảo dưỡng dưới đây sẽ giúp thiết bị của bạn luôn sáng bóng, nhạy bén và nâng cao tuổi thọ vận hành trên 10 năm.</p>

<h2>Nguyên tắc 1: Chọn đúng loại pin chuyên dụng — Không dùng pin rẻ tiền</h2>
<p>Hơn 70% các ca hỏng bo mạch khóa điện tử mà trung tâm kỹ thuật Đức Trí 226 tiếp nhận xử lý đều bắt nguồn từ một nguyên nhân: <strong>Dùng pin cacbon giá rẻ dẫn đến pin chảy nước axit ăn mòn chân tiếp xúc và cháy vi mạch.</strong></p>

<div class="article-tip-box">
	<strong>Lời khuyên từ chuyên gia:</strong>
	<ul>
		<li>Chỉ sử dụng pin <strong>Alkaline (pin kiềm) chất lượng cao</strong> như: <em>Panasonic Eneloop, Energizer Max, Duracell hoặc pin sạc Lithium chuyên dụng</em> đi kèm của Mozlex.</li>
		<li>Thời gian thay pin định kỳ: Từ <strong>10 đến 12 tháng</strong> một lần, bất kể khóa đã báo pin yếu hay chưa.</li>
		<li>Khi thấy khóa phát tín hiệu cảnh báo pin yếu (âm thanh bíp liên hồi hoặc đèn đỏ nhấp nháy), hãy thay pin ngay trong vòng 3 ngày, không nên chần chừ.</li>
	</ul>
</div>

<h2>Nguyên tắc 2: Vệ sinh bề mặt cảm biến và mặt kính quang học đúng cách</h2>
<p>Bụi mịn, mồ hôi tay và dầu mỡ tích tụ lâu ngày trên mắt đọc vân tay hay thấu kính hồng ngoại Face ID sẽ làm giảm độ nhạy quét:</p>
<ul>
	<li>Dùng khăn vải sợi mịn (loại microfiber dùng lau kính mắt hoặc ống kính máy ảnh) thấm nhẹ cồn y tế 70 độ để lau sạch bề mặt cảm biến.</li>
	<li><strong>Tuyệt đối không dùng chất tẩy rửa mạnh</strong> (như nước tẩy bồn cầu, xăng thơm, cồn công nghiệp) vì sẽ làm bong tróc lớp phủ chống bám vân tay Oleophobic của màn hình cảm ứng.</li>
</ul>

<h2>Nguyên tắc 3: Kiểm tra độ rơ của bản lề cửa và căn chỉnh chốt khóa</h2>
<p>Sau 1-2 năm sử dụng, cánh cửa gỗ hoặc cửa nhôm kính thường có xu hướng bị xệ nhẹ do trọng lực. Khi cửa xệ, chốt khóa cơ khí sẽ bị cạ vào mép khung bao (khung cửa), tạo lực cản khiến mô-tơ điện bên trong phải hoạt động quá tải để rút chốt, lâu dần dẫn đến hỏng bánh răng.</p>
<p><strong>Cách tự kiểm tra đơn giản:</strong> Thử khép cửa lại nhẹ nhàng, nếu chốt khóa nhảy vào lỗ đón êm ru không cần kéo đẩy mạnh tay thì cửa đạt chuẩn. Nếu thấy phải dùng lực tỳ mạnh cửa mới khóa được, hãy liên hệ thợ kỹ thuật để chỉnh lại bản lề ngay.</p>

<h2>Nguyên tắc 4: Tra dầu mỡ bôi trơn cho bộ phận cơ khí</h2>
<p>Chỉ sử dụng <strong>dầu silicon chuyên dụng hoặc mỡ bôi trơn dạng khô (Dry Lube)</strong> cho ruột khóa cơ và lưỡi gà. Tuyệt đối không tra dầu luyn xe máy, dầu ăn hay mỡ bò đặc vào ổ khóa, vì mỡ đặc sẽ hút bụi bẩn và kết dính thành cặn đen làm kẹt bi cơ khí bên trong.</p>

<h2>Nguyên tắc 5: Đăng ký chương trình bảo dưỡng định kỳ miễn phí từ Đức Trí 226</h2>
<p>Nhằm đem lại sự an tâm tuyệt đối cho khách hàng, Đức Trí 226 áp dụng chính sách chăm sóc đặc quyền:</p>
<ul>
	<li><strong>Miễn phí 100% công bảo dưỡng, vệ sinh & căn chỉnh cửa</strong> trong 12 tháng đầu tiên sau khi lắp đặt.</li>
	<li>Hỗ trợ pin sạc chính hãng và linh kiện thay thế chuẩn từ nhà máy Mozlex.</li>
	<li>Đường dây nóng hỗ trợ kỹ thuật trực tuyến: <strong>0355514686</strong> hoạt động 24/7 kể cả ngày lễ và Tết.</li>
</ul>
'
    ]
];

// 3. Thực hiện Insert hoặc Update
foreach ($articles as $art) {
    $existing = get_page_by_path($art['slug'], OBJECT, 'post');
    $cid = $cat_ids[$art['cat']] ?? 1;

    $post_data = [
        'post_title'   => $art['title'],
        'post_name'    => $art['slug'],
        'post_content' => $art['content'],
        'post_excerpt' => $art['excerpt'],
        'post_status'  => 'publish',
        'post_type'    => 'post',
        'post_category'=> [$cid]
    ];

    if ($existing) {
        $post_data['ID'] = $existing->ID;
        wp_update_post($post_data);
        echo "Updated post: " . $art['title'] . " (ID: " . $existing->ID . ")\n";
    } else {
        $pid = wp_insert_post($post_data);
        echo "Created post: " . $art['title'] . " (ID: " . $pid . ")\n";
    }
}

flush_rewrite_rules();
echo "All articles created and rules flushed successfully!\n";
