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

// ── Logger ────────────────────────────────────────────────────────────────────
function wlog(string $level, string $message, array $context = []): void
{
    $logDir  = BASE_PATH . '/storage/logs';
    $logFile = $logDir . '/webhook-' . date('Y-m-d') . '.log';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    $line = sprintf(
        "[%s] [%s] %s%s\n",
        date('Y-m-d H:i:s'),
        strtoupper($level),
        $message,
        $context ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : ''
    );
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

// Bootstrap env + DB only (no session / router needed)
$app = Application::getInstance(BASE_PATH);
$app->bootstrapLite();

// ── Validate webhook secret ───────────────────────────────────────────────────
$botId         = (int)($_GET['bot_id'] ?? 0);
$webhookToken  = $_GET['token'] ?? '';

wlog('info', 'Webhook hit', ['bot_id' => $botId, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);

if (!$botId) {
    wlog('warn', 'Missing bot_id');
    http_response_code(400);
    exit('Missing bot_id');
}

$bot = Bot::find($botId);
if (!$bot) {
    wlog('warn', 'Bot not found in DB', ['bot_id' => $botId]);
    http_response_code(404);
    exit('Bot not found');
}
if (($bot['status'] ?? '') !== 'active') {
    wlog('warn', 'Bot not active', ['bot_id' => $botId, 'status' => $bot['status'] ?? 'null']);
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

wlog('info', 'Raw input', ['body' => mb_substr($input, 0, 500)]);

if (!$update) {
    wlog('warn', 'Empty or invalid JSON body');
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

    if (!$message) {
        wlog('info', 'Update has no message', ['update_keys' => array_keys($update)]);
        exit;
    }

    $from = $message['from'] ?? ($update['callback_query']['from'] ?? null);
    if (!$from) {
        wlog('warn', 'Message has no from field');
        exit;
    }

    // Register or update user
    $tgUser = TelegramUser::findOrCreate($botId, [
        'telegram_id' => $from['id'],
        'username'    => $from['username'] ?? null,
        'first_name'  => $from['first_name'] ?? '',
        'last_name'   => $from['last_name'] ?? null,
        'language'    => $from['language_code'] ?? null,
    ]);

    if ($tgUser['is_banned']) {
        wlog('info', 'Banned user ignored', ['telegram_id' => $from['id']]);
        exit;
    }

    $chatId = $message['chat']['id'];
    $text   = trim($message['text'] ?? '');

    wlog('info', 'Processing message', ['chat_id' => $chatId, 'text' => $text]);

    // Route commands
    match (true) {
        str_starts_with($text, '/start')   => handleStart($telegram, $chatId, $botId, $tgUser),
        str_starts_with($text, '/catalog') => handleCatalog($telegram, $chatId, $botId),
        str_starts_with($text, '/order')   => handleOrders($telegram, $chatId, $botId, $tgUser),
        str_starts_with($text, '/help')    => handleHelp($telegram, $chatId, $botId),
        default                            => handleDefault($telegram, $chatId, $botId, $text, $tgUser),
    };

} catch (\Throwable $e) {
    wlog('error', $e->getMessage(), ['file' => $e->getFile() . ':' . $e->getLine(), 'trace' => mb_substr($e->getTraceAsString(), 0, 800)]);
    error_log('[Webhook Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
}

// ── Command handlers ──────────────────────────────────────────────────────────

function handleStart(Api $tg, $chatId, $botId, array $user): void
{
    $chatId  = (int) $chatId;
    $botId   = (int) $botId;
    $welcome  = \App\Models\Setting::get($botId, 'welcome_message', 'Chào mừng bạn đến cửa hàng của chúng tôi! 🛍️');
    $shopName = \App\Models\Setting::get($botId, 'shop_name', 'Cửa hàng');

    $tg->sendMessage([
        'chat_id'    => $chatId,
        'text'       => '<b>' . htmlspecialchars($shopName, ENT_QUOTES) . "</b>\n\n" . htmlspecialchars($welcome, ENT_QUOTES) . "\n\nDùng /catalog để xem sản phẩm.",
        'parse_mode' => 'HTML',
    ]);
}

function handleCatalog(Api $tg, $chatId, $botId): void
{
    $chatId = (int) $chatId;
    $botId  = (int) $botId;
    $categories = \App\Models\Category::forBot($botId, onlyActive: true);

    if (empty($categories)) {
        $tg->sendMessage(['chat_id' => $chatId, 'text' => 'Chưa có danh mục nào.']);
        return;
    }

    $keyboard = array_map(fn($cat) => [['text' => $cat['name']]], $categories);

    $tg->sendMessage([
        'chat_id'      => $chatId,
        'text'         => "📋 *Danh mục sản phẩm*\n\nVui lòng chọn danh mục:",
        'parse_mode'   => 'Markdown',
        'reply_markup' => json_encode([
            'keyboard'          => $keyboard,
            'resize_keyboard'   => true,
            'one_time_keyboard' => true,
        ]),
    ]);
}

function handleOrders(Api $tg, $chatId, $botId, array $user): void
{
    $chatId = (int) $chatId;
    $botId  = (int) $botId;
    $tg->sendMessage([
        'chat_id' => $chatId,
        'text'    => '📦 Tính năng xem đơn hàng sẽ sớm ra mắt!',
    ]);
}

function handleHelp(Api $tg, $chatId, $botId): void
{
    $chatId  = (int) $chatId;
    $botId   = (int) $botId;
    $support = \App\Models\Setting::get($botId, 'support_contact', '');
    $text    = "ℹ️ *Hướng dẫn sử dụng*\n\n/start - Trang chủ\n/catalog - Xem sản phẩm\n/order - Xem đơn hàng\n/help - Trợ giúp";
    if ($support) $text .= "\n\n📞 Hỗ trợ: {$support}";

    $tg->sendMessage(['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'Markdown']);
}

function handleDefault(Api $tg, $chatId, $botId, string $text, array $user): void
{
    $chatId = (int) $chatId;
    $botId  = (int) $botId;
    // Could handle inline product search, order flow, etc.
    $tg->sendMessage([
        'chat_id' => $chatId,
        'text'    => 'Gõ /catalog để xem sản phẩm hoặc /help để xem trợ giúp.',
    ]);
}
