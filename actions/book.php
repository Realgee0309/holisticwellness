<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/user_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../book.php'); exit; }

$name    = htmlspecialchars(trim($_POST['name']    ?? ''), ENT_QUOTES);
$email   = trim($_POST['email']   ?? '');
$service = htmlspecialchars(trim($_POST['service'] ?? ''), ENT_QUOTES);
$date    = trim($_POST['date']    ?? '');
$time    = htmlspecialchars(trim($_POST['time']    ?? ''), ENT_QUOTES);
$message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES);
$userId  = isLoggedIn() ? getCurrentUser()['id'] : null;

// Validate
$errors = [];
if (!$name)                                    $errors[] = 'Name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if (!$service)                                 $errors[] = 'Please select a service.';
if (!$date)                                    $errors[] = 'Please pick a preferred date.';
if (!$time)                                    $errors[] = 'Please pick a preferred time.';
if ($date && $date < date('Y-m-d'))            $errors[] = 'Please choose a future date.';

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: ../book.php');
    exit;
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        INSERT INTO bookings (user_id, name, email, service, preferred_date, preferred_time, message)
        VALUES (:uid, :name, :email, :service, :date, :time, :msg)
    ");
    $stmt->execute([
        ':uid'     => $userId,
        ':name'    => $name,
        ':email'   => $email,
        ':service' => $service,
        ':date'    => $date,
        ':time'    => $time,
        ':msg'     => $message,
    ]);
    setFlash('success', '✅ Booking request received! We\'ll confirm via email or WhatsApp within 24 hours.');
    $redirect = $userId ? '../dashboard.php?tab=bookings' : '../book.php';
    header('Location: ' . $redirect);
    exit;
} catch (PDOException $e) {
    error_log('Booking error: ' . $e->getMessage());
    setFlash('error', 'Something went wrong saving your booking. Please try again or contact us via WhatsApp.');
    header('Location: ../book.php');
    exit;
}
