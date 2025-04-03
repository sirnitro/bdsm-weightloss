<?php
// link_submissive.php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';

if (!is_logged_in()) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SESSION['orientation'] !== 'sub') {
    echo "<p class='error'>Only submissive users can use an invite code.</p>";
    exit;
}

$code = $_GET['code'] ?? '';
if (!$code) {
    echo "<p class='error'>No invite code provided.</p>";
    exit;
}

// Fetch invite
$stmt = $pdo->prepare("SELECT * FROM domme_invites WHERE code = ? AND is_revoked = 0 AND (expires_at IS NULL OR expires_at > NOW())");
$stmt->execute([$code]);
$invite = $stmt->fetch();

if (!$invite) {
    echo "<p class='error'>Invalid or expired invite code.</p>";
    exit;
}

// Make sure sub isn't already linked
$stmt = $pdo->prepare("SELECT domme_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current = $stmt->fetchColumn();

if ($current) {
    echo "<p class='info'>You are already linked to a Dominant. Ask them to unlink you first.</p>";
    exit;
}

// Link the user
$stmt = $pdo->prepare("UPDATE users SET domme_id = ? WHERE id = ?");
$stmt->execute([$invite['domme_id'], $user_id]);

// Mark invite used (for 1-time use)
if ($invite['max_uses'] == 1) {
    $stmt = $pdo->prepare("UPDATE domme_invites SET is_revoked = 1, used_by = ? WHERE id = ?");
    $stmt->execute([$user_id, $invite['id']]);
}

// Success
$_SESSION['flash_success'] = "You are now linked to your Dominant.";
header("Location: /members/dashboard.php");
exit;
/auth

