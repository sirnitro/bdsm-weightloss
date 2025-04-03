<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../auth/captcha.php';

$errors = [];
$success = false;

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $display_name = trim($_POST['display_name'] ?? '');
    $orientation = $_POST['orientation'] ?? '';
$title = trim($_POST['title'] ?? '');
$pronouns = trim($_POST['pronouns'] ?? '');

    $captcha_token = $_POST['cf-turnstile-response'] ?? '';

    // CAPTCHA check
    if (USE_CAPTCHA && !verify_captcha($captcha_token)) {
        $errors[] = "CAPTCHA validation failed.";
    }

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

if (!in_array($orientation, ['dom', 'sub'])) {
    $errors[] = "Please select a valid orientation.";
}


    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email is already registered.";
    }

    // Register
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

$stmt = $pdo->prepare("INSERT INTO users (
    email,
    password,
    display_name,
    orientation,
    title,
    pronouns,
    verification_token,
    is_verified,
    role,
    created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'user', NOW())");

$stmt->execute([
    $email,
    $hash,
    $display_name,
    $orientation,
    $title,
    $pronouns,
    $token
]);


        $verify_url = BASE_URL . "auth/verify_email.php?token=" . $token;

        $template = get_email_template('welcome_user');
        $subject = replace_placeholders($template['subject'], ['name' => $display_name ?: 'New User']);
        $body = replace_placeholders($template['body'], ['name' => $display_name ?: 'New User', 'url' => $verify_url]);

        send_email($email, $subject, $body);

        $success = true;
    }
}
?>

<?php require_once __DIR__ . '/../inc/header.php'; ?>

<section class="container">
    <h2>Create Account</h2>
    <?php if ($success): ?>
        <p class="success">Registration successful! Check your email to verify your account.</p>
    <?php else: ?>
        <?php if ($errors): ?>
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

<form method="POST" class="form-card">
    <div class="form-group">
        <label for="email">Email <span class="required">*</span></label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="display_name">Display Name</label>
        <input type="text" name="display_name" id="display_name" value="<?= htmlspecialchars($display_name ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="password">Password <span class="required">*</span></label>
        <input type="password" name="password" id="password" required>
    </div>

    <div class="form-group">
        <label for="confirm">Confirm Password <span class="required">*</span></label>
        <input type="password" name="confirm" id="confirm" required>
    </div>

    <div class="form-group">
        <label for="orientation">I identify as: <span class="required">*</span></label>
        <select name="orientation" id="orientation" required>
            <option value="">-- Please Choose --</option>
            <option value="dom" <?= ($orientation ?? '') === 'dom' ? 'selected' : '' ?>>Dominant</option>
            <option value="sub" <?= ($orientation ?? '') === 'sub' ? 'selected' : '' ?>>Submissive</option>
        </select>
    </div>

    <div class="form-group">
        <label for="title">Title <small>(optional)</small></label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($title ?? '') ?>" placeholder="e.g., Sir, Dom, Mistress">
    </div>

    <div class="form-group">
        <label for="pronouns">Pronouns <small>(optional)</small></label>
        <input type="text" name="pronouns" id="pronouns" value="<?= htmlspecialchars($pronouns ?? '') ?>" placeholder="e.g., they/them">
    </div>

    <?php if (USE_CAPTCHA): ?>
        <div class="form-group">
            <div class="cf-turnstile" data-sitekey="<?= CAPTCHA_SITE_KEY ?>"></div>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <button type="submit" class="btn-primary">Register</button>
    </div>
</form>

    <?php endif; ?>
</section>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

