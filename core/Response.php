<?php

declare(strict_types=1);

namespace Core;

class Response
{
    // ── Redirect ─────────────────────────────────────────────────────────────

    public static function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }

    public static function back(): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        self::redirect($referer);
    }

    public static function redirectWithSuccess(string $url, string $message): never
    {
        Session::getInstance()->flash('success', $message);
        self::redirect($url);
    }

    public static function redirectWithError(string $url, string $message): never
    {
        Session::getInstance()->flash('error', $message);
        self::redirect($url);
    }

    public static function backWithSuccess(string $message): never
    {
        Session::getInstance()->flash('success', $message);
        self::back();
    }

    public static function backWithError(string $message): never
    {
        Session::getInstance()->flash('error', $message);
        self::back();
    }

    // ── JSON ─────────────────────────────────────────────────────────────────

    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function jsonSuccess(mixed $data = null, string $message = 'OK', int $status = 200): never
    {
        self::json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    public static function jsonError(string $message, int $status = 400): never
    {
        self::json(['success' => false, 'message' => $message], $status);
    }

    // ── HTML ─────────────────────────────────────────────────────────────────

    public static function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        $titles = [403 => 'Forbidden', 404 => 'Not Found', 500 => 'Server Error'];
        $title  = $titles[$code] ?? 'Error';
        echo "<!DOCTYPE html><html><head><title>{$code} {$title}</title></head>";
        echo "<body><h1>{$code} — {$title}</h1><p>" . htmlspecialchars($message) . "</p></body></html>";
        exit;
    }
}
