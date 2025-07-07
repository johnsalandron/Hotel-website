<?php
header("Content-Type: application/json");
require_once "db.php";

try {
  $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC");
  $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(["success" => true, "data" => $bookings]);
} catch (PDOException $e) {
  echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
