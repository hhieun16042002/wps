<?php
/**
 * Template Name: Tư vấn chọn khóa
 * Wizard 5 bước — kết quả chỉ là gợi ý tham khảo, filter client-side trên
 * dữ liệu sản phẩm thật được render sẵn trong markup.
 *
 * @package mozlex
 */

declare( strict_types=1 );

get_header();

$wizard = new WP_Query( array(
	'post_type'      => 'product',
	'posts_per_page' => 100,
	'post_status'    => 'publish',
	'no_found_rows'  => true,
) );
?>
<section class="archive-head">
	<div class="shell">
		<?php mozlex_breadcrumb(); ?>
		<h1 class="page-title"><?php esc_html_e( 'Tìm đúng mẫu khóa cho đúng cánh cửa', 'mozlex' ); ?></h1>
	</div>
</section>

<section class="shell wizard-section" id="wizard" data-wizard>
	<ol class="wizard-steps" aria-label="<?php esc_attr_e( 'Tiến độ', 'mozlex' ); ?>">
		<li data-step-indicator="1">01 <span><?php esc_html_e( 'Loại cửa', 'mozlex' ); ?></span></li>
		<li data-step-indicator="2">02 <span><?php esc_html_e( 'Độ dày cửa', 'mozlex' ); ?></span></li>
		<li data-step-indicator="3">03 <span><?php esc_html_e( 'Mở khóa bằng', 'mozlex' ); ?></span></li>
		<li data-step-indicator="4">04 <span><?php esc_html_e( 'Ngân sách', 'mozlex' ); ?></span></li>
		<li data-step-indicator="5">05 <span><?php esc_html_e( 'Màu sắc', 'mozlex' ); ?></span></li>
	</ol>

	<form class="wizard-panel" id="wizard-form" aria-live="polite">
		<!-- STEP 1 -->
		<fieldset class="wizard-step is-active" data-step="1">
			<legend>01 — <?php esc_html_e( 'Cửa của bạn thuộc loại nào?', 'mozlex' ); ?></legend>
			<div class="choice-row">
				<label><input type="radio" name="door" value="cuua-go-nhua"> <?php esc_html_e( 'Cửa gỗ / nhựa', 'mozlex' ); ?></label>
				<label><input type="radio" name="door" value="cua-nhom-xingfa"> <?php esc_html_e( 'Cửa nhôm', 'mozlex' ); ?></label>
				<label><input type="radio" name="door" value="cua-kinh"> <?php esc_html_e( 'Cửa kính', 'mozlex' ); ?></label>
				<label><input type="radio" name="door" value="khác"> <?php esc_html_e( 'Khác', 'mozlex' ); ?></label>
			</div>
			<button type="button" class="btn btn-ink sm" data-next>Tiếp tục &rarr;</button>
		</fieldset>

		<!-- STEP 2 -->
		<fieldset class="wizard-step" data-step="2" hidden>
			<legend>02 — <?php esc_html_e( 'Độ dày của cánh cửa (mm)?', 'mozlex' ); ?></legend>
			<input type="number" name="thickness" min="8" max="120" inputmode="numeric"
				placeholder="<?php esc_attr_e( 'VD: 45', 'mozlex' ); ?>">
			<p class="hint"><?php esc_html_e( 'Hầu hết khóa cơ Mozlex phù hợp cửa dày 38–50 mm; khóa kính dùng cho cửa 10–12 mm.', 'mozlex' ); ?></p>
			<button type="button" class="btn btn-line sm" data-prev>&larr; Quay lại</button>
			<button type="button" class="btn btn-ink sm" data-next>Tiếp tục &rarr;</button>
		</fieldset>

		<!-- STEP 3 -->
		<fieldset class="wizard-step" data-step="3" hidden>
			<legend>03 — <?php esc_html_e( 'Bạn muốn mở khóa bằng?', 'mozlex' ); ?></legend>
			<div class="choice-row multi">
				<label><input type="checkbox" name="unlock[]" value="face-id"> Face ID</label>
				<label><input type="checkbox" name="unlock[]" value="van-tay"> Vân tay</label>
				<label><input type="checkbox" name="unlock[]" value="mat-ma"> Mật mã</label>
				<label><input type="checkbox" name="unlock[]" value="the-tu"> Thẻ từ</label>
				<label><input type="checkbox" name="unlock[]" value="chia-co"> Chìa cơ</label>
			</div>
			<button type="button" class="btn btn-line sm" data-prev>&larr; Quay lại</button>
			<button type="button" class="btn btn-ink sm" data-next>Tiếp tục &rarr;</button>
		</fieldset>

		<!-- STEP 4 -->
		<fieldset class="wizard-step" data-step="4" hidden>
			<legend>04 — <?php esc_html_e( 'Ngân sách dự kiến?', 'mozlex' ); ?></legend>
			<div class="choice-row">
				<label><input type="radio" name="budget" value="duoi-5-trieu"> <?php esc_html_e( 'Dưới 5 triệu', 'mozlex' ); ?></label>
				<label><input type="radio" name="budget" value="tu-5-10-trieu"> <?php esc_html_e( '5 – 10 triệu', 'mozlex' ); ?></label>
				<label><input type="radio" name="budget" value="tren-10-trieu"> <?php esc_html_e( 'Trên 10 triệu', 'mozlex' ); ?></label>
				<label><input type="radio" name="budget" value=""> <?php esc_html_e( 'Chưa chắc — cần tư vấn', 'mozlex' ); ?></label>
			</div>
			<button type="button" class="btn btn-line sm" data-prev>&larr; Quay lại</button>
			<button type="button" class="btn btn-ink sm" data-next>Tiếp tục &rarr;</button>
		</fieldset>

		<!-- STEP 5 -->
		<fieldset class="wizard-step" data-step="5" hidden>
			<legend>05 — <?php esc_html_e( 'Màu sắc yêu thích?', 'mozlex' ); ?></legend>
			<div class="choice-row">
				<label><input type="radio" name="color" value="den"> Đen</label>
				<label><input type="radio" name="color" value="pvd"> PVD vàng</label>
				<label><input type="radio" name="color" value="ghi"> Ghi</label>
				<label><input type="radio" name="color" value="inox"> Inox</label>
				<label><input type="radio" name="color" value=""> Không quan trọng</label>
			</div>
			<button type="button" class="btn btn-line sm" data-prev>&larr; Quay lại</button>
			<button type="submit" class="btn btn-gold sm"><?php esc_html_e( 'Xem gợi ý phù hợp', 'mozlex' ); ?></button>
		</fieldset>
	</form>

	<!-- Kết quả -->
	<section class="wizard-results" id="wizard-results" hidden aria-labelledby="results-title">
		<h2 class="section-title sm" id="results-title"><?php esc_html_e( 'Một số lựa chọn phù hợp', 'mozlex' ); ?></h2>
		<p class="hint"><?php esc_html_e( 'Kết quả chỉ mang tính gợi ý tham khảo. Vui lòng liên hệ để được tư vấn chính xác theo cánh cửa thực tế.', 'mozlex' ); ?></p>
		<div class="product-grid grid-4" id="wizard-grid"></div>
		<a class="btn btn-ink" href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>" style="text-transform:uppercase"><?php esc_html_e( 'NHẬN TƯ VẤN CHI TIẾT', 'mozlex' ); ?></a>
	</section>
