<?php
/**
 * Importer: nhập catalogue thật từ inc/data/products.json.
 * Chạy qua Admin → Tools hoặc `wp mozlex import` (WP-CLI).
 *
 * @package mozlex
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import toàn bộ seed data. Idempotent — theo slug nên chạy lại an toàn (cập nhật).
 */
function mozlex_import_catalogue() {
	$file = MOZLEX_DIR . '/inc/data/products.json';
	if ( ! file_exists( $file ) ) {
		return new WP_Error( 'mozlex_seed', 'Không tìm thấy products.json trong theme.' );
	}
	$data   = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$report = array( 'created' => 0, 'updated' => 0 );

	// Đảm bảo terms tồn tại trước khi gán.
	mozlex_seed_terms();
	do_action( 'init' ); // đảm bảo taxonomies đăng ký khi gọi từ CLI.

	foreach ( (array) ( $data['products'] ?? array() ) as $item ) {
		$model = $item['model'] ?? '';
		if ( ! $model ) {
			continue;
		}

		$slug     = sanitize_title( $item['slug'] ?? $model );
		$existing = get_page_by_path( $slug, OBJECT, 'product' );
		$postarr  = array(
			'post_type'    => 'product',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $model,
			'post_content' => sprintf( '<p>%s</p>', esc_html( $item['short_description'] ?? '' ) ),
			'meta_input'   => array(
				'mozlex_model'      => $model,
				'mozlex_price'      => isset( $item['price'] ) ? (float) $item['price'] : '',
				'mozlex_price_status' => 'lien-he' === ( $item['price_status'] ?? '' ) ? 'an-gia' : ( $item['price_status'] ?? '' ),
				'mozlex_material'   => $item['material'] ?? '',
				'mozlex_color'      => $item['color'] ?? '',
				'mozlex_door'       => $item['door_thickness'] ?? '',
				'mozlex_core_type'  => $item['core_type'] ?? '',
				'mozlex_dimensions' => $item['dimensions'] ?? '',
				'mozlex_capacity'   => $item['capacity'] ?? '',
				'mozlex_battery'    => $item['battery'] ?? '',
				'mozlex_tech_specs' => $item['technical_specs'] ?? array(),
			),
		);

		if ( $existing ) {
			$postarr['ID'] = $existing->ID;
			wp_update_post( $postarr );
			$id           = $existing->ID;
			++$report['updated'];
		} else {
			$id = wp_insert_post( $postarr );
			++$report['created'];
		}
		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}

		// Taxonomies — chỉ term có thật trong seed.
		$tax_map = array(
			'product_category' => array( $item['category'] ?? '' ),
			'unlock_method'    => $item['unlock_methods'] ?? array(),
			'application'      => array_filter( array( $item['application'] ?? '' ) ),
			'color'            => isset( $item['color_slug'] ) ? array( $item['color_slug'] ) : array(),
		);
		foreach ( $tax_map as $tax => $slugs ) {
			wp_set_object_terms( $id, array_filter( (array) $slugs ), $tax, false );
		}

		// Feature terms theo tên (vocab chuẩn).
		if ( ! empty( $item['features'] ) ) {
			wp_set_object_terms( $id, array_map( 'mozlex_feature_slug_from_name', $item['features'] ), 'feature', false );
		}

		// Price range dựa trên giá thật.
		if ( ! empty( $item['price'] ) ) {
			$p     = (float) $item['price'];
			$range = $p < 5000000 ? 'duoi-5-trieu' : ( $p <= 10000000 ? 'tu-5-10-trieu' : 'tren-10-trieu' );
			wp_set_object_terms( $id, array( $range ), 'price_range', false );
		} else {
			wp_set_object_terms( $id, array( 'lien-he-tu-van' ), 'price_range', false );
		}
	}

	return $report;
}

function mozlex_feature_slug_from_name( $name ) {
	static $map = null;
	if ( null === $map ) {
		$map = array();
		foreach ( wp_list_pluck( get_terms( array( 'taxonomy' => 'feature', 'hide_empty' => false, 'number' => 200 ) ), 'name', 'slug' ) ?: array() as $slug => $n ) { // phpcs:ignore
			$map[ $n ] = $slug;
		}
	}
	return $map[ $name ] ?? sanitize_title( $name );
}

/**
 * Admin page trigger.
 */
add_action( 'admin_menu', function () {
	add_management_page( __( 'Import catalogue Mozlex', 'mozlex' ), __( 'Import Mozlex', 'mozlex' ), 'manage_options', 'mozlex-import', function () {
		$result = null;
		if ( isset( $_POST['mozlex_import_nonce'], $_POST['action_key'] )
			&& wp_verify_nonce( sanitize_key( $_POST['mozlex_import_nonce'] ), 'mozlex_import' ) ) {
			$result = mozlex_import_catalogue();
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Nhập catalogue Mozlex', 'mozlex' ); ?></h1>
			<?php if ( is_wp_error( $result ) ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( $result->get_error_message() ); ?></p></div>
			<?php elseif ( is_array( $result ) ) : ?>
				<div class="notice notice-success"><p>
					<?php printf( esc_html__( 'Tạo mới %d — cập nhật %d sản phẩm.', 'mozlex' ), $result['created'], $result['updated'] ); ?>
				</p></div>
			<?php endif; ?>
			<form method="post">
				<input type="hidden" name="action_key" value="1">
				<?php wp_nonce_field( 'mozlex_import', 'mozlex_import_nonce' ); ?>
				<p><?php esc_html_e( 'Nhập dữ liệu từ file inc/data/products.json (dữ liệu lấy trực tiếp từ catalogue 2026). Chạy lại sẽ cập nhật theo model, không tạo trùng.', 'mozlex' ); ?></p>
				<p><button class="button button-primary"><?php esc_html_e( 'Chạy import', 'mozlex' ); ?></button></p>
			</form>
		</div>
		<?php
	} );
} );

/**
 * WP-CLI: wp mozlex import
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'mozlex import', function () {
		$r = mozlex_import_catalogue();
		if ( is_wp_error( $r ) ) {
			WP_CLI::error( $r->get_error_message() );
		}
		WP_CLI::success( "Tạo mới {$r['created']} — cập nhật {$r['updated']}." );
	} );
}
