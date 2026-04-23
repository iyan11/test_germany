<?php

declare(strict_types=1);

return [
    'ysell' => [
        'base_url' => getenv('YSELL_BASE_URL') ?: '',
        'bearer_token' => getenv('YSELL_BEARER_TOKEN') ?: '',
        'timeout' => (float) (getenv('YSELL_TIMEOUT') ?: 10),
    ],
    'pricing' => [
        'default_price' => (float) (getenv('DEFAULT_PRICE') ?: 12.99),
        'default_min_price' => (float) (getenv('DEFAULT_MIN_PRICE') ?: 7.99),
        'markup' => (float) (getenv('PRICE_MARKUP') ?: 2.8),
    ],
    'ebay' => [
        'fallback_category_id' => (string) (getenv('FALLBACK_CATEGORY_ID') ?: '262363'),
        'location' => (string) (getenv('LOCATION') ?: 'Bremen'),
        'shipping_profile_name' => (string) (getenv('SHIPPING_PROFILE_NAME') ?: 'DHL-Standardvorlage - (ID: 76770166018)'),
        'return_profile_name' => (string) (getenv('RETURN_PROFILE_NAME') ?: 'Standard-Widerruf 30 Tage für eBay Plus - (ID: 247364069018)'),
        'payment_profile_name' => (string) (getenv('PAYMENT_PROFILE_NAME') ?: 'UK Gutschein'),
    ],
    'description_template_path' => __DIR__ . '/../templates/description.html',
];
