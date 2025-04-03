<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../auth/2fa.php';

// Require user to be partially logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['2fa_required']) {
    header("Location: login.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    $stmt = $pdo->prepare("SELECT 2fa_secret FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && verify_2fa_code($user['2fa_secret'], $code)) {
        unset($_SESSION['2fa_required']); // Clear the 2FA lock
        header("Location: dashboard.php");
        exit;
    } elseif (verify_backup_code($_SESSION['user_id'], $code)) {
        unset($_SESSION['2fa_required']);
        header("Location: dashboard.php");
        exit;
    } else {
        $errors[] = "Invalid 2FA code or backup code.";
    }

}
?>

<?php require_once __DIR__ . '/../inc/header.php'; ?>

<section class="container">
    <h2>Two-Factor Authentication</h2>
    <p>Please enter the 6-digit code from your authenticator app.</p>

    <?php if ($errors): ?>
        <ul class="error-list">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST">
        <label for="code">2FA Code:</label>
        <input type="text" name="code" pattern="\\d{6}" required placeholder="123456" maxlength="6">
        <button type="submit" class="btn-primary">Verify</button>
    </form>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

