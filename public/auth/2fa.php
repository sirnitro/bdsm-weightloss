<?php
require_once '/var/www/vendor/autoload.php'; // in case not included

use RobThree\Auth\TwoFactorAuth;

function get_2fa_object()
{
    return new TwoFactorAuth('BDSM Weight Loss');
}

function generate_2fa_secret()
{
    $tfa = get_2fa_object();
    return $tfa->createSecret();
}

function get_qr_code_url($username, $secret)
{
    $tfa = get_2fa_object();
    return $tfa->getQRCodeImageAsDataUri($username, $secret);
}

function verify_2fa_code($secret, $code)
{
    $tfa = get_2fa_object();
    return $tfa->verifyCode($secret, $code);
}

function generate_backup_codes($count = 5) {
    $codes = [];
    for ($i = 0; $i < $count; $i++) {
        $codes[] = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    return $codes;
}

function verify_backup_code($user_id, $code) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT backup_codes FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row && $row['backup_codes']) {
        $codes = json_decode($row['backup_codes'], true);
        if (in_array($code, $codes)) {
            // Remove used code
            $codes = array_diff($codes, [$code]);
            $stmt = $pdo->prepare("UPDATE users SET backup_codes = ? WHERE id = ?");
            $stmt->execute([json_encode(array_values($codes)), $user_id]);
            return true;
        }
    }
    return false;
}

