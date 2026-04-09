<?php
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $body = json_decode(file_get_contents('php://input'), true);
  $name = trim($body['name'] ?? '');
  if (!$name) { echo json_encode(['error' => 'Name erforderlich']); exit; }

  $stmt = $pdo->prepare('INSERT INTO km_classes (name) VALUES (?)');
  $stmt->execute([$name]);
  echo json_encode(['id' => (int)$pdo->lastInsertId(), 'name' => $name]);

} else {
  // GET — return classes with code count and active student count
  $rows = $pdo->query('
    SELECT cl.id, cl.name, cl.created_at,
           COUNT(c.code)                              AS total_codes,
           COUNT(p.code)                              AS active_students,
           COALESCE(MAX(p.last_sync), NULL)            AS last_activity
    FROM km_classes cl
    LEFT JOIN km_codes    c ON c.class_id = cl.id
    LEFT JOIN km_progress p ON p.code     = c.code
    GROUP BY cl.id
    ORDER BY cl.created_at DESC
  ')->fetchAll();
  echo json_encode($rows);
}
