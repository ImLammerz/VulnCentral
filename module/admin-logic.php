<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if (($_SESSION['role'] ?? '') !== 'ADMIN') {
  header("Location: index.php");
  exit;
}

/* ============================
   DELETE ASSET
   ============================ */
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];

  // ambil file_name dulu untuk dihapus dari folder uploads
  $stmt = $conn->prepare("SELECT file_name FROM systems WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->bind_result($fileToDelete);
  if ($stmt->fetch() && $fileToDelete) {
    $path = dirname(__DIR__) . '/uploads/' . $fileToDelete;
    if (is_file($path)) @unlink($path);
  }
  $stmt->close();

  // hapus asset
  $del = $conn->prepare("DELETE FROM systems WHERE id=?");
  $del->bind_param("i", $id);
  $del->execute();
  $del->close();

  header("Location: admin.php");
  exit;
}

/* ============================
   INSERT / UPDATE + UPLOAD PDF
   ============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? 'save_system';

  // === 1. KHUSUS UPLOAD PDF SAJA (TIDAK MENAMBAHKAN HISTORY) ===
  if ($action === 'upload_pdf') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
      $currentFile = trim($_POST['current_file'] ?? '');
      $removeFile  = !empty($_POST['remove_file']);

      $uploadDir = dirname(__DIR__) . "/uploads/"; // root_project/uploads
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

      $fileNameToSave = $currentFile;

      // hapus file lama kalau diminta
      if ($removeFile && $currentFile !== '') {
        $p = $uploadDir . $currentFile;
        if (is_file($p)) @unlink($p);
        $fileNameToSave = "";
      }

      // upload pdf baru (opsional)
      if (!empty($_FILES['file_upload']['name']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['file_upload']['tmp_name'];
        $orig = basename($_FILES['file_upload']['name']);
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
          if ($fileNameToSave !== '') {
            $p = $uploadDir . $fileNameToSave;
            if (is_file($p)) @unlink($p);
          }

          $safe = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $orig);
          $new  = time() . "_" . $safe;

          if (move_uploaded_file($tmp, $uploadDir . $new)) {
            $fileNameToSave = $new;
          }
        }
      }

      // update hanya kolom file_name
      $stmt = $conn->prepare("UPDATE systems SET file_name=? WHERE id=?");
      $stmt->bind_param("si", $fileNameToSave, $id);
      $stmt->execute();
      $stmt->close();
    }

    header("Location: admin.php");
    exit;
  }

  // === 2. INSERT / UPDATE ASSET (Nambah history kalau ada last_scan) ===
  $id   = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $web  = trim($_POST['website'] ?? '');
  $need = $_POST['need_auth'] ?? 'YES';
  $auth = $_POST['auth_use']  ?? 'YES';

  /* ==== DATE FORMATTER ==== */
  $inputDate = $_POST['last_scan'] ?? '';
  $last = '';

  if (!empty($inputDate)) {
    $d = DateTime::createFromFormat("Y-m-d", $inputDate);
    if ($d) $last = $d->format("d/m/Y"); // simpan: 14/10/2025
  }

  $crit = (int)($_POST['crit'] ?? 0);
  $high = (int)($_POST['high'] ?? 0);
  $med  = (int)($_POST['med']  ?? 0);
  $low  = (int)($_POST['low']  ?? 0);

  $vuln = $crit + $high + $med + $low;

  // auto severity
  if ($crit > 0)       $sev = "CRITICAL";
  elseif ($high > 0)   $sev = "HIGH";
  elseif ($med > 0)    $sev = "MEDIUM";
  elseif ($low > 0)    $sev = "LOW";
  else                 $sev = "NONE";

  // FILE HANDLING (modal Add/Edit)
  $currentFile = trim($_POST['current_file'] ?? '');
  $removeFile  = !empty($_POST['remove_file']);

  $uploadDir = dirname(__DIR__) . "/uploads/"; // root_project/uploads
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

  $fileNameToSave = $currentFile;

  // remove old file
  if ($removeFile && $currentFile !== '') {
    $p = $uploadDir . $currentFile;
    if (is_file($p)) @unlink($p);
    $fileNameToSave = "";
  }

  // upload new PDF
  if (!empty($_FILES['file_upload']['name']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['file_upload']['tmp_name'];
    $orig = basename($_FILES['file_upload']['name']);
    $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

    if ($ext === 'pdf') {
      if ($fileNameToSave !== '') {
        $p = $uploadDir . $fileNameToSave;
        if (is_file($p)) @unlink($p);
      }
      $safe = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $orig);
      $new  = time() . "_" . $safe;

      if (move_uploaded_file($tmp, $uploadDir . $new)) {
        $fileNameToSave = $new;
      }
    }
  }

  /* ================= UPDATE / INSERT SYSTEMS ================= */
  if ($id > 0) {
    $stmt = $conn->prepare(
      "UPDATE systems SET 
       name=?, website=?, severity=?, vulnerabilities=?, last_scan=?, 
       need_auth=?, auth_use=?, file_name=?,
       crit=?, high=?, med=?, low=?
       WHERE id=?"
    );
    $stmt->bind_param(
      "sssissssiiiii",
      $name, $web, $sev, $vuln, $last,
      $need, $auth, $fileNameToSave,
      $crit, $high, $med, $low, $id
    );
    $stmt->execute();
    $stmt->close();

    $systemId = $id;
  } else {
    // INSERT
    $stmt = $conn->prepare(
      "INSERT INTO systems
      (name, website, severity, vulnerabilities, last_scan,
       need_auth, auth_use, file_name,
       crit, high, med, low)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?)"
    );
    $stmt->bind_param(
      "sssissssiiii",
      $name, $web, $sev, $vuln, $last,
      $need, $auth, $fileNameToSave,
      $crit, $high, $med, $low
    );
    $stmt->execute();
    $systemId = $stmt->insert_id;
    $stmt->close();
  }

  /* INSERT HISTORY */
  if (!empty($last)) {
    $h = $conn->prepare(
      "INSERT INTO scan_history
       (system_id, severity, vulnerabilities, scan_date, crit, high, med, low)
       VALUES (?,?,?,?,?,?,?,?)"
    );
    $h->bind_param(
      "isisiiii",
      $systemId, $sev, $vuln, $last,
      $crit, $high, $med, $low
    );
    $h->execute();
    $h->close();
  }

  header("Location: admin.php");
  exit;
}

/* ============================
   DATA SYSTEMS + HISTORY
   ============================ */
$systems = [];
$res = $conn->query("SELECT * FROM systems ORDER BY id ASC");
while ($row = $res->fetch_assoc()) {
  $systems[] = $row;
}

$history = [];
$hres = $conn->query("SELECT * FROM scan_history ORDER BY id DESC");
while ($h = $hres->fetch_assoc()) {
  $sid = (int)$h['system_id'];
  if (!isset($history[$sid])) $history[$sid] = [];
  $history[$sid][] = $h;
}

$clsMap = [
  'CRITICAL' => 'sev-critical',
  'HIGH'     => 'sev-high',
  'MEDIUM'   => 'sev-medium',
  'LOW'      => 'sev-low',
  'NONE'     => 'sev-none',
];

// supaya menu di layout.php tahu lagi di halaman apa
$activeMenu = 'admin';
