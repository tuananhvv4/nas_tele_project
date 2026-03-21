<?php

declare(strict_types=1);

return [
    'name'     => $_ENV['APP_NAME']     ?? 'Telegram Bot Admin',
    'env'      => $_ENV['APP_ENV']      ?? 'production',
    'debug'    => ($_ENV['APP_DEBUG']   ?? 'false') === 'true',
    'url'      => $_ENV['APP_URL']      ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Ho_Chi_Minh',
];
