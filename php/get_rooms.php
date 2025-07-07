<?php
header('Content-Type: application/json');
require_once 'db.php'; // ✅ Include your db connection

try {
    // Change the LIMIT here to control how many rooms appear
    $stmt = $pdo->query("SELECT * FROM rooms LIMIT 3");
    $rooms = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['room_pictures'] = json_decode($row['room_pictures'], true); // Decode JSON image list
        $rooms[] = $row;
    }

    echo json_encode($rooms);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>