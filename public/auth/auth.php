<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login()
{
    if (!USE_AUTH) {
        return; // Auth not required
    }

    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }

    if (USE_2FA && !empty($_SESSION['2fa_required']) && $_SESSION['2fa_required'] === true) {
        header('Location: ' . BASE_URL . 'auth/verify_2fa.php');
        exit;
    }
}

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function logout()
{
    session_unset();
    session_destroy();
}

