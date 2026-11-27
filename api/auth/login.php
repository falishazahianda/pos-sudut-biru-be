
<?php

require_once __DIR__ . '/../../bootstrap.php';

/** @var PDO $pdo */

// 1. Pastikan request menggunakan POST
requireMethod('POST');


// 2. Ambil data request
$data = getRequestData();

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$role     = $data['role'] ?? '';


// 3. Validasi input
if (
    !is_string($username) ||
    !is_string($password) ||
    !is_string($role)
) {
    sendResponse(422, 'error', 'Format input tidak valid.');
}

$username = trim($username);

if ($username === '' || $password === '' || $role === '') {
    sendResponse(
        422,
        'error',
        'Username, password, dan role wajib diisi.'
    );
}


// 4. Validasi role
if (!in_array($role, ['admin', 'kasir'], true)) {
    sendResponse(422, 'error', 'Role tidak valid.');
}


// 5. Cari akun berdasarkan username
$stmt = $pdo->prepare("
    SELECT
        id,
        name,
        username,
        email,
        password_hash,
        role,
        is_active,
        shift_id,
        must_change_password,
        session_version
    FROM users
    WHERE username = ?
    LIMIT 1
");

$stmt->execute([$username]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


// 6. Validasi akun dan password
if (
    !$user ||
    !password_verify($password, $user['password_hash']) ||
    $user['role'] !== $role ||
    (int) $user['is_active'] !== 1
) {
    sendResponse(
        401,
        'error',
        'Username, password, atau role tidak sesuai.'
    );
}


// 7. Buat session login
session_regenerate_id(true);

$_SESSION = [
    'user_id'         => (int) $user['id'],
    'role'            => $user['role'],
    'session_version' => (int) $user['session_version'],
    'csrf_token'      => bin2hex(random_bytes(32))
];


// 8. Kirim response login berhasil
sendResponse(200, 'success', 'Login berhasil.', [

    'user' => [
        'id'          => (int) $user['id'],
        'name'        => $user['name'],
        'username'    => $user['username'],
        'email'       => $user['email'],
        'role'        => $user['role'],

        'shift_id' => $user['shift_id'] !== null
            ? (int) $user['shift_id']
            : null,

        'must_change_password' =>
            (bool) $user['must_change_password']
    ],

    'csrf_token' => $_SESSION['csrf_token']

]);
