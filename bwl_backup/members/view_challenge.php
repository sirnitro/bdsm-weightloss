<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/header.php';

if (!is_logged_in()) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$challenge_id = $_GET['id'] ?? 0;

// Fetch challenge, group, and program
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

// Lockout enforcement for incomplete prior days
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
        echo "<p class='error'>This challenge is locked. Complete previous days first.</p>";
        require_once __DIR__ . '/../inc/footer.php';
        exit;
    }
}

// Handle reflection + completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reflection'])) {
    $reflection = trim($_POST['reflection']);

    // Save the challenge reflection and mark as complete
    $stmt = $pdo->prepare("
        INSERT INTO user_challenges (user_id, challenge_id, completed_at, reflection)
        VALUES (?, ?, NOW(), ?)
        ON DUPLICATE KEY UPDATE completed_at = NOW(), reflection = VALUES(reflection)
    ");
    $stmt->execute([$user_id, $challenge_id, $reflection]);

    // Look for the next challenge
    $stmt = $pdo->prepare("
        SELECT id FROM challenges
        WHERE group_id = ? AND day = ?
        LIMIT 1
    ");
    $stmt->execute([$challenge['group_id'], $day + 1]);
    $nextChallengeId = $stmt->fetchColumn();

    if ($nextChallengeId) {
        $_SESSION['flash_success'] = "✅ Challenge complete! Onward to Day " . ($day + 1) . ".";
        header("Location: view_challenge.php?id=" . $nextChallengeId);
    } else {
        $_SESSION['flash_success'] = "✅ Challenge complete! You’ve finished this group.";
        header("Location: view_challenge.php?id=" . $challenge_id);
    }
    exit;
}

// Progress tracking
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

// Get prev/next challenge IDs
$stmt = $pdo->prepare("SELECT id FROM challenges WHERE group_id = ? AND day = ?");
$stmt->execute([$challenge['group_id'], $day + 1]);
$nextChallenge = $stmt->fetchColumn();

$stmt->execute([$challenge['group_id'], $day - 1]);
$prevChallenge = $stmt->fetchColumn();
?>

<section class="container challenge-viewer">
    <h2><?= htmlspecialchars($challenge['group_name']) ?> — Day <?= $challenge['day'] ?>: <?= htmlspecialchars($challenge['title']) ?></h2>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="flash success"><?= $_SESSION['flash_success'] ?></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <div class="progress-bar">
        <div class="progress" style="width: <?= $progress ?>%"><?= $progress ?>%</div>
    </div>

    <br/>

    <div class="dom-command">
        <?= render_challenge_block("Dominant’s Order", $challenge['dominant_order'] ?? '') ?>
    </div>

    <br/>

    <div class="sub-task">
        <?= render_challenge_block("Your Task", $challenge['submissive_instruction'] ?? '') ?>
    </div>

    <br/>

    <form method="POST">
        <?= render_reflection_prompt() ?>
        <button type="submit" class="btn-primary">Submit Reflection & Complete</button>
    </form>

    <div class="challenge-nav">
        <?php if ($prevChallenge): ?>
            <a href="view_challenge.php?id=<?= $prevChallenge ?>" class="btn-small">⟵ Previous</a>
        <?php endif; ?>

        <?php if ($nextChallenge): ?>
            <?php
            $stmt = $pdo->prepare("
                SELECT completed_at FROM user_challenges WHERE user_id = ? AND challenge_id = ?
            ");
            $stmt->execute([$user_id, $challenge['id']]);
            $isCompleted = $stmt->fetchColumn();
            ?>
            <?php if ($isCompleted): ?>
                <a href="view_challenge.php?id=<?= $nextChallenge ?>" class="btn-small">Next ⟶</a>
            <?php else: ?>
                <span class="btn-small disabled">Next: Locked 🔒</span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

