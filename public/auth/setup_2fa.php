<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/2fa.php';
require_login();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = null;

// Fetch current 2FA status
$stmt = $pdo->prepare("SELECT 2fa_secret FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$has2FA = !empty($user['2fa_secret']);

// Handle disabling 2FA
if (isset($_POST['disable'])) {
    $stmt = $pdo->prepare("UPDATE users SET 2fa_secret = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
    $success = "2FA has been disabled.";
    $has2FA = false;
}

// Handle setup
if (!$has2FA && isset($_POST['setup'])) {
    $secret = generate_2fa_secret();
    $_SESSION['pending_2fa'] = $secret;
}

// Handle verification
if (isset($_POST['verify'])) {
    $code = $_POST['code'] ?? '';
    $pending_secret = $_SESSION['pending_2fa'] ?? null;

if ($pending_secret && verify_2fa_code($pending_secret, $code)) {
    $codes = generate_backup_codes();
    
    $stmt = $pdo->prepare("UPDATE users SET 2fa_secret = ?, backup_codes = ? WHERE id = ?");
    $stmt->execute([$pending_secret, json_encode($codes), $user_id]);
    
    unset($_SESSION['pending_2fa']);
    $has2FA = true;

    // Output success + codes
    echo "<section class='container'>";
    echo "<h2>Two-Factor Authentication Enabled</h2>";
    echo "<p class='success'>✅ 2FA is now active. Below are your backup codes:</p>";
    echo "<ul class='backup-codes'>";
    foreach ($codes as $code) {
        echo "<li><code>$code</code></li>";
    }
    echo "</ul><p>Store these somewhere safe. Each can be used once to bypass 2FA.</p>";
    echo "<p><a href='" . BASE_URL . "members/dashboard.php' class='btn-primary'>Go to Dashboard</a></p>";
    echo "</section>";
    
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}

     else {
        $errors[] = "Invalid verification code.";
    }
}
?>

<?php require_once __DIR__ . '/../inc/header.php'; ?>

<section class="container">
    <h2>Two-Factor Authentication</h2>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if ($errors): ?>
        <ul class="error-list">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($has2FA): ?>
        <p>2FA is currently <strong>enabled</strong> on your account.</p>
        <form method="POST">
            <button name="disable" type="submit" class="btn-secondary">Disable 2FA</button>
        </form>
    <?php elseif (!isset($_SESSION['pending_2fa'])): ?>
        <p>2FA is currently <strong>disabled</strong>. Click below to begin setup.</p>
        <form method="POST">
            <button name="setup" type="submit" class="btn-primary">Set Up 2FA</button>
        </form>
    <?php else: ?>
        <p>Scan ttheir QR code in your authenticator app:</p>
        <img src="<?= get_qr_code_url($_SESSION['user_id'], $_SESSION['pending_2fa']) ?>" alt="2FA QR Code">

        <form method="POST">
            <label for="code">Enter the 6-digit code:</label>
            <input type="text" name="code" pattern="\\d{6}" required>
            <button name="verify" type="submit" class="btn-primary">Verify & Activate</button>
        </form>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

