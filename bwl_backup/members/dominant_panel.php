<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/header.php';

if (!is_logged_in() || ($_SESSION['orientation'] ?? '') !== 'dom') {
    echo "<p class='error'>Access denied.</p>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];

// Get all linked submissives
$stmt = $pdo->prepare("SELECT id, display_name, email FROM users WHERE domme_id = ?");
$stmt->execute([$user_id]);
$subs = $stmt->fetchAll();
?>

<section class="container">
    <h2>Your Submissives</h2>

    <?php if (!$subs): ?>
        <p>You have no linked submissives... yet.</p>
    <?php else: ?>
        <ul class="challenge-list">
            <?php foreach ($subs as $sub): ?>
                <li>
                    <strong><?= htmlspecialchars($sub['display_name']) ?></strong>
                    <small>(<?= htmlspecialchars($sub['email']) ?>)</small>
                    <form method="post" action="unlink_sub.php" style="display:inline;">
                        <input type="hidden" name="sub_id" value="<?= $sub['id'] ?>">
                        <button class="btn-small btn-danger" onclick="return confirm('Unlink this submissive?')">Unlink</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <hr>

    <h3>Create Invite Link</h3>
    <form method="post" action="generate_invite.php">
        <label>Max Uses:</label>
        <input type="number" name="max_uses" value="1" min="1">
        <button class="btn-small" type="submit">Generate Link</button>
    </form>

    <hr>

    <h3>Your Active Invites</h3>
    <?php
    $stmt = $pdo->prepare("SELECT * FROM domme_invites WHERE domme_id = ? AND is_revoked = 0 ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $invites = $stmt->fetchAll();
    ?>

    <?php if ($invites): ?>
        <ul class="challenge-list">
            <?php foreach ($invites as $invite): ?>
                <li>
                    Code: <code><?= htmlspecialchars($invite['code']) ?></code>
                    (<?= $invite['max_uses'] ?> uses)
                    <form method="post" action="revoke_invite.php" style="display:inline;">
                        <input type="hidden" name="invite_id" value="<?= $invite['id'] ?>">
                        <button class="btn-small btn-danger">Revoke</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No active invite links.</p>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

