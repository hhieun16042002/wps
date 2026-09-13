<?php
/**
 * 404 — full-viewport, video background, Geist Mono
 *
 * @package mozlex
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>404 — Not Found</title>
<style>
@font-face {
  font-family: "Geist Mono:SemiBold";
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("https://static.figma.com/font/GeistMono_wght__1") format("woff2");
}
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }
</style>
<?php wp_head(); ?>
</head>
<body style="margin:0;">
<main aria-label="404" style="position:relative; min-height:100svh; width:100%; background:black; overflow-x:hidden;">
  <video autoplay loop muted playsinline aria-hidden="true" style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:1;">
    <source src="https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260801_001207_ec20d138-aa45-4b2b-ab8c-bdc71607f240.mp4" type="video/mp4">
  </video>

  <header aria-label="LGPSM" style="position:absolute; top:80px; left:50%; transform:translateX(-50%); width:233px; height:40px; display:flex; align-items:center; z-index:2;">
    <svg viewBox="0 0 54 40" fill="none" aria-hidden="true" style="width:54px; height:40px; flex-shrink:0; display:block;">
      <path d="M38 0H26V12H38V0Z" fill="white"/><path d="M54 12H38V28H54V12Z" fill="white"/><path d="M38 28H26V40H38V28Z" fill="white"/><path d="M26 12H16V22H26V12Z" fill="white"/><path d="M16 22H8V30H16V22Z" fill="white"/><path d="M16 2H6V12H16V2Z" fill="white"/><path d="M6 12H0V18H6V12Z" fill="white"/>
    </svg>
    <div style="margin-left:14px; width:164.311px; height:100px; display:flex; align-items:center;">
      <svg viewBox="0 0 164.311 100" fill="none" aria-hidden="true" style="width:164.311px; height:100px; display:block;">
        <path d="M122.498 37.4573H131.321L139.533 51.6222L147.772 37.4573H156.595V56.0604H152.449V37.6433L141.739 56.0604H137.354L126.617 37.6433V56.0604H122.498V37.4573ZM95.921 48.8317C92.785 48.8317 90.261 46.307 90.261 43.1445C90.261 40.0086 92.785 37.4573 95.921 37.4573H119.972V41.6031H95.921C95.071 41.6031 94.38 42.2941 94.38 43.1445C94.38 44.0215 95.071 44.7125 95.921 44.7125H114.285C117.421 44.7125 119.972 47.2372 119.972 50.3997C119.972 53.5357 117.421 56.0604 114.285 56.0604H90.261V51.9411H114.285C115.136 51.9411 115.827 51.2501 115.827 50.3997C115.827 49.5227 115.136 48.8317 114.285 48.8317H95.921ZM80.857 37.4573C84.843 37.4573 88.086 40.6995 88.086 44.7125C88.086 48.6989 84.843 51.9411 80.857 51.9411H62.254V56.0604H58.135V37.4573H80.857ZM80.83 47.7953C82.558 47.7953 83.94 46.4133 83.94 44.7125C83.94 42.985 82.558 41.6031 80.83 41.6031H62.254V47.7953H80.83ZM35.975 41.6031C33.105 41.6031 30.7927 43.9152 30.7927 46.7588C30.7927 49.629 33.105 51.9411 35.975 51.9411H51.336V48.6989H35.576V44.5796H55.482V56.0604H35.975C30.8192 56.0604 26.6734 51.9145 26.6734 46.7588C26.6734 41.6297 30.8192 37.4573 35.975 37.4573H55.482V41.6031H35.975ZM0 56.0604V37.4573H4.1192V51.9411H24.9281V56.0604H0ZM164.311 36.4177C164.311 37.7529 163.228 38.8354 161.893 38.8354C160.558 38.8354 159.475 37.7529 159.475 36.4177C159.475 35.0824 160.558 34 161.893 34C163.228 34 164.311 35.0824 164.311 36.4177Z" fill="white"/>
      </svg>
    </div>
  </header>

  <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:483px; display:flex; flex-direction:column; align-items:center; text-align:center; gap:44px; z-index:2;">
    <h1 style="margin:0; font-family:'Geist Mono:SemiBold'; font-weight:600; font-size:295.751px; line-height:1.1; letter-spacing:-24.6459px; text-align:center; background:linear-gradient(247.3282658084845deg, rgb(255,255,255) 2.5334%, rgba(255,255,255,0.4) 93.612%); -webkit-background-clip:text; background-clip:text; color:transparent; -webkit-text-fill-color:transparent; padding-bottom:18px; height:auto; min-height:0; width:100%; display:block;">404</h1>
    <div aria-hidden="true" style="width:425px; height:1px; background:white; flex-shrink:0;"></div>
    <p style="margin:0; font-family:'Geist Mono:SemiBold'; font-weight:600; font-size:24px; line-height:1.1; letter-spacing:-2px; color:white; text-align:center; width:100%; max-width:483px;">The path may be broken, but the journey isn't. Let's get you back.</p>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:inline-flex; align-items:center; justify-content:center; gap:8px; margin-top:4px; padding:12px 28px; border:1px solid rgba(255,255,255,0.9); border-radius:999px; color:white; text-decoration:none; font-family:'Geist Mono:SemiBold'; font-weight:600; font-size:14px; letter-spacing:-0.5px; background:rgba(255,255,255,0.08); backdrop-filter:blur(6px); transition: all 200ms ease;">← Quay lại trang chủ</a>
  </div>

  <style>
    @media (max-width: 640px) {
      header[aria-label="LGPSM"] { top: 32px !important; transform: translateX(-50%) scale(0.75) !important; transform-origin: center top !important; }
      div[style*="translate(-50%, -50%)"] { width: min(calc(100% - 40px), 360px) !important; gap: 28px !important; }
      div[style*="translate(-50%, -50%)"] h1 { font-size: clamp(140px, 52vw, 200px) !important; letter-spacing: -0.09em !important; height: auto !important; min-height: 0 !important; padding-bottom: 10px !important; }
      div[style*="translate(-50%, -50%)"] div[aria-hidden="true"] { width: 100% !important; }
      div[style*="translate(-50%, -50%)"] p { font-size: clamp(16px, 4.5vw, 20px) !important; letter-spacing: -1.3px !important; }
    }
  </style>
</main>
<?php wp_footer(); ?>
</body>
</html>
