<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - BDSM Weight Loss</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <header class="admin-header">
        <h1>Admin Panel</h1>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="users.php">Users</a>
	   <a href="user_add.php">Add User</a>
<?php if (is_logged_in()): ?>
    <a href="<?= BASE_URL ?>/auth/logout.php" class="logout-link">Logout</a>
<?php endif; ?>


            <!-- future: programs, email logs, audit log -->
        </nav>
    </header>
    <main class="admin-main">

