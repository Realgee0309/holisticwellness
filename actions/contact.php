<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/user_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../contact.php'); exit; }

$name    = htmlspecialchars(trim($_POST['name']    ?? ''), ENT_QUOTES);
$email   = trim($_POST['email']   ?? '');
$subject = htmlspecialchars(trim($_POST['subject'] ?? 'General Inquiry'), ENT_QUOTES);
$message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES);
$userId  = isLoggedIn() ? getCurrentUser()['id'] : null;

$errors = [];
if (!$name)                                    $errors[] = 'Name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if (strlen($message) < 10)                     $errors[] = 'Message must be at least 10 characters.';

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: ../contact.php');
    exit;
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        INSERT INTO contacts (user_id, name, email, subject, message)
        VALUES (:uid, :name, :email, :subject, :message)
    ");
    $stmt->execute([
        ':uid'     => $userId,
        ':name'    => $name,
        ':email'   => $email,
        ':subject' => $subject,
        ':message' => $message,
    ]);
    setFlash('success', '✅ Message sent! We\'ll respond within 24 hours.');
    $redirect = $userId ? '../dashboard.php?tab=messages' : '../contact.php';
    header('Location: ' . $redirect);
    exit;
} catch (PDOException $e) {
    error_log('Contact error: ' . $e->getMessage());
    setFlash('error', 'Something went wrong. Please try again or contact us via WhatsApp.');
    header('Location: ../contact.php');
    exit;
}
