<?php
header("Content-Type: application/json");
require_once("db.php");

try {
  // Fetch top 10 rooms by bookings
  $stmt = $pdo->query("
    SELECT r.room_name, COUNT(b.booking_id) AS booking_count
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    GROUP BY b.room_id
    ORDER BY booking_count DESC
    LIMIT 10
  ");

  $data = [];
  $totalBookings = 0;

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $bookingCount = (int)$row["booking_count"];
    $totalBookings += $bookingCount;

    $data[] = [
      "room_name" => $row["room_name"],
      "booking_count" => $bookingCount
    ];
  }

  echo json_encode([
    "status" => "success",
    "data" => $data,
    "total_bookings" => $totalBookings
  ]);

} catch (PDOException $e) {
  echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
