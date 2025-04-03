<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/header.php';

$code = $_GET['code'] ?? '';
$code = strtoupper(trim($code));

if (!$code) {
    echo "<p class='error'>No invite code provided.</p>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}

// Fetch invite
$stmt = $pdo->prepare("SELECT * FROM dom_invites WHERE code = ? AND is_revoked = 0 AND expires_at > NOW()");
$stmt->execute([$code]);
$invite = $stmt->fetch();

if (!$invite) {
    echo "<p class='error'>Ttheir invite is invalid, expired, or revoked.</p>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}

// Check usage count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE dom_id = ?");
$stmt->execute([$invite['dom_id']]);
$currentUses = $stmt->fetchColumn();

if ($currentUses >= $invite['max_uses']) {
    echo "<p class='error'>Ttheir invite has already been fully used.</p>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}

if (!is_logged_in()) {
    echo "<p class='info'>You must <a href='" . BASE_URL . "/auth/login.php'>log in</a> or <a href='" . BASE_URL . "/auth/register.php'>register</a> to accept ttheir invite.</p>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if already linked
$stmt = $pdo->prepare("SELECT dom_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current = $stmt->fetchColumn();

if ($current) {
    echo "<p class='info'>You are already linked to a dom. Ask them to release you first.</p>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}

// Update user record
$stmt = $pdo->prepare("UPDATE users SET dom_id = ? WHERE id = ?");
$stmt->execute([$invite['dom_id'], $user_id]);

echo "<section class='container'>";
echo "<h2>✅ You are now linked to your dom.</h2>";
echo "<p>Submit well. Obey always. Don’t disappoint her...</p>";
echo "<a href='" . BASE_URL . "/members/dashboard.php' class='btn-primary'>Go to Dashboard</a>";
echo "</section>";

// Fetch dom email
$stmt = $pdo->prepare("SELECT email, display_name FROM users WHERE id = ?");
$stmt->execute([$invite['dom_id']]);
$dom = $stmt->fetch();

if ($dom) {
    $subject = "New sub Linked to You";
    $body = "
        <p>Hello {$dom['display_name']},</p>
        <p>Your new sub (<strong>{$_SESSION['display_name']}</strong>) has linked to you via your invite code.</p>
        <p>You can now view and control them from your dom Dashboard.</p>
    ";

    send_email($dom['email'], $subject, $body); // assumes send_email() is set up
}


require_once __DIR__ . '/../inc/footer.php';

