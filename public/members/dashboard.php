<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../inc/header.php';

if (!is_logged_in()) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$orientation = $_SESSION['orientation'] ?? 'sub';

?>

<section class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['display_name'] ?? 'User') ?>!</h2>

    <?php
    if ($orientation === 'sub') {
        render_sub_dashboard($pdo, $user_id);
    } else {
        render_dom_dashboard($pdo, $user_id);
    }
    ?>
</section>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>

