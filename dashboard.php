<?php
require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';

require_login();
$u = current_user($pdo);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
</head>
<body>

<h1>Dashboard</h1>

<p>You are logged in.</p>

<p>
  <strong>User:</strong> <?= e($u['email'] ?? '') ?><br>
  <strong>Role:</strong> <?= e($u['role'] ?? '') ?>
</p>

<?php if (($u['role'] ?? '') === 'admin'): ?>
  <p><a href="admin.php">Go to Admin Page</a></p>
<?php endif; ?>

<form method="post" action="logout.php">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
    <button type="submit">Logout</button>
</form>
<p><a href="request_evaluation.php">Request an Evaluation</a></p>


</body>
</html>

