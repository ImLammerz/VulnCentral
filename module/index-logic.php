<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$role = $_SESSION['role'] ?? "VIEW";

// DATA SYSTEMS
$result = $conn->query("SELECT * FROM systems ORDER BY id ASC");
$rows = [];

$severityAgg = [
  'CRITICAL' => 0,
  'HIGH'     => 0,
  'MEDIUM'   => 0,
  'LOW'      => 0,
  'NONE'     => 0
];

while ($r = $result->fetch_assoc()) {
  $rows[] = $r;
  $sev = $r['severity'];
  if (isset($severityAgg[$sev])) {
    $severityAgg[$sev]++;
  }
}

// DATA HISTORY PER SYSTEM
$histRes = $conn->query(
  "SELECT system_id, severity, vulnerabilities, scan_date
   FROM scan_history
   ORDER BY id DESC"
);

$historyBySystem = [];
while ($h = $histRes->fetch_assoc()) {
  $sid = $h['system_id'];
  if (!isset($historyBySystem[$sid])) {
    $historyBySystem[$sid] = [];
  }
  $historyBySystem[$sid][] = $h;
}

$activeMenu = 'dashboard';
