<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/header.php';

// Fetch active programs from the database
$stmt = $pdo->prepare("SELECT * FROM programs WHERE is_active = 1 ORDER BY name");
$stmt->execute();
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="programs-list container">
    <h2 class="section-title">Available Programs</h2>
    <div class="programs-grid">
        <?php foreach ($programs as $program): ?>
            <div class="program-card">
                <h3><?= htmlspecialchars($program['name']) ?></h3>
                <p class="style-label"><?= htmlspecialchars($program['style']) ?></p>
                <p><?= htmlspecialchars($program['description']) ?></p>
                <a href="program_view.php?slug=<?= urlencode($program['slug']) ?>" class="btn-primary">View Program</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

