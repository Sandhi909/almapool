<?php
// Auth session + RBAC + CSRF.

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth_check(): bool
{
    return auth_user() !== null;
}

function auth_role(): ?string
{
    return auth_user()['role'] ?? null;
}

function is_admin(): bool { return auth_role() === 'admin'; }
function is_kasir(): bool { return in_array(auth_role(), ['admin', 'kasir'], true); }
function is_owner(): bool { return in_array(auth_role(), ['admin', 'manajemen'], true); }
function is_internal_control(): bool { return auth_role() === 'internal_control'; }

function auth_login(array $user): void
{
    $old_token = $_SESSION['csrf_token'] ?? null;
    if (!headers_sent()) {
        session_regenerate_id(true);
    }
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'username' => $user['username'],
        'role' => $user['role'],
    ];
    if ($old_token) {
        $_SESSION['csrf_token'] = $old_token;
    }
}

function auth_logout(): void
{
    unset($_SESSION['user']);
}

/** Wajib login. */
function require_login(): void
{
    if (!auth_check()) {
        header('Location: ' . url('index.php?page=login'));
        exit;
    }
}

/** Wajib salah satu role. Backend selalu validasi, bukan hanya menu. */
function require_role(array $roles): void
{
    require_login();
    if (!in_array(auth_role(), $roles, true)) {
        http_response_code(403);
        die('403 - Akses ditolak untuk role Anda.');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        die('419 - Token CSRF tidak valid. Muat ulang halaman.');
    }
}

function json_response($data, int $status = 200): void
{
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flash(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}
