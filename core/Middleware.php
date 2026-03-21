<?php

declare(strict_types=1);

namespace Core;

interface Middleware
{
    public function handle(Request $request, callable $next): void;
}
