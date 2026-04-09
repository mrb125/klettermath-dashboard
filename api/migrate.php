<?php
// Run once to add new columns to existing km_progress table.
require 'config.php';
header('Content-Type: text/plain');

$migrations = [
  "ALTER TABLE km_progress ADD COLUMN errors TEXT",
  "CREATE TABLE IF NOT EXISTS km_custom_missions (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    class_id   INT         NOT NULL,
    title      VARCHAR(200) NOT NULL,
    story      TEXT,
    data       TEXT        NOT NULL,
    xp         INT         DEFAULT 60,
    active     TINYINT     DEFAULT 1,
    created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES km_classes(id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS km_mission_log (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    code         CHAR(6)   NOT NULL,
    mission_id   TINYINT   NOT NULL,
    mastery      CHAR(6),
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_mission (code, mission_id),
    FOREIGN KEY (code) REFERENCES km_codes(code)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($migrations as $sql) {
  try {
    $pdo->exec($sql);
    echo "OK: $sql\n";
  } catch (PDOException $e) {
    // 1060 = duplicate column — already applied
    if ($e->errorInfo[1] === 1060) {
      echo "SKIP (already exists): $sql\n";
    } else {
      echo "ERROR: " . $e->getMessage() . "\n";
    }
  }
}
echo "Fertig.\n";
