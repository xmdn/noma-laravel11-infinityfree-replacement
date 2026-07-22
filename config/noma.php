<?php

return [
    'shop_url_mode' => env('NOMA_SHOP_URL_MODE', 'auto') === 'auto'
        ? (env('CODESPACE_NAME') ? 'path' : 'subdomain')
        : env('NOMA_SHOP_URL_MODE', 'auto'),
    'auto_verify_emails' => (bool) env(
        'NOMA_AUTO_VERIFY_EMAILS',
        in_array(env('APP_ENV'), ['local', 'development'], true),
    ),
    'cart_ttl_minutes' => (int) env('NOMA_CART_TTL_MINUTES', 2),
    'customer_access_ttl_minutes' => (int) env('NOMA_CUSTOMER_ACCESS_TTL_MINUTES', 3),
];
