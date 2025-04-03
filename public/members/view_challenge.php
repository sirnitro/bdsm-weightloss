<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/header.php';

if (!is_logged_in()) {
    header("Location: /auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$challenge_id = $_GET['id'] ?? 0;

// Get the current challenge, group, and program
$stmt = $pdo->prepare("
    SELECT c.*, g.name AS group_name, g.id AS group_id, g.program_id
    FROM challenges c
    JOIN challenge_groups g ON c.group_id = g.id
    WHERE c.id = ?
");
$stmt->execute([$challenge_id]);
$challenge = $stmt->fetch();

if (!$challenge) {
    echo "<p class='error'>Challenge not found.</p>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}

// Enforce lockout: all previous challenges must be completed
$day = (int)$challenge['day'];
if ($day > 1) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM user_challenges uc
        JOIN challenges c ON uc.challenge_id = c.id
        WHERE uc.user_id = ? AND c.group_id = ? AND c.day < ? AND uc.completed_at IS NOT NULL
    ");
    $stmt->execute([$user_id, $challenge['group_id'], $day]);
    $completed_prior = $stmt->fetchColumn();

    if ($completed_prior < $day - 1) {
        echo "<p class='error'>Ttheir challenge is locked. Complete previous days first.</p>";
        require_once __DIR__ . '/../inc/footer.php';
        exit;
    }
}

// Handle completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete'])) {
    $journal = trim($_POST['journal'] ?? '');
    $stmt = $pdo->prepare("
        INSERT INTO user_challenges (user_id, challenge_id, journal, completed_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE journal = VALUES(journal), completed_at = NOW()
    ");
    $stmt->execute([$user_id, $challenge_id, $journal]);

    header("Location: view_challenge.php?id=" . $challenge_id);
    exit;
}

// Get challenge progress stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM challenges WHERE group_id = ?");
$stmt->execute([$challenge['group_id']]);
$total = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM user_challenges uc
    JOIN challenges c ON uc.challenge_id = c.id
    WHERE uc.user_id = ? AND c.group_id = ? AND uc.completed_at IS NOT NULL
");
$stmt->execute([$user_id, $challenge['group_id']]);
$completed = $stmt->fetchColumn();

$progress = round(($completed / $total) * 100);

// Get next and previous challenge IDs
$stmt = $pdo->prepare("
    SELECT id FROM challenges 
    WHERE group_id = ? AND day = ?
");
$nextDay = $day + 1;
$prevDay = $day - 1;
$stmt->execute([$challenge['group_id'], $nextDay]);
$nextChallenge = $stmt->fetchColumn();

$stmt->execute([$challenge['group_id'], $prevDay]);
$prevChallenge = $stmt->fetchColumn();
?>

<section class="container challenge-viewer">
    <h2><?= htmlspecialchars($challenge['group_name']) ?> — Day <?= $challenge['day'] ?>: <?= htmlspecialchars($challenge['title']) ?></h2>

    <div class="progress-bar">
        <div class="progress" style="width: <?= $progress ?>%"><?= $progress ?>%</div>
    </div>

    <div class="dom-command">
        <h3>Dominant’s Order</h3>
        <p><?= nl2br(htmlspecialchars($challenge['dominant_order'])) ?></p>
    </div>

    <div class="sub-task">
        <h3>Your Task</h3>
        <p><?= nl2br(htmlspecialchars($challenge['sub_instruction'])) ?></p>
    </div>

    <form method="POST">
        <label for="journal">Reflection / Journal (Optional):</label>
        <textarea name="journal" id="journal" rows="6" placeholder="How did it feel? Did you obey without hesitation? What do you need to improve?"></textarea>

        <button type="submit" name="complete" class="btn-primary">Mark Challenge Complete</button>
    </form>

    <div class="challenge-nav">
        <?php if ($prevChallenge): ?>
            <a href="view_challenge.php?id=<?= $prevChallenge ?>" class="btn-small">⟵ Previous</a>
        <?php endif; ?>

        <?php if ($nextChallenge): ?>
            <?php if ($challenge['completed_at'] ?? false): ?>
                <a href="view_challenge.php?id=<?= $nextChallenge ?>" class="btn-small">Next ⟶</a>
            <?php else: ?>
                <span class="btn-small disabled">Next: Locked 🔒</span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

