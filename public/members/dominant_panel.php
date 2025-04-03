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
$stmt = $pdo->prepare("SELECT id, display_name, email FROM users WHERE dom_id = ?");
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
    $stmt = $pdo->prepare("SELECT * FROM dom_invites WHERE dom_id = ? AND is_revoked = 0 ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $invites = $stmt->fetchAll();
    ?>

    <?php if ($invites): ?>
<ul class="challenge-list">
    <?php foreach ($invites as $invite): ?>
        <li id="invite-<?= $invite['id'] ?>">
            Code: <code><?= htmlspecialchars($invite['code']) ?></code>
            — <span class="status"><?= $invite['is_revoked'] ? 'Revoked' : 'Active' ?></span>
            (<?= $invite['max_uses'] ?> uses)

            <button
                class="toggle-invite btn-small"
                data-id="<?= $invite['id'] ?>"
            >
                <?= $invite['is_revoked'] ? 'Restore' : 'Revoke' ?>
            </button>
        </li>
    <?php endforeach; ?>
</ul>

    <?php else: ?>
        <p>No active invite links.</p>
    <?php endif; ?>
</section>

<script>
document.querySelectorAll('.toggle-invite').forEach(btn => {
    btn.addEventListener('click', function () {
        const inviteId = this.dataset.id;

        fetch('toggle_invite.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'invite_id=' + encodeURIComponent(inviteId)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('invite-' + inviteId);
                row.querySelector('.status').textContent = data.new_status ? 'Revoked' : 'Active';
                this.textContent = data.new_status ? 'Restore' : 'Revoke';
            } else {
                alert('Failed to toggle invite: ' + data.message);
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

