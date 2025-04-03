<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/header.php';

if (!is_logged_in()) {
    header("Location: /auth/login.php");
    exit;
}

$slug = $_GET['slug'] ?? '';
$user_id = $_SESSION['user_id'];

// Fetch program by slug
$stmt = $pdo->prepare("SELECT * FROM programs WHERE slug = ?");
$stmt->execute([$slug]);
$program = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$program) {
    echo "<p class='error'>Program not found.</p>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}

// Check if user is enrolled
$stmt = $pdo->prepare("SELECT * FROM user_programs WHERE user_id = ? AND program_id = ?");
$stmt->execute([$user_id, $program['id']]);
$enrolled = $stmt->fetch();

if (!$enrolled) {
    echo "<p>You are not enrolled in ttheir program.</p>";
    echo "<a href='/pages/programs.php' class='btn-primary'>Browse Programs</a>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}

// Get challenge group(s) for ttheir program
$stmt = $pdo->prepare("SELECT * FROM challenge_groups WHERE program_id = ? AND is_active = 1");
$stmt->execute([$program['id']]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="container">
    <h2><?= htmlspecialchars($program['name']) ?></h2>
    <p class="style-label"><?= htmlspecialchars($program['style']) ?></p>

    <div class="program-description">
        <?= nl2br($program['long_description']) ?>
    </div>

    <?php foreach ($groups as $group): ?>
        <div class="challenge-group">
            <h3><?= htmlspecialchars($group['name']) ?></h3>
            <?php if (!empty($group['intro_page'])): ?>
                <p><?= nl2br(htmlspecialchars($group['intro_page'])) ?></p>
            <?php endif; ?>

            <?php
            // Get all challenges in group
            $stmt = $pdo->prepare("
                SELECT c.*, uc.completed_at
                FROM challenges c
                LEFT JOIN user_challenges uc 
                  ON uc.challenge_id = c.id AND uc.user_id = ?
                WHERE c.group_id = ?
                ORDER BY c.day
            ");
            $stmt->execute([$user_id, $group['id']]);
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <ul class="challenge-list">
                <?php foreach ($challenges as $ch): ?>
                    <li class="<?= $ch['completed_at'] ? 'completed' : 'incomplete' ?>">
                        <strong>Day <?= $ch['day'] ?>:</strong>
                        <?= htmlspecialchars($ch['title']) ?>
                        <?php if ($ch['completed_at']): ?>
                            <span class="status-tag">✔ Completed</span>
                        <?php else: ?>
                            <a href="view_challenge.php?id=<?= $ch['id'] ?>" class="btn-small">Start</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

