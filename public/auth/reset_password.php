<?php
require_once __DIR__ . '/../inc/config.php';

$token = $_GET['token'] ?? '';
$errors = [];
$success = false;

if (!$token) {
    $errors[] = "No reset token provided.";
} else {
    $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user || strtotime($user['reset_expires']) < time()) {
        $errors[] = "Invalid or expired token.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pass = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        if (strlen($pass) < 6) {
            $errors[] = "Password too short.";
        } elseif ($pass !== $confirm) {
            $errors[] = "Passwords do not match.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$hash, $user['id']]);
            $success = true;
        }
    }
}
?>

<?php require_once __DIR__ . '/../inc/header.php'; ?>
<section class="container">
    <h2>Reset Your Password</h2>
    <?php if ($success): ?>
        <p class="success">Password has been reset. You may now <a href="login.php">log in</a>.</p>
    <?php elseif ($errors): ?>
        <ul class="error-list"><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
    <?php endif; ?>

    <?php if (!$success && empty($errors) || ($_SERVER['REQUEST_METHOD'] === 'POST' && !$success)): ?>
        <form method="POST">
            <label>New Password:</label>
            <input type="password" name="password" required>

            <label>Confirm Password:</label>
            <input type="password" name="confirm" required>

            <button type="submit" class="btn-primary">Reset Password</button>
        </form>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>

