<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';

if (!is_logged_in()) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate and sanitize the invite ID
$invite_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($invite_id <= 0) {
    $_SESSION['flash_error'] = "Invalid invite ID.";
    header("Location: " . BASE_URL . "members/domme_invites.php");
    exit;
}

// Ensure the invite belongs to the current domme
$stmt = $pdo->prepare("UPDATE domme_invites SET is_revoked = 1 WHERE id = ? AND domme_id = ?");
$stmt->execute([$invite_id, $user_id]);

if ($stmt->rowCount() > 0) {
    $_SESSION['flash_success'] = "Invite successfully revoked.";
} else {
    $_SESSION['flash_error'] = "Failed to revoke invite. It may not exist or you do not have permission.";
}

header("Location: " . BASE_URL . "members/domme_invites.php");
exit;

