<?php
/**
 * Lớp quản lý Jobs, Items và Structured Logs trong cơ sở dữ liệu.
 *
 * @package WP_Product_Crawler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Crawler_Job_Manager {

	private static ?WP_Crawler_Job_Manager $instance = null;

	public static function get_instance(): WP_Crawler_Job_Manager {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Tạo một Job cào mới
	 */
	public function create_job( string $source_url, string $url_type = 'unknown', string $import_mode = 'create_update', array $settings = array() ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'crawler_jobs';

		$now  = current_time( 'mysql' );
		$data = array(
			'source_url'    => esc_url_raw( $source_url ),
			'url_type'      => sanitize_key( $url_type ),
			'status'        => 'scanning',
			'import_mode'   => sanitize_key( $import_mode ),
			'settings_json' => wp_json_encode( $settings ),
			'created_at'    => $now,
			'updated_at'    => $now,
		);

		$wpdb->insert( $table, $data );
		$job_id = (int) $wpdb->insert_id;

		$this->log( $job_id, 'INFO', sprintf( __( 'Khởi tạo phiên quét mới từ: %s (Kiểu URL: %s)', 'wp-product-crawler' ), $source_url, $url_type ) );

		return $job_id;
	}

	/**
	 * Cập nhật thông tin Job
	 */
	public function update_job( int $job_id, array $data ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'crawler_jobs';

		$data['updated_at'] = current_time( 'mysql' );
		$res = $wpdb->update( $table, $data, array( 'id' => $job_id ) );

		return false !== $res;
	}

	/**
	 * Lấy thông tin chi tiết một Job
	 */
	public function get_job( int $job_id ): ?object {
		global $wpdb;
		$table = $wpdb->prefix . 'crawler_jobs';

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $job_id ) );
	}

	/**
	 * Lấy danh sách các Jobs gần nhất
	 */
	public function get_jobs( int $limit = 20, int $offset = 0 ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'crawler_jobs';

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset ) );
	}

	/**
	 * Đếm tổng số lượng Jobs
	 */
	public function count_jobs(): int {
		global $wpdb;
		$table = $wpdb->prefix . 'crawler_jobs';

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	/**
	 * Thêm một sản phẩm khám phá được vào Job
	 */
	public function add_item( int $job_id, string $source_url, string $status = 'discovered', array $raw_data = array(), ?string $name = null, ?string $sku = null, ?float $price = null ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'crawler_items';

		$data = array(
			'job_id'        => $job_id,
			'source_url'    => esc_url_raw( $source_url ),
			'product_name'  => $name ? sanitize_text_field( $name ) : '',
			'sku'           => $sku ? sanitize_text_field( $sku ) : '',
			'price'         => null !== $price ? $price : null,
			'status'        => sanitize_key( $status ),
			'raw_data_json' => ! empty( $raw_data ) ? wp_json_encode( $raw_data ) : null,
			'created_at'    => current_time( 'mysql' ),
		);

		$wpdb->insert( $table, $data );
		return (int) $wpdb->insert_id;
	}

	/**
	 * Cập nhật Item sản phẩm
	 */
	public function update_item( int $item_id, array $data ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'crawler_items';

		$res = $wpdb->update( $table, $data, array( 'id' => $item_id ) );
		return false !== $res;
	}

	/**
	 * Lấy thông tin một Item theo ID
	 */
	public function get_item( int $item_id ): ?object {
		global $wpdb;
		$table = $wpdb->prefix . 'crawler_items';

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $item_id ) );
	}

	/**
	 * Lấy tất cả items của một Job
	 */
	public function get_job_items( int $job_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'crawler_items';

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE job_id = %d ORDER BY id ASC", $job_id ) );
	}

	/**
	 * Ghi Structured Log cho Job
	 */
	public function log( int $job_id, string $level, string $message, array $context = array() ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'crawler_logs';

		$level = strtoupper( sanitize_key( $level ) );
		if ( ! in_array( $level, array( 'INFO', 'WARN', 'ERROR', 'DEBUG' ), true ) ) {
			$level = 'INFO';
		}

		$wpdb->insert( $table, array(
			'job_id'       => $job_id,
			'level'        => $level,
			'message'      => sanitize_text_field( $message ),
			'context_json' => ! empty( $context ) ? wp_json_encode( $context ) : null,
			'created_at'   => current_time( 'mysql' ),
		) );
	}

	/**
	 * Lấy danh sách logs của Job
	 */
	public function get_job_logs( int $job_id, int $limit = 200 ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'crawler_logs';

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE job_id = %d ORDER BY id ASC LIMIT %d", $job_id, $limit ) );
	}

	/**
	 * Lấy phiên làm việc chưa hoàn tất gần nhất (Crash Recovery)
	 */
	public function get_active_session(): ?array {
		global $wpdb;
		$table_jobs  = $wpdb->prefix . 'crawler_jobs';

		// Tìm job gần nhất trong 48 giờ có trạng thái chưa kết thúc/chưa hủy
		$job = $wpdb->get_row(
			"SELECT * FROM {$table_jobs} 
			WHERE status IN ('scanning', 'discovered', 'extracted', 'preview_ready', 'importing', 'paused') 
			AND created_at >= DATE_SUB(NOW(), INTERVAL 48 HOUR) 
			ORDER BY id DESC LIMIT 1"
		);

		if ( ! $job ) {
			return null;
		}

		$job_id = (int) $job->id;
		$items  = $this->get_job_items( $job_id );

		if ( empty( $items ) ) {
			return null;
		}

		$total     = count( $items );
		$imported  = 0;
		$updated   = 0;
		$duplicate = 0;
		$failed    = 0;
		$pending   = 0;

		foreach ( $items as $it ) {
			if ( 'imported' === $it->status ) {
				$imported++;
			} elseif ( 'updated' === $it->status ) {
				$updated++;
			} elseif ( 'duplicate' === $it->status ) {
				$duplicate++;
			} elseif ( 'failed' === $it->status ) {
				$failed++;
			} elseif ( in_array( $it->status, array( 'discovered', 'extracted', 'pending' ), true ) ) {
				$pending++;
			}
		}

		// Nếu tất cả sản phẩm trong phiên đã được xử lý (pending == 0), tự động đánh dấu job hoàn tất và không báo dở dang
		if ( 0 === $pending && $total > 0 ) {
			$this->update_job( $job_id, array( 'status' => 'completed' ) );
			return null;
		}

		return array(
			'job'   => $job,
			'stats' => array(
				'total'     => $total,
				'imported'  => $imported,
				'updated'   => $updated,
				'duplicate' => $duplicate,
				'failed'    => $failed,
				'pending'   => $pending,
			),
			'items' => $items,
		);
	}

	/**
	 * Tạm dừng phiên làm việc
	 */
	public function pause_job( int $job_id ): bool {
		$res = $this->update_job( $job_id, array( 'status' => 'paused' ) );
		if ( $res ) {
			$this->log( $job_id, 'INFO', __( 'Phiên làm việc đã được người dùng tạm dừng.', 'wp-product-crawler' ) );
		}
		return $res;
	}

	/**
	 * Tiếp tục phiên làm việc
	 */
	public function resume_job( int $job_id ): bool {
		$res = $this->update_job( $job_id, array( 'status' => 'importing' ) );
		if ( $res ) {
			$this->log( $job_id, 'INFO', __( 'Tiếp tục phiên làm việc.', 'wp-product-crawler' ) );
		}
		return $res;
	}

	/**
	 * Hủy bỏ phiên làm việc:
	 * - Các sản phẩm đã nhập thành công (status = imported) GIỮ NGUYÊN trên website
	 * - Các sản phẩm còn lại chưa nhập (discovered, extracted, pending) chuyển thành 'cancelled'
	 */
	public function cancel_job( int $job_id ): bool {
		global $wpdb;
		$table_jobs  = $wpdb->prefix . 'crawler_jobs';
		$table_items = $wpdb->prefix . 'crawler_items';

		// Đếm số sản phẩm đã nhập thành công
		$imported_count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table_items} WHERE job_id = %d AND status IN ('imported', 'updated')",
			$job_id
		) );

		// Đánh dấu các sản phẩm chưa nhập thành 'cancelled'
		$cancelled_items = $wpdb->query( $wpdb->prepare(
			"UPDATE {$table_items} SET status = 'cancelled' WHERE job_id = %d AND status IN ('discovered', 'extracted', 'pending')",
			$job_id
		) );

		// Đổi trạng thái Job sang 'cancelled'
		$res = $this->update_job( $job_id, array( 'status' => 'cancelled' ) );

		$this->log(
			$job_id,
			'WARN',
			sprintf(
				__( 'Đã hủy phiên làm việc #%d. %d sản phẩm đã nhập thành công được lưu giữ trên website, %d sản phẩm chưa xử lý đã bị hủy.', 'wp-product-crawler' ),
				$job_id,
				$imported_count,
				(int) $cancelled_items
			)
		);

		return $res;
	}

	/**
	 * Bỏ qua một sản phẩm trong hàng đợi
	 */
	public function skip_item( int $item_id ): bool {
		global $wpdb;
		$table_jobs = $wpdb->prefix . 'crawler_jobs';

		$item = $this->get_item( $item_id );
		if ( ! $item ) {
			return false;
		}

		$res = $this->update_item( $item_id, array( 'status' => 'skipped' ) );
		if ( $res && $item->job_id > 0 ) {
			$wpdb->query( $wpdb->prepare(
				"UPDATE {$table_jobs} SET products_skipped = products_skipped + 1 WHERE id = %d",
				$item->job_id
			) );
			$this->log( (int) $item->job_id, 'INFO', sprintf( __( 'Đã bỏ qua sản phẩm: %s (Item #%d)', 'wp-product-crawler' ), $item->product_name ?: $item->source_url, $item_id ) );
		}

		return $res;
	}

	/**
	 * Xóa một sản phẩm khỏi danh sách cào dữ liệu (wp_crawler_items).
	 * Nếu sản phẩm đã được nhập và $delete_post = true, xóa luôn bài viết trên WordPress.
	 */
	public function delete_item( int $item_id, bool $delete_post = false ): bool {
		global $wpdb;
		$table_jobs  = $wpdb->prefix . 'crawler_jobs';
		$table_items = $wpdb->prefix . 'crawler_items';

		$item = $this->get_item( $item_id );
		if ( ! $item ) {
			return false;
		}

		if ( $delete_post && ! empty( $item->post_id ) ) {
			wp_delete_post( (int) $item->post_id, true );
		}

		$res = $wpdb->delete( $table_items, array( 'id' => $item_id ), array( '%d' ) );

		if ( false !== $res && $item->job_id > 0 ) {
			$wpdb->query( $wpdb->prepare(
				"UPDATE {$table_jobs} SET products_found = (SELECT COUNT(*) FROM {$table_items} WHERE job_id = %d) WHERE id = %d",
				$item->job_id,
				$item->job_id
			) );

			$this->log(
				(int) $item->job_id,
				'INFO',
				sprintf( __( 'Đã xóa sản phẩm khỏi danh sách: %s (Item #%d)', 'wp-product-crawler' ), $item->product_name ?: $item->source_url, $item_id )
			);
		}

		return false !== $res;
	}

	/**
	 * Xóa hàng loạt sản phẩm khỏi danh sách cào dữ liệu.
	 */
	public function delete_items( array $item_ids, bool $delete_post = false ): int {
		$deleted = 0;
		foreach ( $item_ids as $id ) {
			if ( $this->delete_item( (int) $id, $delete_post ) ) {
				$deleted++;
			}
		}
		return $deleted;
	}
}
