<?php
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $body     = json_decode(file_get_contents('php://input'), true);
  $class_id = (int)($body['class_id'] ?? 0);
  $count    = min(max(1, (int)($body['count'] ?? 5)), 50);

  $check = $pdo->prepare('SELECT id FROM km_classes WHERE id = ?');
  $check->execute([$class_id]);
  if (!$check->fetch()) { echo json_encode(['error' => 'Klasse nicht gefunden']); exit; }

  // Characters: no 0/O/I/1/L to avoid confusion
  $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
  $len   = strlen($chars);
  $generated = [];
  $attempts  = 0;

  $insert = $pdo->prepare('INSERT IGNORE INTO km_codes (code, class_id) VALUES (?, ?)');

  while (count($generated) < $count && $attempts < 300) {
    $code = '';
    for ($i = 0; $i < 6; $i++) $code .= $chars[random_int(0, $len - 1)];
    $insert->execute([$code, $class_id]);
    if ($insert->rowCount() > 0) $generated[] = $code;
    $attempts++;
  }

  echo json_encode(['codes' => $generated, 'class_id' => $class_id]);

} else {
  // GET ?class_id=X — list all codes with progress
  $class_id = (int)($_GET['class_id'] ?? 0);

  $stmt = $pdo->prepare('
    SELECT c.code, c.created_at,
           p.missions_done, p.xp, p.streak, p.mastery, p.last_sync
    FROM km_codes c
    LEFT JOIN km_progress p ON p.code = c.code
    WHERE c.class_id = ?
    ORDER BY COALESCE(p.xp, -1) DESC, c.created_at ASC
  ');
  $stmt->execute([$class_id]);
  echo json_encode($stmt->fetchAll());
}
