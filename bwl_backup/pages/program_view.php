<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/header.php';

// Get the program slug from the URL
$slug = $_GET['slug'] ?? '';

// Fetch the program by slug
$stmt = $pdo->prepare("SELECT * FROM programs WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$program = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle missing or inactive programs
if (!$program) {
    echo "<div class='container'><h2>Program Not Found</h2><p>Ttheir program does not exist or is currently unavailable.</p></div>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}
?>

<section class="container program-view">
    <h2><?= htmlspecialchars($program['name']) ?></h2>
    <p class="style-label"><?= htmlspecialchars($program['style']) ?></p>
    <div class="program-description">
        <p class="program-short-desc"><?= nl2br(htmlspecialchars($program['description'])) ?></p>

<?php if (!empty($program['long_description'])): ?>
    <div class="program-description">
        <?= nl2br($program['long_description']) ?>
    </div>
<?php endif; ?>

<div class="dom-card">
    <h3>What to Expect</h3>
    <ul>
        <li>Daily workouts with a dominant twist</li>
        <li>Creative punishments for slacking</li>
        <li>sub rituals to reshape your mindset</li>
        <li>Powerful accountability through obedience</li>
    </ul>
</div>

<blockquote class="dom-quote">
    “You will not just lose pounds — you’ll lose your resistance, your excuses, and that bratty little smirk.”
    <footer>— dominant N</footer>
</blockquote>

<?php
$stmt = $pdo->prepare("SELECT title, dominant_order FROM challenges WHERE group_id = 1 ORDER BY day ASC LIMIT 1");
$stmt->execute();
$first = $stmt->fetch();
?>

<?php if (is_logged_in()): ?>
    <?php
    $stmt = $pdo->prepare("SELECT title, dominant_order FROM challenges WHERE group_id = 1 ORDER BY day ASC LIMIT 1");
    $stmt->execute();
    $first = $stmt->fetch();
    ?>

    <?php if ($first): ?>
        <div class="challenge-preview">
            <h3>First Challenge: <?= htmlspecialchars($first['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($first['dominant_order'])) ?></p>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (!is_logged_in()): ?>
    <p><em>Login to preview the first challenge and begin your obedience training.</em></p>
<?php endif; ?>

    </div>

<?php if (is_logged_in()): ?>
    <form method="POST" action="program_enroll.php">
        <input type="hidden" name="slug" value="<?= htmlspecialchars($program['slug']) ?>">
        <button class="btn-primary" type="submit">Start Ttheir Program</button>
    </form>
<?php else: ?>
    <p><a href="/auth/login.php" class="btn-primary">Login to Start Ttheir Program</a></p>
    <p><a href="<?= BASE_URL ?>auth/login.php" class="btn-primary">wLogin</a>

<?php endif; ?>

</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

