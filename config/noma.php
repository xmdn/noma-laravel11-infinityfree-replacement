<?php

$shopUrlMode = env('NOMA_SHOP_URL_MODE', 'auto');
$appHost = strtolower((string) parse_url((string) env('APP_URL', ''), PHP_URL_HOST));
$codespacesDomain = trim((string) env('GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN'));

if ($shopUrlMode === 'auto') {
    $shopUrlMode = env('CODESPACE_NAME') || $codespacesDomain !== '' || str_ends_with($appHost, '.app.github.dev')
        ? 'path'
        : 'subdomain';
}

return [
    'shop_url_mode' => $shopUrlMode,
    'auto_verify_emails' => (bool) env(
        'NOMA_AUTO_VERIFY_EMAILS',
        in_array(env('APP_ENV'), ['local', 'development'], true),
    ),
    'cart_ttl_minutes' => (int) env('NOMA_CART_TTL_MINUTES', 2),
    'customer_access_ttl_minutes' => (int) env('NOMA_CUSTOMER_ACCESS_TTL_MINUTES', 3),
];
