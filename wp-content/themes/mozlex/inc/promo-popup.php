<?php
/**
 * Promotional Popup System — Quản lý Popup Khuyến mãi & Thông báo từ Admin
 *
 * @package mozlex
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kiểm tra xem Popup Khuyến Mãi có đang hoạt động hay không.
 *
 * @return bool True nếu popup được bật và nằm trong khoảng thời gian hợp lệ.
 */
function mozlex_is_promo_popup_active(): bool {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return false;
	}

	$enabled = mozlex_opt( 'promo_popup_enabled', '0' );
	if ( '1' !== $enabled ) {
		return false;
	}

	// Kiểm tra lịch trình (nếu bật)
	$schedule_enabled = mozlex_opt( 'promo_popup_schedule_enabled', '0' );
	if ( '1' === $schedule_enabled ) {
		$now        = current_time( 'timestamp' );
		$start_date = trim( (string) mozlex_opt( 'promo_popup_start_date', '' ) );
		$end_date   = trim( (string) mozlex_opt( 'promo_popup_end_date', '' ) );

		if ( ! empty( $start_date ) ) {
			$start_ts = strtotime( $start_date . ' 00:00:00' );
			if ( false !== $start_ts && $now < $start_ts ) {
				return false;
			}
		}

		if ( ! empty( $end_date ) ) {
			$end_ts = strtotime( $end_date . ' 23:59:59' );
			if ( false !== $end_ts && $now > $end_ts ) {
				return false;
			}
		}
	}

	return true;
}

/**
 * Render Popup ra wp_footer nếu popup đang kích hoạt.
 * Khi tắt, không xuất bất kỳ thẻ HTML hoặc mã JS/CSS nào.
 */
