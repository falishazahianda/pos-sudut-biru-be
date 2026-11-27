<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../helpers/mail.php';

requireMethod('POST');

$data = getRequestData();
$email = $data['email'] ?? '';

// 1. Validasi input
if (!is_string($email)) {
    sendResponse(422, 'error', 'Format email tidak valid.');
}

$email = trim($email);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(422, 'error', 'Email tidak valid.');
}

// Response generik untuk menjaga privasi akun
$message = 'Jika email terdaftar, link reset password akan dikirim.';

// 2. Cari pengguna aktif
$stmt = $pdo->prepare("
    SELECT id, name, email
    FROM users
    WHERE email = ? AND is_active = 1
    LIMIT 1
");

$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    sendResponse(200, 'success', $message);
}

// 3. Buat token
$token = bin2hex(random_bytes(32));
$tokenHash = hash('sha256', $token);

$expiry = (int) ($_ENV['RESET_TOKEN_EXPIRY'] ?? 30);

$expiresAt = date(
    'Y-m-d H:i:s',
    time() + ($expiry * 60)
);

try {
    $pdo->beginTransaction();

    // 4. Kunci akun untuk mencegah request bersamaan
    $stmt = $pdo->prepare("
        SELECT id FROM users
        WHERE id = ? AND is_active = 1
        FOR UPDATE
    ");

    $stmt->execute([$user['id']]);

    if (!$stmt->fetch()) {
        $pdo->rollBack();
        sendResponse(200, 'success', $message);
    }

    // 5. Batasi permintaan: satu kali per menit
    $stmt = $pdo->prepare("
        SELECT id
        FROM password_resets
        WHERE user_id = ?
        AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
        LIMIT 1
    ");

    $stmt->execute([$user['id']]);

    if ($stmt->fetch()) {
        $pdo->rollBack();
        sendResponse(200, 'success', $message);
    }

    // 6. Nonaktifkan token sebelumnya
    $stmt = $pdo->prepare("
        UPDATE password_resets
        SET used_at = NOW()
        WHERE user_id = ?
        AND used_at IS NULL
    ");

    $stmt->execute([$user['id']]);

    // 7. Simpan hash token
    $stmt = $pdo->prepare("
        INSERT INTO password_resets
        (user_id, token_hash, expires_at)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $user['id'],
        $tokenHash,
        $expiresAt
    ]);

    $pdo->commit();

    // 8. Buat link reset password
    $resetLink = $_ENV['RESET_PASSWORD_URL']
        . '?token=' . urlencode($token);

    // 9. Kirim email
    sendResetPasswordEmail(
        $user['email'],
        $user['name'],
        $resetLink
    );

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Forgot password process failed.');

}

// 10. Kirim response
sendResponse(200, 'success', $message);
