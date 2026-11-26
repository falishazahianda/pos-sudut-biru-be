<?php

require_once __DIR__ . '/../../bootstrap.php';
// 1. Validasi method
requireMethod('POST');
// 2. Pastikan pengguna sudah login
requireLogin();
// 3. Validasi CSRF token
requireCsrf();
// 4. Hapus seluruh data session
$_SESSION = [];
// 5. Hapus session cookie
if (ini_get('session.use_cookies')) {

    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax'
    ]);
}
// 6. Hapus session di server
session_destroy();
// 7. Kirim response
sendResponse(200, 'success', 'Logout berhasil.');
