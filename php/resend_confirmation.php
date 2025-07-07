<?php
require 'db.php';
require_once __DIR__ . '/../vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_GET['email'])) {
    die("⛔ Email not provided.");
}

$email = $_GET['email'];

// 1. Fetch the latest unconfirmed booking
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE email = ? AND is_confirmed = 0 ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$email]);
$booking = $stmt->fetch();

if (!$booking) {
    die("❌ No unconfirmed booking found for $email.");
}

// 2. Resend confirmation email
$token = $booking['confirmation_token'];
$confirmLink = "https://ee8f-180-191-144-4.ngrok-free.app/hotel/php/confirm.php?token=$token";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['EMAIL_USERNAME'];
    $mail->Password = $_ENV['EMAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom($_ENV['EMAIL_USERNAME'], $_ENV['EMAIL_FROM_NAME']);
    $mail->addAddress($booking['email'], $booking['full_name']);
    $mail->isHTML(true);
    $mail->Subject = '🔁 Resending Booking Confirmation';
    $mail->Body = "
        <h3>Hello {$booking['full_name']},</h3>
        <p>This is a resend of your booking confirmation.</p>
        <p><strong>Booking Code:</strong> {$booking['booking_code']}</p>
        <p><a href='$confirmLink'>Confirm My Booking</a></p>
        <p>- Villa Rosal Resort Team</p>
    ";

    $mail->send();
    echo "✅ Email resent to {$booking['email']}.";
} catch (Exception $e) {
    echo "❌ Failed to resend email. Error: {$mail->ErrorInfo}";
}
