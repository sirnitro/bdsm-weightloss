<?php
require_once __DIR__ . '/../inc/config.php';

$token = $_GET['token'] ?? '';
$verified = false;
$invalid = false;

if ($token) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, verified_at = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        $verified = true;
    } else {
        $invalid = true;
    }
} else {
    $invalid = true;
}
?>

<?php require_once __DIR__ . '/../inc/header.php'; ?>

<section class="container">
    <h2>Email Verification</h2>
    <?php if ($verified): ?>
        <p class="success">✅ Your email has been verified! You can now <a href="<?= BASE_URL ?>auth/login.php">log in</a>.</p>
    <?php elseif ($invalid): ?>
        <p class="error">❌ Invalid or expired verification link. Please contact support if the problem persists.</p>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

