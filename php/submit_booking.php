<?php
require_once __DIR__ . '/../vendor/autoload.php'; // ✅ Loads PHPMailer + Dotenv

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php'; // also loads .env now

function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // ✅ fixed the missing opening parenthesis "i" → "if"
    $recaptchaSecret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? '';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
    $responseData = json_decode($verify);

    if (!$responseData->success) {
        die("❌ reCAPTCHA failed. Please verify you're not a robot.");
    }

    // Sanitize and validate input
    $name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $contact = trim($_POST['contact_number'] ?? '');
    $checkInDate = $_POST['check_in'];
    $checkInTime = $_POST['checkin_time'];
    $checkOutDate = $_POST['check_out'];
    $checkOutTime = $_POST['checkout_time'];
    $adults = (int) ($_POST['adults'] ?? 1);
    $children = (int) ($_POST['children'] ?? 0);
    $roomId = (int) $_POST['room_id'];
    $specialRequest = trim(filter_input(INPUT_POST, 'special_request', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    if (!$name || !$email || !$checkInDate || !$checkOutDate || !$checkInTime || !$checkOutTime || !$roomId) {
        die("❌ Invalid form submission.");
    }

    // 🔍 Get room name and price for email display
    $stmt = $pdo->prepare("SELECT room_name, price FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch();

    $roomName = $room['room_name'] ?? 'Unknown Room';
    $roomPrice = isset($room['price']) ? number_format($room['price'], 2) : '0.00';

    // 🔁 Check for duplicate booking
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE email = ? AND room_id = ? AND check_in_date = ? AND is_confirmed = 0");
    $stmt->execute([$email, $roomId, $checkInDate]);
    $existingBooking = $stmt->fetch();

    if ($existingBooking) {
        $token = $existingBooking['confirmation_token'];
        $bookingCode = $existingBooking['booking_code'];
    } else {
        $token = generateToken();
        $bookingCode = 'HB-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $stmt = $pdo->prepare("INSERT INTO bookings (
            room_id, full_name, email, contact_number,
            check_in_date, check_in_time,
            check_out_date, check_out_time,
            adults, children,
            booking_status, confirmation_token, booking_code,
            is_confirmed, is_checked_in, is_checked_out
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, 0, 0, 0)");

        $stmt->execute([
            $roomId,
            $name,
            $email,
            $contact,
            $checkInDate,
            $checkInTime,
            $checkOutDate,
            $checkOutTime,
            $adults,
            $children,
            $token,
            $bookingCode
        ]);
    }

    // ✅ Update this if you restart ngrok:
    $confirmLink = "https://0b85-180-191-144-4.ngrok-free.app/hotel_booking/php/confirm.php?token=$token";

    // ✅ Fetch email config
    $sendgridKey = $_ENV['SENDGRID_API_KEY'] ?? '';
    $emailFrom = $_ENV['EMAIL_FROM'] ?? '';
    $emailFromName = $_ENV['EMAIL_FROM_NAME'] ?? 'Villa Rosal Resort';

    if (empty($sendgridKey) || empty($emailFrom)) {
        die("❌ Email credentials not set in .env file. Please configure SENDGRID_API_KEY and EMAIL_FROM.");
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.sendgrid.net';
        $mail->SMTPAuth = true;
        $mail->Username = 'apikey';
        $mail->Password = $sendgridKey;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($emailFrom, $emailFromName);
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Confirm Your Booking at Villa Rosal Beach Resort';
        $mail->Body = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 10px; background: #fefefe; padding: 30px;'>
    <h2 style='color: #2c3e50;'>✅ Thank You for Booking, $name!</h2>
    <p style='font-size: 16px; color: #444;'>We're excited to welcome you to <strong>Villa Rosal Resort</strong>.</p>

    <table cellpadding='6' cellspacing='0' style='width: 100%; border-collapse: collapse; font-size: 15px;'>
        <tr><td style='font-weight: bold;'>🔢 Booking Code:</td><td>$bookingCode</td></tr>
        <tr><td style='font-weight: bold;'>🏨 Room:</td><td>$roomName</td></tr>
        <tr><td style='font-weight: bold;'>💰 Price:</td><td>₱$roomPrice / night</td></tr>
        <tr><td style='font-weight: bold;'>📅 Check-in:</td><td>$checkInDate @ $checkInTime</td></tr>
        <tr><td style='font-weight: bold;'>📅 Check-out:</td><td>$checkOutDate @ $checkOutTime</td></tr>
        <tr><td style='font-weight: bold;'>👥 Guests:</td><td>$adults Adult(s), $children Child(ren)</td></tr>
    </table>

    <div style='margin: 30px 0; text-align: center;'>
        <a href='$confirmLink' style='display: inline-block; padding: 12px 24px; background: #28a745; color: #fff; text-decoration: none; border-radius: 5px; font-size: 16px;'>✅ Confirm My Booking</a>
    </div>

    <hr style='margin: 40px 0;'>
    <p style='font-size: 14px; color: #777;'>If you did not request this booking, please disregard this email.</p>
    <p style='font-size: 13px; color: #aaa; text-align: center; margin-top: 40px;'>— Villa Rosal Resort Team</p>
</div>
";


        $mail->send();

        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>Booking Confirmation Sent</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background: #f2f2f2; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                .card { padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); background: #fff; max-width: 500px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='card'>
                <h2 class='text-success'>✅ Booking Email Sent!</h2>
                <p>Thank you, <strong>$name</strong>. A confirmation email has been sent to <strong>$email</strong>.</p>
                <a href='../booking.html' class='btn btn-primary mt-3'>⬅️ Return to Booking Page</a>
            </div>
        </body>
        </html>
        ";
        exit;
    } catch (Exception $e) {
        echo "❌ Booking saved, but failed to send email. Error: {$mail->ErrorInfo}";
    }
}
