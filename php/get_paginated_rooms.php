<?php
require_once 'db.php';

$limit = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total room count
$totalStmt = $pdo->query("SELECT COUNT(*) FROM rooms");
$totalRooms = $totalStmt->fetchColumn();
$totalPages = ceil($totalRooms / $limit);

// Get rooms for current page
$stmt = $pdo->prepare("SELECT * FROM rooms ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Respond with JSON
header('Content-Type: application/json');
echo json_encode([
    'rooms' => $rooms,
    'total_pages' => $totalPages,
    'current_page' => $page
]);
