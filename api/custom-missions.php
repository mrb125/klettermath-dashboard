<?php
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // GET ?class_id=X  — list missions for a class
  // GET ?code=XXXXXX — list active missions for a student's class
  if (isset($_GET['code'])) {
    $code = strtoupper(preg_replace('/[^A-Z0-9]/', '', $_GET['code']));
    $stmt = $pdo->prepare('
      SELECT m.id, m.title, m.story, m.data, m.xp
      FROM km_custom_missions m
      JOIN km_codes c ON c.class_id = m.class_id
      WHERE c.code = ? AND m.active = 1
      ORDER BY m.created_at ASC
    ');
    $stmt->execute([$code]);
  } else {
    $class_id = (int)($_GET['class_id'] ?? 0);
    $stmt = $pdo->prepare('
      SELECT id, title, story, data, xp, active, created_at
      FROM km_custom_missions
      WHERE class_id = ?
      ORDER BY created_at ASC
    ');
    $stmt->execute([$class_id]);
  }
  echo json_encode($stmt->fetchAll());

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $body     = json_decode(file_get_contents('php://input'), true);
  $class_id = (int)($body['class_id'] ?? 0);
  $title    = trim($body['title']    ?? '');
  $story    = trim($body['story']    ?? '');
  $xp       = max(10, min(200, (int)($body['xp'] ?? 60)));
  $steps    = $body['steps'] ?? [];

  if (!$class_id || !$title || !$steps) {
    echo json_encode(['error' => 'Fehlende Felder']); exit;
  }

  // Sanitize steps — only keep allowed fields
  $clean = [];
  foreach (array_slice($steps, 0, 3) as $s) {
    $type = in_array($s['type'] ?? '', ['number','vector3']) ? $s['type'] : 'number';
    $ans  = $s['answer'] ?? 0;
    $clean[] = [
      'type'   => $type,
      'prompt' => substr(trim($s['prompt'] ?? ''), 0, 300),
      'answer' => $type === 'vector3' ? array_map('floatval', (array)$ans) : (float)$ans,
      'hints'  => [substr(trim($s['hint'] ?? ''), 0, 300)],
    ];
  }

  $stmt = $pdo->prepare('
    INSERT INTO km_custom_missions (class_id, title, story, data, xp)
    VALUES (?, ?, ?, ?, ?)
  ');
  $stmt->execute([$class_id, $title, $story, json_encode($clean), $xp]);
  echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $id = (int)($_GET['id'] ?? 0);
  $class_id = (int)($_GET['class_id'] ?? 0);
  if (!$id || !$class_id) { echo json_encode(['error' => 'invalid']); exit; }
  // Only delete if it belongs to the given class (prevents cross-class deletion)
  $stmt = $pdo->prepare('DELETE FROM km_custom_missions WHERE id = ? AND class_id = ?');
  $stmt->execute([$id, $class_id]);
  echo json_encode(['ok' => true]);
}
