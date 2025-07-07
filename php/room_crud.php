<?php
require_once("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  $name = $_POST['room_name'] ?? '';
  $price = $_POST['price'] ?? '';
  $beds = $_POST['beds'] ?? '';
  $desc = $_POST['description'] ?? '';
  $id = $_POST['id'] ?? null;
  $room_pictures_json = json_decode($_POST['room_pictures_json'] ?? '[]', true);

  $images = [];

  foreach ($room_pictures_json as $path) {
    if (!empty($path)) {
      $images[] = $path;
    }
  }

  if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
      $fileName = uniqid() . "_" . basename($_FILES['images']['name'][$i]);
      $targetPath = "../img/" . $fileName;
      move_uploaded_file($tmpName, $targetPath);
      $images[] = "img/" . $fileName;
    }
  }

  $images_json = json_encode($images);

  if ($action === 'create') {
    $stmt = $pdo->prepare("INSERT INTO rooms (room_name, price, beds, description, room_pictures) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $price, $beds, $desc, $images_json]);
    echo json_encode(['success' => true]);
    exit;
  }

  if ($action === 'update' && $id) {
    $stmt = $pdo->prepare("UPDATE rooms SET room_name=?, price=?, beds=?, description=?, room_pictures=? WHERE id=?");
    $stmt->execute([$name, $price, $beds, $desc, $images_json, $id]);
    echo json_encode(['success' => true]);
    exit;
  }

  if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
  }

  echo json_encode(['success' => false, 'error' => 'Invalid action']);
  exit;
}

// Load rooms
$rooms = $pdo->query("SELECT * FROM rooms ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rooms as &$room) {
  $room['room_pictures'] = json_decode($room['room_pictures'], true) ?? [];
}

echo json_encode([
  'rooms' => $rooms,
  'total_rooms' => count($rooms)
]);