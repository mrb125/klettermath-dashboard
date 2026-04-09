<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$host = getenv('KM_DB_HOST') ?: 'localhost';
$name = getenv('KM_DB_NAME') ?: '';
$user = getenv('KM_DB_USER') ?: '';
$pass = getenv('KM_DB_PASS') ?: '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  header('Content-Type: application/json');
  http_response_code(500);
  echo json_encode(['error' => 'DB connection failed']);
  exit;
}
