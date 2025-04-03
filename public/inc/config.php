<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Composer (dotenv, etc.)
require_once '/var/www/vendor/autoload.php';

// Load .env file (2 levels up from /public/inc)
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

// Define feature toggles
define('DEBUG_MODE', filter_var($_ENV['DEBUG'], FILTER_VALIDATE_BOOLEAN));
define('USE_CAPTCHA', filter_var($_ENV['CAPTCHA'], FILTER_VALIDATE_BOOLEAN));
define('USE_AUTH', filter_var($_ENV['AUTH'], FILTER_VALIDATE_BOOLEAN));
define('USE_2FA', filter_var($_ENV['USE_2FA'] ?? false, FILTER_VALIDATE_BOOLEAN));

// Define paths & URLs
define('BASE_PATH', $_ENV['BASE_PATH']);
define('BASE_URL', $_ENV['BASE_URL']);

// Database credentials
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);

// CAPTCHA keys
define('CAPTCHA_SITE_KEY', $_ENV['CAPTCHA_SITE_KEY'] ?? '');
define('CAPTCHA_SECRET_KEY', $_ENV['CAPTCHA_SECRET_KEY'] ?? '');

// Mail Settings
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS']);
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME']);
define('MAIL_HOST', $_ENV['MAIL_HOST']);
define('MAIL_PORT', $_ENV['MAIL_PORT']);
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME']);
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD']);
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION']);


// Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Load shared functions
require_once __DIR__ . '/functions.php';

// Load auth functions if enabled
if (USE_AUTH && !function_exists('is_logged_in')) {
    require_once __DIR__ . '/../auth/auth.php';
}

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    if (DEBUG_MODE) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database error.");
    }
}

require_once __DIR__ . '/functions.php';

