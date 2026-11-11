<?php
require_once __DIR__ . '/config.php';

$error = '';
$success = '';

// Must be logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// CSRF token (create once per session)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    $description = trim((string)($_POST['description'] ?? ''));
    $contact_method = trim((string)($_POST['contact_method'] ?? ''));

    if ($description === '' || !in_array($contact_method, ['Email', 'Phone'], true)) {
        $error = 'Please fill in all required fields.';
    } else {

        // Optional file upload
        $photoPath = null;

        if (!empty($_FILES['photo']['name'])) {

            if (!is_dir(__DIR__ . '/uploads')) {
                mkdir(__DIR__ . '/uploads', 0755, true);
            }

            if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['photo']['tmp_name'];
                $origName = basename($_FILES['photo']['name']);

                // Validate size and MIME content before moving uploaded data.
                $maxBytes = 5 * 1024 * 1024;
                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                ];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $detectedType = $finfo->file($tmpName);
                if ($_FILES['photo']['size'] > $maxBytes) {
                    $error = 'Images must be 5 MB or smaller.';
                } elseif (!isset($allowedTypes[$detectedType])) {
                    $error = 'Only jpg, png, gif and webp images are allowed.';
                } else {
                    $extension = $allowedTypes[$detectedType];
                    $newName = bin2hex(random_bytes(16)) . '.' . $extension;
                    $dest = __DIR__ . '/uploads/' . $newName;
                    if (move_uploaded_file($tmpName, $dest)) {
                        $photoPath = 'uploads/' . $newName;
                    } else {
                        $error = 'Photo upload failed.';
                    }
                }
            } else {
                $error = 'Photo upload error.';
            }
        }

        if ($error === '') {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO evaluation_requests (user_id, description, contact_method, photo_path)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    (int)$_SESSION['user_id'],
                    $description,
                    $contact_method,
                    $photoPath
                ]);

                $success = 'Request submitted successfully!';
            } catch (PDOException $e) {
                // For coursework/debugging we show message:
                $error = 'Unable to save the request. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Request Evaluation</title>
</head>
<body>
    <h1>Request Evaluation</h1>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <?php if ($error): ?>
        <div style="color:red; background:#ffd6de; padding:8px;">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="color:green; background:#d6ffdf; padding:8px;">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <p>
            <label>Description:</label><br>
            <textarea name="description" rows="5" cols="50"><?= e($_POST['description'] ?? '') ?></textarea>
        </p>

        <p>
            <label>Preferred contact method:</label><br>
            <select name="contact_method">
                <?php
                    $current = $_POST['contact_method'] ?? 'Email';
                    $opts = ['Email', 'Phone'];
                    foreach ($opts as $opt) {
                        $sel = ($current === $opt) ? 'selected' : '';
                        echo '<option ' . $sel . '>' . e($opt) . '</option>';
                    }
                ?>
            </select>
        </p>

        <p>
            <label>Upload photo (optional):</label><br>
            <input type="file" name="photo">
        </p>

        <button type="submit">Submit Request</button>
    </form>
</body>
</html>

