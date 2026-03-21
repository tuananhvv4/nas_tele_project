<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' — ' : '' ?><?= htmlspecialchars($_ENV['APP_NAME'] ?? 'Bot Admin') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        /* Prevent sidebar flash on mobile before Alpine.js initialises */
        @media (max-width:1023px) { #sidebar { transform: translateX(-100%); } }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-gray-50" x-data="sidebarData()" :class="{'overflow-hidden lg:overflow-auto': mobileOpen && !isLg}">

<!-- Mobile overlay backdrop -->
<div x-cloak x-show="mobileOpen" @click="mobileOpen = false"
     class="fixed inset-0 z-40 bg-black/60 lg:hidden"
     x-transition:enter="transition-opacity duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"></div>

<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$isSuperAdmin = ($authUser['role'] ?? '') === 'super_admin';
$authId = (int)($authUser['id'] ?? 0);

// Get permissions for nav rendering
$permissions = [];
if ($isSuperAdmin) {
    $permissions = \App\Models\AdminUser::fullPermissions();
} elseif ($authId > 0) {
    $permissions = \App\Models\AdminUser::getPermissions($authId);
}

function navActive(string $prefix, string $currentPath): string {
    return str_starts_with($currentPath, $prefix) ? 'active' : '';
}

function canSee(array $perms, string $feature): bool {
    return (bool)($perms[$feature] ?? false);
}
?>

<!-- ── Sidebar ─────────────────────────────────────────────────────────────── -->
<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 flex flex-col bg-slate-900 transition-all duration-300"
    :style="`width:${collapsed ? 72 : 260}px; transform:${(isLg || mobileOpen) ? 'translateX(0)' : 'translateX(-100%)'}`">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-4 py-5 border-b border-slate-700">
        <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
            </svg>
        </div>
        <span class="font-semibold text-white truncate transition-all" x-show="!collapsed">
            <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'Bot Admin') ?>
        </span>
        <!-- Mobile close button -->
        <button @click="mobileOpen = false" class="lg:hidden ml-auto text-slate-400 hover:text-white p-1 rounded shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">

        <p class="nav-section" x-show="!collapsed">Tổng quan</p>
        <a href="/admin/dashboard" class="nav-item <?= navActive('/admin/dashboard', $currentPath) ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span x-show="!collapsed" class="transition-all">Dashboard</span>
        </a>

        <?php if (canSee($permissions, 'bots')): ?>
        <a href="/admin/bots" class="nav-item <?= navActive('/admin/bots', $currentPath) ?>">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
            <span x-show="!collapsed">Bots</span>
        </a>
        <?php endif; ?>

        <p class="nav-section" x-show="!collapsed">Sản phẩm</p>

        <?php if (canSee($permissions, 'categories')): ?>
        <a href="/admin/categories" class="nav-item <?= navActive('/admin/categories', $currentPath) ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span x-show="!collapsed">Danh mục</span>
        </a>
        <?php endif; ?>

        <?php if (canSee($permissions, 'products')): ?>
        <a href="/admin/products" class="nav-item <?= navActive('/admin/products', $currentPath) ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <span x-show="!collapsed">Sản phẩm</span>
        </a>
        <?php endif; ?>

        <?php if (canSee($permissions, 'promotions')): ?>
        <a href="/admin/promotions" class="nav-item <?= navActive('/admin/promotions', $currentPath) ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            <span x-show="!collapsed">Khuyến mãi</span>
        </a>
        <?php endif; ?>

        <p class="nav-section" x-show="!collapsed">Giao dịch</p>

        <a href="/admin/users" class="nav-item <?= navActive('/admin/users', $currentPath) ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span x-show="!collapsed">Người dùng</span>
        </a>

        <?php if (canSee($permissions, 'orders')): ?>
        <a href="/admin/orders" class="nav-item <?= navActive('/admin/orders', $currentPath) ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span x-show="!collapsed">Đơn hàng</span>
        </a>
        <?php endif; ?>

        <p class="nav-section" x-show="!collapsed">Công cụ</p>

        <?php if (canSee($permissions, 'broadcast')): ?>
        <a href="/admin/broadcast" class="nav-item <?= navActive('/admin/broadcast', $currentPath) ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span x-show="!collapsed">Broadcast</span>
        </a>
        <?php endif; ?>

        <?php if (canSee($permissions, 'api_sources')): ?>
        <a href="/admin/api-sources" class="nav-item <?= navActive('/admin/api-sources', $currentPath) ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span x-show="!collapsed">API Sources</span>
        </a>
        <?php endif; ?>

        <?php if (canSee($permissions, 'settings')): ?>
        <a href="/admin/settings" class="nav-item <?= navActive('/admin/settings', $currentPath) ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span x-show="!collapsed">Cài đặt</span>
        </a>
        <?php endif; ?>

        <?php if ($isSuperAdmin): ?>
        <p class="nav-section" x-show="!collapsed">Super Admin</p>
        <a href="/super-admin/admins" class="nav-item <?= navActive('/super-admin', $currentPath) ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span x-show="!collapsed">Quản lý Admins</span>
        </a>
        <?php endif; ?>
    </nav>

    <!-- User info -->
    <div class="border-t border-slate-700 px-3 py-3" x-show="!collapsed">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-semibold shrink-0">
                <?= strtoupper(substr($authUser['username'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate"><?= htmlspecialchars($authUser['username'] ?? '') ?></p>
                <p class="text-xs text-slate-400"><?= $isSuperAdmin ? 'Super Admin' : 'Admin' ?></p>
            </div>
            <form method="POST" action="/logout">
                <?= \Core\View::csrfField() ?>
                <button type="submit" class="text-slate-400 hover:text-white p-1 rounded" title="Đăng xuất">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- ── Main content ───────────────────────────────────────────────────────── -->
<div id="main-content" class="flex flex-col min-h-screen transition-all duration-300" :style="sidebarMargin">

    <!-- Topbar -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3 sticky top-0 z-40">
        <!-- Mobile hamburger toggle (mobile only) -->
        <button @click="mobileOpen = true" class="lg:hidden p-2 -ml-1 rounded text-gray-500 hover:text-gray-700 hover:bg-gray-100 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <!-- Desktop collapse/expand toggle (desktop only) -->
        <button @click="collapsed = !collapsed" class="hidden lg:flex p-2 -ml-1 rounded text-gray-500 hover:text-gray-700 hover:bg-gray-100 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-semibold text-gray-800 truncate"><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
            <?php if (isset($breadcrumb)): ?>
            <nav class="text-xs text-gray-400 mt-0.5">
                <?php foreach ($breadcrumb as $label => $url): ?>
                    <?php if ($url): ?><a href="<?= htmlspecialchars($url) ?>" class="hover:text-blue-600"><?= htmlspecialchars($label) ?></a> <span class="mx-1">/</span>
                    <?php else: ?><span class="text-gray-600"><?= htmlspecialchars($label) ?></span><?php endif; ?>
                <?php endforeach; ?>
            </nav>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <span class="hidden sm:block"><?= date('d/m/Y') ?></span>
        </div>
    </header>

    <!-- Flash messages -->
    <?php if (isset($flashSuccess) && $flashSuccess): ?>
    <div class="mx-6 mt-4 alert-success" x-data="{show:true}" x-show="show">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span><?= htmlspecialchars($flashSuccess) ?></span>
        <button @click="show=false" class="ml-auto opacity-60 hover:opacity-100">✕</button>
    </div>
    <?php endif; ?>

    <?php if (isset($flashError) && $flashError): ?>
    <div class="mx-6 mt-4 alert-error" x-data="{show:true}" x-show="show">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
        <span><?= htmlspecialchars($flashError) ?></span>
        <button @click="show=false" class="ml-auto opacity-60 hover:opacity-100">✕</button>
    </div>
    <?php endif; ?>

    <?php if (isset($flashInfo) && $flashInfo): ?>
    <div class="mx-6 mt-4 alert-info" x-data="{show:true}" x-show="show">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
        <span><?= htmlspecialchars($flashInfo) ?></span>
        <button @click="show=false" class="ml-auto opacity-60 hover:opacity-100">✕</button>
    </div>
    <?php endif; ?>

    <!-- Page content -->
    <main class="flex-1 p-4 md:p-6">
        <?= $content ?>
    </main>
</div>

<script>
function sidebarData() {
    return {
        mobileOpen: false,
        collapsed: localStorage.getItem('sidebar_collapsed') === '1',
        isLg: window.innerWidth >= 1024,
        init() {
            this.$watch('collapsed', val => {
                localStorage.setItem('sidebar_collapsed', val ? '1' : '0');
            });
            window.addEventListener('resize', () => {
                const lg = window.innerWidth >= 1024;
                if (lg && !this.isLg) this.mobileOpen = false;
                this.isLg = lg;
            });
        },
        get sidebarMargin() {
            if (!this.isLg) return '';
            return this.collapsed ? 'margin-left:72px' : 'margin-left:260px';
        }
    };
}
</script>
<script src="//cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
