<?php
/**
 * Template Name: Lĩnh vực hoạt động
 * Layout theo form Hòa Bình: hero cần cẩu + header Dịch vụ/services + tabs 3 nhánh + nội dung trái/phải
 *
 * @package mozlex
 */
declare(strict_types=1);
get_header();

$branches = [
  'xay-dung' => [
    'label' => 'Xây dựng & Sửa chữa',
    'desc'  => 'Cung cấp các giải pháp khảo sát, thiết kế, thi công, cải tạo, hoàn thiện và bảo trì công trình dân dụng, công nghiệp, hạ tầng kỹ thuật và nội thất.',
    'content' => '<p>Với định hướng cung cấp giải pháp xây dựng toàn diện, chúng tôi triển khai các dịch vụ từ <strong>khảo sát, tư vấn, thiết kế, lập phương án kỹ thuật, thi công, cải tạo, hoàn thiện đến bảo trì và nâng cấp công trình</strong>. Phạm vi hoạt động được phát triển đa dạng, đáp ứng nhu cầu của nhiều loại hình công trình như <strong>công trình dân dụng, công nghiệp, hạ tầng kỹ thuật, văn phòng, nhà ở, không gian thương mại và nội thất</strong>.</p><p>Chúng tôi chú trọng xây dựng quy trình triển khai bài bản, trong đó mỗi dự án đều được đánh giá dựa trên đặc điểm thực tế, yêu cầu kỹ thuật, công năng sử dụng, ngân sách và tiến độ của khách hàng. Từ giai đoạn khảo sát ban đầu đến khi hoàn thiện và bàn giao, các hạng mục được kiểm soát chặt chẽ nhằm hạn chế phát sinh, đảm bảo tính đồng bộ và nâng cao hiệu quả đầu tư.</p><p>Bên cạnh việc đáp ứng các yêu cầu về kỹ thuật và chất lượng, chúng tôi đặc biệt coi trọng <strong>an toàn lao động, chất lượng vật tư, tiêu chuẩn thi công và tiến độ thực hiện</strong>. Đội ngũ triển khai luôn phối hợp chặt chẽ giữa các khâu tư vấn, thiết kế, cung ứng và thi công để đưa ra phương án phù hợp với từng điều kiện công trình.</p><p>Đối với các công trình cải tạo, sửa chữa và nâng cấp, chúng tôi tập trung vào việc đánh giá hiện trạng, xác định nguyên nhân, đưa ra phương án xử lý phù hợp và tối ưu chi phí. Mục tiêu không chỉ là khắc phục các vấn đề trước mắt mà còn nâng cao <strong>độ bền, tính an toàn, công năng và giá trị sử dụng lâu dài</strong> của công trình.</p><p>Với phương châm <strong>“An toàn – Chất lượng – Đúng tiến độ – Hiệu quả đầu tư”</strong>, chúng tôi hướng đến việc trở thành đối tác đáng tin cậy trong từng dự án, đồng hành cùng khách hàng từ những ý tưởng ban đầu đến khi công trình hoàn thiện và trong suốt quá trình vận hành, bảo trì sau này.</p>',
    'img' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=900&auto=format&fit=crop&q=80',
  ],
  'thuong-mai' => [
    'label' => 'Thương mại',
    'desc'  => 'Cung cấp, phân phối vật tư, thiết bị và các giải pháp đồng bộ — mắt xích quan trọng trong hệ sinh thái.',
    'content' => '<p>Hoạt động thương mại được định hướng trở thành một mắt xích quan trọng trong hệ sinh thái dịch vụ của chúng tôi, tập trung vào <strong>cung cấp, phân phối vật tư, thiết bị và các giải pháp đồng bộ</strong> phục vụ lĩnh vực xây dựng, sản xuất, vận hành công trình và nhiều nhu cầu chuyên biệt khác của khách hàng.</p><p>Chúng tôi xây dựng năng lực cung ứng dựa trên việc tìm kiếm, lựa chọn và phát triển hệ thống sản phẩm phù hợp với từng nhóm nhu cầu. Không chỉ đơn thuần cung cấp sản phẩm, chúng tôi hướng đến việc <strong>tư vấn giải pháp, lựa chọn chủng loại, tiêu chuẩn kỹ thuật, số lượng và phương án cung ứng</strong> phù hợp với đặc điểm của từng dự án.</p><p>Trong quá trình triển khai, chúng tôi chú trọng kiểm soát các yếu tố ảnh hưởng trực tiếp đến hiệu quả của khách hàng như <strong>chất lượng sản phẩm, tính ổn định của nguồn cung, thời gian giao hàng, chi phí và khả năng phối hợp với tiến độ thi công</strong>. Việc kết nối giữa hoạt động thương mại và năng lực xây dựng giúp quá trình cung ứng được triển khai linh hoạt hơn, hạn chế tình trạng thiếu hụt vật tư hoặc gián đoạn công việc.</p><p>Bên cạnh các sản phẩm và vật tư phục vụ xây dựng, chúng tôi từng bước mở rộng danh mục thiết bị và giải pháp nhằm đáp ứng nhu cầu ngày càng đa dạng của thị trường. Các sản phẩm được lựa chọn dựa trên tiêu chí <strong>chất lượng, nguồn gốc, tính ứng dụng, độ bền, khả năng tương thích và hiệu quả kinh tế</strong>.</p><p>Chúng tôi cũng hướng đến việc xây dựng mô hình hợp tác lâu dài với khách hàng và đối tác thông qua chính sách cung ứng linh hoạt, minh bạch và chuyên nghiệp. Tùy theo quy mô và yêu cầu của từng dự án, chúng tôi có thể phối hợp từ khâu tư vấn, lập danh mục, báo giá, cung cấp đến hỗ trợ triển khai.</p><p>Với định hướng <strong>“Đúng nhu cầu – Đúng chất lượng – Đúng tiến độ – Tối ưu chi phí”</strong>, hoạt động thương mại không chỉ tạo ra giá trị thông qua sản phẩm được cung cấp mà còn góp phần giúp khách hàng <strong>kiểm soát ngân sách, rút ngắn thời gian triển khai, giảm chi phí phát sinh và nâng cao hiệu quả tổng thể của dự án</strong>.</p>',
    'img' => get_template_directory_uri() . '/img/1.png',
  ],
  'cong-nghe' => [
    'label' => 'Công nghệ',
    'desc'  => 'Nghiên cứu, phát triển và ứng dụng các sản phẩm, nền tảng và giải pháp công nghệ có tính thực tiễn cao.',
    'content' => '<p>Trong bối cảnh chuyển đổi số đang trở thành một trong những động lực quan trọng đối với sự phát triển của doanh nghiệp, chúng tôi tập trung nghiên cứu, phát triển và ứng dụng <strong>các sản phẩm, nền tảng và giải pháp công nghệ có tính thực tiễn cao</strong>, hướng đến việc giải quyết những vấn đề cụ thể trong hoạt động sản xuất, kinh doanh và quản lý.</p><p>Lĩnh vực công nghệ được phát triển theo hướng kết hợp giữa <strong>nghiên cứu, sáng tạo và khả năng ứng dụng thực tế</strong>, tập trung vào các lĩnh vực có nhu cầu chuyển đổi và tối ưu hóa cao như <strong>xây dựng, giáo dục, sản xuất, quản lý doanh nghiệp, thương mại và các hoạt động vận hành</strong>.</p><p>Chúng tôi nghiên cứu và phát triển các giải pháp nhằm hỗ trợ doanh nghiệp <strong>số hóa quy trình, tự động hóa những công việc lặp lại, quản lý dữ liệu tập trung, nâng cao khả năng theo dõi và phân tích thông tin</strong>, từ đó giúp các bộ phận ra quyết định nhanh chóng và chính xác hơn.</p><p>Đối với lĩnh vực xây dựng, công nghệ được định hướng hỗ trợ từ khâu quản lý dự án, theo dõi tiến độ, quản lý vật tư, nhân sự và hồ sơ đến việc nâng cao khả năng phối hợp giữa các bên tham gia dự án. Trong giáo dục, các giải pháp công nghệ hướng đến việc tạo ra môi trường học tập và quản lý hiện đại, tăng khả năng kết nối và khai thác dữ liệu. Đối với sản xuất và doanh nghiệp, công nghệ có thể được ứng dụng để tối ưu quy trình, quản lý nguồn lực và nâng cao hiệu suất vận hành.</p><p>Bên cạnh việc phát triển các sản phẩm riêng, chúng tôi cũng chú trọng khả năng <strong>tích hợp và kết nối giữa các hệ thống</strong>, giúp doanh nghiệp từng bước hình thành một hệ sinh thái công nghệ phù hợp với quy mô và nhu cầu thực tế thay vì phải thay đổi toàn bộ hệ thống trong một thời gian ngắn.</p><p>Mỗi sản phẩm và giải pháp công nghệ được định hướng dựa trên ba yếu tố: <strong>Tính ứng dụng – Khả năng mở rộng – Hiệu quả thực tế</strong>. Chúng tôi không chạy theo công nghệ chỉ vì xu hướng, mà tập trung vào việc biến công nghệ thành công cụ tạo ra giá trị cụ thể, giúp tiết kiệm thời gian, giảm chi phí, tối ưu nguồn lực và nâng cao năng lực cạnh tranh.</p><p>Trong dài hạn, chúng tôi hướng đến xây dựng năng lực nghiên cứu và phát triển công nghệ một cách bền vững, chủ động nắm bắt những xu hướng mới như <strong>trí tuệ nhân tạo, tự động hóa, dữ liệu lớn, nền tảng số và các công nghệ hỗ trợ quản trị hiện đại</strong>. Qua đó, từng bước hình thành những sản phẩm và giải pháp có khả năng ứng dụng rộng rãi, không chỉ phục vụ hoạt động nội bộ mà còn mang lại giá trị cho khách hàng, đối tác, doanh nghiệp và cộng đồng.</p><p>Với định hướng <strong>“Công nghệ thực tiễn – Đổi mới liên tục – Tối ưu nguồn lực – Tạo giá trị bền vững”</strong>, chúng tôi xem công nghệ không chỉ là một lĩnh vực kinh doanh mà còn là nền tảng thúc đẩy sự đổi mới, nâng cao hiệu quả hoạt động và mở ra những cơ hội phát triển mới trong tương lai.</p>',
    'img' => get_template_directory_uri() . '/img/2.png',
  ],
];

