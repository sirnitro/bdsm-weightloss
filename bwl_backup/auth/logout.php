<?php
require_once __DIR__ . '/../inc/config.php';

session_unset();
session_destroy();

header("Location: " . BASE_URL . "/auth/login.php");
exit;

