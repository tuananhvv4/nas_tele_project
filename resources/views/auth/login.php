<?php $layout = 'auth'; ?>

<form method="POST" action="/login" class="space-y-5">
    <?= \Core\View::csrfField() ?>

    <div>
        <label class="form-label" for="username">Tên đăng nhập</label>
        <input
            type="text"
            id="username"
            name="username"
            class="form-input"
            placeholder="Nhập tài khoản"
            value="<?= \Core\View::old('username') ?>"
            required
            autocomplete="username"
            autofocus
        >
    </div>

    <div>
        <label class="form-label" for="password">Mật khẩu</label>
        <div class="relative" x-data="{ show: false }">
            <input
                :type="show ? 'text' : 'password'"
                id="password"
                name="password"
                class="form-input pr-10"
                placeholder="Nhập mật khẩu"
                required
                autocomplete="current-password"
            >
            <button type="button" @click="show = !show"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            </button>
        </div>
    </div>

    <button type="submit" class="btn-primary w-full justify-center py-2.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
        Đăng nhập
    </button>
</form>
