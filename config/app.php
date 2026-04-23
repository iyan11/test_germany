<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'ysell' => [
        'base_url' => Env::getString('YSELL_BASE_URL'),
        'bearer_token' => Env::getString('YSELL_BEARER_TOKEN'),
        'timeout' => Env::getFloat('YSELL_TIMEOUT', 10),
    ],
    'pricing' => [
        'default_price' => Env::getFloat('DEFAULT_PRICE', 12.99),
        'default_min_price' => Env::getFloat('DEFAULT_MIN_PRICE', 7.99),
        'markup' => Env::getFloat('PRICE_MARKUP', 2.8),
    ],
    'ebay' => [
        'fallback_category_id' => Env::getString('FALLBACK_CATEGORY_ID', '262363'),
        'location' => Env::getString('LOCATION', 'Bremen'),
        'shipping_profile_name' => Env::getString('SHIPPING_PROFILE_NAME', 'DHL-Standardvorlage - (ID: 76770166018)'),
        'return_profile_name' => Env::getString('RETURN_PROFILE_NAME', 'Standard-Widerruf 30 Tage für eBay Plus - (ID: 247364069018)'),
        'payment_profile_name' => Env::getString('PAYMENT_PROFILE_NAME', 'UK Gutschein'),
    ],
    'description_template_path' => __DIR__ . '/../templates/description.html',
];
