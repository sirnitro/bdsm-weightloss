<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/functions.php';

if (!is_logged_in()) {
    $_SESSION['flash_error'] = "You must be logged in to accept an invite.";
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$orientation = $_SESSION['orientation'] ?? '';

if ($orientation !== 'sub') {
    $_SESSION['flash_error'] = "Only submissives can accept invite links.";
    header("Location: " . BASE_URL . "members/dashboard.php");
    exit;
}

$code = $_GET['code'] ?? '';
if (!$code) {
    $_SESSION['flash_error'] = "Invalid or missing invite code.";
    header("Location: " . BASE_URL . "members/dashboard.php");
    exit;
}

// Fetch invite
$stmt = $pdo->prepare("SELECT * FROM dom_invites WHERE code = ? AND is_revoked = 0 AND (expires_at IS NULL OR expires_at > NOW())");
$stmt->execute([$code]);
$invite = $stmt->fetch();

if (!$invite) {
    $_SESSION['flash_error'] = "This invite is invalid, expired, or revoked.";
    header("Location: " . BASE_URL . "members/dashboard.php");
    exit;
}

// Check if invite is already used
if ($invite['used_by']) {
    $_SESSION['flash_error'] = "This invite has already been used.";
    header("Location: " . BASE_URL . "members/dashboard.php");
    exit;
}

// Check if user is already linked
$stmt = $pdo->prepare("SELECT dom_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$existing = $stmt->fetchColumn();

if ($existing) {
    $_SESSION['flash_error'] = "You are already linked to a dominant. Unlink before using another invite.";
    header("Location: " . BASE_URL . "members/dashboard.php");
    exit;
}

// Link the user to the dominant
$stmt = $pdo->prepare("UPDATE users SET dom_id = ? WHERE id = ?");
$stmt->execute([$invite['dom_id'], $user_id]);

// Mark invite as used
$stmt = $pdo->prepare("UPDATE dom_invites SET used_by = ? WHERE id = ?");
$stmt->execute([$user_id, $invite['id']]);

$_SESSION['flash_success'] = "You are now linked to your Dominant!";
header("Location: " . BASE_URL . "members/dashboard.php");
exit;

