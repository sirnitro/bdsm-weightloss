<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/header.php';

if (!is_logged_in() || $_SESSION['orientation'] !== 'dom') {
    header("Location: " . BASE_URL . "/members/dashboard.php");
    exit;
}

// Handle unlink
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlink_sub_id'])) {
    $sub_id = $_POST['unlink_sub_id'];
    $stmt = $pdo->prepare("UPDATE users SET dom_id = NULL WHERE id = ? AND dom_id = ?");
    $stmt->execute([$sub_id, $_SESSION['user_id']]);
    $message = "sub unlinked.";
}

// Get subs
$stmt = $pdo->prepare("SELECT id, display_name, email FROM users WHERE dom_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$subs = $stmt->fetchAll();
?>

<section class="container">
    <h2>Your subs</h2>

    <?php if (!empty($message)): ?>
        <p class="success"><?= $message ?></p>
    <?php endif; ?>

    <?php if (empty($subs)): ?>
        <p>You have no linked subs... yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subs as $sub): ?>
                <tr>
                    <td><?= htmlspecialchars($sub['display_name']) ?></td>
                    <td><?= htmlspecialchars($sub['email']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="unlink_sub_id" value="<?= $sub['id'] ?>">
                            <button class="btn-danger btn-small" type="submit">Unlink</button>
                        </form>
                        <a href="<?= BASE_URL ?>/members/punish_sub.php?id=<?= $sub['id'] ?>" class="btn-small">Punish</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

