<?php

date_default_timezone_set('Asia/Jakarta');

// 1. Muat konfigurasi dari .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}

// 2. Ubah semua uncaught error/exception PHP menjadi JSON bersih
set_exception_handler(function (Throwable $e): void {
    $isLocal = ($_ENV['APP_ENV'] ?? 'local') === 'local';
    $message = $isLocal
        ? 'Server Error: ' . $e->getMessage() . ' (di ' . basename($e->getFile()) . ':' . $e->getLine() . ')'
        : 'Terjadi kesalahan internal pada server.';

    if (function_exists('sendResponse')) {
        sendResponse(500, 'error', $message);
    }

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
});

// 3. Muat Composer autoload jika sudah ada
$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

// 4. Jalankan CORS & header JSON
require_once __DIR__ . '/config/cors.php';

// 5. Mulai session PHP
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 6. Muat helper dasar & koneksi PDO
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/helpers/request.php';

if (file_exists(__DIR__ . '/helpers/auth.php')) {
    require_once __DIR__ . '/helpers/auth.php';
}

require_once __DIR__ . '/config/database.php';