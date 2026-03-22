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

    // Handle inline button taps (callback_query)
    if (isset($update['callback_query'])) {
        $cq       = $update['callback_query'];
        $cbChatId = (int) ($cq['message']['chat']['id'] ?? 0);
        $cbMsgId  = (int) ($cq['message']['message_id'] ?? 0) ?: null;
        $cbData   = $cq['data'] ?? '';
        try { $telegram->answerCallbackQuery(['callback_query_id' => $cq['id']]); } catch (\Throwable) {}

        wlog('info', 'Callback query', ['data' => $cbData, 'chat_id' => $cbChatId]);

        if (str_starts_with($cbData, 'cat_')) {
            handleCategoryProducts($telegram, $cbChatId, $botId, (int) substr($cbData, 4), $cbMsgId);
        } elseif (str_starts_with($cbData, 'prod_')) {
            handleProductDetail($telegram, $cbChatId, $botId, (int) substr($cbData, 5), $cbMsgId);
        } elseif (str_starts_with($cbData, 'buy_')) {
            tgSend($telegram, $cbChatId, $cbMsgId, [
                'text'         => '🛒 Tính năng mua hàng sẽ sớm ra mắt!',
                'reply_markup' => json_encode(['inline_keyboard' => [[['text' => '⬅️ Quay lại', 'callback_data' => 'menu_catalog']]]]),
            ]);
        } elseif ($cbData === 'menu_catalog' || $cbData === 'menu_refresh') {
            handleCatalog($telegram, $cbChatId, $botId, $cbMsgId);
        } elseif ($cbData === 'menu_orders') {
            handleOrders($telegram, $cbChatId, $botId, $tgUser, $cbMsgId);
        } elseif ($cbData === 'menu_help') {
            handleHelp($telegram, $cbChatId, $botId, $cbMsgId);
        } elseif ($cbData === 'menu_search') {
            handleSearch($telegram, $cbChatId, $botId, $cbMsgId);
        } elseif ($cbData === 'menu_start') {
            handleStart($telegram, $cbChatId, $botId, $tgUser, $cbMsgId);
        }
        exit;
    }

    $chatId = $message['chat']['id'];
    $text   = trim($message['text'] ?? '');

    wlog('info', 'Processing message', ['chat_id' => $chatId, 'text' => $text]);

    // Route commands and persistent keyboard buttons
    match (true) {
        str_starts_with($text, '/start')        => handleStart($telegram, $chatId, $botId, $tgUser),
        str_starts_with($text, '/catalog')      => handleCatalog($telegram, $chatId, $botId),
        str_starts_with($text, '/order')        => handleOrders($telegram, $chatId, $botId, $tgUser),
        str_starts_with($text, '/help')         => handleHelp($telegram, $chatId, $botId),
        $text === '📋 Xem sản phẩm'            => handleCatalog($telegram, $chatId, $botId),
        $text === '📦 Đơn hàng của tôi'        => handleOrders($telegram, $chatId, $botId, $tgUser),
        $text === '🔄 Làm mới / check slot'    => handleCatalog($telegram, $chatId, $botId),
        $text === '🔍 Tra cứu'                 => handleSearch($telegram, $chatId, $botId),
        $text === '❓ Hướng dẫn'               => handleHelp($telegram, $chatId, $botId),
        default                                 => handleDefault($telegram, $chatId, $botId, $text, $tgUser),
    };

} catch (\Throwable $e) {
    wlog('error', $e->getMessage(), ['file' => $e->getFile() . ':' . $e->getLine(), 'trace' => mb_substr($e->getTraceAsString(), 0, 800)]);
    error_log('[Webhook Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
}

// ── Command handlers ──────────────────────────────────────────────────────────

/**
 * Edit existing message if msgId given, otherwise send new message.
 * Falls back to sendMessage if edit fails (e.g. message too old).
 */
function tgSend(Api $tg, int $chatId, ?int $msgId, array $params): void
{
    if ($msgId) {
        try {
            $tg->editMessageText(array_merge($params, [
                'chat_id'    => $chatId,
                'message_id' => $msgId,
            ]));
            return;
        } catch (\Throwable) {
            // fall through to sendMessage
        }
    }
    $tg->sendMessage(array_merge($params, ['chat_id' => $chatId]));
}

// function mainKeyboard(): array
// {
//     return [
//         'keyboard' => [
//             [['text' => '📋 Xem sản phẩm'],     ['text' => '📦 Đơn hàng của tôi']],
//             [['text' => '🔄 Làm mới / check slot'], ['text' => '🔍 Tra cứu']],
//             [['text' => '❓ Hướng dẫn']],
//         ],
//         'resize_keyboard'   => true,
//         'persistent'        => true,
//     ];
// }

function mainKeyboard(): array
{
    return [
        'inline_keyboard' => [
            [
                ['text' => '📋 Xem sản phẩm',         'callback_data' => 'menu_catalog'],
                ['text' => '📦 Đơn hàng của tôi',     'callback_data' => 'menu_orders'],
            ],
            [
                ['text' => '🔄 Làm mới / check slot', 'callback_data' => 'menu_refresh'],
                ['text' => '🔍 Tra cứu',               'callback_data' => 'menu_search'],
            ],
            [
                ['text' => '❓ Hướng dẫn',             'callback_data' => 'menu_help'],
            ],
        ],
    ];
}

function handleStart(Api $tg, $chatId, $botId, array $user, ?int $msgId = null): void
{
    $chatId = (int) $chatId;
    $botId  = (int) $botId;

    wlog('info', 'handleStart called', ['chat_id' => $chatId, 'bot_id' => $botId]);

    try {
        $welcome  = \App\Models\Setting::get($botId, 'welcome_message', 'Chào mừng bạn đến cửa hàng của chúng tôi! 🛍️');
        $shopName = \App\Models\Setting::get($botId, 'shop_name', 'Cửa hàng');

        wlog('info', 'handleStart settings loaded', ['shop' => $shopName, 'welcome_len' => mb_strlen((string)$welcome)]);

        $text = '<b>' . htmlspecialchars((string)$shopName, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</b>\n\n"
              . htmlspecialchars((string)$welcome, ENT_QUOTES | ENT_HTML5, 'UTF-8')
              . "\n\nChọn chức năng bên dưới 👇";

        tgSend($tg, $chatId, $msgId, [
            'text'         => $text,
            'parse_mode'   => 'HTML',
            'reply_markup' => json_encode(mainKeyboard()),
        ]);

        wlog('info', 'handleStart sendMessage OK');

    } catch (\Throwable $e) {
        wlog('error', 'handleStart failed: ' . $e->getMessage(), [
            'file'  => $e->getFile() . ':' . $e->getLine(),
            'trace' => mb_substr($e->getTraceAsString(), 0, 600),
        ]);
        try {
            tgSend($tg, $chatId, $msgId, [
                'text'         => "Chào mừng bạn! 🛍️\n\nChọn chức năng bên dưới 👇",
                'reply_markup' => json_encode(mainKeyboard()),
            ]);
        } catch (\Throwable) {}
    }
}

function handleCatalog(Api $tg, $chatId, $botId, ?int $msgId = null): void
{
    $chatId = (int) $chatId;
    $botId  = (int) $botId;
    $categories = \App\Models\Category::forBot($botId, onlyActive: true);

    if (empty($categories)) {
        tgSend($tg, $chatId, $msgId, ['text' => 'Chưa có danh mục nào.']);
        return;
    }

    $keyboard = array_map(
        fn($cat) => [['text' => $cat['name'], 'callback_data' => 'cat_' . $cat['id']]],
        $categories
    );
    $keyboard[] = [['text' => '🔄 Làm mới / check slot', 'callback_data' => 'menu_refresh']];
    $keyboard[] = [['text' => '🏠 Menu chính', 'callback_data' => 'menu_start']];

    tgSend($tg, $chatId, $msgId, [
        'text'         => "📋 *Danh mục sản phẩm*\n\nVui lòng chọn danh mục:",
        'parse_mode'   => 'Markdown',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
    ]);
}

function handleCategoryProducts(Api $tg, $chatId, $botId, int $catId, ?int $msgId = null): void
{
    $chatId = (int) $chatId;
    $botId  = (int) $botId;

    $products = \App\Models\Product::forBot($botId, ['category_id' => $catId, 'status' => 'active']);

    $backBtn = [['text' => '⬅️ Quay lại danh mục', 'callback_data' => 'menu_catalog']];

    if (empty($products)) {
        tgSend($tg, $chatId, $msgId, [
            'text'         => 'Danh mục này chưa có sản phẩm nào.',
            'reply_markup' => json_encode(['inline_keyboard' => [$backBtn]]),
        ]);
        return;
    }

    $keyboard = [];
    foreach ($products as $p) {
        $stock = \App\Models\ProductAccount::countByStatus((int)$p['id'])['available'] ?? 0;
        $price = number_format((int)$p['price'], 0, '.', ',');
        $label = "{$p['name']} {$price}đ ({$stock})";
        $keyboard[] = [['text' => $label, 'callback_data' => 'prod_' . $p['id']]];
    }
    $keyboard[] = $backBtn;

    tgSend($tg, $chatId, $msgId, [
        'text'         => "🛍️ *Chọn sản phẩm bạn muốn mua:*\n\n💡 _Bấm \"Làm mới / check slot\" để cập nhật số lượng mới nhất_",
        'parse_mode'   => 'Markdown',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
    ]);
}

function handleProductDetail(Api $tg, $chatId, $botId, int $prodId, ?int $msgId = null): void
{
    $chatId = (int) $chatId;
    $botId  = (int) $botId;

    $product = \App\Models\Product::findForBot($prodId, $botId);
    if (!$product) {
        tgSend($tg, $chatId, $msgId, ['text' => 'Sản phẩm không tồn tại.']);
        return;
    }

    $stock = \App\Models\ProductAccount::countByStatus($prodId)['available'] ?? 0;
    $price = number_format((int)$product['price'], 0, '.', ',');

    $text = '<b>' . htmlspecialchars((string)$product['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</b>\n\n"
          . "💰 Giá: <b>{$price}đ</b>\n"
          . "📦 Còn lại: <b>{$stock} slot</b>";
    if (!empty($product['description'])) {
        $text .= "\n\n" . htmlspecialchars((string)$product['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    $catId = (int)($product['category_id'] ?? 0);
    $keyboard = [];
    $keyboard[] = $stock > 0
        ? [['text' => '💳 Mua ngay', 'callback_data' => 'buy_' . $prodId]]
        : [['text' => '❌ Hết hàng', 'callback_data' => 'noop']];
    $keyboard[] = [['text' => '⬅️ Quay lại', 'callback_data' => 'cat_' . $catId]];

    tgSend($tg, $chatId, $msgId, [
        'text'         => $text,
        'parse_mode'   => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
    ]);
}

function handleSearch(Api $tg, $chatId, $botId, ?int $msgId = null): void
{
    $chatId = (int) $chatId;
    tgSend($tg, $chatId, $msgId, [
        'text'         => '🔍 Tính năng tra cứu sẽ sớm ra mắt!',
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => '🏠 Menu chính', 'callback_data' => 'menu_start']]]]),
    ]);
}

function handleOrders(Api $tg, $chatId, $botId, array $user, ?int $msgId = null): void
{
    $chatId = (int) $chatId;
    $botId  = (int) $botId;
    tgSend($tg, $chatId, $msgId, [
        'text'         => '📦 Tính năng xem đơn hàng sẽ sớm ra mắt!',
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => '🏠 Menu chính', 'callback_data' => 'menu_start']]]]),
    ]);
}

function handleHelp(Api $tg, $chatId, $botId, ?int $msgId = null): void
{
    $chatId  = (int) $chatId;
    $botId   = (int) $botId;
    $support = \App\Models\Setting::get($botId, 'support_contact', '');
    $text    = "ℹ️ *Hướng dẫn sử dụng*\n\n/start - Trang chủ\n/catalog - Xem sản phẩm\n/order - Xem đơn hàng\n/help - Trợ giúp";
    if ($support) $text .= "\n\n📞 Hỗ trợ: {$support}";

    tgSend($tg, $chatId, $msgId, [
        'text'         => $text,
        'parse_mode'   => 'Markdown',
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => '🏠 Menu chính', 'callback_data' => 'menu_start']]]]),
    ]);
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
