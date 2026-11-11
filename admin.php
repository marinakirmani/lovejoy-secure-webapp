<?php
// Retain the original admin.php URL, using the shared admin access check.
require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';
require_admin($pdo);
header('Location: admin_requests.php');
exit;
