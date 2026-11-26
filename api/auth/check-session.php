
<?php

require_once __DIR__ . '/../../bootstrap.php';

// 1. Validasi method
requireMethod('GET');

// 2. Periksa session pengguna
$user = requireLogin();

// 3. Kirim data pengguna
sendResponse(200, 'success', 'Session aktif.', [

    'user' => [
        'id'         => (int) $user['id'],
        'staff_code' => $user['staff_code'],
        'name'       => $user['name'],
        'username'   => $user['username'],
        'email'      => $user['email'],
        'role'       => $user['role'],

        'shift_id' => $user['shift_id'] !== null
            ? (int) $user['shift_id']
            : null,

        'must_change_password' =>
            (bool) $user['must_change_password']
    ],

    'csrf_token' => $_SESSION['csrf_token']

]);