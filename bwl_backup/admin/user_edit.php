<?php
require_once 'config.php';
require_once 'header.php';

$user_id = $_GET['id'] ?? 0;
if (!is_numeric($user_id)) {
    header("Location: users.php");
    exit;
}

$errors = [];
$success = false;

// Get user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p>User not found.</p>";
    require_once 'footer.php';
    exit;
}

// QueryChallenges
$stmt = $pdo->prepare("
    SELECT c.id, c.title, uc.enrolled_at, uc.completed_at
    FROM user_challenges uc
    JOIN challenges c ON c.id = uc.challenge_id
    WHERE uc.user_id = ?
    ORDER BY uc.enrolled_at DESC
");
$stmt->execute([$user_id]);
$user_challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query Programs
$stmt = $pdo->prepare("
    SELECT p.id, p.name, up.enrolled_at, up.completed_at
    FROM user_programs up
    JOIN programs p ON p.id = up.program_id
    WHERE up.user_id = ?
    ORDER BY up.enrolled_at DESC
");
$stmt->execute([$user_id]);
$user_programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// All available programs
$all_programs = $pdo->query("SELECT id, name FROM programs WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// All available challenges
$all_challenges = $pdo->query("SELECT id, title FROM challenges ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);


// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $display_name = trim($_POST['display_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $role = $_POST['role'] ?? 'user';
  $is_verified = isset($_POST['is_verified']) ? 1 : 0;
  $new_pass = $_POST['new_pass'] ?? '';
  $reset_2fa = isset($_POST['reset_2fa']);
  $orientation = $_POST['orientation'] ?? 'sub'; // ✅ Ttheir line prevents both errors
 

    // Optional password reset
    $new_pass = $_POST['new_password'] ?? '';
    $reset_2fa = isset($_POST['reset_2fa']);
    $errors = [];

    if (!in_array($role, ['user', 'admin'])) {
        $errors[] = "Invalid role.";
    }

    if ($new_pass && strlen($new_pass) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

if (empty($errors)) {
    $query = "UPDATE users SET display_name = ?, email = ?, role = ?, orientation = ?, is_verified = ?";
    $params = [$display_name, $email, $role, $orientation, $is_verified];

    if ($new_pass) {
        $query .= ", password = ?";
        $params[] = password_hash($new_pass, PASSWORD_DEFAULT);
    }

    if ($reset_2fa) {
        $query .= ", 2fa_secret = NULL, backup_codes = NULL";
    }

    $query .= " WHERE id = ?";
    $params[] = $user_id;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $success = "User updated successfully.";

    // Refresh user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}


}
// Handle program/challenge enroll/remove
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enroll_program'])) {
        $pid = (int)$_POST['program_id'];
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_programs (user_id, program_id, enrolled_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $pid]);
    }

    if (isset($_POST['remove_program'])) {
        $pid = (int)$_POST['program_id'];
        $stmt = $pdo->prepare("DELETE FROM user_programs WHERE user_id = ? AND program_id = ?");
        $stmt->execute([$user_id, $pid]);
    }

    if (isset($_POST['enroll_challenge'])) {
        $cid = (int)$_POST['challenge_id'];
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_challenges (user_id, challenge_id, enrolled_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $cid]);
    }

    if (isset($_POST['remove_challenge'])) {
        $cid = (int)$_POST['challenge_id'];
        $stmt = $pdo->prepare("DELETE FROM user_challenges WHERE user_id = ? AND challenge_id = ?");
        $stmt->execute([$user_id, $cid]);
    }

    header("Location: user_edit.php?id=" . $user_id); // Refresh to reflect changes
    exit;
}

?>

<h2>Edit User: <?= htmlspecialchars($user['display_name'] ?: $user['email']) ?> #<?= $user['id'] ?></h2>

<div class="user-status-card">
    <h3>User Summary</h3>
    <ul>
        <li><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></li>
        <li><strong>2FA:</strong> <?= $user['2fa_secret'] ? '✅ Enabled' : '❌ Not Enabled' ?></li>
        <li><strong>Backup Codes:</strong>
            <?php
            $codes = $user['backup_codes'] ? json_decode($user['backup_codes'], true) : [];
            echo count($codes);
            ?>
        </li>
        <li><strong>Last Login:</strong> <?= $user['last_login'] ? date('Y-m-d H:i:s', strtotime($user['last_login'])) : '—' ?></li>
        <li><strong>Last IP:</strong> <?= $user['last_ip'] ?? '—' ?></li>
    </ul>
</div>

<?php if ($user_challenges): ?>
    <div class="user-status-card">
        <h3>Challenge Enrollments</h3>
        <ul>
            <?php foreach ($user_challenges as $ch): ?>
                <li>
                    <strong><?= htmlspecialchars($ch['title']) ?></strong><br>
                    Enrolled: <?= date('Y-m-d', strtotime($ch['enrolled_at'])) ?><br>
                    <?= $ch['completed_at'] ? "Completed: " . date('Y-m-d', strtotime($ch['completed_at'])) : "<em>In Progress</em>" ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else: ?>
    <div class="user-status-card">
        <h3>Challenge Enrollments</h3>
        <p>No challenges enrolled yet.</p>
    </div>
<?php endif; ?>

<?php if ($user_programs): ?>
    <div class="user-status-card">
        <h3>Program Enrollments</h3>
        <ul>
<?php foreach ($user_programs as $p): ?>
    <li>
        <strong><?= htmlspecialchars($p['name']) ?></strong>
        <?php if (!empty($p['style'])): ?>
            <span class="badge"><?= htmlspecialchars($p['style']) ?></span>
        <?php endif; ?><br>
        Enrolled: <?= date('Y-m-d', strtotime($p['enrolled_at'])) ?><br>
        <?= $p['completed_at'] ? "Completed: " . date('Y-m-d', strtotime($p['completed_at'])) : "<em>In Progress</em>" ?>
    </li>
<?php endforeach; ?>

<div class="user-status-card">
    <h3>Enroll in a Program</h3>
    <form method="POST">
        <select name="program_id">
            <?php foreach ($all_programs as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button name="enroll_program" class="btn-small">Enroll</button>
        <button name="remove_program" class="btn-small btn-ban" onclick="return confirm('Remove user from ttheir program?')">Remove</button>
    </form>
</div>

<div class="user-status-card">
    <h3>Enroll in a Challenge</h3>
    <form method="POST">
        <select name="challenge_id">
            <?php foreach ($all_challenges as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
            <?php endforeach; ?>
        </select>
        <button name="enroll_challenge" class="btn-small">Enroll</button>
        <button name="remove_challenge" class="btn-small btn-ban" onclick="return confirm('Remove user from ttheir challenge?')">Remove</button>
    </form>
</div>


        </ul>
    </div>
<?php else: ?>
    <div class="user-status-card">
        <h3>Program Enrollments</h3>
        <p>No programs enrolled yet.</p>
    </div>
<?php endif; ?>






<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>
<?php if ($errors): ?>
    <ul class="error-list"><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
<?php endif; ?>

<form method="POST" class="admin-form styled-user-form">
    <fieldset>
        <legend>Account Info</legend>

	<label for="email">Email:</label>
	<input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>


        <label>Display Name:</label>
        <input type="text" name="display_name" value="<?= htmlspecialchars($user['display_name']) ?>">

        <label>Role:</label>
        <select name="role">
            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>

<label for="orientation">Orientation:</label>
<select name="orientation" id="orientation" class="form-control">
    <option value="sub" <?= $user['orientation'] === 'sub' ? 'selected' : '' ?>>sub</option>
    <option value="dom" <?= $user['orientation'] === 'dom' ? 'selected' : '' ?>>dom</option>
</select>

        <label class="checkbox-label">
            <input type="checkbox" name="is_verified" <?= $user['is_verified'] ? 'checked' : '' ?>>
            Mark as Verified
        </label>
    </fieldset>

    <fieldset>
        <legend>Security Options</legend>

        <label>New Password:</label>
        <input type="password" name="new_password" placeholder="Leave blank to keep current password">

        <label class="checkbox-label">
            <input type="checkbox" name="reset_2fa"> Reset 2FA and Backup Codes
        </label>
    </fieldset>

    <button type="submit" class="btn-primary">Save Changes</button>
</form>

<p><a href="users.php" class="btn-small">← Back to User List</a></p>

<?php require_once 'footer.php'; ?>

