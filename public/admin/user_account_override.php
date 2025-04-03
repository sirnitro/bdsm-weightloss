<?php
if ($_POST['reset_2fa']) {
    $stmt = $pdo->prepare("UPDATE users SET 2fa_secret = NULL, backup_codes = NULL WHERE id = ?");
    $stmt->execute([$_POST['user_id']]);
    echo "✅ 2FA has been reset for user ID " . $_POST['user_id'];
}

