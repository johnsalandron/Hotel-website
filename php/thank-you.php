<?php
require 'db.php';

if (!isset($_GET['code'])) {
    die("<h2 style='color:red;'>❌ Booking code missing.</h2>");
}

$code = $_GET['code'];

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_code = ?");
$stmt->execute([$code]);
$data = $stmt->fetch();

if (!$data) {
    die("<h2 style='color:red;'>❌ Booking not found.</h2>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Booking Confirmation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .confirmation-box {
      max-width: 500px;
      margin: 80px auto;
      padding: 30px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      animation: fadeInUp 0.5s ease;
    }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>

<div class="confirmation-box text-center">
  <h3 class="text-success">🎉 Booking Confirmed!</h3>
  <p class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($data['full_name']) ?></p>
  <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>
  <p class="mb-2"><strong>Check-in:</strong> <?= htmlspecialchars($data['check_in_date']) ?> <?= htmlspecialchars($data['check_in_time']) ?></p>
  <p class="mb-2"><strong>Check-out:</strong> <?= htmlspecialchars($data['check_out_date']) ?> <?= htmlspecialchars($data['check_out_time']) ?></p>
  <p class="mb-4"><strong>Booking Code:</strong> <code><?= htmlspecialchars($data['booking_code']) ?></code></p>
  
  <button onclick="printConfirmation()" class="btn btn-outline-primary">🖨️ Print Confirmation</button>
</div>

<script>
  function printConfirmation() {
    window.print();
  }
</script>

</body>
</html>
