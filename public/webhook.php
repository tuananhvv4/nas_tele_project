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

// Khởi tạo env + DB (không cần session / router)
$app = Application::getInstance(BASE_PATH);
$app->bootstrapLite();

// ── Kiểm tra secret webhook ───────────────────────────────────────────────────
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
    http_response_code(200); // Trả về 200 cho Telegram
    exit;
}

// Trả về ngay lập tức để Telegram không thử lại
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ok' => true]);

// Gửi output
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    ob_end_flush();
}

// ── Xử lý update ────────────────────────────────────────────────────────────
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

    // Đăng ký hoặc cập nhật người dùng
    $tgUser = TelegramUser::findOrCreate($botId, [
        'telegram_id' => $from['id'],
        'username'    => $from['username'] ?? null,
        'first_name'  => $from['first_name'] ?? '',
        'last_name'   => $from['last_name'] ?? null,
        'language'    => $from['language_code'] ?? null,
    ]);

    wlog('info', 'Telegram user: ' . json_encode($tgUser, JSON_UNESCAPED_UNICODE));

    // Kiểm tra xem người dùng có bị chặn không
    if ($tgUser['is_banned']) {
        wlog('info', 'Banned user ignored', ['telegram_id' => $from['id']]);
        exit;
    }

    // Xử lý các nút bấm inline (callback_query)
    if (isset($update['callback_query'])) {
        $cq       = $update['callback_query'];
        $cbChatId = (int) ($cq['message']['chat']['id'] ?? 0);
        $cbMsgId  = (int) ($cq['message']['message_id'] ?? 0) ?: null;
        $cbData   = $cq['data'] ?? '';
        try { $telegram->answerCallbackQuery(['callback_query_id' => $cq['id']]); } catch (\Throwable) {}

        wlog('info', 'Callback query', ['data' => $cbData, 'chat_id' => $cbChatId]);

        // Xử lý khi chọn category
        if (str_starts_with($cbData, 'cat_')) {
            $categoryId = (int) substr($cbData, 4);
            handleCategoryProducts($telegram, $cbChatId, $botId, $categoryId, $cbMsgId);
        // Xử lý khi chọn product
        } elseif (str_starts_with($cbData, 'prod_')) {
            $productId = (int) substr($cbData, 5);
            handleProductDetail($telegram, $cbChatId, $botId, $productId, $cbMsgId);
            // Xử lý khi chọn sản phẩm để mua (Sẽ yêu cầu nhập số lượng) sau đó sẽ set state là select_qty
        } elseif (str_starts_with($cbData, 'select_qty_')) {
            $productId = (int) substr($cbData, 11);
            handleSelectProductToBuy($telegram, $cbChatId, $botId, $productId, $cbMsgId);
        // Xử lý sau khi nhập số lượng
        } elseif (str_starts_with($cbData, 'buy_qty_')) {
            $productId = (int) substr($cbData, 8);
            tgSend($telegram, $cbChatId, $cbMsgId, [
                'text'         => '🛒 Tính năng mua hàng sẽ sớm ra mắt!',
                'reply_markup' => json_encode(['inline_keyboard' => [[['text' => '⬅️ Quay lại', 'callback_data' => 'menu_catalog']]]]),
            ]);
        // Xử lý nếu bấm huỷ nhập số lượng
        if (str_starts_with($cbData, 'cancel_buy_')) {
            $productId = (int) substr($cbData, 11);
            // Làm mới state
            \App\Models\UserState::setUserState($botId, $tgUser['id'], '');
            handleProductDetail($telegram, $cbChatId, $botId, $productId, $cbMsgId);
        }
        // Xử lý khi chọn menu catalog
        } elseif ($cbData === 'menu_catalog' || $cbData === 'menu_refresh') {
            handleCatalog($telegram, $cbChatId, $botId, $cbMsgId);
        // Xử lý khi chọn menu orders
        } elseif ($cbData === 'menu_orders') {
            handleOrders($telegram, $cbChatId, $botId, $tgUser, $cbMsgId);
        // Xử lý khi chọn menu help
        } elseif ($cbData === 'menu_help') {
            handleHelp($telegram, $cbChatId, $botId, $cbMsgId);
        // Xử lý khi chọn menu search
        } elseif ($cbData === 'menu_search') {
            handleSearch($telegram, $cbChatId, $botId, $cbMsgId);
        // Xử lý khi chọn menu start
        } elseif ($cbData === 'menu_start') {
            handleStart($telegram, $cbChatId, $botId, $tgUser, $cbMsgId);
        }
        exit;
    }

    $chatId = $message['chat']['id'];
    $text   = trim($message['text'] ?? '');

    wlog('info', 'Processing message', ['chat_id' => $chatId, 'text' => $text]);

    // Xử lý khi user có state
    $userState = \App\Models\UserState::getUserState($botId, $tgUser['id']);
    wlog('info', 'User state', ['user_state' => json_encode($userState, JSON_UNESCAPED_UNICODE) ?? 'null']);
    if (!empty($userState)) {
        switch ($userState['state']) {
            case 'select_qty':
                handleSelectQty($telegram, $chatId, $botId, $productId, $cbMsgId);
                break;
        }
    }

    // Định tuyến các lệnh và các nút bấm persistent
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
 * Chỉnh sửa tin nhắn hiện có nếu msgId được cung cấp, nếu không thì gửi tin nhắn mới.
 * Nếu chỉnh sửa thất bại thì sẽ gửi tin nhắn mới (ví dụ: tin nhắn quá cũ).
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
            // chuyển sang gửi tin nhắn mới
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


/**
 * Trả về bàn phím chính
 */
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

