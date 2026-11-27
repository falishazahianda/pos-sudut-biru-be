<?php

require_once __DIR__ . '/../../bootstrap.php';

requireMethod(['GET', 'POST']);

$method = $_SERVER['REQUEST_METHOD'];

$data = $method === 'GET'
    ? $_GET
    : getRequestData();

$token = $data['token'] ?? '';

// 1. Validasi format token
if (
    !is_string($token) ||
    !preg_match('/^[a-f0-9]{64}$/', $token)
) {
    sendResponse(400, 'error', 'Token tidak valid.');
}

$tokenHash = hash('sha256', $token);

// 2. Cari token yang masih berlaku
$stmt = $pdo->prepare("
    SELECT pr.id, pr.user_id
    FROM password_resets pr
    JOIN users u ON u.id = pr.user_id
    WHERE pr.token_hash = ?
    AND pr.used_at IS NULL
    AND pr.expires_at > NOW()
    AND u.is_active = 1
    LIMIT 1
");

$stmt->execute([$tokenHash]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    sendResponse(
        400,
        'error',
        'Link reset tidak valid atau sudah kedaluwarsa.'
    );
}

// 3. GET: validasi token saja
if ($method === 'GET') {
    sendResponse(200, 'success', 'Token valid.', [
        'valid' => true
    ]);
}

// 4. POST: validasi password baru
$password = $data['password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

if (
    !is_string($password) ||
    !is_string($confirmPassword)
) {
    sendResponse(422, 'error', 'Format password tidak valid.');
}

if (strlen($password) < 8 || strlen($password) > 72 ||
    strpos($password, "\0") !== false) {
    sendResponse(
        422,
        'error',
        'Password harus terdiri dari 8 sampai 72 byte tanpa karakter null.'
    );
}

if ($password !== $confirmPassword) {
    sendResponse(
        422,
        'error',
        'Konfirmasi password tidak sesuai.'
    );
}

// 5. Hash password baru
$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

try {

    $pdo->beginTransaction();

    // 6. Kunci akun pengguna
    $stmt = $pdo->prepare("
        SELECT id
        FROM users
        WHERE id = ? AND is_active = 1
        FOR UPDATE
    ");

    $stmt->execute([$reset['user_id']]);

    if (!$stmt->fetch()) {
        $pdo->rollBack();
        sendResponse(400, 'error', 'Akun tidak valid.');
    }

    // 7. Validasi ulang dan gunakan token
    $stmt = $pdo->prepare("
        UPDATE password_resets
        SET used_at = NOW()
        WHERE id = ?
        AND used_at IS NULL
        AND expires_at > NOW()
    ");

    $stmt->execute([$reset['id']]);

    if ($stmt->rowCount() !== 1) {
        $pdo->rollBack();

        sendResponse(
            400,
            'error',
            'Token sudah digunakan atau kedaluwarsa.'
        );
    }

    // 8. Update password dan cabut session lama
    $stmt = $pdo->prepare("
        UPDATE users
        SET password_hash = ?,
            must_change_password = 0,
            session_version = session_version + 1
        WHERE id = ?
    ");

    $stmt->execute([
        $passwordHash,
        $reset['user_id']
    ]);

    // 9. Nonaktifkan seluruh token milik pengguna
    $stmt = $pdo->prepare("
        UPDATE password_resets
        SET used_at = NOW()
        WHERE user_id = ?
        AND used_at IS NULL
    ");

    $stmt->execute([$reset['user_id']]);

    $pdo->commit();

    sendResponse(
        200,
        'success',
        'Password berhasil direset. Silakan login kembali.'
    );

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Reset password process failed.');

    sendResponse(
        500,
        'error',
        'Terjadi kesalahan saat mereset password.'
    );

}
