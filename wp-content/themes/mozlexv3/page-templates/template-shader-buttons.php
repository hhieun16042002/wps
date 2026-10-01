<?php
/**
 * Template Name: ShaderButtons — Star Portal
 * Nhúng nguyên bản canonical source ShaderButtons variant "star-portal" (ThreeUI)
 * đúng cấu trúc ShaderButtons.tsx / Scene:
 * div.shader-frame > div.threeui-background.shader-buttons.star-portal > iframe srcdoc.
 *
 * Component: ShaderButtons
 * Variant: star-portal
 * Runtime: Raw WebGL + Canvas 2D + CSS
 * Source revision: SHA-256 6f56c4f91814
 *
 * @package mozlex
 */

declare( strict_types=1 );

$src_file = get_template_directory() . '/assets/shader-buttons/star-portal.html';
$src_doc  = file_exists( $src_file ) ? file_get_contents( $src_file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions -- đọc file cục bộ của theme.
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php the_title(); ?> — <?php bloginfo( 'name' ); ?></title>
<?php wp_head(); ?>
<style>
	html, body { margin: 0; padding: 0; height: 100%; background: #0d0a12; }
	.shader-frame { position: relative; width: 100%; height: 100vh; height: 100dvh; overflow: hidden; background: #0d0a12; }
	.threeui-background { position: relative; width: 100%; height: 100%; min-width: 0; min-height: 0; overflow: hidden; }
	.threeui-background > iframe { position: absolute; inset: 0; display: block; width: 100%; height: 100%; border: 0; }
</style>
</head>
<body <?php body_class( 'shader-buttons-page star-portal-page' ); ?>>
<div class="shader-frame">
	<div
		class="threeui-background shader-buttons star-portal"
		id="shader-buttons-host"
		role="group"
		aria-label="Interactive ThreeUI ShaderButtons Star Portal"
		data-state="loading"
		style="position:relative;overflow:hidden;background:#0d0a12;pointer-events:auto;"
	>
		<iframe
			id="shader-buttons-frame"
			title="ShaderButtons — Star Portal"
			srcdoc="<?php echo esc_attr( (string) $src_doc ); ?>"
			sandbox="allow-scripts allow-same-origin"
			loading="eager"
			style="position:absolute;inset:0;display:block;width:100%;height:100%;border:0;background:#0d0a12;opacity:0;pointer-events:none;transition:opacity 240ms ease-out;"
		></iframe>
	</div>
</div>
<script>
(function () {
	var host = document.getElementById('shader-buttons-host');
	var frame = document.getElementById('shader-buttons-frame');
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
