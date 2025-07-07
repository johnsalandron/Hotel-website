<?php
require_once "db.php";
header("Content-Type: application/json");

$action = $_GET["action"] ?? "";

// READ all bookings with room name
if ($action === "read") {
  $stmt = $pdo->query("
    SELECT b.*, r.room_name
    FROM bookings b
    LEFT JOIN rooms r ON b.room_id = r.id
    ORDER BY b.booking_id DESC
  ");
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// CREATE a new booking
if ($action === "create") {
  $stmt = $pdo->prepare("INSERT INTO bookings (
    room_id, full_name, check_in_date, check_in_time, check_out_date, check_out_time, booking_status
  ) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([
    $data['room_id'],
    $data['full_name'],
    $data['check_in_date'],
    $data['check_in_time'],
    $data['check_out_date'],
    $data['check_out_time'],
    $data['booking_status'],
  ]);
  echo json_encode(["status" => "created"]);
  exit;
}

// UPDATE booking
if ($action === "update") {
  $stmt = $pdo->prepare("UPDATE bookings SET
    room_id = ?, full_name = ?, check_in_date = ?, check_in_time = ?,
    check_out_date = ?, check_out_time = ?, booking_status = ?
    WHERE booking_id = ?");
  $stmt->execute([
    $data['room_id'],
    $data['full_name'],
    $data['check_in_date'],
    $data['check_in_time'],
    $data['check_out_date'],
    $data['check_out_time'],
    $data['booking_status'],
    $data['booking_id']
  ]);
  echo json_encode(["status" => "updated"]);
  exit;
}

// DELETE booking
if ($action === "delete") {
  $id = $_GET["id"] ?? 0;
  $stmt = $pdo->prepare("DELETE FROM bookings WHERE booking_id = ?");
  $stmt->execute([$id]);
  echo json_encode(["status" => "deleted"]);
  exit;
}

echo json_encode(["error" => "Invalid action"]);