<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../auth/captcha.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha_token = $_POST['cf-turnstile-response'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'];

    // CAPTCHA check
    if (USE_CAPTCHA && !verify_captcha($captcha_token)) {
        $errors[] = "CAPTCHA verification failed.";
    }

    // Brute force protection: check recent failed attempts
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > (NOW() - INTERVAL 15 MINUTE)");
    $stmt->execute([$ip]);
    $attempts = $stmt->fetchColumn();
    $maxAttempts = 5;

    if ($attempts >= $maxAttempts) {
        $errors[] = "Too many login attempts. Try again in 15 minutes.";
    }

    // Proceed if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $log = $pdo->prepare("INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)");
            $log->execute([$ip, $email]);
            $errors[] = "Invalid email or password.";
        } elseif (!$user['is_verified']) {
            $errors[] = "Please verify your email before logging in.";
        } elseif ($user['is_banned']) {
            $errors[] = "Your account has been banned.";
        }elseif ($user['is_suspended']) {
            $errors[] = "Your account has been suspended.";
        } else {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
	    $_SESSION['orientation'] = $user['orientation']; // after successful login

	    
	    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW(), last_ip = ? WHERE id = ?");
	    $stmt->execute([$_SERVER['REMOTE_ADDR'], $user['id']]);

            // Check for 2FA requirement
            if (USE_2FA && !empty($user['2fa_secret'])) {
                $_SESSION['2fa_required'] = true;
                header("Location: ../auth/verify_2fa.php");
                exit;
            }

if ($user['role'] === 'admin') {
    header("Location: ../admin/index.php");
    exit;
} else {
    header("Location: ../members/dashboard.php");
    exit;
}

        }
    }
}
?>

<?php require_once __DIR__ . '/../inc/header.php'; ?>

<section class="container">
    <h2>Login</h2>

    <?php if ($errors): ?>
        <ul class="error-list">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <?php if (USE_CAPTCHA): ?>
            <div class="cf-turnstile" data-sitekey="<?= CAPTCHA_SITE_KEY ?>"></div>
        <?php endif; ?>

        <button type="submit" class="btn-primary">Login</button>
    </form>
</section>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

