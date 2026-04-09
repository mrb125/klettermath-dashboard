<?php
require 'config.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$code = strtoupper(preg_replace('/[^A-Z0-9]/', '', $body['code'] ?? ''));

if (strlen($code) !== 6) {
  echo json_encode(['valid' => false, 'error' => 'Code muss 6 Zeichen haben']);
  exit;
}

$stmt = $pdo->prepare('
  SELECT c.code, cl.name AS class_name, cl.id AS class_id
  FROM km_codes c
  JOIN km_classes cl ON c.class_id = cl.id
  WHERE c.code = ?
');
$stmt->execute([$code]);
$row = $stmt->fetch();

if ($row) {
  echo json_encode(['valid' => true, 'class' => $row['class_name'], 'class_id' => $row['class_id']]);
} else {
  echo json_encode(['valid' => false, 'error' => 'Unbekannter Code']);
}
