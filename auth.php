<?php
// auth.php
// NOTE: config.php is already required by the page that includes auth.php.
// So DO NOT require config.php again in here.

// Require login for any protected page
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Get current user record
function current_user(PDO $pdo): ?array {
    if (empty($_SESSION['user_id'])) return null;

    $stmt = $pdo->prepare('SELECT id, email, name, phone, role FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    return $u ?: null;
}

// Require admin role
function require_admin(PDO $pdo): void {
    require_login();
    $u = current_user($pdo);
    if (!$u || ($u['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo "403 Forbidden (admin only)";
        exit;
    }
}