$active = isset($_GET['nhanh']) ? sanitize_key($_GET['nhanh']) : 'xay-dung';
if (!isset($branches[$active])) $active = 'xay-dung';
?>
<?php
$hero_imgs = [
  'xay-dung' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1920&auto=format&fit=crop&q=80',
  'thuong-mai' => function_exists('mozlex_category_thumbnail_url') ? (mozlex_category_thumbnail_url((int)get_term_by('slug','thuong-mai','product_category')->term_id, 'full') ?: 'https://images.unsplash.com/photo-1441986300917-646a00fda6b7?w=1920&auto=format&fit=crop&q=80') : 'https://images.unsplash.com/photo-1441986300917-646a00fda6b7?w=1920&auto=format&fit=crop&q=80',
  'cong-nghe' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1920&auto=format&fit=crop&q=80',
];
// Nếu có ảnh custom cho Thương mại/Công nghệ từ Cấu hình 3 nhánh thì ưu tiên
$custom_thuongmai = mozlex_opt('branch_thuongmai_image');
$custom_congnghe = mozlex_opt('branch_congnghe_image');
if ($custom_thuongmai) $hero_imgs['thuong-mai'] = $custom_thuongmai;
if ($custom_congnghe) $hero_imgs['cong-nghe'] = $custom_congnghe;
// Thuong-mai dùng ảnh khóa đen bạn vừa gửi (thuongmai.png) nếu có
if (empty($custom_thuongmai)) {
  $tm_url = wp_get_attachment_url(1455);
  if ($tm_url) $hero_imgs['thuong-mai'] = $tm_url;
}
?>
<!-- Hero theo nhánh -->
<section class="linhvuc-hero" style="position:relative; height:420px; overflow:hidden; background:#0a0a0a;">
  <img src="<?php echo esc_url($hero_imgs[$active] ?? $hero_imgs['xay-dung']); ?>" alt="<?php echo esc_attr($branches[$active]['label']); ?>" style="width:100%; height:100%; object-fit:cover; opacity:0.92; display:block;">
  <div style="position:absolute; inset:0; background:linear-gradient(to bottom, rgba(0,0,0,0.18) 0%, rgba(0,0,0,0.06) 60%, transparent 100%);"></div>
