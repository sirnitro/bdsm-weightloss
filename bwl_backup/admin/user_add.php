<?php
require_once 'config.php';
require_once 'header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $display_name = trim($_POST['display_name'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $orientation = $_POST['orientation'] ?? 'sub';


    // Validate
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if (!in_array($role, ['user', 'admin'])) {
        $errors[] = "Invalid role selected.";
    }
    if (!in_array($orientation, ['sub', 'dom'])) {
        $errors[] = "Invalid orientation selected.";
    }

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email already exists.";
    }

    // Add user
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
	$stmt = $pdo->prepare("INSERT INTO users (email, password, display_name, role, orientation, is_verified, created_at)
                       VALUES (?, ?, ?, ?, ?, 1, NOW())");
	$stmt->execute([$email, $hash, $display_name, $role, $orientation]);
        $success = true;
    }
}
?>

<h2>Add New User</h2>

<?php if ($success): ?>
    <p class="success">✅ New user created successfully.</p>
<?php endif; ?>

<?php if ($errors): ?>
    <ul class="error-list">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="POST" class="form-grid">
    <div class="form-group">
        <label for="display_name">Display Name:</label>
        <input type="text" name="display_name" id="display_name" required value="<?= htmlspecialchars($display_name ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="email">Email Address:</label>
        <input type="email" name="email" id="email" required value="<?= htmlspecialchars($email ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="password">Password (min 6 chars):</label>
        <input type="password" name="password" id="password" required>
    </div>

    <div class="form-group">
        <label for="role">System Role:</label>
        <select name="role" id="role">
            <option value="user" <?= ($role ?? '') === 'user' ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
    </div>

    <div class="form-group">
        <label for="orientation">Orientation:</label>
        <select name="orientation" id="orientation">
            <option value="sub" <?= ($orientation ?? '') === 'sub' ? 'selected' : '' ?>>sub</option>
            <option value="dom" <?= ($orientation ?? '') === 'dom' ? 'selected' : '' ?>>Dominant</option>
        </select>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Add User</button>
    </div>
</form>

<?php require_once 'footer.php'; ?>

