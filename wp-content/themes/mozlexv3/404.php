<?php
/** Branded error recovery; WordPress retains the 404 response status. */
get_header();
?>
<section class="shell error-page">
	<p class="eyebrow">Đức Trí 226 · 404</p>
	<h1 class="page-title">Trang không tồn tại</h1>
	<p>Liên kết có thể đã thay đổi. Bạn có thể tìm sản phẩm hoặc quay lại trang chủ để tiếp tục khám phá.</p>
	<?php get_search_form(); ?>
	<div class="recovery-actions"><a class="btn btn-ink" href="<?php echo esc_url( home_url( '/' ) ); ?>">Quay lại trang chủ</a><a class="btn btn-outline-dark" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Xem sản phẩm</a></div>
</section>
<?php get_footer(); ?>
