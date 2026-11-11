<?php
require __DIR__ . '/config.php';

$error = '';
$success = '';
$reset_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Email is required';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $update = $pdo->prepare(
                'UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?'
            );
            $update->execute([$token, $expires, $email]);

            $reset_link = "http://localhost/lovejoy/reset.php?token=$token";
            $success = 'Password reset link generated below';
        } else {
            $error = 'No account found with that email';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
</head>
<body>

<h1>Forgot Password</h1>

<?php if ($error): ?>
    <p style="color:red"><?= e($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green"><?= e($success) ?></p>
    <p><a href="<?= e($reset_link) ?>">Reset Password</a></p>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>
    <button type="submit">Send reset link</button>
</form>

<p><a href="login.php">Back to login</a></p>

</body>
</html>

