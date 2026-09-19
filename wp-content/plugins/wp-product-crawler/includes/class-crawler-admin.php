<?php
/**
 * Lớp điều khiển Admin: Menus, Giao diện và các cổng kết nối AJAX thời gian thực.
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Crawler_Admin {

	private static ?WP_Crawler_Admin $instance = null;

	public static function get_instance(): WP_Crawler_Admin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_admin_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// AJAX Endpoints
		add_action( 'wp_ajax_dt_crawler_start_scan', array( $this, 'ajax_start_scan' ) );
		add_action( 'wp_ajax_dt_crawler_discover_urls', array( $this, 'ajax_discover_urls' ) );
		add_action( 'wp_ajax_dt_crawler_extract_item', array( $this, 'ajax_extract_item' ) );
		add_action( 'wp_ajax_dt_crawler_save_preview_item', array( $this, 'ajax_save_preview_item' ) );
		add_action( 'wp_ajax_dt_crawler_import_item', array( $this, 'ajax_import_item' ) );
		add_action( 'wp_ajax_dt_crawler_retry_item', array( $this, 'ajax_retry_item' ) );
		add_action( 'wp_ajax_dt_crawler_create_category', array( $this, 'ajax_create_category' ) );
		add_action( 'wp_ajax_dt_crawler_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_dt_crawler_get_logs', array( $this, 'ajax_get_logs' ) );
		add_action( 'wp_ajax_dt_crawler_rerun_job', array( $this, 'ajax_rerun_job' ) );
		add_action( 'wp_ajax_dt_crawler_get_active_session', array( $this, 'ajax_get_active_session' ) );
		add_action( 'wp_ajax_dt_crawler_pause_job', array( $this, 'ajax_pause_job' ) );
		add_action( 'wp_ajax_dt_crawler_resume_job', array( $this, 'ajax_resume_job' ) );
		add_action( 'wp_ajax_dt_crawler_cancel_job', array( $this, 'ajax_cancel_job' ) );
		add_action( 'wp_ajax_dt_crawler_skip_item', array( $this, 'ajax_skip_item' ) );
		add_action( 'wp_ajax_dt_crawler_delete_item', array( $this, 'ajax_delete_item' ) );
		add_action( 'wp_ajax_dt_crawler_delete_items', array( $this, 'ajax_delete_items' ) );
	}

	public function register_admin_menus(): void {
		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Import từ Website', 'wp-product-crawler' ),
			__( 'Import từ Website', 'wp-product-crawler' ),
			'manage_options',
			'dt-crawler-import',
			array( $this, 'render_import_page' )
		);

		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Lịch sử Import', 'wp-product-crawler' ),
			__( 'Lịch sử Import', 'wp-product-crawler' ),
			'manage_options',
			'dt-crawler-history',
			array( $this, 'render_history_page' )
		);

		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Cấu hình Crawler', 'wp-product-crawler' ),
			__( 'Cấu hình Crawler', 'wp-product-crawler' ),
			'manage_options',
			'dt-crawler-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, 'dt-crawler' ) ) {
			return;
		}

		$ver_css = file_exists( WP_PRODUCT_CRAWLER_PATH . 'assets/css/crawler-admin.css' ) ? (string) filemtime( WP_PRODUCT_CRAWLER_PATH . 'assets/css/crawler-admin.css' ) : WP_PRODUCT_CRAWLER_VERSION;
		$ver_js  = file_exists( WP_PRODUCT_CRAWLER_PATH . 'assets/js/crawler-admin.js' ) ? (string) filemtime( WP_PRODUCT_CRAWLER_PATH . 'assets/js/crawler-admin.js' ) : WP_PRODUCT_CRAWLER_VERSION;

		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'wp-product-crawler-admin', WP_PRODUCT_CRAWLER_URL . 'assets/css/crawler-admin.css', array( 'dashicons' ), $ver_css );
		wp_enqueue_script( 'wp-product-crawler-admin', WP_PRODUCT_CRAWLER_URL . 'assets/js/crawler-admin.js', array( 'jquery' ), $ver_js, true );

		wp_localize_script( 'wp-product-crawler-admin', 'dtCrawlerData', array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'dt_crawler_action' ),
			'categories' => WP_Category_Mapper::get_local_categories(),
			'i18n'       => array(
				'scanning'           => __( 'Đang quét website...', 'wp-product-crawler' ),
				'extracting'         => __( 'Đang bóc tách dữ liệu sản phẩm...', 'wp-product-crawler' ),
				'importing'          => __( 'Đang nhập sản phẩm và tải media...', 'wp-product-crawler' ),
				'completed'          => __( 'Hoàn thành!', 'wp-product-crawler' ),
				'error'              => __( 'Có lỗi xảy ra', 'wp-product-crawler' ),
				'confirm_import'     => __( 'Bạn có chắc chắn muốn nhập các sản phẩm đã chọn?', 'wp-product-crawler' ),
				'no_items_selected'  => __( 'Vui lòng chọn ít nhất một sản phẩm để nhập.', 'wp-product-crawler' ),
				'new_cat_prompt'     => __( 'Nhập tên danh mục sản phẩm mới:', 'wp-product-crawler' ),
				'paused'             => __( 'Đang tạm dừng', 'wp-product-crawler' ),
				'resumed'            => __( 'Đang tiếp tục...', 'wp-product-crawler' ),
				'cancelled'          => __( 'Đã hủy bỏ', 'wp-product-crawler' ),
				'confirm_cancel'          => __( 'Bạn có chắc chắn muốn hủy bỏ tiến trình nhập? Những sản phẩm đã nhập thành công sẽ được giữ nguyên trên website, các sản phẩm còn lại sẽ bị bỏ qua.', 'wp-product-crawler' ),
				'confirm_discard'         => __( 'Bạn có chắc chắn muốn bỏ qua phiên làm việc dở dang này và quét mới không?', 'wp-product-crawler' ),
				'confirm_delete'          => __( 'Bạn có chắc chắn muốn xóa sản phẩm này khỏi danh sách không?', 'wp-product-crawler' ),
				'confirm_bulk_delete'     => __( 'Bạn có chắc chắn muốn xóa các sản phẩm đã chọn khỏi danh sách không?', 'wp-product-crawler' ),
				'no_items_selected_delete'=> __( 'Vui lòng chọn ít nhất một sản phẩm để xóa.', 'wp-product-crawler' ),
			),
		) );
	}

	/* =========================================================================
	 * AJAX HANDLERS
	 * ========================================================================= */

	public function ajax_start_scan(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		if ( empty( $url ) ) {
			wp_send_json_error( array( 'message' => __( 'Vui lòng nhập URL hợp lệ.', 'wp-product-crawler' ) ) );
		}

		// Kiểm tra SSRF
		$valid = WP_Crawler_Security::validate_url( $url );
		if ( is_wp_error( $valid ) ) {
			wp_send_json_error( array( 'message' => $valid->get_error_message() ) );
		}

		$url_type    = WP_Crawler_Engine::detect_url_type( $url );
		$import_mode = isset( $_POST['import_mode'] ) ? sanitize_key( $_POST['import_mode'] ) : 'create_update';
		$settings    = WP_Crawler_Settings::get_instance()->get_all();

		$job_mgr = WP_Crawler_Job_Manager::get_instance();
		$job_id  = $job_mgr->create_job( $url, $url_type, $import_mode, $settings );

		wp_send_json_success( array(
			'job_id'   => $job_id,
			'url'      => $url,
			'url_type' => $url_type,
		) );
	}

	public function ajax_discover_urls(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
		$job    = WP_Crawler_Job_Manager::get_instance()->get_job( $job_id );
		if ( ! $job ) {
			wp_send_json_error( array( 'message' => __( 'Không tìm thấy phiên quét.', 'wp-product-crawler' ) ) );
		}

		$settings     = WP_Crawler_Settings::get_instance()->get_all();
		$max_products = (int) ( $settings['max_products'] ?? 50 );
		$max_depth    = (int) ( $settings['max_crawl_depth'] ?? 3 );

		$engine  = new WP_Crawler_Engine();
		$job_mgr = WP_Crawler_Job_Manager::get_instance();

		$urls = $engine->discover_product_urls( $job->source_url, $job_id, $max_products, $max_depth );

		if ( empty( $urls ) ) {
			$job_mgr->log( $job_id, 'WARN', __( 'Không tìm thấy liên kết sản phẩm nào trên trang web này.', 'wp-product-crawler' ) );
			$job_mgr->update_job( $job_id, array( 'status' => 'failed', 'products_found' => 0 ) );
			wp_send_json_error( array( 'message' => __( 'Không tìm thấy liên kết sản phẩm nào từ URL đã nhập. Vui lòng kiểm tra lại cấu trúc website.', 'wp-product-crawler' ) ) );
		}

		$item_ids = array();
		foreach ( $urls as $u ) {
			$iid = $job_mgr->add_item( $job_id, $u, 'discovered' );
			$item_ids[] = array( 'item_id' => $iid, 'url' => $u );
		}

		$job_mgr->update_job( $job_id, array(
			'products_found' => count( $urls ),
			'status'         => 'discovered',
		) );

		wp_send_json_success( array(
			'job_id' => $job_id,
			'total'  => count( $urls ),
			'items'  => $item_ids,
		) );
	}

	public function ajax_extract_item(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$item_id = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
		$job_mgr = WP_Crawler_Job_Manager::get_instance();
		$item    = $job_mgr->get_item( $item_id );

		if ( ! $item ) {
			wp_send_json_error( array( 'message' => __( 'Không tìm thấy mục sản phẩm.', 'wp-product-crawler' ) ) );
		}

		$client = WP_Crawler_Client::get_instance();
		$res    = $client->get( $item->source_url );

		if ( is_wp_error( $res ) || 200 !== $res['code'] ) {
			$err_msg = is_wp_error( $res ) ? $res->get_error_message() : sprintf( __( 'Lỗi HTTP %d', 'wp-product-crawler' ), $res['code'] );
			$job_mgr->update_item( $item_id, array(
				'status'        => 'failed',
				'error_message' => $err_msg,
			) );
			$job_mgr->log( (int) $item->job_id, 'ERROR', sprintf( __( 'Lỗi tải trang %s: %s', 'wp-product-crawler' ), $item->source_url, $err_msg ) );

			wp_send_json_error( array( 'item_id' => $item_id, 'message' => $err_msg ) );
		}

		// Bóc tách dữ liệu
		$product = WP_Product_Extractor::extract( $res['body'], $item->source_url );

		// Kiểm tra trùng lặp
		$dup_info = WP_Duplicate_Detector::check( $product );

		// Gợi ý danh mục
		$cat_suggest = WP_Category_Mapper::suggest_mapping( $product['category'] ?? '' );
		$mapped_cat  = $cat_suggest['term_id'] ?: 0;

		$status = ( 'ERROR' === $dup_info['status'] ) ? 'failed' : 'extracted';

		$job_mgr->update_item( $item_id, array(
			'product_name'       => $product['name'],
			'sku'                => $product['sku'],
			'price'              => $product['price'],
			'status'             => $status,
			'raw_data_json'      => wp_json_encode( $product ),
			'mapped_category_id' => $mapped_cat,
			'error_message'      => ( 'ERROR' === $dup_info['status'] ) ? $dup_info['reason'] : null,
		) );

		$job_mgr->log( (int) $item->job_id, 'INFO', sprintf(
			__( 'Đã bóc tách: %s | SKU: %s | Trạng thái: %s | %d ảnh | %d thông số', 'wp-product-crawler' ),
			$product['name'] ?: 'Chưa rõ tên',
			$product['sku'] ?: '—',
			$dup_info['status'],
			( ! empty( $product['main_image'] ) ? 1 : 0 ) + count( $product['gallery_images'] ),
			count( $product['specifications'] )
		) );

		wp_send_json_success( array(
			'item_id'          => $item_id,
			'product'          => $product,
			'duplicate_status' => $dup_info['status'],
			'duplicate_reason' => $dup_info['reason'],
			'category_suggest' => $cat_suggest,
			'categories'       => WP_Category_Mapper::get_local_categories(),
		) );
	}

	public function ajax_save_preview_item(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$item_id = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
		$job_mgr = WP_Crawler_Job_Manager::get_instance();
		$item    = $job_mgr->get_item( $item_id );

		if ( ! $item ) {
			wp_send_json_error( array( 'message' => __( 'Không tìm thấy mục sản phẩm.', 'wp-product-crawler' ) ) );
		}

		$raw = json_decode( (string) $item->raw_data_json, true ) ?: array();

		if ( isset( $_POST['name'] ) ) {
			$raw['name'] = sanitize_text_field( wp_unslash( $_POST['name'] ) );
		}
		if ( isset( $_POST['sku'] ) ) {
			$raw['sku']   = sanitize_text_field( wp_unslash( $_POST['sku'] ) );
			$raw['model'] = $raw['sku'];
		}
		if ( isset( $_POST['price'] ) ) {
			$raw['price'] = (float) sanitize_text_field( wp_unslash( $_POST['price'] ) );
		}
		if ( isset( $_POST['short_description'] ) ) {
			$raw['short_description'] = sanitize_textarea_field( wp_unslash( $_POST['short_description'] ) );
		}
		if ( isset( $_POST['description'] ) ) {
			$raw['description'] = WP_Crawler_Security::sanitize_html_content( wp_unslash( $_POST['description'] ) );
		}
		if ( isset( $_POST['category_id'] ) ) {
			$cat_id = absint( $_POST['category_id'] );
		} else {
			$cat_id = (int) $item->mapped_category_id;
		}

		// Cập nhật lại Duplicate Check
		$dup_info = WP_Duplicate_Detector::check( $raw );

		$job_mgr->update_item( $item_id, array(
			'product_name'       => $raw['name'],
			'sku'                => $raw['sku'],
			'price'              => $raw['price'],
			'raw_data_json'      => wp_json_encode( $raw ),
			'mapped_category_id' => $cat_id,
		) );

		wp_send_json_success( array(
			'item_id'          => $item_id,
			'duplicate_status' => $dup_info['status'],
			'duplicate_reason' => $dup_info['reason'],
		) );
	}

	public function ajax_import_item(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$item_id     = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
		$import_mode = isset( $_POST['import_mode'] ) ? sanitize_key( $_POST['import_mode'] ) : 'create_update';
		$post_status = isset( $_POST['post_status'] ) && 'publish' === $_POST['post_status'] ? 'publish' : 'draft';

		$job_mgr = WP_Crawler_Job_Manager::get_instance();
		$item    = $job_mgr->get_item( $item_id );

		if ( ! $item ) {
			wp_send_json_error( array( 'message' => __( 'Không tìm thấy mục sản phẩm.', 'wp-product-crawler' ) ) );
		}

		$product = json_decode( (string) $item->raw_data_json, true );
		if ( empty( $product ) ) {
			wp_send_json_error( array( 'message' => __( 'Dữ liệu sản phẩm không hợp lệ.', 'wp-product-crawler' ) ) );
		}

		$options = array(
			'import_mode'        => $import_mode,
			'status'             => $post_status,
			'mapped_category_id' => (int) $item->mapped_category_id,
			'job_id'             => (int) $item->job_id,
		);

		$result = WP_Product_Importer::import( $product, $options );

		if ( ! $result['success'] ) {
			$job_mgr->update_item( $item_id, array(
				'status'        => 'failed',
				'error_message' => $result['error'],
			) );
			$job_mgr->log( (int) $item->job_id, 'ERROR', sprintf( __( 'Lỗi nhập sản phẩm "%s": %s', 'wp-product-crawler' ), $product['name'], $result['error'] ) );

			wp_send_json_error( array( 'message' => $result['error'] ) );
		}

		$new_status = 'imported';
		if ( 'updated' === $result['action'] ) {
			$new_status = 'updated';
		} elseif ( 'skipped' === $result['action'] ) {
			$new_status = 'duplicate';
		}

		$job_mgr->update_item( $item_id, array(
			'status'  => $new_status,
			'post_id' => $result['post_id'],
		) );

		// Cập nhật thống kê Job
		$job = $job_mgr->get_job( (int) $item->job_id );
		if ( $job ) {
			$imp_count = (int) $job->products_imported;
			$upd_count = (int) $job->products_updated;
			$skp_count = (int) $job->products_skipped;

			if ( 'created' === $result['action'] ) {
				++$imp_count;
			} elseif ( 'updated' === $result['action'] ) {
				++$upd_count;
			} elseif ( 'skipped' === $result['action'] ) {
				++$skp_count;
			}

			$job_mgr->update_job( (int) $item->job_id, array(
				'products_imported' => $imp_count,
				'products_updated'  => $upd_count,
				'products_skipped'  => $skp_count,
			) );
		}

		$job_mgr->log( (int) $item->job_id, 'INFO', $result['message'] );

		wp_send_json_success( array(
			'item_id' => $item_id,
			'action'  => $result['action'],
			'post_id' => $result['post_id'],
			'message' => $result['message'],
		) );
	}

	public function ajax_retry_item(): void {
		$this->ajax_extract_item();
	}

	public function ajax_create_category(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$name   = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$cat_id = WP_Category_Mapper::create_local_category( $name );

		if ( is_wp_error( $cat_id ) ) {
			wp_send_json_error( array( 'message' => $cat_id->get_error_message() ) );
		}

		wp_send_json_success( array(
			'id'   => $cat_id,
			'name' => $name,
		) );
	}

	public function ajax_save_settings(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$data = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
		WP_Crawler_Settings::get_instance()->update( $data );

		wp_send_json_success( array( 'message' => __( 'Đã lưu cấu hình crawler thành công.', 'wp-product-crawler' ) ) );
	}

	public function ajax_get_logs(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
		$logs   = WP_Crawler_Job_Manager::get_instance()->get_job_logs( $job_id );

		wp_send_json_success( array( 'logs' => $logs ) );
	}

	public function ajax_rerun_job(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
		$job    = WP_Crawler_Job_Manager::get_instance()->get_job( $job_id );

		if ( ! $job ) {
			wp_send_json_error( array( 'message' => __( 'Không tìm thấy Job cần chạy lại.', 'wp-product-crawler' ) ) );
		}

		// Tạo Job mới dựa trên cấu hình Job cũ
		$settings = json_decode( (string) $job->settings_json, true ) ?: WP_Crawler_Settings::get_instance()->get_all();
		$new_job_id = WP_Crawler_Job_Manager::get_instance()->create_job( $job->source_url, $job->url_type, $job->import_mode, $settings );

		wp_send_json_success( array(
			'job_id'   => $new_job_id,
			'url'      => $job->source_url,
			'url_type' => $job->url_type,
		) );
	}

	public function ajax_get_active_session(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$job_mgr = WP_Crawler_Job_Manager::get_instance();
		$session = $job_mgr->get_active_session();

		if ( ! $session ) {
			wp_send_json_success( array( 'has_session' => false ) );
		}

		$job   = $session['job'];
		$items = array();

		foreach ( $session['items'] as $it ) {
			$raw = json_decode( (string) $it->raw_data_json, true ) ?: array();
			$cat_suggest = WP_Category_Mapper::suggest_mapping( $raw['category'] ?? '' );

			$dup_status = 'NEW';
			$dup_reason = '';
			if ( ! empty( $raw ) ) {
				$dup_info   = WP_Duplicate_Detector::check( $raw );
				$dup_status = $dup_info['status'];
				$dup_reason = $dup_info['reason'];
			}

			$items[] = array(
				'id'                 => (int) $it->id,
				'product_name'       => $it->product_name ?: ( $raw['name'] ?? '' ),
				'sku'                => $it->sku ?: ( $raw['sku'] ?? '' ),
				'price'              => null !== $it->price ? (float) $it->price : ( $raw['price'] ?? null ),
				'status'             => $it->status,
				'post_id'            => (int) $it->post_id,
				'source_url'         => $it->source_url,
				'mapped_category_id' => (int) $it->mapped_category_id,
				'product'            => $raw,
				'duplicate_status'   => $dup_status,
				'duplicate_reason'   => $dup_reason,
				'category_suggest'   => $cat_suggest,
			);
		}

		wp_send_json_success( array(
			'has_session' => true,
			'job'         => $job,
			'stats'       => $session['stats'],
			'items'       => $items,
			'categories'  => WP_Category_Mapper::get_local_categories(),
		) );
	}

	public function ajax_pause_job(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
		$res    = WP_Crawler_Job_Manager::get_instance()->pause_job( $job_id );

		wp_send_json_success( array( 'success' => $res, 'job_id' => $job_id ) );
	}

	public function ajax_resume_job(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
		$res    = WP_Crawler_Job_Manager::get_instance()->resume_job( $job_id );

		wp_send_json_success( array( 'success' => $res, 'job_id' => $job_id ) );
	}

	public function ajax_cancel_job(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
		$res    = WP_Crawler_Job_Manager::get_instance()->cancel_job( $job_id );

		wp_send_json_success( array(
			'success' => $res,
			'job_id'  => $job_id,
			'message' => __( 'Đã hủy phiên làm việc. Các sản phẩm đã nhập trước đó được giữ nguyên trên website.', 'wp-product-crawler' ),
		) );
	}

	public function ajax_skip_item(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$item_id = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
		$res     = WP_Crawler_Job_Manager::get_instance()->skip_item( $item_id );

		wp_send_json_success( array( 'success' => $res, 'item_id' => $item_id ) );
	}

	public function ajax_delete_item(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$item_id     = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
		$delete_post = ! empty( $_POST['delete_post'] );
		$res         = WP_Crawler_Job_Manager::get_instance()->delete_item( $item_id, $delete_post );

		if ( $res ) {
			wp_send_json_success( array(
				'success' => true,
				'item_id' => $item_id,
				'message' => __( 'Đã xóa sản phẩm khỏi danh sách thành công.', 'wp-product-crawler' ),
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Không tìm thấy sản phẩm hoặc không thể xóa.', 'wp-product-crawler' ) ) );
		}
	}

	public function ajax_delete_items(): void {
		$check = WP_Crawler_Security::verify_admin_request();
		if ( is_wp_error( $check ) ) {
			wp_send_json_error( array( 'message' => $check->get_error_message() ) );
		}

		$item_ids    = isset( $_POST['item_ids'] ) && is_array( $_POST['item_ids'] ) ? array_map( 'absint', $_POST['item_ids'] ) : array();
		$delete_post = ! empty( $_POST['delete_post'] );

		if ( empty( $item_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'Không có sản phẩm nào được chọn để xóa.', 'wp-product-crawler' ) ) );
		}

		$deleted = WP_Crawler_Job_Manager::get_instance()->delete_items( $item_ids, $delete_post );

		wp_send_json_success( array(
			'success'       => true,
			'deleted_count' => $deleted,
			'message'       => sprintf( __( 'Đã xóa %d sản phẩm khỏi danh sách thành công.', 'wp-product-crawler' ), $deleted ),
		) );
	}

	/* =========================================================================
	 * VIEW RENDERING
	 * ========================================================================= */

	public function render_import_page(): void {
		$settings   = WP_Crawler_Settings::get_instance()->get_all();
		$categories = WP_Category_Mapper::get_local_categories();
		?>
		<div class="wrap dt-crawler-wrap">
			<header class="dt-crawler-header">
				<div class="dt-crawler-title-area">
					<h1><?php esc_html_e( 'Nhập sản phẩm từ Website (Product Website Crawler)', 'wp-product-crawler' ); ?></h1>
					<p class="description"><?php esc_html_e( 'Tự động quét, bóc tách dữ liệu có cấu trúc (JSON-LD, Microdata, Specs), xem trước và nhập vào danh mục Mozlex.', 'wp-product-crawler' ); ?></p>
				</div>
				<div class="dt-crawler-header-actions">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=dt-crawler-history' ) ); ?>" class="button"><span class="dashicons dashicons-backup"></span> <?php esc_html_e( 'Xem lịch sử nhập', 'wp-product-crawler' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=dt-crawler-settings' ) ); ?>" class="button"><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Cấu hình', 'wp-product-crawler' ); ?></a>
				</div>
			</header>

			<!-- KHÔI PHỤC PHIÊN LÀM VIỆC DỞ DANG (CRASH RECOVERY) -->
			<div id="dt-recovery-banner" class="dt-crawler-card dt-recovery-banner" style="display:none;">
				<div class="dt-recovery-content">
					<div class="dt-recovery-icon">
						<span class="dashicons dashicons-backup"></span>
					</div>
					<div class="dt-recovery-info">
						<h3 class="dt-recovery-title"><?php esc_html_e( 'Phát hiện phiên làm việc chưa hoàn tất', 'wp-product-crawler' ); ?></h3>
						<p class="dt-recovery-desc" id="dt-recovery-desc"></p>
					</div>
					<div class="dt-recovery-actions">
						<button type="button" id="dt-recovery-resume-btn" class="button button-primary dt-btn-restore">
							<span class="dashicons dashicons-controls-play"></span> <?php esc_html_e( 'Khôi phục & Tiếp tục làm', 'wp-product-crawler' ); ?>
						</button>
						<button type="button" id="dt-recovery-discard-btn" class="button button-secondary dt-btn-discard">
							<span class="dashicons dashicons-trash"></span> <?php esc_html_e( 'Bỏ qua phiên này', 'wp-product-crawler' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- 1. HỘP NHẬP URL -->
			<div class="dt-crawler-card dt-crawler-scanner-box">
				<div class="dt-crawler-input-group">
					<label for="dt-crawler-url"><strong><?php esc_html_e( 'Địa chỉ Website nguồn (URL)', 'wp-product-crawler' ); ?></strong></label>
					<div class="dt-crawler-field-row">
						<input type="url" id="dt-crawler-url" class="regular-text dt-url-input" placeholder="https://example.com/products hoặc https://example.com/sitemap.xml" required>
						<button type="button" id="dt-crawler-scan-btn" class="button button-primary button-hero">
							<span class="dashicons dashicons-search"></span> <?php esc_html_e( 'Scan Website', 'wp-product-crawler' ); ?>
						</button>
					</div>
					<div class="dt-crawler-hints">
						<span class="dt-pill dt-pill-info"><?php esc_html_e( 'Hỗ trợ:', 'wp-product-crawler' ); ?></span>
						<span class="dt-hint-item"><?php esc_html_e( 'Trang chủ', 'wp-product-crawler' ); ?></span> • 
						<span class="dt-hint-item"><?php esc_html_e( 'Danh mục sản phẩm', 'wp-product-crawler' ); ?></span> • 
						<span class="dt-hint-item"><?php esc_html_e( 'Trang danh sách', 'wp-product-crawler' ); ?></span> • 
						<span class="dt-hint-item"><?php esc_html_e( 'Trang chi tiết SP', 'wp-product-crawler' ); ?></span> • 
						<span class="dt-hint-item"><?php esc_html_e( 'Sitemap XML', 'wp-product-crawler' ); ?></span>
					</div>
				</div>

				<div id="dt-url-type-detected" class="dt-detected-badge" style="display:none;">
					<?php esc_html_e( 'Nhận diện loại URL:', 'wp-product-crawler' ); ?> <strong id="dt-url-type-text">—</strong>
				</div>
			</div>

			<!-- 2. BẢNG TIẾN TRÌNH THỜI GIAN THỰC (PROGRESS) -->
			<div id="dt-crawler-progress-card" class="dt-crawler-card" style="display:none;">
				<div class="dt-progress-header">
					<h3 id="dt-progress-status-title"><?php esc_html_e( 'Đang quét website...', 'wp-product-crawler' ); ?></h3>
					<span id="dt-progress-percent" class="dt-progress-badge">0%</span>
				</div>
				<div class="dt-progress-bar-wrap">
					<div id="dt-progress-bar" class="dt-progress-bar-fill" style="width: 0%;"></div>
				</div>
				<div class="dt-stats-grid">
					<div class="dt-stat-item">
						<span class="dt-stat-label"><?php esc_html_e( 'Liên kết tìm thấy', 'wp-product-crawler' ); ?></span>
						<strong id="dt-stat-found" class="dt-stat-value">0</strong>
					</div>
					<div class="dt-stat-item">
						<span class="dt-stat-label"><?php esc_html_e( 'Đã bóc tách', 'wp-product-crawler' ); ?></span>
						<strong id="dt-stat-processed" class="dt-stat-value">0</strong>
					</div>
					<div class="dt-stat-item">
						<span class="dt-stat-label"><?php esc_html_e( 'Ảnh tải về', 'wp-product-crawler' ); ?></span>
						<strong id="dt-stat-images" class="dt-stat-value">0</strong>
					</div>
					<div class="dt-stat-item">
						<span class="dt-stat-label"><?php esc_html_e( 'Lỗi', 'wp-product-crawler' ); ?></span>
						<strong id="dt-stat-errors" class="dt-stat-value dt-val-error">0</strong>
					</div>
				</div>

				<!-- CỤM NÚT ĐIỀU KHIỂN TIẾN TRÌNH (PROCESS CONTROLS) -->
				<div class="dt-progress-controls-toolbar" id="dt-progress-controls" style="display:none;">
					<button type="button" id="dt-control-import-top-btn" class="button button-primary dt-btn-import">
						<span class="dashicons dashicons-database-import"></span> <?php esc_html_e( 'Nhập vào website ngay', 'wp-product-crawler' ); ?>
					</button>
					<button type="button" id="dt-control-pause-btn" class="button dt-btn-pause">
						<span class="dashicons dashicons-controls-pause"></span> <?php esc_html_e( 'Tạm dừng', 'wp-product-crawler' ); ?>
					</button>
					<button type="button" id="dt-control-resume-btn" class="button button-primary dt-btn-resume" style="display:none;">
						<span class="dashicons dashicons-controls-play"></span> <?php esc_html_e( 'Tiếp tục làm tiếp', 'wp-product-crawler' ); ?>
					</button>
					<button type="button" id="dt-control-skip-btn" class="button dt-btn-skip">
						<span class="dashicons dashicons-controls-skipforward"></span> <?php esc_html_e( 'Bỏ qua sản phẩm này', 'wp-product-crawler' ); ?>
					</button>
					<button type="button" id="dt-control-cancel-btn" class="button dt-btn-cancel">
						<span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Hủy bỏ tiến trình', 'wp-product-crawler' ); ?>
					</button>
				</div>

				<div id="dt-error-notice-box" class="dt-error-box" style="display:none;">
					<h4><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Các sản phẩm gặp sự cố khi cào dữ liệu:', 'wp-product-crawler' ); ?></h4>
					<ul id="dt-error-list"></ul>
				</div>
			</div>

			<!-- 3. MÀN HÌNH PREVIEW & CATEGORY MAPPING -->
			<div id="dt-crawler-preview-section" class="dt-crawler-card" style="display:none;">
				<div class="dt-preview-topbar">
					<div>
						<h2><?php esc_html_e( 'Product Import Preview', 'wp-product-crawler' ); ?></h2>
						<p class="description"><?php esc_html_e( 'Xem trước danh sách sản phẩm khám phá được. Bạn có thể chọn/bỏ chọn, sửa thông tin trước khi lưu vào kho dữ liệu.', 'wp-product-crawler' ); ?></p>
					</div>
					<div class="dt-preview-actions" style="display:flex; align-items:center; gap:12px;">
						<label style="margin:0;"><input type="checkbox" id="dt-select-all" checked> <strong><?php esc_html_e( 'Chọn tất cả', 'wp-product-crawler' ); ?></strong></label>
						<button type="button" id="dt-bulk-delete-btn" class="button button-secondary" style="color:#b32d2e; display:inline-flex; align-items:center; gap:4px;" title="<?php esc_attr_e( 'Xóa các sản phẩm đã đánh dấu khỏi danh sách', 'wp-product-crawler' ); ?>">
							<span class="dashicons dashicons-trash" style="font-size:16px; width:16px; height:16px;"></span> <?php esc_html_e( 'Xóa mục đã chọn', 'wp-product-crawler' ); ?>
						</button>
					</div>
				</div>

				<!-- Bảng Preview Sản Phẩm -->
				<div class="dt-table-responsive">
					<table class="wp-list-table widefat fixed striped dt-preview-table">
						<thead>
							<tr>
								<th style="width: 40px;"><input type="checkbox" id="dt-select-all-header" checked></th>
								<th style="width: 70px;"><?php esc_html_e( 'Ảnh', 'wp-product-crawler' ); ?></th>
								<th><?php esc_html_e( 'Tên sản phẩm / Model', 'wp-product-crawler' ); ?></th>
								<th style="width: 130px;"><?php esc_html_e( 'Giá bán', 'wp-product-crawler' ); ?></th>
								<th style="width: 220px;"><?php esc_html_e( 'Danh mục gán', 'wp-product-crawler' ); ?></th>
								<th style="width: 100px;"><?php esc_html_e( 'Trạng thái', 'wp-product-crawler' ); ?></th>
								<th style="width: 100px;"><?php esc_html_e( 'Dữ liệu', 'wp-product-crawler' ); ?></th>
								<th style="width: 130px; text-align:center;"><?php esc_html_e( 'Thao tác', 'wp-product-crawler' ); ?></th>
							</tr>
						</thead>
						<tbody id="dt-preview-tbody">
							<!-- Dynamic JavaScript Rows -->
						</tbody>
					</table>
				</div>

				<!-- Thanh điều khiển Import -->
				<div class="dt-import-controls-card">
					<div class="dt-import-options-grid">
						<div class="dt-control-col">
							<label for="dt-import-mode"><strong><?php esc_html_e( 'Chế độ nhập (Import Mode):', 'wp-product-crawler' ); ?></strong></label>
							<select id="dt-import-mode" class="widefat">
								<option value="create_update" <?php selected( $settings['default_import_mode'], 'create_update' ); ?>><?php esc_html_e( 'Tạo mới + Cập nhật (Create + Update)', 'wp-product-crawler' ); ?></option>
								<option value="create_only" <?php selected( $settings['default_import_mode'], 'create_only' ); ?>><?php esc_html_e( 'Chỉ tạo mới (Bỏ qua nếu đã có)', 'wp-product-crawler' ); ?></option>
								<option value="update_existing" <?php selected( $settings['default_import_mode'], 'update_existing' ); ?>><?php esc_html_e( 'Chỉ cập nhật sản phẩm đã tồn tại', 'wp-product-crawler' ); ?></option>
							</select>
						</div>
						<div class="dt-control-col">
							<label for="dt-import-status"><strong><?php esc_html_e( 'Trạng thái khi nhập:', 'wp-product-crawler' ); ?></strong></label>
							<select id="dt-import-status" class="widefat">
								<option value="publish" <?php selected( $settings['default_product_status'], 'publish' ); ?>><?php esc_html_e( 'Đang hiển thị (Publish - Tự động)', 'wp-product-crawler' ); ?></option>
								<option value="draft" <?php selected( $settings['default_product_status'], 'draft' ); ?>><?php esc_html_e( 'Bản nháp (Draft)', 'wp-product-crawler' ); ?></option>
							</select>
						</div>
						<div class="dt-control-col dt-btn-col">
							<button type="button" id="dt-start-import-btn" class="button button-primary button-hero">
								<span class="dashicons dashicons-database-import"></span> <?php esc_html_e( 'Import Selected Products', 'wp-product-crawler' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- MODAL CHỈNH SỬA THÔNG TIN SẢN PHẨM TRƯỚC KHI IMPORT -->
			<div id="dt-edit-modal" class="dt-modal" style="display:none;">
				<div class="dt-modal-content">
					<div class="dt-modal-header">
						<h3><?php esc_html_e( 'Chỉnh sửa sản phẩm trước khi nhập', 'wp-product-crawler' ); ?></h3>
						<button type="button" class="dt-modal-close">×</button>
					</div>
					<div class="dt-modal-body">
						<input type="hidden" id="dt-modal-item-id">
						<p>
							<label for="dt-modal-name"><strong><?php esc_html_e( 'Tên sản phẩm:', 'wp-product-crawler' ); ?></strong></label>
							<input type="text" id="dt-modal-name" class="widefat">
						</p>
						<div class="dt-grid-2">
							<p>
								<label for="dt-modal-sku"><strong><?php esc_html_e( 'Model / Mã SP:', 'wp-product-crawler' ); ?></strong></label>
								<input type="text" id="dt-modal-sku" class="widefat">
							</p>
							<p>
								<label for="dt-modal-price"><strong><?php esc_html_e( 'Giá bán (VNĐ):', 'wp-product-crawler' ); ?></strong></label>
								<input type="number" id="dt-modal-price" class="widefat" step="1000">
							</p>
						</div>
						<p>
							<label for="dt-modal-category"><strong><?php esc_html_e( 'Danh mục sản phẩm Mozlex:', 'wp-product-crawler' ); ?></strong></label>
							<select id="dt-modal-category" class="widefat">
								<option value="0"><?php esc_html_e( '— Chưa phân loại —', 'wp-product-crawler' ); ?></option>
								<?php foreach ( $categories as $c ) : ?>
									<option value="<?php echo esc_attr( $c['id'] ); ?>"><?php echo esc_html( $c['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<p>
							<label for="dt-modal-short-desc"><strong><?php esc_html_e( 'Mô tả ngắn (Short Description):', 'wp-product-crawler' ); ?></strong></label>
							<textarea id="dt-modal-short-desc" class="widefat" rows="2" placeholder="<?php esc_attr_e( 'Tóm tắt đặc điểm nổi bật của sản phẩm...', 'wp-product-crawler' ); ?>"></textarea>
						</p>
						<p>
							<label for="dt-modal-desc"><strong><?php esc_html_e( 'Mô tả chi tiết sản phẩm (Full Description):', 'wp-product-crawler' ); ?></strong></label>
							<textarea id="dt-modal-desc" class="widefat" rows="7" style="font-size:12px; line-height:1.4;" placeholder="<?php esc_attr_e( 'Nội dung chi tiết, tính năng, đặc điểm sản phẩm...', 'wp-product-crawler' ); ?>"></textarea>
						</p>
						<div>
							<strong><?php esc_html_e( 'Thông số kỹ thuật trích xuất được:', 'wp-product-crawler' ); ?></strong>
							<div id="dt-modal-specs-preview" class="dt-specs-box"></div>
						</div>
					</div>
					<div class="dt-modal-footer">
						<button type="button" class="button dt-modal-close"><?php esc_html_e( 'Đóng', 'wp-product-crawler' ); ?></button>
						<button type="button" id="dt-modal-save-btn" class="button button-primary"><?php esc_html_e( 'Lưu thay đổi', 'wp-product-crawler' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_history_page(): void {
		$job_mgr = WP_Crawler_Job_Manager::get_instance();
		$jobs    = $job_mgr->get_jobs( 50 );
		?>
		<div class="wrap dt-crawler-wrap">
			<h1><?php esc_html_e( 'Lịch sử Import sản phẩm (Import History)', 'wp-product-crawler' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Theo dõi các phiên quét, kết quả nhập và chạy lại (Re-import) các nguồn website đã lưu.', 'wp-product-crawler' ); ?></p>

			<div class="dt-crawler-card">
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 70px;"><?php esc_html_e( 'Job ID', 'wp-product-crawler' ); ?></th>
							<th><?php esc_html_e( 'URL nguồn', 'wp-product-crawler' ); ?></th>
							<th style="width: 100px;"><?php esc_html_e( 'Loại URL', 'wp-product-crawler' ); ?></th>
							<th style="width: 140px;"><?php esc_html_e( 'Thời gian', 'wp-product-crawler' ); ?></th>
							<th style="width: 80px;"><?php esc_html_e( 'Tìm thấy', 'wp-product-crawler' ); ?></th>
							<th style="width: 80px;"><?php esc_html_e( 'Đã nhập', 'wp-product-crawler' ); ?></th>
							<th style="width: 80px;"><?php esc_html_e( 'Cập nhật', 'wp-product-crawler' ); ?></th>
							<th style="width: 80px;"><?php esc_html_e( 'Bỏ qua', 'wp-product-crawler' ); ?></th>
							<th style="width: 80px;"><?php esc_html_e( 'Lỗi', 'wp-product-crawler' ); ?></th>
							<th style="width: 110px;"><?php esc_html_e( 'Trạng thái', 'wp-product-crawler' ); ?></th>
							<th style="width: 160px;"><?php esc_html_e( 'Thao tác', 'wp-product-crawler' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $jobs ) ) : ?>
							<tr><td colspan="11" style="text-align:center; padding: 20px;"><?php esc_html_e( 'Chưa có phiên quét nào được thực hiện.', 'wp-product-crawler' ); ?></td></tr>
						<?php else : ?>
							<?php foreach ( $jobs as $j ) : ?>
								<tr>
									<td><strong>#<?php echo esc_html( $j->id ); ?></strong></td>
									<td><a href="<?php echo esc_url( $j->source_url ); ?>" target="_blank"><?php echo esc_html( $j->source_url ); ?></a></td>
									<td><span class="dt-pill"><?php echo esc_html( strtoupper( $j->url_type ) ); ?></span></td>
									<td><?php echo esc_html( $j->created_at ); ?></td>
									<td><strong><?php echo esc_html( $j->products_found ); ?></strong></td>
									<td><span style="color: green; font-weight:bold;"><?php echo esc_html( $j->products_imported ); ?></span></td>
									<td><span style="color: #2271b1; font-weight:bold;"><?php echo esc_html( $j->products_updated ); ?></span></td>
									<td><span style="color: #646970;"><?php echo esc_html( $j->products_skipped ); ?></span></td>
									<td><span style="color: <?php echo $j->errors_count > 0 ? '#d63638' : '#50575e'; ?>; font-weight:bold;"><?php echo esc_html( $j->errors_count ); ?></span></td>
									<td>
										<span class="dt-status-badge dt-status-<?php echo esc_attr( $j->status ); ?>">
											<?php echo esc_html( strtoupper( $j->status ) ); ?>
										</span>
									</td>
									<td>
										<button type="button" class="button button-small dt-view-logs-btn" data-job-id="<?php echo esc_attr( $j->id ); ?>"><?php esc_html_e( 'Xem Logs', 'wp-product-crawler' ); ?></button>
										<button type="button" class="button button-small dt-rerun-job-btn" data-job-id="<?php echo esc_attr( $j->id ); ?>"><?php esc_html_e( 'Chạy lại', 'wp-product-crawler' ); ?></button>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<!-- LOGS MODAL -->
			<div id="dt-logs-modal" class="dt-modal" style="display:none;">
				<div class="dt-modal-content dt-modal-large">
					<div class="dt-modal-header">
						<h3 id="dt-logs-modal-title"><?php esc_html_e( 'Nhật ký phiên cào (Crawl Logs)', 'wp-product-crawler' ); ?></h3>
						<button type="button" class="dt-modal-close">×</button>
					</div>
					<div class="dt-modal-body">
						<div id="dt-logs-container" class="dt-logs-console"></div>
					</div>
					<div class="dt-modal-footer">
						<button type="button" class="button dt-modal-close"><?php esc_html_e( 'Đóng', 'wp-product-crawler' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_settings_page(): void {
		$settings = WP_Crawler_Settings::get_instance()->get_all();
		?>
		<div class="wrap dt-crawler-wrap">
			<h1><?php esc_html_e( 'Cấu hình Bộ cào sản phẩm (Crawler Settings)', 'wp-product-crawler' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Thiết lập các tham số giới hạn tải, bảo vệ máy chủ, quy tắc bóc tách và quyền hạn nhập dữ liệu.', 'wp-product-crawler' ); ?></p>

			<div class="dt-crawler-card">
				<form id="dt-settings-form">
					<table class="form-table">
						<tr>
							<th scope="row"><label for="request_timeout"><?php esc_html_e( 'Thời gian chờ (Timeout)', 'wp-product-crawler' ); ?></label></th>
							<td>
								<input type="number" id="request_timeout" name="request_timeout" value="<?php echo esc_attr( $settings['request_timeout'] ); ?>" min="3" max="60" class="small-text"> <?php esc_html_e( 'giây (Mặc định: 15s)', 'wp-product-crawler' ); ?>
								<p class="description"><?php esc_html_e( 'Thời gian chờ tối đa cho một kết nối mạng trước khi ngắt.', 'wp-product-crawler' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="requests_per_minute"><?php esc_html_e( 'Giới hạn tần suất (Rate Limit)', 'wp-product-crawler' ); ?></label></th>
							<td>
								<input type="number" id="requests_per_minute" name="requests_per_minute" value="<?php echo esc_attr( $settings['requests_per_minute'] ); ?>" min="5" max="120" class="small-text"> <?php esc_html_e( 'requests / phút (Mặc định: 30)', 'wp-product-crawler' ); ?>
								<p class="description"><?php esc_html_e( 'Tránh làm quá tải máy chủ nguồn bằng cách điều tiết khoảng cách giữa các request.', 'wp-product-crawler' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="max_products"><?php esc_html_e( 'Số lượng sản phẩm tối đa / lần quét', 'wp-product-crawler' ); ?></label></th>
							<td>
								<input type="number" id="max_products" name="max_products" value="<?php echo esc_attr( $settings['max_products'] ); ?>" min="1" max="500" class="small-text">
								<p class="description"><?php esc_html_e( 'Giới hạn tối đa số lượng sản phẩm được thu thập trong một lần quét.', 'wp-product-crawler' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="max_crawl_depth"><?php esc_html_e( 'Độ sâu phân trang tối đa (Crawl Depth)', 'wp-product-crawler' ); ?></label></th>
							<td>
								<input type="number" id="max_crawl_depth" name="max_crawl_depth" value="<?php echo esc_attr( $settings['max_crawl_depth'] ); ?>" min="1" max="10" class="small-text">
								<p class="description"><?php esc_html_e( 'Số tầng phân trang (Trang 1 -> 2 -> 3...) crawler sẽ duyệt tiếp.', 'wp-product-crawler' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="max_image_size_mb"><?php esc_html_e( 'Dung lượng ảnh tối đa', 'wp-product-crawler' ); ?></label></th>
							<td>
								<input type="number" id="max_image_size_mb" name="max_image_size_mb" value="<?php echo esc_attr( $settings['max_image_size_mb'] ); ?>" min="1" max="20" class="small-text"> MB
								<p class="description"><?php esc_html_e( 'Tự động bỏ qua các ảnh vượt quá dung lượng này để tiết kiệm băng thông và bộ nhớ.', 'wp-product-crawler' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Tùy chọn trích xuất & Media', 'wp-product-crawler' ); ?></th>
							<td>
								<fieldset>
									<label><input type="checkbox" name="download_images" value="1" <?php checked( $settings['download_images'], 1 ); ?>> <?php esc_html_e( 'Tải hình ảnh về thư viện Media WordPress (Không hotlink ảnh ngoài)', 'wp-product-crawler' ); ?></label><br>
									<label><input type="checkbox" name="import_descriptions" value="1" <?php checked( $settings['import_descriptions'], 1 ); ?>> <?php esc_html_e( 'Nhập bài viết mô tả sản phẩm (đã lọc thẻ nguy hiểm XSS)', 'wp-product-crawler' ); ?></label><br>
									<label><input type="checkbox" name="import_specifications" value="1" <?php checked( $settings['import_specifications'], 1 ); ?>> <?php esc_html_e( 'Nhập thông số kỹ thuật dạng bảng/cặp nhãn giá trị', 'wp-product-crawler' ); ?></label><br>
									<label><input type="checkbox" name="import_prices" value="1" <?php checked( $settings['import_prices'], 1 ); ?>> <?php esc_html_e( 'Nhập giá bán niêm yết', 'wp-product-crawler' ); ?></label>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="default_product_status"><?php esc_html_e( 'Trạng thái sản phẩm mặc định', 'wp-product-crawler' ); ?></label></th>
							<td>
								<select id="default_product_status" name="default_product_status">
									<option value="draft" <?php selected( $settings['default_product_status'], 'draft' ); ?>><?php esc_html_e( 'Bản nháp (Draft - Khuyên dùng)', 'wp-product-crawler' ); ?></option>
									<option value="publish" <?php selected( $settings['default_product_status'], 'publish' ); ?>><?php esc_html_e( 'Công khai (Publish)', 'wp-product-crawler' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Sản phẩm mới nhập sẽ ở trạng thái này để người quản trị kiểm duyệt trước khi hiển thị cho khách hàng.', 'wp-product-crawler' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="default_import_mode"><?php esc_html_e( 'Chế độ nhập mặc định', 'wp-product-crawler' ); ?></label></th>
							<td>
								<select id="default_import_mode" name="default_import_mode">
									<option value="create_update" <?php selected( $settings['default_import_mode'], 'create_update' ); ?>><?php esc_html_e( 'Tạo mới + Cập nhật (Create + Update)', 'wp-product-crawler' ); ?></option>
									<option value="create_only" <?php selected( $settings['default_import_mode'], 'create_only' ); ?>><?php esc_html_e( 'Chỉ tạo mới (Create Only)', 'wp-product-crawler' ); ?></option>
									<option value="update_existing" <?php selected( $settings['default_import_mode'], 'update_existing' ); ?>><?php esc_html_e( 'Chỉ cập nhật sản phẩm cũ (Update Existing)', 'wp-product-crawler' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
					<p class="submit">
						<button type="button" id="dt-save-settings-btn" class="button button-primary"><?php esc_html_e( 'Lưu cấu hình', 'wp-product-crawler' ); ?></button>
						<span id="dt-settings-saved-notice" class="dt-saved-notice" style="display:none; color:green; margin-left:10px;">✓ <?php esc_html_e( 'Đã lưu cấu hình!', 'wp-product-crawler' ); ?></span>
					</p>
				</form>
			</div>
		</div>
		<?php
	}
}
