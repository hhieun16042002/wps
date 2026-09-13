<?php
/**
 * Template Name: Về Mozlex — Premium
 */
declare(strict_types=1);
get_header();
?>
<section class="about-hero" style="position:relative; height:360px; display:grid; place-items:center; overflow:hidden; background:#0a0a0a;">
  <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&auto=format&fit=crop&q=80" alt="Giới thiệu" style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0.45;">
  <div style="position:relative; z-index:1; text-align:center; color:#fff; padding:0 16px;">
    <h1 style="margin:0; font-size:clamp(1.8rem,4vw,2.6rem); font-weight:800; letter-spacing:-0.02em; color:#fff;"><?php the_title(); ?></h1>

  </div>
</section>

<section class="shell" style="padding:40px 0 0;">
  <?php mozlex_breadcrumb(); ?>
</section>

<section class="about-editorial shell" style="padding:32px 0;">
  <?php if(have_posts()): while(have_posts()): the_post(); ?>
    <div class="editorial-content" style="max-width:720px; margin:0 auto; font-size:1.05rem; line-height:1.8; color:#2a2a2a;">
      <?php the_content(); ?>
    </div>
  <?php endwhile; endif; ?>
  <div class="why-choose" style="max-width:800px; margin:24px auto 0; display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:center; background:var(--warm-white); border:1px solid rgba(0,0,0,0.06); border-radius:16px; padding:24px;">
    <div>
      <h2 style="margin:0 0 8px; font-size:1.25rem; font-weight:800;">Vì sao chọn Đức Trí 226?</h2>
      <p style="margin:0; color:rgba(0,0,0,0.65); line-height:1.7; text-align:justify; text-justify:inter-word;">Chúng tôi không chỉ bán khóa — chúng tôi tư vấn, khảo sát, lắp đặt và bảo hành tận nơi. Mỗi sản phẩm đều có CO/CQ, bảo hành 36 tháng, đổi mới 6 tháng.</p>
      <p style="margin:8px 0 0; color:#1a1a1a; line-height:1.7; text-align:justify; text-justify:inter-word;">- Tư vấn đúng loại khóa cho từng loại cửa<br>- Lắp đặt chuyên nghiệp, bàn giao đúng kỹ thuật<br>- Bảo trì miễn phí năm đầu</p>
    </div>
    <div class="why-choose-logo">
      <img src="<?php echo esc_url(get_template_directory_uri().'/assets/img/logo-dt226.png'); ?>" alt="Đức Trí 226" style="width:160px; max-width:160px; height:auto; filter:drop-shadow(0 8px 24px rgba(0,0,0,0.08));">
    </div>
  </div>
  <ol class="brand-values" aria-label="Giá trị thương hiệu" style="margin-top:36px; display:grid; grid-template-columns:repeat(4,1fr); gap:16px; list-style:none; padding:0;">
    <li style="background:#fff; border:1px solid rgba(0,0,0,0.06); border-radius:12px; padding:18px; text-align:center;">
      <span class="value-index" style="display:inline-block; font-size:0.7rem; letter-spacing:0.12em; color:var(--primary); font-weight:700; background:var(--cream); padding:4px 8px; border-radius:999px;">01</span>
      <h2 style="margin:10px 0 6px; font-size:0.95rem; font-weight:800; letter-spacing:0.04em;">TINH TẾ</h2>
      <p style="margin:0; font-size:0.9rem; color:rgba(0,0,0,0.6);">Thiết kế giao thoa giữa nghệ thuật & công nghệ.</p>
    </li>
    <li style="background:#fff; border:1px solid rgba(0,0,0,0.06); border-radius:12px; padding:18px; text-align:center;">
      <span class="value-index" style="display:inline-block; font-size:0.7rem; letter-spacing:0.12em; color:var(--primary); font-weight:700; background:var(--cream); padding:4px 8px; border-radius:999px;">02</span>
      <h2 style="margin:10px 0 6px; font-size:0.95rem; font-weight:800; letter-spacing:0.04em;">CHẤT LƯỢNG</h2>
      <p style="margin:0; font-size:0.9rem; color:rgba(0,0,0,0.6);">Ứng dụng công nghệ mới tiên tiến.</p>
    </li>
    <li style="background:#fff; border:1px solid rgba(0,0,0,0.06); border-radius:12px; padding:18px; text-align:center;">
      <span class="value-index" style="display:inline-block; font-size:0.7rem; letter-spacing:0.12em; color:var(--primary); font-weight:700; background:var(--cream); padding:4px 8px; border-radius:999px;">03</span>
      <h2 style="margin:10px 0 6px; font-size:0.95rem; font-weight:800; letter-spacing:0.04em;">BỀN BỈ</h2>
      <p style="margin:0; font-size:0.9rem; color:rgba(0,0,0,0.6);">Chịu được thời tiết khắc nghiệt.</p>
    </li>
    <li style="background:#fff; border:1px solid rgba(0,0,0,0.06); border-radius:12px; padding:18px; text-align:center;">
      <span class="value-index" style="display:inline-block; font-size:0.7rem; letter-spacing:0.12em; color:var(--primary); font-weight:700; background:var(--cream); padding:4px 8px; border-radius:999px;">04</span>
      <h2 style="margin:10px 0 6px; font-size:0.95rem; font-weight:800; letter-spacing:0.04em;">BẢO HÀNH</h2>
      <p style="margin:0; font-size:0.9rem; color:rgba(0,0,0,0.6);">36 tháng — đổi mới 6 tháng khi lỗi NSX.</p>
    </li>
  </ol>
  <div style="text-align:center; margin-top:28px;">
    <a class="btn btn-ink" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn</a>
    <a class="btn btn-line" href="<?php echo esc_url(home_url('/san-pham/')); ?>" style="border-color:#0a0a0a; color:#0a0a0a; margin-left:8px;">Xem sản phẩm</a>
  </div>
</section>
<style>
@media (max-width:768px){
  .about-hero{ height:280px !important; }
  .brand-values{ grid-template-columns:1fr 1fr !important; }
}
@media (max-width:480px){
  .brand-values{ grid-template-columns:1fr !important; }
}
</style>
<?php get_footer(); ?>
<style>
@media (min-width:769px){
  .why-choose{ grid-template-columns:1.2fr 0.8fr !important; gap:40px !important; padding:36px 40px !important; border-radius:20px !important; box-shadow:0 12px 40px rgba(0,0,0,0.06); align-items:center !important; max-width:880px !important; }
  .why-choose h2{ font-size:1.5rem !important; position:relative; padding-bottom:12px; margin-bottom:16px !important; }
  .why-choose h2::after{ content:""; position:absolute; left:0; bottom:0; width:56px; height:3px; background:var(--primary,#b8894d); border-radius:999px; }
  .why-choose-logo{ background:#fff; border:1px solid rgba(0,0,0,0.05); border-radius:16px; padding:28px 24px; display:grid; place-items:center; }
  .why-choose-logo img{ width:220px !important; max-width:100% !important; }
}
@media (max-width:768px){
  .about-editorial{ padding-left:16px !important; padding-right:16px !important; }
  .about-editorial .editorial-content{ padding:0 4px; text-align:justify; text-justify:inter-word; }
  .about-editorial .editorial-content p{ text-align:justify; }
  .why-choose{ grid-template-columns:1fr !important; }
  .why-choose p, .why-choose li{ text-align:justify; text-justify:inter-word; }
  .why-choose-logo{ display:grid; place-items:center; }
}
</style>
