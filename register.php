<?php
require __DIR__ . '/config.php';

/**
 * Register.php
 * - CSRF protected
 * - Validates input
 * - Inserts into users(name,email,password_hash,phone,role)
 * - Redirects to login.php on success
 */

$error = '';
$name = '';
$email = '';
$phone = '';

/** CSRF token (generate once per session) */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- CSRF check ---
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    // --- Read/validate inputs ---
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $phone = trim((string)($_POST['phone'] ?? ''));

    if ($name === '' || $email === '' || $password === '' || $phone === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if email already exists
            $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $check->execute([$email]);

            if ($check->fetch()) {
                $error = 'Email already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'user';

                $stmt = $pdo->prepare(
                    'INSERT INTO users (name, email, password_hash, phone, role)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->execute([$name, $email, $hash, $phone, $role]);

                // optional: rotate token after successful POST
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            // Show a helpful message while you are developing
            $error = 'Registration failed. Please check your details and try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register</title>
</head>
<body>
  <h1>Register</h1>

  <?php if ($error !== ''): ?>
    <p style="color:red;"><?= e($error) ?></p>
  <?php endif; ?>

  <form method="post" action="register.php">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

    <label>
      Name:
      <input type="text" name="name" value="<?= e($name) ?>" required>
    </label>
    <br><br>

    <label>
      Email:
      <input type="email" name="email" value="<?= e($email) ?>" required>
    </label>
    <br><br>

    <label>
      Password:
      <input type="password" name="password" required>
    </label>
    <br><br>

    <label>
      Phone:
      <input type="text" name="phone" value="<?= e($phone) ?>" required>
    </label>
    <br><br>

    <button type="submit">Register</button>
  </form>

  <p><a href="login.php">Login</a></p>
</body>
</html>



