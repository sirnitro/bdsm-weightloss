<?php
require_once __DIR__ . '/../inc/config.php';

header('Content-Type: application/json');

if (!is_logged_in() || ($_SESSION['orientation'] ?? '') !== 'dom') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$invite_id = $_POST['invite_id'] ?? 0;
$dom_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM dom_invites WHERE id = ? AND dom_id = ?");
$stmt->execute([$invite_id, $dom_id]);
$invite = $stmt->fetch();

if (!$invite) {
    echo json_encode(['success' => false, 'message' => 'Invite not found']);
    exit;
}

$new_status = $invite['is_revoked'] ? 0 : 1;
$stmt = $pdo->prepare("UPDATE dom_invites SET is_revoked = ? WHERE id = ?");
$stmt->execute([$new_status, $invite_id]);

echo json_encode(['success' => true, 'new_status' => $new_status]);

