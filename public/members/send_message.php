<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/functions.php';

if (!is_logged_in()) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];

// Check required POST fields
$receiver_id = $_POST['receiver_id'] ?? 0;
$type = $_POST['type'] ?? 'text';
$message = trim($_POST['message'] ?? '');

if (!$receiver_id || !in_array($type, ['text', 'image', 'order', 'praise', 'punishment'])) {
    $errors[] = "Invalid input.";
}

if ($type === 'image') {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Image upload failed.";
    } else {
        // Validate and store the image
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($fileType, $allowed)) {
            $errors[] = "Only JPG, PNG, or GIF images allowed.";
        } else {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('msg_', true) . '.' . $ext;
            $destination = __DIR__ . '/../uploads/messages/' . $filename;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $errors[] = "Failed to save the uploaded image.";
            } else {
                $message = $filename;
            }
        }
    }
}

if (empty($errors)) {
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, receiver_id, message, type, seen, created_at)
        VALUES (?, ?, ?, ?, 0, NOW())
    ");
    $stmt->execute([$user_id, $receiver_id, $message, $type]);

    $_SESSION['flash_success'] = "Message sent.";
} else {
    $_SESSION['flash_error'] = implode('<br>', $errors);
}

header("Location: " . BASE_URL . "members/message_center.php");
exit;

