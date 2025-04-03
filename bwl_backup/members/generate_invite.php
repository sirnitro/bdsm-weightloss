<?php
// generate_invite.php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';

if (!is_logged_in() || $_SESSION['orientation'] !== 'dom') {
    http_response_code(403);
    exit('Forbidden');
}

$code = bin2hex(random_bytes(6));
$expires = date('Y-m-d H:i:s', strtotime('+7 days'));
$domme_id = $_SESSION['user_id'];
$max_uses = isset($_POST['multi_use']) ? 10 : 1;

$stmt = $pdo->prepare("INSERT INTO domme_invites (domme_id, code, expires_at, max_uses) VALUES (?, ?, ?, ?)");
$stmt->execute([$domme_id, $code, $expires, $max_uses]);

header('Content-Type: application/json');
echo json_encode(['success' => true, 'code' => $code]);

