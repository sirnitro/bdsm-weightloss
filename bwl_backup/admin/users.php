<?php
require_once 'config.php';
require_once 'header.php';

// Fetch all users
//$stmt = $pdo->query("SELECT id, email, display_name, role, is_verified, is_banned, verified_at, created_at FROM users ORDER BY created_at DESC");
$stmt = $pdo->query("SELECT id, email, display_name, role, is_verified, is_banned, is_suspended, verified_at, created_at FROM users ORDER BY created_at DESC");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>User Management</h2>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Display Name</th>
            <th>Role</th>
            <th>Verified</th>
            <th>Banned</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['display_name'] ?? '-') ?></td>
            <td><?= $user['role'] ?></td>
            <td><?= $user['is_verified'] ? '✅' : '❌' ?></td>
            <td><?= $user['is_banned'] ? '🚫' : '—' ?></td>
            <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
            <td>
                <a href="user_edit.php?id=<?= $user['id'] ?>" class="btn-small">Edit</a>
                <a href="user_toggle_ban.php?id=<?= $user['id'] ?>" class="btn-small <?= $user['is_banned'] ? 'btn-unban' : 'btn-ban' ?>">
                    <?= $user['is_banned'] ? 'Unban' : 'Ban' ?>
                </a>
<a href="user_toggle_suspend.php?id=<?= $user['id'] ?>" 
   class="btn-small <?= $user['is_suspended'] ? 'btn-unsuspend' : 'btn-suspend' ?>">
   <?= $user['is_suspended'] ? 'Unsuspend' : 'Suspend' ?>
</a>

            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>

