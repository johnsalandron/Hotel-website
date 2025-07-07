<?php
$host = "localhost";
$port = "3307";
$dbname = "hotel_db";
$username = "root";
$password = ""; // change if needed

try {
  $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
?>