<?php
require_once __DIR__ . '/../inc/config.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);

            $link = BASE_URL . "auth/reset_password.php?token=" . urlencode($token);
            $subject = "Password Reset Request";
            $body = "<p>Click below to reset your password:</p><p><a href=\"$link\">$link</a></p>";

            send_email($email, $subject, $body);
            $success = true;
        }
    }
}
?>

<?php require_once __DIR__ . '/../inc/header.php'; ?>
<section class="container">
    <h2>Forgot Password</h2>
    <?php if ($success): ?>
        <p class="success">A reset link has been sent if the email is registered.</p>
    <?php else: ?>
        <?php if ($errors): ?>
            <ul class="error-list"><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
        <?php endif; ?>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" required>
            <button type="submit" class="btn-primary">Send Reset Link</button>
        </form>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>

