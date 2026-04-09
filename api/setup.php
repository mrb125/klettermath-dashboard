<?php
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
  missions_done TEXT,
  xp            INT       DEFAULT 0,
  streak        INT       DEFAULT 0,
  mastery       TEXT,
  errors        TEXT,
  last_sync     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (code) REFERENCES km_codes(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS km_custom_missions (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  class_id   INT         NOT NULL,
  title      VARCHAR(200) NOT NULL,
  story      TEXT,
  data       TEXT        NOT NULL,
  xp         INT         DEFAULT 60,
  active     TINYINT     DEFAULT 1,
  created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES km_classes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS km_mission_log (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  code         CHAR(6)   NOT NULL,
  mission_id   TINYINT   NOT NULL,
  mastery      CHAR(6),
  completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_mission (code, mission_id),
  FOREIGN KEY (code) REFERENCES km_codes(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

echo "Setup abgeschlossen. Tabellen angelegt.\n";
} catch (Exception $e) { echo "ERROR: " . $e->getMessage() . "\n"; }
