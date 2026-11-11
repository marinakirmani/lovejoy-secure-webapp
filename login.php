<?php
require __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password required.';
    } else {

        // Fetch user
        $stmt = $pdo->prepare(
            'SELECT id, password_hash, failed_attempts, lock_until
             FROM users
             WHERE email = ?'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Invalid login.';
        } else {

            // Check lockout
            if ($user['lock_until'] !== null && strtotime($user['lock_until']) > time()) {
                $error = 'Account locked. Try again later.';
            } else {

                if (password_verify($password, $user['password_hash'])) {

                    // Successful login → reset attempts
                    $pdo->prepare(
                        'UPDATE users
                         SET failed_attempts = 0, lock_until = NULL
                         WHERE id = ?'
                    )->execute([$user['id']]);

                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    header('Location: dashboard.php');
                    exit;

                } else {

                    // Failed login
                    $attempts = $user['failed_attempts'] + 1;
                    $lockUntil = null;

                    if ($attempts >= 5) {
                        $lockUntil = date('Y-m-d H:i:s', time() + 900); // 15 minutes
                        $attempts = 0;
                    }

                    $pdo->prepare(
                        'UPDATE users
                         SET failed_attempts = ?, lock_until = ?
                         WHERE id = ?'
                    )->execute([$attempts, $lockUntil, $user['id']]);

                    $error = 'Invalid login.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<h1>Login</h1>

<?php if ($error): ?>
<p style="color:red"><?= e($error) ?></p>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>

<p><a href="forgot.php">Forgot password?</a></p>

</body>
</html>

