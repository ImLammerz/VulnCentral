<?php
require 'module/admin-logic.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Asset - VulnCentral</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'module/layout.php'; ?>

<div class="page">
  <div class="container">
    <h3>Asset Administration</h3>

    <button type="button" onclick="openModal()">+ Add Asset</button>

    <!-- TOPBAR TABLE: SEARCH -->
    <div class="table-topbar" 
         style="margin:10px 0; display:flex; justify-content:space-between; align-items:center;">
      <div class="table-search">
        <input type="text"
               id="adminTableSearch"
               placeholder="Search Anything but Money"
               style="padding:6px 10px;width:280px;border-radius:4px;border:1px solid #ccc;font-size:12px;">
      </div>
    </div>
    <!-- Table -->
<div class="table-responsive">
    <table id="adminSystemsTable">
      <thead>
        <tr>
          <th>No</th>
          <th>Name</th>
          <th>Asset</th>
          <th>Severity</th>
          <th>Vuln</th>
          <th>Detail Severity</th>
          <th>Last Scan</th>
          <th>Need Auth</th>
          <th>Use Auth</th>
          <th>Report</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php
        $no = 1;
        foreach ($systems as $s):
          $id = (int)$s['id'];

          // default systems
          $sev  = $s['severity'];
          $vuln = (int)$s['vulnerabilities'];
          $crit = (int)$s['crit'];
          $high = (int)$s['high'];
          $med  = (int)$s['med'];
          $low  = (int)$s['low'];
          $date = $s['last_scan'];

          // theres history → use latest history
          if (!empty($history[$id])) {
            $h = $history[$id][0];
            $sev  = $h['severity'];
            $vuln = (int)$h['vulnerabilities'];
            $crit = (int)$h['crit'];
            $high = (int)$h['high'];
            $med  = (int)$h['med'];
            $low  = (int)$h['low'];
            $date = $h['scan_date'];
          }

          $cls  = $clsMap[$sev] ?? '';
          $file = $s['file_name'] ?? "";
      ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td><?= htmlspecialchars($s['website']) ?></td>
          <td class="<?= $cls ?>"><?= htmlspecialchars($sev) ?></td>
          <td><?= $vuln ?></td>
          <td>
            C <?= $crit ?> |
            H <?= $high ?> |
            M <?= $med ?> |
            L <?= $low ?>
          </td>
          <td><?= htmlspecialchars($date) ?></td>
          <td><?= htmlspecialchars($s['need_auth']) ?></td>
          <td><?= htmlspecialchars($s['auth_use']) ?></td>
          <td>
            <?php if ($file): ?>
              <a href="uploads/<?= htmlspecialchars($file) ?>" target="_blank">PDF</a>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td>
            <button type="button"
                    onclick="editAsset(this)"
                    data-id="<?= $id ?>"
                    data-name="<?= htmlspecialchars($s['name'],ENT_QUOTES) ?>"
                    data-web="<?= htmlspecialchars($s['website'],ENT_QUOTES) ?>"
                    data-date="<?= htmlspecialchars($date,ENT_QUOTES) ?>"
                    data-need="<?= htmlspecialchars($s['need_auth'],ENT_QUOTES) ?>"
                    data-auth="<?= htmlspecialchars($s['auth_use'],ENT_QUOTES) ?>"
                    data-crit="<?= $crit ?>"
                    data-high="<?= $high ?>"
                    data-med="<?= $med ?>"
                    data-low="<?= $low ?>"
                    data-file="<?= htmlspecialchars($file,ENT_QUOTES) ?>"
                    data-system="<?= htmlspecialchars($s['name'],ENT_QUOTES) ?>"
            >Edit Asset</button>

            <button type="button"
                    onclick="openUploadModal(this)"
                    data-id="<?= $id ?>"
                    data-file="<?= htmlspecialchars($file,ENT_QUOTES) ?>"
                    data-system="<?= htmlspecialchars($s['name'],ENT_QUOTES) ?>"
            >Edit PDF</button>

            <form method="post" class="inline-form" onsubmit="return confirm('Delete this asset?');">
              <input type="hidden" name="action" value="delete_system">
              <input type="hidden" name="id" value="<?= $id ?>">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
              <button type="submit" class="btn-delete">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- MODAL ADD / EDIT ASET -->
<div class="modal-bg" id="modal-bg">
  <div class="modal">
    <h3 id="modalTitle">Add Asset</h3>

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="save_system">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="id" id="idField" value="0">
      <input type="hidden" name="current_file" id="currentFile" value="">

      <label>System Name</label>
      <input type="text" name="name" id="nameField" required>

      <label>Asset</label>
      <input type="text" name="website" id="webField" required>

      <label>Detail Severity</label>
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:6px;">
        <div>CRIT <input type="number" id="critField" name="crit" min="0" value="0"></div>
        <div>HIGH <input type="number" id="highField" name="high" min="0" value="0"></div>
        <div>MED <input type="number" id="medField"  name="med"  min="0" value="0"></div>
        <div>LOW <input type="number" id="lowField"  name="low"  min="0" value="0"></div>
      </div>

      <div style="margin:6px 0;font-size:13px;">
        <b>Severity:</b> <span id="sevPreview">NONE</span><br>
        <b>Total Vuln:</b> <span id="vulnPreview">0</span>
      </div>

      <label>Last Scan</label>
      <input type="date" name="last_scan" id="dateField">

      <label>Need Auth</label>
      <select name="need_auth" id="needField">
        <option value="YES">YES</option>
        <option value="NO">NO</option>
      </select>

      <label>Use Auth</label>
      <select name="auth_use" id="authField">
        <option value="YES">YES</option>
        <option value="NO">NO</option>
      </select>

     	<div id="uploadGroup">
        <label>Upload PDF</label>
        <input type="file" name="file_upload" id="fileField" accept="application/pdf">

        <label style="font-size:13px;">
          <input type="hidden" name="remove_file" id="removeFile">
        </label>
	    </div>

      <button type="submit">Simpan</button>
      <button type="button" class="close" onclick="closeModal()">Batal</button>
    </form>
  </div>
</div>

<!-- MODAL UPLOAD PDF ONLY -->
<div class="modal-bg" id="upload-modal-bg" style="display:none;">
  <div class="modal">
    <h3>Upload Report PDF</h3>

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="upload_pdf">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="id" id="uploadIdField" value="0">
      <input type="hidden" name="current_file" id="uploadCurrentFile" value="">

      <p id="uploadSystemName" style="font-weight:bold;margin-top:0;"></p>

      <label>File Sekarang</label>
      <p id="uploadCurrentFileLabel" style="font-size:13px;"></p>

      <label>Upload PDF Baru</label>
      <input type="file" name="file_upload" accept="application/pdf">

      <label style="font-size:13px;margin-top:6px;display:block;">
        <input type="checkbox" name="remove_file" id="uploadRemoveFile">
        Hapus file yang ada
      </label>

      <button type="submit">Simpan</button>
      <button type="button" class="close" onclick="closeUploadModal()">Batal</button>
    </form>
  </div>
</div>

<!-- JS  -->
<script src="module/admin.js"></script>

</body>
</html>
