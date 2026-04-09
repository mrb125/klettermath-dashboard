<?php
require 'config.php';
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$code    = strtoupper(preg_replace('/[^A-Z0-9]/', '', $body['code'] ?? ''));
$missions = json_encode(array_values(array_filter((array)($body['missions'] ?? []), 'is_int')));
$xp      = max(0, (int)($body['xp'] ?? 0));
$streak  = max(0, (int)($body['streak'] ?? 0));
$mastery = json_encode((object)($body['mastery'] ?? []));

if (strlen($code) !== 6) {
  echo json_encode(['ok' => false, 'error' => 'invalid code format']);
  exit;
}

$check = $pdo->prepare('SELECT code FROM km_codes WHERE code = ?');
$check->execute([$code]);
if (!$check->fetch()) {
  echo json_encode(['ok' => false, 'error' => 'unknown code']);
  exit;
}

$stmt = $pdo->prepare('
  INSERT INTO km_progress (code, missions_done, xp, streak, mastery)
  VALUES (?, ?, ?, ?, ?)
  ON DUPLICATE KEY UPDATE
    missions_done = VALUES(missions_done),
    xp            = VALUES(xp),
    streak        = VALUES(streak),
    mastery       = VALUES(mastery),
    last_sync     = CURRENT_TIMESTAMP
');
$stmt->execute([$code, $missions, $xp, $streak, $mastery]);

echo json_encode(['ok' => true]);
