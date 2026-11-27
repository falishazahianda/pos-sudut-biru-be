
<?php

require_once __DIR__ . '/../../bootstrap.php';

requireMethod('POST');

// 1. Validasi session dan CSRF
$user = requireLogin();
requireCsrf();

$data = getRequestData();

$currentPassword = $data['current_password'] ?? '';
$newPassword = $data['new_password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

// 2. Validasi tipe input
if (
    !is_string($currentPassword) ||
    !is_string($newPassword) ||
    !is_string($confirmPassword)
) {
    sendResponse(422, 'error', 'Format password tidak valid.');
}

// 3. Validasi kelengkapan input
if (
    $currentPassword === '' ||
    $newPassword === '' ||
    $confirmPassword === ''
) {
    sendResponse(422, 'error', 'Semua kolom password wajib diisi.');
}

// 4. Validasi password baru
if (
    strlen($newPassword) < 8 ||
    strlen($newPassword) > 72 ||
    strpos($newPassword, "\0") !== false
) {
    sendResponse(
        422,
        'error',
        'Password baru harus terdiri dari 8 sampai 72 byte tanpa karakter null.'
    );
}

if ($newPassword !== $confirmPassword) {
    sendResponse(
        422,
        'error',
        'Konfirmasi password tidak sesuai.'
    );
}

try {

    $pdo->beginTransaction();

    // 5. Ambil password dan kunci akun
    $stmt = $pdo->prepare("
        SELECT password_hash, session_version
        FROM users
        WHERE id = ? AND is_active = 1
        FOR UPDATE
    ");

    $stmt->execute([$user['id']]);

    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$account ||
        (int) $account['session_version'] !==
        (int) $_SESSION['session_version']
    ) {
        $pdo->rollBack();

        sendResponse(401, 'error', 'Session tidak valid.');
    }

    // 6. Verifikasi password lama
    if (!password_verify(
        $currentPassword,
        $account['password_hash']
    )) {
        $pdo->rollBack();

        sendResponse(
            422,
            'error',
            'Password lama tidak sesuai.'
        );
    }

    // 7. Password baru harus berbeda
    if (password_verify(
        $newPassword,
        $account['password_hash']
    )) {
        $pdo->rollBack();

        sendResponse(
            422,
            'error',
            'Password baru harus berbeda dari password lama.'
        );
    }

    // 8. Hash password baru
    $passwordHash = password_hash(
        $newPassword,
        PASSWORD_DEFAULT
    );

    $newSessionVersion =
        (int) $account['session_version'] + 1;

    // 9. Update password
    $stmt = $pdo->prepare("
        UPDATE users
        SET password_hash = ?,
            must_change_password = 0,
            session_version = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $passwordHash,
        $newSessionVersion,
        $user['id']
    ]);

    // 10. Nonaktifkan token reset password lama
    $stmt = $pdo->prepare("
        UPDATE password_resets
        SET used_at = NOW()
        WHERE user_id = ?
        AND used_at IS NULL
    ");

    $stmt->execute([$user['id']]);

    $pdo->commit();

    // 11. Perbarui session perangkat saat ini
    session_regenerate_id(true);

    $_SESSION['session_version'] = $newSessionVersion;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    sendResponse(
        200,
        'success',
        'Password berhasil diperbarui.',
        [
            'csrf_token' => $_SESSION['csrf_token']
        ]
    );

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Change password process failed.');

    sendResponse(
        500,
        'error',
        'Terjadi kesalahan saat mengubah password.'
    );
}
