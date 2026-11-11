<?php
require __DIR__ . '/config.php';

/*
 * admin_requests.php
 * - Admin-only page
 * - Displays evaluation requests
 */

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check role
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo "403 Forbidden - Admins only";
    exit;
}

// Fetch evaluation requests
try {
    $requests = $pdo->query(
        "SELECT id, description, contact_method, created_at 
         FROM evaluation_requests 
         ORDER BY created_at DESC"
    )->fetchAll();
} catch (PDOException $e) {
    die("Database error loading requests.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin – Evaluation Requests</title>
</head>
<body>

<h1>Evaluation Requests (Admin)</h1>

<p><a href="dashboard.php">← Back to Dashboard</a></p>

<?php if (empty($requests)): ?>
    <p>No evaluation requests found.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Description</th>
            <th>Contact Method</th>
            <th>Submitted At</th>
        </tr>
        <?php foreach ($requests as $r): ?>
            <tr>
                <td><?= e($r['id']) ?></td>
                <td><?= e($r['description']) ?></td>
                <td><?= e($r['contact_method']) ?></td>
                <td><?= e($r['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</body>
</html>

