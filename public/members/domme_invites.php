<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/header.php';

if (!is_logged_in() || $_SESSION['orientation'] !== 'dom') {
    header("Location: " . BASE_URL . "/members/dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = false;
$message = '';

// Handle new invite generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $code = strtoupper(bin2hex(random_bytes(4))); // 8-char alphanumeric
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
    $max_uses = isset($_POST['multi_use']) ? 10 : 1;

    $stmt = $pdo->prepare("INSERT INTO dom_invites (dom_id, code, expires_at, max_uses) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $code, $expires, $max_uses]);

    $success = true;
    $message = "Invite created successfully!";
}

// Handle revocation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['revoke'])) {
    $invite_id = $_POST['invite_id'];
    $stmt = $pdo->prepare("UPDATE dom_invites SET is_revoked = 1 WHERE id = ? AND dom_id = ?");
    $stmt->execute([$invite_id, $user_id]);
    $message = "Invite revoked.";
}

// Fetch invite theirtory
$stmt = $pdo->prepare("SELECT * FROM dom_invites WHERE dom_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$invites = $stmt->fetchAll();
?>

<section class="container">
    <h2>sub Invite Links</h2>

    <?php if ($message): ?>
        <p class="success">✅ <?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" class="form-inline">
        <label><input type="checkbox" name="multi_use"> Allow multiple subs to use the link</label>
        <button type="submit" name="generate" class="btn-primary">Generate New Invite Link</button>
    </form>

    <h3>Invite theirtory</h3>

    <table class="table">
        <thead>
            <tr>
                <th>Invite Link</th>
                <th>Uses</th>
                <th>Expires</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($invites as $invite): ?>
            <?php
            $used = $invite['used_by'] ? 1 : 0;
            $status = $invite['is_revoked'] ? 'Revoked' : ($used >= $invite['max_uses'] ? 'Used' : 'Active');
            $link = BASE_URL . "/auth/link.php?code=" . $invite['code'];
            ?>
            <tr>
                <td>
                    <input type="text" class="copy-field" value="<?= htmlspecialchars($link) ?>" readonly>
                    <button class="btn-small copy-btn">Copy</button>
                </td>
                <td><?= $used ?> / <?= $invite['max_uses'] ?></td>
                <td><?= date("M j, Y", strtotime($invite['expires_at'])) ?></td>
                <td><?= $status ?></td>
                <td>
                    <?php if (!$invite['is_revoked'] && $status === 'Active'): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="invite_id" value="<?= $invite['id'] ?>">
                            <button type="submit" name="revoke" class="btn-danger btn-small">Revoke</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<script>
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        const input = btn.previousElementSibling;
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand('copy');
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = 'Copy', 1500);
    });
});
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

