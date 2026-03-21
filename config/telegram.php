<?php

declare(strict_types=1);

return [
    'default_token' => $_ENV['TELEGRAM_BOT_TOKEN'] ?? '',
    // Per-bot tokens are stored in the `bots` table, not here
];
