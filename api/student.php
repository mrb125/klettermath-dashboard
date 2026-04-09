<?php
// GET ?code=XXXXXX — returns mission log for one student
require 'config.php';
header('Content-Type: application/json');

$code = strtoupper(preg_replace('/[^A-Z0-9]/', '', $_GET['code'] ?? ''));
if (strlen($code) !== 6) { echo json_encode(['error' => 'invalid code']); exit; }

$stmt = $pdo->prepare('
  SELECT mission_id, mastery, completed_at
  FROM km_mission_log
  WHERE code = ?
  ORDER BY completed_at ASC
');
$stmt->execute([$code]);
$log = $stmt->fetchAll();

echo json_encode(['code' => $code, 'log' => $log]);
