<?php
/**
 * Template Name: Ashen Press — 3D Art Book Shelf
 * Nhúng nguyên bản canonical source AshenPress (ThreeUI, Three.js r181)
 * đúng cấu trúc AshenPress.tsx: div.shader-frame >
 * div.threeui-background.ashen-press > iframe srcdoc sandbox. Template
 * standalone (không header/footer của theme) vì source là trải nghiệm
 * fullscreen fixed-canvas có header riêng.
 * Lưu ý kỹ thuật (đã kiểm chứng): bản TSX gốc dùng sandbox="allow-scripts"
 * (opaque origin), nhưng import-map của Three.js r181 không resolve được trong
 * iframe opaque-origin trên Chromium hiện tại (render trắng, không báo lỗi),
 * nên ở đây thêm allow-same-origin. Nội dung nhúng là file first-party của
 * chính theme nên không tăng mặt tấn công.
 *
 * @package mozlex
 */

declare( strict_types=1 );

$src_file = get_template_directory() . '/assets/ashen-press/ashen-press.html';
$src_doc   = file_exists( $src_file ) ? file_get_contents( $src_file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions -- đọc file cục bộ của theme.
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php the_title(); ?> — <?php bloginfo( 'name' ); ?></title>
<?php wp_head(); ?>
<style>
	/* Khung mount đúng usage đã cấu hình: div.shader-frame > <AshenPress /> */
	html, body { margin: 0; padding: 0; height: 100%; background: #c6ae8e; }
	.shader-frame { position: relative; width: 100%; height: 100vh; height: 100dvh; overflow: hidden; }
	.threeui-background { position: relative; width: 100%; height: 100%; min-width: 0; min-height: 0; overflow: hidden; }
	.threeui-background > iframe { position: absolute; inset: 0; display: block; width: 100%; height: 100%; border: 0; }
</style>
</head>
<body <?php body_class( 'ashen-press-page' ); ?>>
<div class="shader-frame">
	<div
		class="threeui-background ashen-press"
		id="ashen-press-host"
		role="group"
		aria-label="Interactive Ashen Press art book shelf"
		data-state="loading"
		style="position:relative;overflow:hidden;background:#c6ae8e;pointer-events:auto;"
	>
		<iframe
			id="ashen-press-frame"
			title="Ashen Press — The Art Book Shelf"
			srcdoc="<?php echo esc_attr( (string) $src_doc ); ?>"
			sandbox="allow-scripts allow-same-origin"
			loading="eager"
			style="position:absolute;inset:0;display:block;width:100%;height:100%;border:0;background:#c6ae8e;opacity:0;pointer-events:none;transition:opacity 240ms ease-out;"
		></iframe>
	</div>
</div>
<script>
(function () {
	// Tương đương logic mounted/ready của AshenPress.tsx:
	// mounted = hostVisible (IntersectionObserver, rootMargin 80px) && documentVisible.
	var host = document.getElementById('ashen-press-host');
	var frame = document.getElementById('ashen-press-frame');
	if (!host || !frame) return;

	var hostVisible = true;
	var documentVisible = !document.hidden;
	var ready = false;

	function state() {
		return (!hostVisible || !documentVisible) ? 'paused' : (ready ? 'ready' : 'loading');
	}
	function render() {
		var s = state();
		host.setAttribute('data-state', s);
		var show = (s === 'ready');
		frame.style.opacity = show ? '1' : '0';
		frame.style.pointerEvents = show ? 'auto' : 'none';
		// Tạm dừng iframe khi out-of-view giống unmount của React: gỡ srcdoc.
		if (s === 'paused' && frame.getAttribute('srcdoc')) {
			frame.setAttribute('data-srcdoc', frame.getAttribute('srcdoc'));
			frame.removeAttribute('srcdoc');
		} else if (s !== 'paused' && !frame.getAttribute('srcdoc') && frame.getAttribute('data-srcdoc')) {
			ready = false;
			frame.setAttribute('srcdoc', frame.getAttribute('data-srcdoc'));
		}
	}
	if ('IntersectionObserver' in window) {
		var observer = new IntersectionObserver(function (entries) {
			hostVisible = entries[0] ? entries[0].isIntersecting : true;
			render();
		}, { rootMargin: '80px' });
		observer.observe(host);
	}
	document.addEventListener('visibilitychange', function () {
		documentVisible = !document.hidden;
		render();
	});
	frame.addEventListener('load', function () {
		ready = true;
		render();
	});
	render();
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
