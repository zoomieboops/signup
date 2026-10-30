<?php
declare(strict_types=1);

// Preview only. The live index.html remains unchanged.
$library = dirname(__DIR__, 2) . '/shared';
$ready = is_file($library . '/src/Renderer.php') && is_file($library . '/src/PublicationConsumer.php');
if ($ready) {
    require_once $library . '/src/Renderer.php';
    require_once $library . '/src/PublicationConsumer.php';
}
$site = [
    'brand' => 'ZOOMIE BOOPS',
    'navigation' => [],
    'footer_brand' => 'ZOOMIE BOOPS',
    'footer_tagline' => 'Zoomies × boops = happiness',
    'footer_link_groups' => [],
];
// Activation requires private runtime credentials; do not commit tokens to Git.
$env = dirname(__DIR__) . '/private/.env';
if (is_file($env)) {
    foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        if (in_array($key, ['ZB_SIGNUP_CONFIG_ENABLED','ZB_SIGNUP_CONFIG_READ_TOKEN','ZB_SIGNUP_CONFIG_AUTO_APPROVE','ZB_SIGNUP_CONFIG_APPROVED_VERSION'], true)) {
            if (getenv($key) === false) putenv($key . '=' . trim($value, " \t\n\r\0\x0B\"'"));
        }
    }
}
if ($ready && getenv('ZB_SIGNUP_CONFIG_ENABLED') === '1') {
    $version = getenv('ZB_SIGNUP_CONFIG_APPROVED_VERSION');
    $settings = [
        'enabled' => true,
        'token' => getenv('ZB_SIGNUP_CONFIG_READ_TOKEN'),
        'cache_dir' => dirname(__DIR__) . '/private/cache',
        'approved_version' => is_string($version) && ctype_digit($version) ? (int) $version : null,
        'auto_approve' => getenv('ZB_SIGNUP_CONFIG_AUTO_APPROVE') === '1',
        'public_roots' => [__DIR__],
    ];
    $published = \ZoomieBoops\Shared\PublicationConsumer::read('signup', $settings);
    if ($published !== null) {
        $site['navigation'] = $published['navigation'];
        $site['brand'] = $published['header']['brand_text'] ?? $site['brand'];
        $site['footer_brand'] = $published['footer']['brand_text'] ?? $site['footer_brand'];
        $site['footer_tagline'] = $published['footer']['tagline'] ?? $site['footer_tagline'];
        $site['footer_link_groups'] = $published['footer']['link_groups'] ?? [];
    }
}
$header = $ready ? \ZoomieBoops\Shared\Renderer::render('header', $site) : '<header><strong>ZOOMIE BOOPS</strong></header>';
$footer = $ready ? \ZoomieBoops\Shared\Renderer::render('footer', $site) : '<footer>Zoomies × boops = happiness</footer>';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Coming Soon | Zoomie Boops (Preview)</title>
<style>
<?php if ($ready) { readfile($library . '/assets/css/shared.css'); } ?>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;flex-direction:column;background:#fff7ed;color:#713f32;font-family:Arial,sans-serif}
main{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 24px;text-align:center}
.content{max-width:620px}h1{font-size:clamp(36px,7vw,60px);line-height:1.12;margin:0 0 20px}
.tagline{font-size:22px;font-weight:600;margin-bottom:26px}.description{color:#80685d;font-size:17px;line-height:1.8;margin-bottom:36px}
.coming-soon{display:inline-block;background:#713f32;color:#fff;border-radius:40px;padding:15px 38px;font-weight:600;letter-spacing:1px}
.zb-footer__inner>div{display:flex;flex-wrap:wrap;align-items:center;gap:1rem}.zb-footer__inner>div p{margin:0}
.zb-skip{position:absolute;left:-9999px;top:auto}.zb-skip:focus{left:1rem;top:1rem;background:white;padding:.5rem;z-index:10}
</style>
</head>
<body>
<a class="zb-skip" href="#main">Skip to content</a>
<?= $header ?>
<main id="main"><div class="content">
<h1>Something pawsome<br>is baking!</h1>
<p class="tagline">fancy treats for fancy pets.</p>
<p class="description">We're getting ready to bring our homemade, thoughtfully crafted dog treats to your favorite four-legged friends.</p>
<div class="coming-soon">Coming Soon</div>
</div></main>
<?= $footer ?>
</body></html>
