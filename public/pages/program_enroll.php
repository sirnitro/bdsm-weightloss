<?php
require_once __DIR__ . '/../inc/config.php';

if (!is_logged_in()) {
    header("Location: /auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$slug = $_POST['slug'] ?? '';

$stmt = $pdo->prepare("SELECT id FROM programs WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$program = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$program) {
    $_SESSION['flash_error'] = "Invalid program.";
    header("Location: /pages/programs.php");
    exit;
}

$program_id = $program['id'];

// Insert if not already enrolled
$stmt = $pdo->prepare("INSERT IGNORE INTO user_programs (user_id, program_id, enrolled_at) VALUES (?, ?, NOW())");
$stmt->execute([$user_id, $program_id]);

$_SESSION['flash_success'] = "You've been enrolled in the program!";
header("Location: program_view.php?slug=" . urlencode($slug));
exit;

