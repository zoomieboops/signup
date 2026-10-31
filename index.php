<?php
declare(strict_types=1);

// Shared public navigation is published once by Admin for all sites.
$library = dirname(__DIR__, 2) . '/shared';
$envFile = dirname(__DIR__) . '/private/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        if (in_array($key, ['ZB_HOME_CONFIG_ENABLED', 'ZB_HOME_CONFIG_READ_TOKEN', 'ZB_HOME_CONFIG_APPROVED_VERSION', 'ZB_HOME_CONFIG_AUTO_APPROVE'], true) && getenv($key) === false) {
            putenv($key . '=' . trim($value, " \t\n\r\0\x0B\"'"));
        }
    }
}
if (!is_file($library . '/src/Renderer.php') || !is_file($library . '/src/PublicationConsumer.php')) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Signup temporarily unavailable.';
    exit;
}
require_once $library . '/src/Renderer.php';
require_once $library . '/src/PublicationConsumer.php';
$version = getenv('ZB_HOME_CONFIG_APPROVED_VERSION');
$published = \ZoomieBoops\Shared\PublicationConsumer::read('main', [
    'enabled' => getenv('ZB_HOME_CONFIG_ENABLED') === '1',
    'token' => getenv('ZB_HOME_CONFIG_READ_TOKEN'),
    'cache_dir' => dirname(__DIR__) . '/private/cache',
    'approved_version' => is_string($version) && ctype_digit($version) ? (int) $version : null,
    'auto_approve' => getenv('ZB_HOME_CONFIG_AUTO_APPROVE') === '1',
    'public_roots' => [__DIR__],
]);
if ($published === null) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Signup temporarily unavailable.';
    exit;
}
$site = [
    'brand' => $published['header']['brand_text'] ?? 'Zoomie Boops',
    'navigation' => $published['navigation'],
    'footer_brand' => $published['footer']['brand_text'] ?? 'Zoomie Boops',
    'footer_tagline' => $published['footer']['tagline'] ?? 'Fancy treats for fancy pets.',
    'footer_link_groups' => $published['footer']['link_groups'] ?? [],
];
$header = \ZoomieBoops\Shared\Renderer::render('header', $site, ['current_url' => 'https://signup.zoomieboops.com/']);
$footer = \ZoomieBoops\Shared\Renderer::render('footer', $site);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Join the Zoomie Boops mailing list for news and updates.">
<title>Join the Boop Crew | Zoomie Boops</title>
<style><?php readfile($library . '/assets/css/shared.css'); ?></style>
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;flex-direction:column;font-family:Arial,sans-serif}
main{flex:1;width:min(100% - 2rem,38rem);margin:0 auto;padding:3rem 0 4rem}
h1{font-size:clamp(2rem,5vw,3rem);line-height:1.2;margin:0 0 .75rem}
p{line-height:1.6}
form{display:grid;gap:1rem;margin-top:2rem}
.field{display:grid;gap:.4rem}
.field input{width:100%;padding:.8rem;border:1px solid #777;border-radius:.35rem;font:inherit}
.consent{display:flex;gap:.7rem;align-items:flex-start;line-height:1.5}
.consent input{margin-top:.3rem;width:1.1rem;height:1.1rem;flex:none}
button{border:0;border-radius:.4rem;padding:.85rem 1.25rem;background:#713f32;color:#fff;font:inherit;font-weight:700;cursor:pointer}
button:focus-visible,input:focus-visible{outline:3px solid #215ca3;outline-offset:3px}
.notice{padding:.65rem 1rem;border-left:4px solid #713f32;background:#f4e9de}
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
<h1>Join the Boop Crew</h1>
<p>Sign up for Zoomie Boops news and updates.</p>
<p class="notice"><strong>Preview only.</strong> Signup is not yet connected. No information entered here will be saved or sent.</p>
<form id="signup-form">
<div class="field"><label for="first-name">First name</label><input id="first-name" name="first_name" autocomplete="given-name" maxlength="80" required></div>
<div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" maxlength="254" required></div>
<label class="consent"><input id="consent" name="consent" type="checkbox" required><span>I agree to receive marketing emails from Zoomie Boops. I understand I can unsubscribe at any time.</span></label>
<button type="submit">Test signup</button>
</form>
<p id="result" role="status" aria-live="polite" hidden>Form validation successful. Your information was not saved or sent.</p>
</main>
<?= $footer ?>
<script>
document.getElementById('signup-form').addEventListener('submit', function(event) {
  event.preventDefault();
  if (!this.reportValidity()) return;
  document.getElementById('result').hidden = false;
  this.reset();
});
</script>
</body>
</html>
