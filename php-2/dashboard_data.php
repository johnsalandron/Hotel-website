<?php
require_once("db.php");

header("Content-Type: application/json");

try {
  // Get total room count
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM rooms");
  $totalResult = $stmt->fetch(PDO::FETCH_ASSOC);
  $totalRooms = $totalResult['total'] ?? 0;

  // Get room prices for chart
  $stmt2 = $pdo->query("SELECT room_name, price FROM rooms ORDER BY price DESC");
  $priceData = $stmt2->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'totalRooms' => $totalRooms,
    'priceChart' => $priceData
  ]);
} catch (PDOException $e) {
  echo json_encode([
    'error' => true,
    'message' => $e->getMessage()
  ]);
}