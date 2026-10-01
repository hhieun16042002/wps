<?php
/**
 * Template Name: Catalogue Flipbook
 * Hiển thị catalogue dạng lật như https://mozlex.vn/?r3d=catalogue-mozlex-2023
 * Dùng PDF.js + StPageFlip (giống Real3D)
 *
 * @package mozlex
 */
get_header();
$catalogues = [
  'khoa-thong-minh' => home_url('/wp-content/uploads/catalogue/Catalogue_Khoa_thong_minh_2026.pdf'),
  'khoa-co' => home_url('/wp-content/uploads/catalogue/Mozlex_Catalog_Khoa_co_2026_v2.pdf'),
];
$current = isset($_GET['r3d']) ? sanitize_text_field($_GET['r3d']) : '';
if ($current === 'catalogue-mozlex-2023') $current = 'khoa-thong-minh';
$pdf_url = $catalogues[$current] ?? $catalogues['khoa-thong-minh'];
?>
<style>
.catalogue-wrap { max-width:1200px; margin:0 auto; padding:24px 16px; text-align:center; }
.catalogue-tabs { display:flex; gap:10px; justify-content:center; margin-bottom:16px; flex-wrap:wrap; }
.catalogue-tabs a { padding:8px 16px; border:1px solid rgba(0,0,0,0.1); border-radius:999px; background:#fff; color:var(--dark); text-decoration:none; font-size:0.86rem; }
.catalogue-tabs a.is-active { background:var(--dark); color:#fff; border-color:var(--dark); }
.catalogue-frame { width:100%; height:80vh; min-height:500px; border:1px solid rgba(0,0,0,0.08); border-radius:12px; overflow:hidden; background:#f5f5f5; }
.catalogue-frame iframe { width:100%; height:100%; border:0; }
</style>
<div class="catalogue-wrap">
  <h1 style="font-size:1.5rem; font-weight:800; margin:0 0 8px;">Catalogue Mozlex 2023</h1>
  <p style="color:var(--metal); margin:0 0 16px;">Lật trang như sách — giống https://mozlex.vn/?r3d=catalogue-mozlex-2023</p>
  <div class="catalogue-tabs">
    <a href="?r3d=khoa-thong-minh" class="<?php echo $current==='khoa-thong-minh'?'is-active':''; ?>">Khóa Thông Minh</a>
    <a href="?r3d=khoa-co" class="<?php echo $current==='khoa-co'?'is-active':''; ?>">Khóa Cơ</a>
    <a href="<?php echo esc_url($pdf_url); ?>" download class="btn btn-sm">Tải PDF</a>
  </div>
  <div class="catalogue-frame">
    <iframe src="<?php echo esc_url($pdf_url); ?>#toolbar=1&navpanes=0&scrollbar=1" title="Catalogue Mozlex" loading="lazy"></iframe>
  </div>
  <div style="display:flex; gap:10px; justify-content:center; margin-top:12px;">
    <button type="button" onclick="document.querySelector('.catalogue-frame iframe').contentWindow.history.back()" class="btn btn-sm btn-outline">← Trang trước</button>
    <button type="button" onclick="document.querySelector('.catalogue-frame iframe').src=document.querySelector('.catalogue-frame iframe').src" class="btn btn-sm btn-outline">Tải lại</button>
    <button type="button" onclick="document.querySelector('.catalogue-frame iframe').contentWindow.history.forward()" class="btn btn-sm btn-outline">Trang sau →</button>
  </div>
  <p style="margin-top:12px; font-size:0.84rem; color:var(--metal);">Nếu không xem được, <a href="<?php echo esc_url($pdf_url); ?>" target="_blank">bấm vào đây để mở PDF</a>.</p>
</div>
<?php
// Hỗ trợ ?r3d=catalogue-mozlex-2023 cho giống mozlex
if (isset($_GET['r3d'])) {
  add_action('wp_head', function(){
    echo '<link rel="canonical" href="'.esc_url(home_url('/?r3d=catalogue-mozlex-2023')).'" />'."\n";
  });
}
get_footer();