</section>

<section class="shell" style="padding:28px 0 0;">
  <!-- Header như ảnh: Dịch vụ trái -->
  <div style="display:flex; justify-content:space-between; align-items:baseline; gap:16px; border-bottom:1px solid #eee; padding-bottom:14px; margin-bottom:18px;">
    <h1 style="margin:0; font-size:clamp(1.6rem, 2.5vw, 2rem); font-weight:700; color:#0a2a5e; letter-spacing:-0.02em;">Lĩnh vực hoạt động</h1>
  </div>

  <!-- Tabs 3 nhánh -->
  <div class="linhvuc-tabs" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px;">
    <?php foreach ($branches as $slug => $b) : ?>
      <a href="<?php echo esc_url(add_query_arg('nhanh', $slug, get_permalink())); ?>" class="linhvuc-tab<?php echo $slug === $active ? ' is-active' : ''; ?>" style="padding:8px 16px; border-radius:999px; font-size:0.88rem; font-weight:600; text-decoration:none; border:1px solid <?php echo $slug === $active ? '#0a0a0a' : '#ddd'; ?>; background:<?php echo $slug === $active ? '#0a0a0a' : '#fff'; ?>; color:<?php echo $slug === $active ? '#fff' : '#1a1a1a'; ?>;"><?php echo esc_html($b['label']); ?></a>
    <?php endforeach; ?>
  </div>

  <!-- Nội dung: trái text, phải ảnh như ảnh mẫu -->
  <div style="display:grid; grid-template-columns: 1.35fr 0.85fr; gap:28px; align-items:start;">
    <div style="font-size:0.95rem; line-height:1.75; color:#222;">
      <p style="color:#6b6b6b; font-size:0.88rem; margin:0 0 8px;"><em><?php echo esc_html($branches[$active]['desc']); ?></em></p>
      <div class="linhvuc-content">
        <?php echo wp_kses_post($branches[$active]['content']); ?>
      </div>
      <div style="margin-top:18px; display:flex; gap:10px; flex-wrap:wrap;">
        <a href="<?php echo esc_url(get_term_link(get_term_by('slug', $active, 'product_category'))); ?>" class="btn btn-ink" style="text-transform:uppercase; padding:10px 18px; font-size:0.82rem;">Xem sản phẩm <?php echo esc_html($branches[$active]['label']); ?> →</a>
        <a href="<?php echo esc_url(home_url('/lien-he/')); ?>" class="btn btn-line" style="padding:10px 18px; font-size:0.82rem; border-color:#0a0a0a; color:#0a0a0a;">Liên hệ tư vấn</a>
      </div>
    </div>
    <div style="position:sticky; top:96px;">
      <img src="<?php echo esc_url($branches[$active]['img']); ?>" alt="<?php echo esc_attr($branches[$active]['label']); ?>" style="width:100%; height:auto; border-radius:8px; border:1px solid #eee; display:block; object-fit:cover;">
    </div>
  </div>

  <!-- Lưới 3 nhánh nhỏ dưới để chuyển nhanh -->
  <div style="margin-top:36px; display:grid; grid-template-columns: repeat(3, 1fr); gap:14px;">
    <?php foreach ($branches as $slug => $b) : $isActive = $slug === $active; $term = get_term_by('slug', $slug, 'product_category'); $count = $term ? (int)$term->count : 0; $childs = $term ? count(get_terms(['taxonomy'=>'product_category','hide_empty'=>false,'parent'=>(int)$term->term_id])) : 0; ?>
      <a href="<?php echo esc_url(add_query_arg('nhanh', $slug, get_permalink())); ?>" style="display:block; padding:16px; border-radius:10px; border:1px solid <?php echo $isActive ? 'var(--primary)' : '#eee'; ?>; background:<?php echo $isActive ? 'var(--warm-white)' : '#fff'; ?>; text-decoration:none; color:inherit;">
        <h3 style="margin:0 0 6px; font-size:0.95rem; font-weight:700; text-transform:uppercase; color:<?php echo $isActive ? 'var(--primary)' : '#0a0a0a'; ?>;"><?php echo esc_html($b['label']); ?></h3>
        <p style="margin:0; font-size:0.85rem; color:#4a5568;"><?php echo esc_html($b['desc']); ?></p>

      </a>
    <?php endforeach; ?>
  </div>