</section>

<template id="wizard-cards-template">
<?php if ( $wizard->have_posts() ) : ?>
	<?php while ( $wizard->have_posts() ) : $wizard->the_post(); ?>
		<article class="product-card"
			data-model="<?php echo esc_attr( get_post_meta( get_the_ID(), 'mozlex_model', true ) ?: get_the_title() ); ?>"
			data-price="<?php echo esc_attr( (string) get_post_meta( get_the_ID(), 'mozlex_price', true ) ); ?>"
			data-url="<?php the_permalink(); ?>"
			data-filters="<?php echo esc_attr( implode( ',',
				array_merge(
					wp_list_pluck( wp_get_post_terms( get_the_ID(), 'application' ), 'slug' ),
					wp_list_pluck( wp_get_post_terms( get_the_ID(), 'unlock_method' ), 'slug' ),
					wp_list_pluck( wp_get_post_terms( get_the_ID(), 'price_range' ), 'slug' ),
					wp_list_pluck( wp_get_post_terms( get_the_ID(), 'color' ), 'slug' )
				)
			) ); ?>">
			<a href="<?php the_permalink(); ?>" class="card-link">
				<figure class="card-figure">
					<?php echo get_the_post_thumbnail( get_the_ID(), 'mozlex-card', array( 'loading' => 'lazy', 'class' => 'card-image' ) ) ?: '<div class="card-image card-image-placeholder"><span>' . esc_html( get_post_meta( get_the_ID(), 'mozlex_model', true ) ?: get_the_title() ) . '</span></div>'; // phpcs:ignore ?>
				</figure>
				<div class="card-body">
					<h3 class="card-title"><?php echo esc_html( get_post_meta( get_the_ID(), 'mozlex_model', true ) ?: get_the_title() ); ?></h3>
					<p class="card-price"><?php echo esc_html( mozlex_price_text() ?: __( 'Liên hệ tư vấn', 'mozlex' ) ); ?></p>
				</div>
			</a>
		</article>
	<?php endwhile; wp_reset_postdata(); ?>
<?php endif; ?>
</template>

<?php get_footer(); ?>
