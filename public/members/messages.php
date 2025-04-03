<?php
// messages.php — shared message dashboard
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/header.php';

if (!is_logged_in()) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$orientation = $_SESSION['orientation'] ?? '';
$is_dom = $orientation === 'dom';

// Fetch messages
$stmt = $pdo->prepare("SELECT m.*, u.display_name AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.receiver_id = ?
    ORDER BY m.created_at DESC");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();

// Fetch linked partner(s)
if ($is_dom) {
    $stmt = $pdo->prepare("SELECT id, display_name FROM users WHERE dom_id = ?");
    $stmt->execute([$user_id]);
    $partners = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT id, display_name FROM users WHERE id = (SELECT dom_id FROM users WHERE id = ?)");
    $stmt->execute([$user_id]);
    $partners = $stmt->fetchAll();
}
?>

<section class="container">
    <h2>Messaging Center</h2>

    <form method="POST" action="send_message.php" enctype="multipart/form-data">
        <label for="receiver_id">Send To:</label>
        <select name="receiver_id" required>
            <?php foreach ($partners as $partner): ?>
                <option value="<?= $partner['id'] ?>"><?= htmlspecialchars($partner['display_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="message">Message:</label>
        <textarea name="message" required></textarea>

        <label>Type:</label>
        <select name="type">
            <option value="text">Text</option>
            <option value="image">Image</option>
            <option value="order">Order</option>
            <option value="praise">Praise</option>
            <option value="punishment">Punishment</option>
        </select>

        <input type="file" name="upload" accept="image/*">
        <button class="btn-primary" type="submit">Send</button>
    </form>

    <hr>
    <h3>Inbox</h3>
    <?php if (!$messages): ?>
        <p>No messages received yet.</p>
    <?php else: ?>
        <ul class="challenge-list">
            <?php foreach ($messages as $msg): ?>
                <li>
                    <strong><?= htmlspecialchars($msg['sender_name']) ?></strong>:
                    <?php if ($msg['type'] === 'image'): ?>
                        <br><img src="/uploads/messages/<?= htmlspecialchars($msg['message']) ?>" style="max-width:300px;">
                    <?php else: ?>
                        <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                    <?php endif; ?>
                    <small><?= ucfirst($msg['type']) ?> • <?= date("M j, Y H:i", strtotime($msg['created_at'])) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

