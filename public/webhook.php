<?php

declare(strict_types=1);

/**
 * Telegram Webhook Entry Point
 *
 * URL: https://yourdomain.com/webhook.php?bot_id=X&token=SECRET
 *
 * Register per bot via:
 *   https://api.telegram.org/bot{BOT_TOKEN}/setWebhook?url=https://yourdomain.com/webhook.php?bot_id=X%26token={WEBHOOK_SECRET}
 */

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use Core\Application;
use Core\Database;
use App\Models\Bot;
use App\Models\TelegramUser;
use App\Models\BroadcastLog;
use Telegram\Bot\Api;

// Bootstrap env + DB only (no session / router needed)
$app = Application::getInstance(BASE_PATH);
$app->bootstrapLite();

// ── Validate webhook secret ───────────────────────────────────────────────────
$botId         = (int)($_GET['bot_id'] ?? 0);
$webhookToken  = $_GET['token'] ?? '';

if (!$botId) {
    http_response_code(400);
    exit('Missing bot_id');
}

$bot = Bot::find($botId);
if (!$bot || !$bot['is_active']) {
    http_response_code(404);
    exit('Bot not found');
}

// Optional: validate secret token stored in settings
// $expectedToken = \App\Models\Setting::get($botId, 'webhook_secret', '');
// if ($expectedToken && !hash_equals($expectedToken, $webhookToken)) {
//     http_response_code(403);
//     exit('Forbidden');
// }

// ── Parse update ─────────────────────────────────────────────────────────────
$input  = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    http_response_code(200); // Always return 200 to Telegram
    exit;
}

// Respond immediately so Telegram doesn't retry
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ok' => true]);

// Flush output
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    ob_end_flush();
}

// ── Process update ────────────────────────────────────────────────────────────
try {
    $telegram = new Api($bot['bot_token']);
    $message  = $update['message'] ?? $update['callback_query']['message'] ?? null;

    if (!$message) exit;

    $from = $message['from'] ?? ($update['callback_query']['from'] ?? null);
    if (!$from) exit;

    // Register or update user
    $tgUser = TelegramUser::findOrCreate($botId, [
        'telegram_id' => $from['id'],
        'username'    => $from['username'] ?? null,
        'first_name'  => $from['first_name'] ?? '',
        'last_name'   => $from['last_name'] ?? null,
        'language'    => $from['language_code'] ?? null,
    ]);

    if ($tgUser['is_banned']) exit;

    $chatId = $message['chat']['id'];
    $text   = trim($message['text'] ?? '');

    // Route commands
    match (true) {
        str_starts_with($text, '/start')   => handleStart($telegram, $chatId, $botId, $tgUser),
        str_starts_with($text, '/catalog') => handleCatalog($telegram, $chatId, $botId),
        str_starts_with($text, '/order')   => handleOrders($telegram, $chatId, $botId, $tgUser),
        str_starts_with($text, '/help')    => handleHelp($telegram, $chatId, $botId),
        default                            => handleDefault($telegram, $chatId, $botId, $text, $tgUser),
    };

} catch (\Throwable $e) {
    error_log('[Webhook Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
}

// ── Command handlers ──────────────────────────────────────────────────────────

function handleStart(Api $tg, int $chatId, int $botId, array $user): void
{
    $welcome = \App\Models\Setting::get($botId, 'welcome_message', 'Chào mừng bạn đến cửa hàng của chúng tôi! 🛍️');
    $shopName = \App\Models\Setting::get($botId, 'shop_name', 'Cửa hàng');

    $tg->sendMessage([
        'chat_id'    => $chatId,
        'text'       => "*{$shopName}*\n\n{$welcome}\n\nDùng /catalog để xem sản phẩm.",
        'parse_mode' => 'Markdown',
    ]);
}

function handleCatalog(Api $tg, int $chatId, int $botId): void
{
    $categories = \App\Models\Category::forBot($botId, onlyActive: true);

    if (empty($categories)) {
        $tg->sendMessage(['chat_id' => $chatId, 'text' => 'Chưa có sản phẩm nào.']);
        return;
    }

    $keyboard = array_map(fn($cat) => [['text' => $cat['name']]], $categories);

    $tg->sendMessage([
        'chat_id'      => $chatId,
        'text'         => '📋 *Danh mục sản phẩm*\n\nVui lòng chọn danh mục:',
        'parse_mode'   => 'Markdown',
        'reply_markup' => json_encode([
            'keyboard'          => $keyboard,
            'resize_keyboard'   => true,
            'one_time_keyboard' => true,
        ]),
    ]);
}

function handleOrders(Api $tg, int $chatId, int $botId, array $user): void
{
    $tg->sendMessage([
        'chat_id' => $chatId,
        'text'    => '📦 Tính năng xem đơn hàng sẽ sớm ra mắt!',
    ]);
}

function handleHelp(Api $tg, int $chatId, int $botId): void
{
    $support = \App\Models\Setting::get($botId, 'support_contact', '');
    $text    = "ℹ️ *Hướng dẫn sử dụng*\n\n/start - Trang chủ\n/catalog - Xem sản phẩm\n/order - Xem đơn hàng\n/help - Trợ giúp";
    if ($support) $text .= "\n\n📞 Hỗ trợ: {$support}";

    $tg->sendMessage(['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'Markdown']);
}

function handleDefault(Api $tg, int $chatId, int $botId, string $text, array $user): void
{
    // Could handle inline product search, order flow, etc.
    $tg->sendMessage([
        'chat_id' => $chatId,
        'text'    => 'Gõ /catalog để xem sản phẩm hoặc /help để xem trợ giúp.',
    ]);
}
