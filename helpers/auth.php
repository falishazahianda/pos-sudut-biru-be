<?php

// 1. Memastikan pengguna sudah login
function requireLogin(): array
{
    global $pdo;

    if (empty($_SESSION['user_id'])) {
        sendResponse(
            401,
            'error',
            'Silakan login terlebih dahulu.'
        );
    }

    $stmt = $pdo->prepare("
        SELECT
            id,
            name,
            username,
            email,
            role,
            shift_id,
            is_active,
            must_change_password,
            session_version
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$_SESSION['user_id']]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$user ||
        (int) $user['is_active'] !== 1 ||
        (int) $user['session_version'] !==
        (int) ($_SESSION['session_version'] ?? -1)
    ) {
        $_SESSION = [];
        session_destroy();

        sendResponse(
            401,
            'error',
            'Session sudah tidak valid.'
        );
    }

    return $user;
}


// 2. Memastikan role pengguna sesuai
function requireRole(string $role): array
{
    $user = requireLogin();

    if ($user['role'] !== $role) {
        sendResponse(
            403,
            'error',
            'Anda tidak memiliki hak akses.'
        );
    }

    return $user;
}


// 3. Membatasi akses khusus Admin
function requireAdmin(): array
{
    return requireRole('admin');
}


// 4. Membatasi akses khusus Kasir
function requireCashier(): array
{
    return requireRole('kasir');
}


// 5. Memvalidasi CSRF token
function requireCsrf(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        sendResponse(
            403,
            'error',
            'CSRF token tidak valid.'
        );
    }
}
