<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Room ID is required']);
    exit;
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    echo json_encode(['error' => 'Room not found']);
    exit;
}

$room['room_pictures'] = json_decode($room['room_pictures'], true);
echo json_encode($room);
