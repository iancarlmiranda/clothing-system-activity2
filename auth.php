<?php
/**
 * Shared access-control helpers. Include at the top of every protected page.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

/** Escape output. */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Require a signed-in user. The role is re-read from the database on every
 * request, so a deleted or demoted account loses access immediately.
 */
function require_login(): void
{
    global $conn;

    if (empty($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT fullname, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) { // account was deleted
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }

    $_SESSION['fullname'] = $row['fullname'];
    $_SESSION['role']     = $row['role'];
}

/** Require a specific role; otherwise show a 403 page. */
function require_role(string $role): void
{
    require_login();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        require __DIR__ . '/forbidden.php';
        exit();
    }
}

function is_admin(): bool
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(400);
        die("Invalid or expired form token. Go back, refresh the page and try again.");
    }
}

/* ---------- Flash messages ---------- */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert ' . e($f['type']) . '">' . e($f['message']) . '</div>';
    }
}