add_action( 'wp_footer', function () {
	if ( ! mozlex_is_promo_popup_active() ) {
		return;
	}

	$title          = trim( (string) mozlex_opt( 'promo_popup_title', 'Ưu đãi đặc biệt' ) );
	$badge          = trim( (string) mozlex_opt( 'promo_popup_badge', 'Khuyến mãi' ) );
	$desc           = trim( (string) mozlex_opt( 'promo_popup_desc', '' ) );
	$image          = trim( (string) mozlex_opt( 'promo_popup_image', '' ) );
	$btn_text       = trim( (string) mozlex_opt( 'promo_popup_btn_text', 'Xem ngay' ) );
	$btn_url        = trim( (string) mozlex_opt( 'promo_popup_btn_url', home_url( '/san-pham/' ) ) );
	$secondary_text = trim( (string) mozlex_opt( 'promo_popup_secondary_text', '' ) );
	$close_text     = trim( (string) mozlex_opt( 'promo_popup_close_text', 'Bỏ qua hôm nay' ) );
	$frequency      = mozlex_opt( 'promo_popup_frequency', '1hour' );

	$countdown_enabled = ! empty( mozlex_opt( 'promo_popup_countdown_enabled' ) ) && '1' === mozlex_opt( 'promo_popup_countdown_enabled' );
	$countdown_label   = trim( (string) mozlex_opt( 'promo_popup_countdown_label', 'Ưu đãi kết thúc sau:' ) );
	$countdown_end_raw = trim( (string) mozlex_opt( 'promo_popup_countdown_end', '' ) );
	$end_date_raw      = trim( (string) mozlex_opt( 'promo_popup_end_date', '' ) );

	$countdown_target_ts = 0;
	if ( $countdown_enabled ) {
		if ( ! empty( $countdown_end_raw ) ) {
			$countdown_target_ts = strtotime( $countdown_end_raw );
		} elseif ( ! empty( $end_date_raw ) ) {
			$countdown_target_ts = strtotime( $end_date_raw . ' 23:59:59' );
		} else {
			$countdown_target_ts = current_time( 'timestamp' ) + ( 3 * 86400 );
		}
	}

	if ( ! in_array( $frequency, array( '1hour', 'today', 'always' ), true ) ) {
		$frequency = '1hour';
	}
	?>
	<!-- Mozlex Promotional Popup Modal -->
	<aside id="mozlex-promo-popup"
		class="mozlex-promo-modal"
		role="dialog"
		aria-modal="true"
		aria-labelledby="mozlex-promo-title"
		<?php if ( ! empty( $desc ) ) : ?>aria-describedby="mozlex-promo-desc"<?php endif; ?>
		hidden
		data-frequency="<?php echo esc_attr( $frequency ); ?>">
		
		<div class="mozlex-promo-backdrop" data-promo-dismiss tabindex="-1" aria-hidden="true"></div>

		<div class="mozlex-promo-dialog" role="document">
			<button type="button" class="mozlex-promo-close-btn" data-promo-dismiss aria-label="<?php esc_attr_e( 'Đóng cửa sổ khuyến mãi', 'mozlex' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
			</button>

			<div class="mozlex-promo-body <?php echo ! empty( $image ) ? 'has-media' : 'no-media'; ?>">
				<?php if ( ! empty( $image ) ) : ?>
					<div class="mozlex-promo-media">
						<?php if ( ! empty( $btn_url ) ) : ?>
							<a href="<?php echo esc_url( $btn_url ); ?>" data-promo-cta style="display:block; width:100%; height:100%;">
								<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="eager" width="560" height="420">
							</a>
						<?php else : ?>
							<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="eager" width="560" height="420">
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="mozlex-promo-content">
					<?php if ( ! empty( $badge ) ) : ?>
						<span class="mozlex-promo-badge"><?php echo esc_html( $badge ); ?></span>
					<?php endif; ?>

					<h2 id="mozlex-promo-title" class="mozlex-promo-title">
						<?php if ( ! empty( $btn_url ) ) : ?>
							<a href="<?php echo esc_url( $btn_url ); ?>" data-promo-cta style="color:inherit; text-decoration:none;">
								<?php echo esc_html( $title ); ?>
							</a>
						<?php else : ?>
							<?php echo esc_html( $title ); ?>
						<?php endif; ?>
					</h2>

					<?php if ( ! empty( $desc ) ) : ?>
						<p id="mozlex-promo-desc" class="mozlex-promo-desc"><?php echo nl2br( esc_html( $desc ) ); ?></p>
					<?php endif; ?>

					<?php if ( $countdown_enabled && $countdown_target_ts > 0 ) : ?>
						<div class="mozlex-promo-countdown" data-countdown-target="<?php echo esc_attr( (string) ( $countdown_target_ts * 1000 ) ); ?>">
							<?php if ( ! empty( $countdown_label ) ) : ?>
								<div class="mozlex-countdown-label">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
									<span><?php echo esc_html( $countdown_label ); ?></span>
								</div>
							<?php endif; ?>
							<div class="mozlex-countdown-timer" role="timer" aria-label="<?php esc_attr_e( 'Đồng hồ đếm ngược', 'mozlex' ); ?>">
								<div class="mozlex-countdown-box">
									<span class="mozlex-countdown-num" data-cd-days>00</span>
									<span class="mozlex-countdown-txt"><?php esc_html_e( 'Ngày', 'mozlex' ); ?></span>
								</div>
								<span class="mozlex-countdown-divider">:</span>
								<div class="mozlex-countdown-box">
									<span class="mozlex-countdown-num" data-cd-hours>00</span>
									<span class="mozlex-countdown-txt"><?php esc_html_e( 'Giờ', 'mozlex' ); ?></span>
								</div>
								<span class="mozlex-countdown-divider">:</span>
								<div class="mozlex-countdown-box">
									<span class="mozlex-countdown-num" data-cd-mins>00</span>
									<span class="mozlex-countdown-txt"><?php esc_html_e( 'Phút', 'mozlex' ); ?></span>
								</div>
								<span class="mozlex-countdown-divider">:</span>
								<div class="mozlex-countdown-box">
									<span class="mozlex-countdown-num" data-cd-secs>00</span>
									<span class="mozlex-countdown-txt"><?php esc_html_e( 'Giây', 'mozlex' ); ?></span>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<div class="mozlex-promo-actions">
						<?php if ( ! empty( $btn_text ) && ! empty( $btn_url ) ) : ?>
							<a href="<?php echo esc_url( $btn_url ); ?>" class="btn mozlex-promo-cta" data-promo-cta>
								<span><?php echo esc_html( $btn_text ); ?></span>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
							</a>
						<?php endif; ?>

						<?php if ( ! empty( $close_text ) ) : ?>
							<button type="button" class="mozlex-promo-skip-btn" data-promo-dismiss>
								<?php echo esc_html( $close_text ); ?>
							</button>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $secondary_text ) ) : ?>
						<p class="mozlex-promo-secondary"><?php echo esc_html( $secondary_text ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</aside>

	<style id="mozlex-promo-css">
	.mozlex-promo-modal {
		position: fixed;
		inset: 0;
		z-index: 99999;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 16px;
		opacity: 0;
		visibility: hidden;
		transition: opacity 0.28s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.28s;
	}
	.mozlex-promo-modal[hidden] {
		display: none;
	}
	.mozlex-promo-modal.is-visible {
		opacity: 1;
		visibility: visible;
	}
	.mozlex-promo-modal.is-closing {
		opacity: 0;
		visibility: hidden;
		transition: opacity 0.2s ease-in;
	}
	.mozlex-promo-backdrop {
		position: absolute;
		inset: 0;
		background: rgba(8, 10, 15, 0.76);
		backdrop-filter: blur(8px);
		-webkit-backdrop-filter: blur(8px);
		cursor: pointer;
	}
	.mozlex-promo-dialog {
		position: relative;
		z-index: 2;
		width: 100%;
		max-width: 760px;
		max-height: min(90vh, 720px);
		background: linear-gradient(155deg, #181c25 0%, #0e1117 100%);
		border: 1px solid rgba(201, 163, 129, 0.35);
		border-radius: 12px;
		box-shadow: 0 25px 60px -10px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.06), 0 0 50px -15px rgba(201,163,129,0.25);
		overflow: hidden;
		transform: translateY(14px) scale(0.96);
		transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1);
	}
	.mozlex-promo-modal.is-visible .mozlex-promo-dialog {
		transform: translateY(0) scale(1);
	}
	.mozlex-promo-modal.is-closing .mozlex-promo-dialog {
		transform: translateY(8px) scale(0.97);
		transition: transform 0.2s ease-in;
	}
	.mozlex-promo-close-btn {
		position: absolute;
		top: 14px;
		right: 14px;
		z-index: 10;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 38px;
		height: 38px;
		padding: 0;
		border-radius: 50%;
		background: rgba(255, 255, 255, 0.12);
		color: #ffffff;
		border: 1px solid rgba(255, 255, 255, 0.18);
		cursor: pointer;
		transition: background 0.2s, transform 0.2s, color 0.2s;
	}
	.mozlex-promo-close-btn:hover,
	.mozlex-promo-close-btn:focus-visible {
		background: var(--primary, #c9a381);
		color: #000;
		outline: none;
		transform: rotate(90deg);
	}
	.mozlex-promo-body {
		display: grid;
		grid-template-columns: 1fr;
		max-height: min(90vh, 720px);
		overflow-y: auto;
		-webkit-overflow-scrolling: touch;
	}
	.mozlex-promo-body.has-media {
		grid-template-columns: 46% 54%;
	}
	.mozlex-promo-media {
		position: relative;
		min-height: 260px;
		background: #090b0e;
		overflow: hidden;
	}
	.mozlex-promo-media img {
		width: 100%;
		height: 100%;
		object-fit: cover;
		display: block;
	}
	.mozlex-promo-content {
		padding: 38px 34px 34px;
		display: flex;
		flex-direction: column;
		justify-content: center;
		color: #f5f5f7;
		font-family: 'Be Vietnam Pro', system-ui, sans-serif;
	}
	.mozlex-promo-badge {
		display: inline-block;
		align-self: flex-start;
		padding: 4px 12px;
		background: rgba(201, 163, 129, 0.18);
		color: var(--primary, #c9a381);
		border: 1px solid rgba(201, 163, 129, 0.35);
		border-radius: 999px;
		font-size: 0.72rem;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		margin-bottom: 14px;
	}
	.mozlex-promo-title {
		font-size: clamp(1.3rem, 2.4vw, 1.7rem);
		font-weight: 700;
		line-height: 1.28;
		color: #ffffff;
		margin: 0 0 12px;
		font-family: inherit;
		letter-spacing: -0.01em;
	}
	.mozlex-promo-desc {
		font-size: 0.95rem;
		line-height: 1.6;
		color: rgba(255, 255, 255, 0.78);
		margin: 0 0 20px;
	}

	/* Đồng hồ đếm ngược (Countdown Timer) */
	.mozlex-promo-countdown {
		background: rgba(0, 0, 0, 0.38);
		border: 1px solid rgba(201, 163, 129, 0.3);
		border-radius: 8px;
		padding: 10px 14px 12px;
		margin: 0 0 20px;
		box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.45);
	}
	.mozlex-countdown-label {
		display: flex;
		align-items: center;
		gap: 6px;
		font-size: 0.75rem;
		font-weight: 700;
		color: var(--primary, #c9a381);
		text-transform: uppercase;
		letter-spacing: 0.06em;
		margin-bottom: 8px;
	}
	.mozlex-countdown-timer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 6px;
	}
	.mozlex-countdown-box {
		flex: 1;
		background: linear-gradient(180deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.02) 100%);
		border: 1px solid rgba(255, 255, 255, 0.12);
		border-radius: 6px;
		padding: 7px 4px 5px;
		text-align: center;
		min-width: 44px;
		box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
	}
	.mozlex-countdown-num {
		display: block;
		font-size: 1.28rem;
		font-weight: 800;
		line-height: 1.1;
		color: #ffffff;
		font-variant-numeric: tabular-nums;
		font-family: 'Inter', system-ui, -apple-system, sans-serif;
		letter-spacing: -0.02em;
	}
	.mozlex-countdown-txt {
		display: block;
		font-size: 0.62rem;
		color: rgba(255, 255, 255, 0.55);
		text-transform: uppercase;
		font-weight: 600;
		letter-spacing: 0.04em;
		margin-top: 2px;
	}
	.mozlex-countdown-divider {
		font-size: 1.15rem;
		font-weight: 700;
		color: var(--primary, #c9a381);
		line-height: 1;
		margin-bottom: 12px;
		opacity: 0.85;
	}

	.mozlex-promo-actions {
		display: flex;
		align-items: center;
		gap: 14px;
		flex-wrap: wrap;
		margin-bottom: 12px;
	}
	.mozlex-promo-cta {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		background: var(--primary, #c9a381);
		color: #0b0d12 !important;
		font-weight: 700;
		font-size: 0.92rem;
		padding: 12px 24px;
		border-radius: 6px;
		text-decoration: none;
		box-shadow: 0 4px 16px rgba(201, 163, 129, 0.3);
		transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
	}
	.mozlex-promo-cta:hover,
	.mozlex-promo-cta:focus-visible {
		background: var(--primary-hover, #d48a49);
		transform: translateY(-1px);
		box-shadow: 0 6px 20px rgba(201, 163, 129, 0.45);
		color: #000 !important;
	}
	.mozlex-promo-skip-btn {
		background: none;
		border: none;
		color: rgba(255, 255, 255, 0.58);
		font-size: 0.88rem;
		cursor: pointer;
		padding: 8px 10px;
		text-decoration: underline;
		text-underline-offset: 3px;
		transition: color 0.2s;
	}
	.mozlex-promo-skip-btn:hover,
	.mozlex-promo-skip-btn:focus-visible {
		color: #ffffff;
		outline: none;
	}
	.mozlex-promo-secondary {
		font-size: 0.78rem;
		color: rgba(255, 255, 255, 0.46);
		margin: 8px 0 0;
		font-style: italic;
	}

	@media (max-width: 680px) {
		.mozlex-promo-dialog {
			max-width: 100%;
			max-height: 88vh;
			border-radius: 10px;
		}
		.mozlex-promo-body.has-media {
			grid-template-columns: 1fr;
		}
		.mozlex-promo-media {
			min-height: 180px;
			max-height: 220px;
		}
		.mozlex-promo-content {
			padding: 24px 20px 22px;
		}
		.mozlex-promo-title {
			font-size: 1.25rem;
		}
		.mozlex-promo-desc {
			font-size: 0.88rem;
			margin-bottom: 18px;
		}
		.mozlex-promo-actions {
			flex-direction: column;
			align-items: stretch;
			gap: 10px;
		}
		.mozlex-promo-cta {
			justify-content: center;
			padding: 13px;
		}
		.mozlex-promo-skip-btn {
			text-align: center;
			padding: 6px;
		}
		.mozlex-promo-close-btn {
			width: 44px;
			height: 44px;
			top: 10px;
			right: 10px;
		}
		.mozlex-promo-countdown {
			padding: 8px 10px 10px;
			margin-bottom: 16px;
		}
		.mozlex-countdown-box {
			min-width: 36px;
			padding: 5px 2px 4px;
		}
		.mozlex-countdown-num {
			font-size: 1.15rem;
		}
		.mozlex-countdown-txt {
			font-size: 0.56rem;
		}
		.mozlex-countdown-divider {
			font-size: 1rem;
			margin-bottom: 10px;
		}
	}

	@media (prefers-reduced-motion: reduce) {
		.mozlex-promo-modal,
		.mozlex-promo-dialog,
		.mozlex-promo-close-btn,
		.mozlex-promo-cta {
			transition: none !important;
			animation: none !important;
			transform: none !important;
		}
	}
	</style>

	<script id="mozlex-promo-js">
	(function () {
		var modal = document.getElementById('mozlex-promo-popup');
		if (!modal) return;

		var STORAGE_KEY = 'mozlex_promo_dismissed_until';
		var frequency = modal.getAttribute('data-frequency') || '1hour';
		var dismissedUntil = parseInt(localStorage.getItem(STORAGE_KEY), 10) || 0;
		var now = Date.now();

		// Nếu đã đóng và chưa hết thời hạn chặn -> Không hiển thị
		if (dismissedUntil && now < dismissedUntil) {
			return;
		}

		var lastActiveElement = null;
		var focusableSelectors = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

		// Bộ đếm ngược thời gian thực (Countdown Timer)
		var cdWrap = modal.querySelector('.mozlex-promo-countdown');
		if (cdWrap) {
			var targetMs = parseInt(cdWrap.getAttribute('data-countdown-target'), 10) || 0;
			var dayEl = cdWrap.querySelector('[data-cd-days]');
			var hrEl  = cdWrap.querySelector('[data-cd-hours]');
			var minEl = cdWrap.querySelector('[data-cd-mins]');
			var secEl = cdWrap.querySelector('[data-cd-secs]');

			function tickCountdown() {
				var diff = targetMs - Date.now();
				if (diff <= 0) {
					if (dayEl) dayEl.textContent = '00';
					if (hrEl)  hrEl.textContent  = '00';
					if (minEl) minEl.textContent = '00';
					if (secEl) secEl.textContent = '00';
					return;
				}

				var d = Math.floor(diff / (1000 * 60 * 60 * 24));
				var h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
				var m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
				var s = Math.floor((diff % (1000 * 60)) / 1000);

				if (dayEl) dayEl.textContent = (d < 10 ? '0' : '') + d;
				if (hrEl)  hrEl.textContent  = (h < 10 ? '0' : '') + h;
				if (minEl) minEl.textContent = (m < 10 ? '0' : '') + m;
				if (secEl) secEl.textContent = (s < 10 ? '0' : '') + s;
			}

			tickCountdown();
			setInterval(tickCountdown, 1000);
		}

		function showModal() {
			lastActiveElement = document.activeElement;
			modal.hidden = false;
			document.body.style.overflow = 'hidden';

			// Trigger animation mượt mà
			requestAnimationFrame(function () {
				modal.classList.add('is-visible');
				var focusTarget = modal.querySelector('.mozlex-promo-cta') || modal.querySelector('.mozlex-promo-close-btn');
				if (focusTarget) {
					focusTarget.focus();
				}
			});
		}

		function recordDismissal() {
			var curr = Date.now();
			if (frequency === '1hour') {
				localStorage.setItem(STORAGE_KEY, String(curr + (3600 * 1000)));
			} else if (frequency === 'today') {
				var midnight = new Date();
				midnight.setHours(23, 59, 59, 999);
				localStorage.setItem(STORAGE_KEY, String(midnight.getTime()));
			} else if (frequency === 'always') {
				localStorage.removeItem(STORAGE_KEY);
			}
		}

		function closeModal() {
			recordDismissal();
			modal.classList.remove('is-visible');
			modal.classList.add('is-closing');
			document.body.style.overflow = '';

			setTimeout(function () {
				modal.hidden = true;
				modal.classList.remove('is-closing');
				if (lastActiveElement && typeof lastActiveElement.focus === 'function') {
					lastActiveElement.focus();
				}
			}, 220);
		}

		// Đóng khi bấm nút Đóng, Bỏ qua hoặc Backdrop ngoài
		modal.addEventListener('click', function (e) {
			if (e.target.closest('[data-promo-dismiss]')) {
				e.preventDefault();
				closeModal();
			}
		});

		// Ghi nhận khi người dùng bấm nút CTA
		var ctaBtn = modal.querySelector('[data-promo-cta]');
		if (ctaBtn) {
			ctaBtn.addEventListener('click', function () {
				recordDismissal();
			});
		}

		// Đóng bằng phím ESC & Giữ phím Tab (Focus Trap)
		document.addEventListener('keydown', function (e) {
			if (modal.hidden) return;

			if (e.key === 'Escape') {
				e.preventDefault();
				closeModal();
				return;
			}

			if (e.key === 'Tab') {
				var focusables = Array.prototype.slice.call(modal.querySelectorAll(focusableSelectors)).filter(function (el) {
					return el.offsetParent !== null && !el.disabled;
				});
				if (!focusables.length) return;

				var first = focusables[0];
				var last = focusables[focusables.length - 1];

				if (e.shiftKey && document.activeElement === first) {
					e.preventDefault();
					last.focus();
				} else if (!e.shiftKey && document.activeElement === last) {
					e.preventDefault();
					first.focus();
				}
			}
		});

		// Chờ 800ms sau khi trang sẵn sàng để hiển thị tinh tế, tránh giật LCP
		if (document.readyState === 'complete') {
			setTimeout(showModal, 800);
		} else {
			window.addEventListener('load', function () {
				setTimeout(showModal, 800);
			});
		}
	})();
	</script>
	<?php
}, 95 );
