<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'config.php';
header('Content-Type: text/plain');

try {

$pdo->exec("
CREATE TABLE IF NOT EXISTS km_classes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS km_codes (
  code       CHAR(6)     PRIMARY KEY,
  class_id   INT         NOT NULL,
  created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES km_classes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS km_progress (
  code          CHAR(6)   PRIMARY KEY,
  missions_done TEXT      NOT NULL DEFAULT '[]',
  xp            INT       DEFAULT 0,
  streak        INT       DEFAULT 0,
  mastery       TEXT      NOT NULL DEFAULT '{}',
  last_sync     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (code) REFERENCES km_codes(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

echo "Setup abgeschlossen. Tabellen angelegt.\n";
} catch (Exception $e) { echo "ERROR: " . $e->getMessage() . "\n"; }
