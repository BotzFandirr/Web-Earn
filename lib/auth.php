<?php

require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, name, email, role, is_verified, points FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => (int) $_SESSION['user_id']]);

    $user = $stmt->fetch();

    return $user ?: null;
}

function require_guest(): void
{
    if (current_user()) {
        redirect('/dashboard.php');
    }
}

function require_auth(): array
{
    $user = current_user();

    if (!$user) {
        redirect('/login.php');
    }

    return $user;
}

function require_admin(): array
{
    $user = require_auth();

    if (($user['role'] ?? 'user') !== 'admin') {
        flash_set('danger', 'Akses ditolak. Halaman ini khusus admin.');
        redirect('/dashboard.php');
    }

    return $user;
}

function flash_set(string $type, string $message): void
{
    $allowed = ['success', 'danger', 'warning', 'info'];
    $_SESSION['flash'] = [
        'type' => in_array($type, $allowed, true) ? $type : 'info',
        'message' => $message,
    ];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_validate(?string $token): bool
{
    if (!$token || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}
