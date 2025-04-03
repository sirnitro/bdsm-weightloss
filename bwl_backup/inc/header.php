<?php
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/../auth/auth.php';
}
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="flash success"><?= $_SESSION['flash_success'] ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="flash error"><?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<header class="site-header">
    <div class="container">
        <h1 class="logo"><a href="<?= BASE_URL ?>">BWL</a></h1>
        <nav class="main-nav">
            <ul>
                <li><a href="<?= BASE_URL ?>">Home</a></li>
                <li><a href="<?= BASE_URL ?>pages/programs.php">Programs</a></li>
                <li><a href="<?= BASE_URL ?>pages/how-it-works.php">How It Works</a></li>
                <li><a href="<?= BASE_URL ?>pages/contact.php">Contact</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="<?= BASE_URL ?>members/dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>auth/login.php">Login</a></li>
                    <li><a href="<?= BASE_URL ?>auth/register.php">Register</a></li>

                <?php endif; ?>
		<?php if (is_logged_in()): ?>
    		   <a href="<?= BASE_URL ?>auth/logout.php" class="logout-link">Logout</a>
		<?php endif; ?>

            </ul>
        </nav>
    </div>
</header>
<main class="site-main">

