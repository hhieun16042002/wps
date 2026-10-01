<?php
/**
 * Template Name: ThreeDPaper — 3D Nocturne Certificate
 * Nhúng nguyên bản canonical source ThreeDPaper variant "original" (ThreeUI,
 * Three.js r149 nhúng sẵn) đúng cấu trúc ThreeDPaper.tsx:
 * div.threeui-background.three-d-paper > iframe srcdoc sandbox="allow-scripts".
 * Standalone (không header/footer của theme) vì source là trải nghiệm
 * fullscreen fixed-canvas có layer riêng.
 *
 * @package mozlex
 */

declare( strict_types=1 );

$src_file = get_template_directory() . '/assets/three-d-paper/3d-paper.html';
$src_doc  = file_exists( $src_file ) ? file_get_contents( $src_file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions -- đọc file cục bộ của theme.
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php the_title(); ?> — <?php bloginfo( 'name' ); ?></title>
<?php wp_head(); ?>
<style>
	html, body { margin: 0; padding: 0; height: 100%; background: #08080a; }
	.shader-frame { position: relative; width: 100%; height: 100vh; height: 100dvh; overflow: hidden; }
	.threeui-background { position: relative; width: 100%; height: 100%; min-width: 0; min-height: 0; overflow: hidden; }
	.threeui-background > iframe { position: absolute; inset: 0; display: block; width: 100%; height: 100%; border: 0; }
</style>
</head>
<body <?php body_class( 'three-d-paper-page' ); ?>>
<div class="shader-frame">
	<div
		class="threeui-background three-d-paper"
		id="three-d-paper-host"
		role="group"
		aria-label="Interactive translucent 3D paper certificate"
		data-state="loading"
		style="position:relative;overflow:hidden;background:#08080a;pointer-events:auto;"
	>
		<iframe
			id="three-d-paper-frame"
			title="3D Paper"
			srcdoc="<?php echo esc_attr( (string) $src_doc ); ?>"
			sandbox="allow-scripts"
			loading="eager"
			style="position:absolute;inset:0;display:block;width:100%;height:100%;border:0;background:#08080a;opacity:0;pointer-events:none;transition:opacity 240ms ease-out;"
		></iframe>
	</div>
</div>
<script>
(function () {
	/* Tương đương logic mounted/ready của ThreeDPaper.tsx. */
	var host = document.getElementById('three-d-paper-host');
	var frame = document.getElementById('three-d-paper-frame');
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
