<?php
// public/members/toggle_invite.php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$invite_id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$invite_id || !in_array($action, ['revoke', 'restore'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

// Make sure the invite belongs to the current dominant
$stmt = $pdo->prepare("SELECT * FROM domme_invites WHERE id = ? AND domme_id = ?");
$stmt->execute([$invite_id, $user_id]);
$invite = $stmt->fetch();

if (!$invite) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Invite not found']);
    exit;
}

// Toggle the status
$new_status = $action === 'revoke' ? 1 : 0;
$stmt = $pdo->prepare("UPDATE domme_invites SET is_revoked = ? WHERE id = ?");
$stmt->execute([$new_status, $invite_id]);

$status_label = $new_status ? 'revoked' : 'active';
echo json_encode(['status' => 'success', 'new_state' => $status_label]);
exit;

