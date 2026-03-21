<?php

declare(strict_types=1);

/** @var \Core\Router $router */

// ── Public ────────────────────────────────────────────────────────────────────
$router->get('/login',  [\App\Controllers\Auth\LoginController::class, 'showLogin']);
$router->post('/login', [\App\Controllers\Auth\LoginController::class, 'login']);
$router->post('/logout', [\App\Controllers\Auth\LoginController::class, 'logout'], ['auth']);

// Redirect root to dashboard
$router->get('/', fn() => \Core\Response::redirect('/admin/dashboard'));

// ── Admin ─────────────────────────────────────────────────────────────────────
$router->group(['middleware' => ['auth', 'csrf']], function ($router) {

    // Dashboard
    $router->get('/admin/dashboard', [\App\Controllers\Admin\DashboardController::class, 'index']);

    // Bots
    $router->group(['middleware' => ['permission:bots']], function ($router) {
        $router->get('/admin/bots',                 [\App\Controllers\Admin\BotController::class, 'index']);
        $router->get('/admin/bots/create',          [\App\Controllers\Admin\BotController::class, 'create']);
        $router->post('/admin/bots',                [\App\Controllers\Admin\BotController::class, 'store']);
        $router->get('/admin/bots/{id}/edit',       [\App\Controllers\Admin\BotController::class, 'edit']);
        $router->put('/admin/bots/{id}',            [\App\Controllers\Admin\BotController::class, 'update']);
        $router->delete('/admin/bots/{id}',         [\App\Controllers\Admin\BotController::class, 'destroy']);
        $router->post('/admin/bots/{id}/webhook',   [\App\Controllers\Admin\BotController::class, 'setWebhook']);
    });

    // Categories
    $router->group(['middleware' => ['permission:categories']], function ($router) {
        $router->get('/admin/categories',             [\App\Controllers\Admin\CategoryController::class, 'index']);
        $router->get('/admin/categories/create',      [\App\Controllers\Admin\CategoryController::class, 'create']);
        $router->post('/admin/categories',            [\App\Controllers\Admin\CategoryController::class, 'store']);
        $router->get('/admin/categories/{id}/edit',   [\App\Controllers\Admin\CategoryController::class, 'edit']);
        $router->put('/admin/categories/{id}',        [\App\Controllers\Admin\CategoryController::class, 'update']);
        $router->delete('/admin/categories/{id}',     [\App\Controllers\Admin\CategoryController::class, 'destroy']);
    });

    // Products
    $router->group(['middleware' => ['permission:products']], function ($router) {
        $router->get('/admin/products',              [\App\Controllers\Admin\ProductController::class, 'index']);
        $router->get('/admin/products/create',       [\App\Controllers\Admin\ProductController::class, 'create']);
        $router->post('/admin/products',             [\App\Controllers\Admin\ProductController::class, 'store']);
        $router->get('/admin/products/{id}/edit',    [\App\Controllers\Admin\ProductController::class, 'edit']);
        $router->put('/admin/products/{id}',         [\App\Controllers\Admin\ProductController::class, 'update']);
        $router->delete('/admin/products/{id}',      [\App\Controllers\Admin\ProductController::class, 'destroy']);

        // Product Accounts
        $router->get('/admin/products/{productId}/accounts',                     [\App\Controllers\Admin\ProductAccountController::class, 'index']);
        $router->post('/admin/products/{productId}/accounts',                    [\App\Controllers\Admin\ProductAccountController::class, 'store']);
        $router->post('/admin/products/{productId}/accounts/reset',              [\App\Controllers\Admin\ProductAccountController::class, 'reset']);
        $router->delete('/admin/products/{productId}/accounts/{accountId}',      [\App\Controllers\Admin\ProductAccountController::class, 'destroy']);
    });

    // Users (Telegram)
    $router->get('/admin/users',          [\App\Controllers\Admin\UserController::class, 'index']);
    $router->get('/admin/users/{id}',     [\App\Controllers\Admin\UserController::class, 'show']);
    $router->post('/admin/users/{id}/ban',[\App\Controllers\Admin\UserController::class, 'toggleBan']);

    // Orders
    $router->group(['middleware' => ['permission:orders']], function ($router) {
        $router->get('/admin/orders',              [\App\Controllers\Admin\OrderController::class, 'index']);
        $router->get('/admin/orders/{id}',         [\App\Controllers\Admin\OrderController::class, 'show']);
        $router->put('/admin/orders/{id}/status',  [\App\Controllers\Admin\OrderController::class, 'updateStatus']);
    });

    // Promotions
    $router->group(['middleware' => ['permission:promotions']], function ($router) {
        $router->get('/admin/promotions',             [\App\Controllers\Admin\PromotionController::class, 'index']);
        $router->get('/admin/promotions/create',      [\App\Controllers\Admin\PromotionController::class, 'create']);
        $router->post('/admin/promotions',            [\App\Controllers\Admin\PromotionController::class, 'store']);
        $router->get('/admin/promotions/{id}/edit',   [\App\Controllers\Admin\PromotionController::class, 'edit']);
        $router->put('/admin/promotions/{id}',        [\App\Controllers\Admin\PromotionController::class, 'update']);
        $router->delete('/admin/promotions/{id}',     [\App\Controllers\Admin\PromotionController::class, 'destroy']);
    });

    // Broadcast
    $router->group(['middleware' => ['permission:broadcast']], function ($router) {
        $router->get('/admin/broadcast',       [\App\Controllers\Admin\BroadcastController::class, 'index']);
        $router->post('/admin/broadcast/send', [\App\Controllers\Admin\BroadcastController::class, 'send']);
        $router->get('/admin/broadcast/logs',  [\App\Controllers\Admin\BroadcastController::class, 'logs']);
    });

    // Settings
    $router->group(['middleware' => ['permission:settings']], function ($router) {
        $router->get('/admin/settings',   [\App\Controllers\Admin\SettingController::class, 'index']);
        $router->post('/admin/settings',  [\App\Controllers\Admin\SettingController::class, 'update']);
    });

    // API Sources
    $router->group(['middleware' => ['permission:api_sources']], function ($router) {
        $router->get('/admin/api-sources',              [\App\Controllers\Admin\ApiSourceController::class, 'index']);
        $router->get('/admin/api-sources/create',       [\App\Controllers\Admin\ApiSourceController::class, 'create']);
        $router->post('/admin/api-sources',             [\App\Controllers\Admin\ApiSourceController::class, 'store']);
        $router->get('/admin/api-sources/{id}/edit',    [\App\Controllers\Admin\ApiSourceController::class, 'edit']);
        $router->put('/admin/api-sources/{id}',         [\App\Controllers\Admin\ApiSourceController::class, 'update']);
        $router->delete('/admin/api-sources/{id}',      [\App\Controllers\Admin\ApiSourceController::class, 'destroy']);
        $router->post('/admin/api-sources/{id}/test',   [\App\Controllers\Admin\ApiSourceController::class, 'testConnection']);
        $router->post('/admin/api-sources/{id}/sync',   [\App\Controllers\Admin\ApiSourceController::class, 'sync']);
    });
});

// ── Super Admin ───────────────────────────────────────────────────────────────
$router->group(['middleware' => ['auth', 'csrf', 'super_admin']], function ($router) {

    // Admin user management
    $router->get('/super-admin/admins',                  [\App\Controllers\SuperAdmin\AdminManagerController::class, 'index']);
    $router->get('/super-admin/admins/create',           [\App\Controllers\SuperAdmin\AdminManagerController::class, 'create']);
    $router->post('/super-admin/admins',                 [\App\Controllers\SuperAdmin\AdminManagerController::class, 'store']);
    $router->get('/super-admin/admins/{id}/edit',        [\App\Controllers\SuperAdmin\AdminManagerController::class, 'edit']);
    $router->put('/super-admin/admins/{id}',             [\App\Controllers\SuperAdmin\AdminManagerController::class, 'update']);
    $router->delete('/super-admin/admins/{id}',          [\App\Controllers\SuperAdmin\AdminManagerController::class, 'destroy']);

    // Feature permissions
    $router->get('/super-admin/admins/{id}/features',    [\App\Controllers\SuperAdmin\FeatureController::class, 'edit']);
    $router->put('/super-admin/admins/{id}/features',    [\App\Controllers\SuperAdmin\FeatureController::class, 'update']);
});
