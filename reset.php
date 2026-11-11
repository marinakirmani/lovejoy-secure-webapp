<?php
require __DIR__ . '/config.php';

/**
 * reset.php
 * URL looks like: reset.php?token=XXXX
 */

$error = '';
$success = '';

$token = trim($_GET['token'] ?? '');

if ($token === '') {
    http_response_code(400);
    exit('Missing reset token.');
}

// When form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check (recommended)
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $newPassword = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    if ($newPassword === '' || $confirm === '') {
        $error = 'Please fill in both password fields.';
    } elseif ($newPassword !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            // Find user with this token AND not expired
            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE reset_token = ?
                  AND reset_expires IS NOT NULL
                  AND reset_expires > NOW()
                LIMIT 1
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'Invalid or expired reset link.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update password + clear token fields so link can’t be reused
                $update = $pdo->prepare("
                    UPDATE users
                    SET password_hash = ?,
                        reset_token = NULL,
                        reset_expires = NULL
                    WHERE id = ?
                ");
                $update->execute([$hash, $user['id']]);

                $success = 'Password updated! You can now log in.';
            }
        } catch (PDOException $e) {
            $error = 'Something went wrong updating your password.';
        }
    }
}

// Ensure CSRF token exists for the reset form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
</head>
<body>

<h1>Reset Password</h1>

<?php if ($error): ?>
    <p style="color:red;"><?php echo e($error); ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?php echo e($success); ?></p>
    <p><a href="login.php">Go to Login</a></p>
<?php else: ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">

        <label>New Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>

        <button type="submit">Update Password</button>
    </form>
<?php endif; ?>

</body>
</html>

