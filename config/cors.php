<?php

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. Daftar origin frontend yang diizinkan
$allowedOrigins = [
    'http://localhost',
    'http://127.0.0.1',
    'http://localhost:5500',
    'http://127.0.0.1:5500'
];
// 2. Tambahkan origin dari konfigurasi .env
$feOrigin = $_ENV['FE_ORIGIN'] ?? '';
if ($feOrigin !== '') {
    $allowedOrigins[] = $feOrigin;
}
// 3. Izinkan origin yang sama dengan backend
$scheme = !empty($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] !== 'off'
    ? 'https'
    : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($host !== '') {
    $allowedOrigins[] = $scheme . '://' . $host;
}
// 4. Header response
header('Content-Type: application/json; charset=utf-8');
header('Vary: Origin');
// 5. Validasi origin
if ($origin !== '') {
    if (!in_array($origin, $allowedOrigins, true)) {
        http_response_code(403);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Origin tidak diizinkan.',
            'data'    => null
        ]);
        exit;
    }

    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
    header(
        'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'
    );
    header(
        'Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token'
    );
}
// 6. Tangani preflight sebelum session/database
if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}