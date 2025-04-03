<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/header.php';

if (!is_logged_in()) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$challenge_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM challenges WHERE id = ?");
$stmt->execute([$challenge_id]);
$challenge = $stmt->fetch();

if (!$challenge) {
    echo "<p class='error'>Challenge not found.</p>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}
?>

<section class="container challenge-viewer">
    <h2>Day <?= (int)$challenge['day'] ?>: <?= htmlspecialchars($challenge['title']) ?></h2>

    <div class="dom-command">
        <h3>Dominant’s Order</h3>
        <p><?= nl2br(htmlspecialchars($challenge['dominant_order'])) ?></p>
    </div>

    <div class="sub-task">
        <h3>Your Task</h3>
        <p><?= nl2br(htmlspecialchars($challenge['sub_instruction'])) ?></p>
    </div>

    <form method="POST" action="">
        <label for="journal">Reflection / Journal (Optional):</label>
        <textarea name="journal" id="journal" rows="6" placeholder="How did it feel? Did you obey without hesitation? What do you need to improve?"></textarea>

        <button type="submit" name="complete" class="btn-primary">Mark Ttheir Challenge as Complete</button>
    </form>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

