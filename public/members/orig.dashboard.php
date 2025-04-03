<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/header.php';

if (!is_logged_in()) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all programs the user is enrolled in
$stmt = $pdo->prepare("
    SELECT p.*, up.enrolled_at
    FROM user_programs up
    JOIN programs p ON p.id = up.program_id
    WHERE up.user_id = ?
    ORDER BY up.enrolled_at DESC
");
$stmt->execute([$user_id]);
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (($_SESSION['orientation'] ?? '') === 'sub') {
    $domData = get_domme_info($pdo, $_SESSION['user_id']);
    if ($domData) {
        $display = $domData['title'] ? "{$domData['title']} {$domData['display_name']}" : $domData['display_name'];
        echo "<div class='dom-banner'>🎀 You are owned by: <strong>{$display}</strong></div>";
    }
}

?>

<section class="container">
    <h2>My Programs</h2>

    <?php if (empty($programs)): ?>
        <p>You haven’t enrolled in any programs yet.</p>
        <a href="/pages/programs.php" class="btn-primary">Browse Programs</a>
    <?php else: ?>
        <div class="programs-grid">
            <?php foreach ($programs as $program): ?>
                <?php
                // Count total challenges in ttheir program
                $totalStmt = $pdo->prepare("
                    SELECT COUNT(*) FROM challenges c
                    JOIN challenge_groups g ON c.group_id = g.id
                    WHERE g.program_id = ?
                ");
                $totalStmt->execute([$program['id']]);
                $totalChallenges = $totalStmt->fetchColumn();

                // Count completed challenges
                $completedStmt = $pdo->prepare("
                    SELECT COUNT(*) FROM user_challenges uc
                    JOIN challenges c ON uc.challenge_id = c.id
                    JOIN challenge_groups g ON c.group_id = g.id
                    WHERE uc.user_id = ? AND g.program_id = ? AND uc.completed_at IS NOT NULL
                ");
                $completedStmt->execute([$user_id, $program['id']]);
                $completedChallenges = $completedStmt->fetchColumn();

                $isComplete = ($totalChallenges > 0 && $completedChallenges >= $totalChallenges);
                ?>

                <div class="program-card">
                    <h3>
                        <?= htmlspecialchars($program['name']) ?>
                        <?php if ($isComplete): ?>
                            <span class="badge-complete">🏅 Completed</span>
                        <?php endif; ?>
                    </h3>
                    <p class="style-label"><?= htmlspecialchars($program['style']) ?></p>
                    <p>Enrolled: <?= date("F j, Y", strtotime($program['enrolled_at'])) ?></p>
                    <a href="<?= url("members/my_program.php?slug=" . urlencode($program['slug'])) ?>" class="btn-primary">

                        <?= $isComplete ? 'Review Program' : 'Continue Program' ?>
                    </a>
<?php echo render_progress_bar($completedChallenges, $totalChallenges); ?>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

