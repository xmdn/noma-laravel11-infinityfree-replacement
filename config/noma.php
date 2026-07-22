<?php

return [
    'auto_verify_emails' => (bool) env(
        'NOMA_AUTO_VERIFY_EMAILS',
        in_array(env('APP_ENV'), ['local', 'development'], true),
    ),
    'cart_ttl_minutes' => (int) env('NOMA_CART_TTL_MINUTES', 2),
    'customer_access_ttl_minutes' => (int) env('NOMA_CUSTOMER_ACCESS_TTL_MINUTES', 3),
];
