<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$user_id = (int)$_GET['id'];

// Get current status
$stmt = $pdo->prepare("SELECT is_suspended FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $new_status = $user['is_suspended'] ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE users SET is_suspended = ? WHERE id = ?");
    $stmt->execute([$new_status, $user_id]);
}

// Redirect back
header("Location: users.php");
exit;

