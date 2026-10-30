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
<title>Signup Test | Zoomie Boops</title>
<style>
<?php if ($ready) { readfile($library . '/assets/css/shared.css'); } ?>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;flex-direction:column;background:#fff7ed;color:#713f32;font-family:Arial,sans-serif}
.zb-header,.zb-footer{width:100%;min-width:0}
.zb-container{width:min(calc(100% - 2rem),72rem);max-width:100%;margin-inline:auto}
main{flex:1;width:100%;padding:3rem 1rem}
.signup-panel{max-width:34rem;margin:0 auto}
h1{font-size:clamp(2rem,5vw,2.75rem);line-height:1.2;margin:0 0 .75rem}
p{line-height:1.6}
form{display:grid;gap:1rem;margin-top:2rem}
.field{display:grid;gap:.4rem}
.field input{width:100%;padding:.8rem;border:1px solid #977b6c;border-radius:.35rem;background:white;color:#33231d;font:inherit}
.consent{display:flex;gap:.7rem;align-items:flex-start;line-height:1.5}
.consent input{margin-top:.3rem;width:1.1rem;height:1.1rem;flex:none}
button{border:0;border-radius:.4rem;padding:.85rem 1.25rem;background:#713f32;color:white;font:inherit;font-weight:700;cursor:pointer}
button:focus-visible,input:focus-visible{outline:3px solid #215ca3;outline-offset:3px}
.notice{border-left:4px solid #713f32;padding:.65rem 1rem;background:#f4e9de}
#result[hidden]{display:none}
.zb-footer__inner>div{display:flex;flex-wrap:wrap;align-items:center;gap:1rem}
.zb-footer__inner>div p{margin:0}
.zb-skip{position:absolute;left:-9999px;top:auto}
.zb-skip:focus{left:1rem;top:1rem;background:white;padding:.5rem;z-index:10}
</style>
</head>
<body>
<a class="zb-skip" href="#main">Skip to content</a>
<?= $header ?>
<main id="main">
<div class="signup-panel">
<h1>Join the Boop Troop</h1>
<p>Sign up for Zoomie Boops news and updates.</p>
<p class="notice"><strong>Test page only.</strong> This form does not subscribe you, send email, or save your information.</p>
<form id="test-signup">
<div class="field"><label for="first-name">First name</label><input id="first-name" name="first_name" autocomplete="given-name" maxlength="80" required></div>
<div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" maxlength="254" required></div>
<label class="consent"><input id="consent" name="consent" type="checkbox" required><span>I agree to receive marketing emails from Zoomie Boops. I understand I can unsubscribe at any time.</span></label>
<button type="submit">Test signup</button>
</form>
<p id="result" role="status" aria-live="polite" hidden>Test submission successful. No information was saved or sent.</p>
</div>
</main>
<?= $footer ?>
<script>
document.getElementById('test-signup').addEventListener('submit',function(event){
  event.preventDefault();
  if (!this.reportValidity()) return;
  document.getElementById('result').hidden=false;
  this.reset();
});
</script>
</body>
</html>