</section>

<style>
/* Base: prevent any fixed width causing overflow */
html, body { max-width:100%; overflow-x:clip; }
/* KHÔNG ép .shell full-width ở đây (global) — sẽ đè cap 1440px của .shell dùng chung,
   làm footer (3 hàng đều là .shell) bị bè hết mép trên desktop. Chống tràn ngang cho
   .shell trên mobile đã được xử lý trong @media (max-width:768px) bên dưới. */
img { max-width:100%; height:auto; box-sizing:border-box; }
.linhvuc-tabs { max-width:100%; box-sizing:border-box; }
.linhvuc-content { min-width:0; word-break:break-word; overflow-wrap:break-word; }
@media (max-width: 860px){
  .linhvuc-hero{ height:260px !important; width:100% !important; max-width:100% !important; }
  .linhvuc-hero img{ width:100% !important; max-width:100% !important; }
  section.shell div[style*="grid-template-columns: 1.35fr"]{ grid-template-columns:1fr !important; min-width:0; width:100% !important; max-width:100% !important; }
  section.shell div[style*="grid-template-columns: repeat(3"]{ grid-template-columns:1fr !important; min-width:0; width:100% !important; max-width:100% !important; }
  /* Header “Lĩnh vực hoạt động / fields” must wrap */
  section.shell > div[style*="display:flex; justify-content:space-between"]{ flex-wrap:wrap !important; gap:8px !important; width:100% !important; max-width:100% !important; }
  section.shell > div[style*="display:flex; justify-content:space-between"] h1{ min-width:0; flex:1 1 auto; font-size:clamp(1.3rem, 5vw, 1.6rem) !important; }
  section.shell > div[style*="display:flex; justify-content:space-between"] span{ font-size:clamp(1.6rem, 8vw, 2.2rem) !important; flex-shrink:0; }
}
@media (max-width: 768px){
  .shell{ padding-inline:16px !important; }
  .linhvuc-tabs{ gap:6px !important; padding-right:0 !important; }
  .linhvuc-tab{ font-size:0.82rem !important; padding:6px 12px !important; white-space:nowrap; }
  /* Ensure sticky image not causing overflow */
  div[style*="position:sticky"]{ position:static !important; width:100% !important; max-width:100% !important; }
  div[style*="position:sticky"] img{ width:100% !important; max-width:100% !important; }
  /* Floating buttons inside viewport */
  .floating-contact{ right:12px !important; left:auto !important; max-width:calc(100vw - 24px) !important; }
}
@media (max-width: 440px){
  .linhvuc-hero{ height:220px !important; }
  .linhvuc-tabs{ gap:6px !important; }
  .linhvuc-tab{ font-size:0.78rem !important; padding:6px 10px !important; }
  section.shell div[style*="grid-template-columns: 1.35fr"] > div:first-child{ padding:0 !important; }
}
@media (max-width: 360px){
  .linhvuc-tabs a{ font-size:0.75rem !important; padding:5px 10px !important; }
}
.linhvuc-tab:hover{ border-color: var(--primary) !important; color: var(--primary) !important; }
.linhvuc-tab.is-active:hover{ color:#fff !important; border-color:#0a0a0a !important; }

@media (max-width: 768px){
  /* Header mobile: ensure no overflow */
  .site-header{ inset: 0 0 auto !important; width:100% !important; max-width:100% !important; left:0 !important; right:0 !important; }
  .header-inner{ padding-inline:16px !important; gap:12px !important; height:64px !important; }
  .brand{ max-width:40% !important; }
  .brand-logo{ max-width:120px !important; height:auto !important; width:auto !important; }
  .header-actions{ gap:8px !important; }
  .search-dropdown{ left:16px !important; right:16px !important; width:auto !important; max-width:none !important; }
  /* Ensure no 100vw */
  .linhvuc-hero, .shell{ width:100% !important; max-width:100% !important; }
}

</style>

<?php get_footer(); ?>
<style>
@media (min-width: 861px){
  section.shell{ padding-inline:32px !important; }
  .linhvuc-content{ padding-right:16px; }
}
</style>
<style>
@media (min-width: 769px){
  /* Footer căn giữa chỉ trên desktop của trang này, mobile giữ nguyên */
  body.page-linh-vuc-hoat-dong .footer-pro-grid { justify-items:center; text-align:center; }
  body.page-linh-vuc-hoat-dong .footer-pro-col { text-align:center; }
}
</style>
