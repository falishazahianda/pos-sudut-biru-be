
<?php

require_once __DIR__ . '/../../bootstrap.php';

requireMethod(['GET', 'PUT']);

// 1. Pastikan pengguna sudah login
$user = requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

// 2. Update profil
if ($method === 'PUT') {

    requireCsrf();

    $data = getRequestData();
    $name = $data['name'] ?? '';

    // Validasi nama
    if (!is_string($name)) {
        sendResponse(422, 'error', 'Format nama tidak valid.');
    }

    $name = trim($name);

    if ($name === '' || mb_strlen($name) > 100) {
        sendResponse(
            422,
            'error',
            'Nama wajib diisi dan maksimal 100 karakter.'
        );
    }

    // Hanya nama yang boleh diubah
    $stmt = $pdo->prepare("
        UPDATE users
        SET name = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $name,
        $user['id']
    ]);

    $user = requireLogin();
}

// 3. Format data profil
$profile = [
    'id' => (int) $user['id'],
    'name' => $user['name'],
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role'],
    'is_active' => (bool) $user['is_active'],
    'shift_id' => $user['shift_id'] !== null
        ? (int) $user['shift_id']
        : null,
    'must_change_password' =>
        (bool) $user['must_change_password']
];

// 4. Response
$message = $method === 'PUT'
    ? 'Profil berhasil diperbarui.'
    : 'Profil berhasil diambil.';

sendResponse(200, 'success', $message, [
    'user' => $profile
]);
