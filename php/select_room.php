<?php
require __DIR__ . '/db.php'; // Ensure this path is correct and includes the PDO setup

header('Content-Type: application/json');

$now = date('Y-m-d H:i:s');
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;

$sql = "
  SELECT 
    r.id AS room_id,
    r.room_name AS room_number,
    r.price,
    r.beds,
    r.description AS room_type,
    r.room_pictures
  FROM rooms r
  WHERE (r.beds * 2) >= ?
    AND NOT EXISTS (
      SELECT 1 FROM bookings b
      WHERE b.room_id = r.id
        AND b.is_confirmed = 1
        AND CONCAT(b.check_out_date, ' ', b.check_out_time) > ?
    )
  ORDER BY r.price ASC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guests, $now]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rooms);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