/**
 * Xử lý khi chọn menu catalog
 */
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

    $keyboard[] = [
        ['text' => '🔄 Làm mới', 'callback_data' => 'menu_refresh'],
        ['text' => '🏠 Menu chính', 'callback_data' => 'menu_start']
    ];

    tgSend($tg, $chatId, $msgId, [
        'text'         => "📋 *Chọn sản phẩm bạn muốn mua bên dưới:*",
        'parse_mode'   => 'Markdown',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
    ]);
}

/**
 * Xử lý khi chọn danh mục sản phẩm
 */
function handleCategoryProducts(Api $tg, $chatId, $botId, int $catId, ?int $msgId = null): void
{
    $chatId = (int) $chatId;
    $botId  = (int) $botId;

    $products = \App\Models\Product::forBot($botId, ['category_id' => $catId, 'status' => 'active']);

    $backBtn = [['text' => '⬅️ Quay lại danh mục', 'callback_data' => 'menu_catalog']];

    if (empty($products)) {
        tgSend($tg, $chatId, $msgId, [
            'text'         => '❌ Danh mục này chưa có sản phẩm nào.',
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
        'text'         => "🛍️ *Chọn sản phẩm bạn muốn mua bên dưới:*",
        'parse_mode'   => 'Markdown',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
    ]);
}

/**
 * Xử lý khi chọn xem chi tiết sản phẩm
 */
function handleProductDetail(Api $tg, $chatId, $botId, int $prodId, ?int $msgId = null): void
{
    $chatId = (int) $chatId;
    $botId  = (int) $botId;

    $product = \App\Models\Product::findForBot($prodId, $botId);
    if (!$product) {
        tgSend($tg, $chatId, $msgId, ['text' => '❌ Sản phẩm không tồn tại.']);
        return;
    }

    $stock = \App\Models\ProductAccount::countByStatus($prodId)['available'] ?? 0;
    $price = number_format((int)$product['price'], 0, '.', ',');

    $text = '<b>' . htmlspecialchars((string)$product['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</b>\n\n";
    if (!empty($product['description'])) {
        $text .= "\n\n <b>📋 Mô tả:</b>\n";
        $desc = (string)$product['description'];

        // Tách theo xuống dòng
        $lines = preg_split('/\r\n|\r|\n/', $desc);

        // Thêm dấu • vào đầu mỗi dòng
        $lines = array_map(function ($line) {
            return trim($line) !== '' ? '• ' . $line : $line;
        }, $lines);

        // Nối lại thành chuỗi (dùng <br> nếu hiển thị HTML)
        $descFormatted = implode("\n", $lines);

        // Escape HTML
        $text .= htmlspecialchars($descFormatted, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    $text .= "\n\n💰 Giá: <b>{$price}đ</b> / 1\n"
        . "📦 Còn lại: <b>{$stock}</b>";

    $catId = (int)($product['category_id'] ?? 0);
    $keyboard = [];
    $keyboard[] = $stock > 0
        ? [['text' => '💳 Mua ngay', 'callback_data' => 'select_qty_' . $prodId]]
        : [['text' => '❌ Hết hàng', 'callback_data' => 'noop']];
    $keyboard[] = [['text' => '⬅️ Quay lại', 'callback_data' => 'cat_' . $catId]];

    tgSend($tg, $chatId, $msgId, [
        'text'         => $text,
        'parse_mode'   => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
    ]);
}

/**
 * Xử lý khi chọn sản phẩm để mua (Yêu cầu nhập số lượng)
 */

 function handleSelectProductToBuy(Api $tg, $chatId, $botId, int $prodId, ?int $msgId = null): void
 {
    global $tgUser;
    $chatId = (int) $chatId;
    $botId  = (int) $botId;
    $product = \App\Models\Product::findForBot($prodId, $botId);
    if (!$product) {
        tgSend($tg, $chatId, $msgId, ['text' => '❌ Sản phẩm không tồn tại.']);
        return;
    }

    // set user state to select_qty
    if (!empty($tgUser) && !empty($tgUser['id'])) {
        \App\Models\UserState::setUserState($botId, $tgUser['id'], 'select_qty');
    } else {
        wlog('error', 'handleSelectProductToBuy failed: user not found', ['chat_id' => $chatId, 'bot_id' => $botId]);
        tgSend($tg, $chatId, $msgId, ['text' => '❌ Lỗi khi xử lý yêu cầu. Vui lòng thử lại sau.']);
        return;
    }

    $stock = \App\Models\ProductAccount::countByStatus($prodId)['available'] ?? 0;
    $price = number_format((int)$product['price'], 0, '.', ',');

    $text = '<b>' . htmlspecialchars((string)$product['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</b>\n\n"
          . "💰 Giá: <b>{$price}đ</b>\n"
          . "📦 Còn lại: <b>{$stock} slot</b>\n\n"
          . "📝 *Vui lòng nhập số lượng bạn muốn mua: (1-{$stock})*";


    tgSend($tg, $chatId, $msgId, [
        'text'         => $text,
        'parse_mode'   => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => '❌ Huỷ', 'callback_data' => 'cancel_buy_' . $prodId]]]]),
    ]);
    
}

/**
 * Xử lý khi user nhập số lượng
 */
function handleSelectQty(Api $tg, $chatId, $botId, int $prodId, ?int $msgId = null): void
{
    $chatId = (int) $chatId;
    $botId  = (int) $botId;
    $product = \App\Models\Product::findForBot($prodId, $botId);
    if (!$product) {
        tgSend($tg, $chatId, $msgId, ['text' => '❌ Sản phẩm không tồn tại.']);
        return;
    }

    tgSend($tg, $chatId, $msgId, [
        'text'         => 'Tính năng mua hàng sẽ sớm ra mắt!',
        'parse_mode'   => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => [[['text' => '🏠 Menu chính', 'callback_data' => 'menu_start']]]]),
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
