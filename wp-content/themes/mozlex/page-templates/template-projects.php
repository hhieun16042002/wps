<?php
/**
 * Template Name: Dự Án Tiêu Biểu
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header(); ?>

<section class="projects-hero">
	<div class="shell">
		<nav class="breadcrumb-trail" aria-label="<?php esc_attr_e( 'Đường dẫn', 'mozlex' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Trang chủ', 'mozlex' ); ?></a>
			<span class="sep">/</span>
			<span><?php esc_html_e( 'Dự án tiêu biểu', 'mozlex' ); ?></span>
		</nav>
		<p class="projects-eyebrow">NĂNG LỰC THỰC CHIẾN</p>
		<h1 class="projects-main-title">DỰ ÁN & CÔNG TRÌNH TIÊU BIỂU</h1>
		<p class="projects-main-sub">Mỗi công trình là một minh chứng sống động cho chất lượng thiết bị, năng lực thi công cơ khí chính xác và trách nhiệm hậu mãi tận tâm của Đức Trí 226.</p>

		<div class="project-filter-bar">
			<button type="button" class="proj-filter-btn is-active" data-filter="all">Tất cả dự án (6)</button>
			<button type="button" class="proj-filter-btn" data-filter="biet-thu">Biệt thự & Nhà phố</button>
			<button type="button" class="proj-filter-btn" data-filter="toa-nha">Tòa nhà & Văn phòng</button>
			<button type="button" class="proj-filter-btn" data-filter="khach-san">Khách sạn & Nghỉ dưỡng</button>
		</div>
	</div>
</section>

<section class="shell projects-body">
	<div class="projects-grid">
		<!-- Project 1: Vinhomes Riverside -->
		<article class="project-card" data-cat="biet-thu">
			<div class="project-thumb-wrap">
				<img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=900&auto=format&fit=crop&q=80" alt="Biệt thự Vinhomes Riverside" class="project-img" loading="lazy">
				<span class="project-cat-badge">Biệt thự cao cấp</span>
				<span class="project-status-badge">Đã bàn giao</span>
			</div>
			<div class="project-content">
				<div class="project-location">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
					<span>Khu đô thị Vinhomes Riverside, Long Biên, Hà Nội</span>
				</div>
				<h2 class="project-title">Hệ thống khóa nhận diện khuôn mặt Face ID & kiểm soát an ninh biệt thự song lập</h2>
				<p class="project-desc">Tư vấn, cung cấp và lắp đặt trọn gói hệ thống khóa thông minh cao cấp Mozlex A16 tích hợp Face ID 3D cho cửa chính gỗ gõ đỏ 4 cánh và hệ thống khóa thẻ từ cho các phòng ngủ master.</p>
				<ul class="project-specs">
					<li><strong>Hạng mục:</strong> Cung cấp thiết bị & thi công lắp đặt</li>
					<li><strong>Thiết bị:</strong> Mozlex A16 Face ID, Khóa thông phòng F7-ML</li>
					<li><strong>Thời gian:</strong> Tháng 01/2026</li>
				</ul>
				<div class="project-footer">
					<span class="project-highlight">Chính xác 100% không làm nứt gỗ</span>
					<a href="tel:0355514686" class="project-link">Tư vấn giải pháp tương tự &rarr;</a>
				</div>
			</div>
		</article>

		<!-- Project 2: SEFICO Building -->
		<article class="project-card" data-cat="toa-nha">
			<div class="project-thumb-wrap">
				<img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=900&auto=format&fit=crop&q=80" alt="Tòa nhà văn phòng SEFICO Building" class="project-img" loading="lazy">
				<span class="project-cat-badge">Tòa nhà & Văn phòng</span>
				<span class="project-status-badge">Đang vận hành</span>
			</div>
			<div class="project-content">
				<div class="project-location">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
					<span>Phố Phan Văn Trường, Cầu Giấy, Hà Nội</span>
				</div>
				<h2 class="project-title">Giải pháp kiểm soát ra vào tích hợp chấm công & cửa phân tầng cho tòa nhà văn phòng</h2>
				<p class="project-desc">Quy mô quản lý hơn 450 nhân sự và khách ra vào mỗi ngày. Tích hợp barrier tự động, cửa phân làn flap turnstile, khóa từ cửa kính và camera AI đo nhiệt độ, điểm danh từ xa.</p>
				<ul class="project-specs">
					<li><strong>Hạng mục:</strong> Giải pháp tổng thể công nghệ & kiểm soát an ninh</li>
					<li><strong>Quy mô:</strong> 9 tầng nổi + 2 tầng hầm</li>
					<li><strong>Thời gian:</strong> Hoàn thành Quý IV/2025</li>
				</ul>
				<div class="project-footer">
					<span class="project-highlight">Giảm 45% chi phí giám sát</span>
					<a href="tel:0355514686" class="project-link">Tư vấn giải pháp tương tự &rarr;</a>
				</div>
			</div>
		</article>

		<!-- Project 3: Starlake Tây Hồ Tây -->
		<article class="project-card" data-cat="biet-thu">
			<div class="project-thumb-wrap">
				<img src="https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=900&auto=format&fit=crop&q=80" alt="Starlake Tây Hồ Tây" class="project-img" loading="lazy">
				<span class="project-cat-badge">Biệt thự cao cấp</span>
				<span class="project-status-badge">Đã bàn giao</span>
			</div>
			<div class="project-content">
				<div class="project-location">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
					<span>Khu đô thị Starlake Tây Hồ Tây, Hà Nội</span>
				</div>
				<h2 class="project-title">Cung cấp 65 bộ khóa cửa điện tử mạ vàng PVD chống oxy hóa cho dãy biệt thự đơn lập</h2>
				<p class="project-desc">Gói thầu yêu cầu thiết bị đạt tiêu chuẩn thẩm mỹ kiến trúc tân cổ điển châu Âu và khả năng chống chịu độ ẩm cao miền Bắc. Dòng khóa Mozlex mạ PVD công nghệ cao đáp ứng độ bền màu trên 10 năm.</p>
				<ul class="project-specs">
					<li><strong>Hạng mục:</strong> Cung ứng thiết bị & lắp đặt đồng bộ</li>
					<li><strong>Dòng sản phẩm:</strong> Mozlex Luxury PVD Gold</li>
					<li><strong>Bảo hành:</strong> 36 tháng tận nơi</li>
				</ul>
				<div class="project-footer">
					<span class="project-highlight">Độ bền màu mạ PVD > 10 năm</span>
					<a href="tel:0355514686" class="project-link">Tư vấn giải pháp tương tự &rarr;</a>
				</div>
			</div>
		</article>

		<!-- Project 4: Khách sạn & Resort Flamingo -->
		<article class="project-card" data-cat="khach-san">
			<div class="project-thumb-wrap">
				<img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=900&auto=format&fit=crop&q=80" alt="Flamingo Resort" class="project-img" loading="lazy">
				<span class="project-cat-badge">Khách sạn & Nghỉ dưỡng</span>
				<span class="project-status-badge">Đang vận hành</span>
			</div>
			<div class="project-content">
				<div class="project-location">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
					<span>Boutique Resort & Spa, Vĩnh Phúc</span>
				</div>
				<h2 class="project-title">Hệ thống khóa thẻ từ RFID khách sạn kết hợp quản lý phòng trung tâm PMS</h2>
				<p class="project-desc">Cung cấp 120 bộ khóa thẻ từ tần số 13.56MHz chống sao chép trái phép, tích hợp công tắc ngắt điện thông minh tiết kiệm năng lượng và phần mềm quản lý check-in/check-out đồng bộ lễ tân.</p>
				<ul class="project-specs">
					<li><strong>Hạng mục:</strong> Khóa thẻ từ & phần mềm quản trị</li>
					<li><strong>Quy mô:</strong> 120 phòng nghỉ cao cấp</li>
					<li><strong>Tiết kiệm điện:</strong> 25% chi phí tiền điện điều hòa</li>
				</ul>
				<div class="project-footer">
					<span class="project-highlight">Phản hồi thẻ từ < 0.2 giây</span>
					<a href="tel:0355514686" class="project-link">Tư vấn giải pháp tương tự &rarr;</a>
				</div>
			</div>
		</article>

		<!-- Project 5: Showroom Chuỗi Thời Trang -->
		<article class="project-card" data-cat="toa-nha">
			<div class="project-thumb-wrap">
				<img src="https://images.unsplash.com/photo-1441986300917-646a00fda6b7?w=900&auto=format&fit=crop&q=80" alt="Chuỗi showroom bán lẻ" class="project-img" loading="lazy">
				<span class="project-cat-badge">Tòa nhà & Văn phòng</span>
				<span class="project-status-badge">Đã bàn giao</span>
			</div>
			<div class="project-content">
				<div class="project-location">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
					<span>Hệ thống 5 cơ sở tại Hà Nội</span>
				</div>
				<h2 class="project-title">Hệ thống kiểm soát cửa kính thủy lực, khóa nam châm điện từ 600Lbs & chuông hình smartphone</h2>
				<p class="project-desc">Thi công đồng bộ hệ thống khóa điện từ lực giữ 280kg cho cửa kính cường lực 12mm, đầu đọc vân tay chống nước và quản lý mở cửa từ xa qua ứng dụng di động cho chủ chuỗi cửa hàng.</p>
				<ul class="project-specs">
					<li><strong>Hạng mục:</strong> Khóa cửa kính & an ninh đa điểm</li>
					<li><strong>Tính năng:</strong> Mở cửa từ xa qua Wi-Fi / 4G</li>
					<li><strong>Thời gian thi công:</strong> 2 ngày hoàn thiện toàn chuỗi</li>
				</ul>
				<div class="project-footer">
					<span class="project-highlight">Báo động khi cửa hé quá 30 giây</span>
					<a href="tel:0355514686" class="project-link">Tư vấn giải pháp tương tự &rarr;</a>
				</div>
			</div>
		</article>

		<!-- Project 6: Căn hộ Penthouse Golden Park -->
		<article class="project-card" data-cat="biet-thu">
			<div class="project-thumb-wrap">
				<img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=900&auto=format&fit=crop&q=80" alt="Penthouse Golden Park" class="project-img" loading="lazy">
				<span class="project-cat-badge">Biệt thự cao cấp</span>
				<span class="project-status-badge">Đã bàn giao</span>
			</div>
			<div class="project-content">
				<div class="project-location">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
					<span>Golden Park Tower, Dương Đình Nghệ, Cầu Giấy, Hà Nội</span>
				</div>
				<h2 class="project-title">Thay thế khóa cơ cũ sang khóa cửa điện tử thông minh Mozlex F7 trên cửa đại sảnh 4 cánh</h2>
				<p class="project-desc">Xử lý kỹ thuật cao: Tháo bỏ khóa cơ cũ kích thước phi tiêu chuẩn, sử dụng tấm ốp PVD gia công riêng che kín 100% vết khoét cũ, tích hợp chốt an toàn chống sao chép và tự động khóa khi khép cửa.</p>
				<ul class="project-specs">
					<li><strong>Hạng mục:</strong> Nâng cấp & phục hồi thẩm mỹ cửa gỗ</li>
					<li><strong>Dòng sản phẩm:</strong> Mozlex F7-ML Cao Cấp</li>
					<li><strong>Thời gian:</strong> 2 giờ thi công tận nhà</li>
				</ul>
				<div class="project-footer">
					<span class="project-highlight">Khảo sát & thi công ngay trong ngày</span>
					<a href="tel:0355514686" class="project-link">Tư vấn giải pháp tương tự &rarr;</a>
				</div>
			</div>
		</article>
	</div>

	<!-- CTA Khảo sát dự án -->
	<div class="projects-cta-box">
		<div class="cta-banner-content">
			<span class="cta-pill">CAM KẾT CHẤT LƯỢNG TIẾN ĐỘ</span>
			<h2 class="cta-head">Bạn đang chuẩn bị hoàn thiện công trình hoặc muốn nâng cấp hệ thống an ninh?</h2>
			<p class="cta-sub">Đội ngũ kỹ sư và thợ chuyên trách của Đức Trí 226 cam kết mang mẫu thiết bị đến tận nơi khảo sát, đo đạc đố cửa và lập phương án giải pháp tối ưu nhất cho bạn hoàn toàn miễn phí.</p>
			<div class="cta-actions">
				<a href="tel:0355514686" class="btn btn-cta-primary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
					<span>Hotline khảo sát: 0355514686</span>
				</a>
				<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" class="btn btn-cta-outline">
					<span>Gửi yêu cầu báo giá dự toán</span>
				</a>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var buttons = document.querySelectorAll('.proj-filter-btn');
	var cards = document.querySelectorAll('.project-card');
	buttons.forEach(function(btn) {
		btn.addEventListener('click', function() {
			buttons.forEach(function(b) { b.classList.remove('is-active'); });
			btn.classList.add('is-active');
			var filter = btn.getAttribute('data-filter');
			cards.forEach(function(c) {
				if (filter === 'all' || c.getAttribute('data-cat') === filter) {
					c.style.display = '';
				} else {
					c.style.display = 'none';
				}
			});
		});
	});
});
</script>

<?php get_footer(); ?>
