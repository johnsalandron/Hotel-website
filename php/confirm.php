<?php
require 'db.php';

if (!isset($_GET['token'])) {
    die("<h2 style='color:red;'>❌ Invalid confirmation link.</h2>");
}

$token = $_GET['token'];

// Look up booking by token
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE confirmation_token = ?");
$stmt->execute([$token]);
$booking = $stmt->fetch();

if (!$booking) {
    die("<h2 style='color:red;'>❌ Invalid or expired token.</h2>");
}

// Already confirmed
if ($booking['is_confirmed']) {
    header("Location: thank-you.php?code=" . $booking['booking_code'] . "&status=already");
    exit;
}

// Update status
$stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'confirmed', is_confirmed = 1 WHERE booking_id = ?");
$stmt->execute([$booking['booking_id']]);

header("Location: thank-you.php?code=" . $booking['booking_code'] . "&status=confirmed");
exit;
?>
