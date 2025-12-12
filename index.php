<?php require 'module/index-logic.php'; ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard - VulnCentral</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include 'module/layout.php'; ?>

<div class="page">
  <div class="container">
    <h3>Dashboard Aset & Severity</h3>

    <!-- CHARTS -->
    <div class="charts-row">
      <div class="chart-card">
        <div class="chart-title">Asset per Severity</div>
        <canvas id="vulnBarChart" class="chart-canvas"></canvas>
      </div>
      <div class="chart-card">
        <div class="chart-title">Asset Severity Distribution</div>
        <canvas id="vulnPieChart" class="chart-canvas"></canvas>
      </div>
    </div>

    <!-- SEARCH BAR + ROWS PER PAGE -->
    <div class="table-topbar" 
         style="margin:10px 0; display:flex; justify-content:space-between; align-items:center;">

      <!-- LEFT: SEARCH -->
      <div class="table-search">
        <input type="text"
               id="tableSearch"
               placeholder="Search Anything but Money"
               style="padding:6px 10px;width:280px;border-radius:4px;border:1px solid #ccc;font-size:12px;">
      </div>

      <!-- RIGHT: ROWS PER PAGE -->
      <div class="table-page-size" style="font-size:12px; display:flex; align-items:center; gap:6px;">
        <span>Rows per page:</span>
        <select id="systemsPageSize"
                onchange="changePageSize('systemsTable', this.value)"
                style="padding:4px 6px; width:70px; border-radius:4px; border:1px solid #bbb;">
          <option value="20" selected>20</option>
          <option value="35">35</option>
          <option value="50">50</option>
        </select>
      </div>

    </div>

    <!-- TABLE -->
<div class="table-responsive">
    <table id="systemsTable">
      <tr>
        <th>NO</th>
        <th class="sortable" onclick="sortTable(this, 'text')">NAME</th>
        <th class="sortable" onclick="sortTable(this, 'text')">Asset</th>
        <th class="sortable" onclick="sortTable(this, 'severity')">SEVERITY</th>
        <th class="sortable" onclick="sortTable(this, 'number')">VULN</th>
        <th>DETAIL SEVERITY</th>
        <th class="sortable" onclick="sortTable(this, 'text')">LAST SCAN</th>
        <th class="sortable" onclick="sortTable(this, 'text')">NEED AUTH</th>
        <th class="sortable" onclick="sortTable(this, 'text')">USE AUTH</th>
        <th>REPORT</th>
        <th>HISTORY</th>
      </tr>
      <?php
      $no = 1;
      foreach ($rows as $row):

        $cls = [
          "CRITICAL" => "sev-critical",
          "HIGH"     => "sev-high",
          "MEDIUM"   => "sev-medium",
          "LOW"      => "sev-low",
          "NONE"     => "sev-none"
        ][$row['severity']] ?? '';

        $fileName = trim($row['file_name'] ?? '');
        $sid      = $row['id'];

        $crit = isset($row['crit']) ? (int)$row['crit'] : 0;
        $high = isset($row['high']) ? (int)$row['high'] : 0;
        $med  = isset($row['med'])  ? (int)$row['med']  : 0;
        $low  = isset($row['low'])  ? (int)$row['low']  : 0;
      ?>
      <tr data-original-index="<?= $no ?>">
        <td><?= $no ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['website']) ?></td>
        <td class="<?= $cls ?>"><?= htmlspecialchars($row['severity']) ?></td>
        <td><?= (int)$row['vulnerabilities'] ?></td>
        <td>
          C <?= $crit ?> |
          H <?= $high ?> |
          M <?= $med ?> |
          L <?= $low ?>
        </td>
        <td><?= htmlspecialchars($row['last_scan']) ?></td>
        <td><?= htmlspecialchars($row['need_auth']) ?></td>
        <td><?= htmlspecialchars($row['auth_use']) ?></td>
        <td>
          <?php if ($fileName !== ''): ?>
            <a href="uploads/<?= htmlspecialchars($fileName) ?>" target="_blank">View PDF</a>
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
        <td>
          <?php if (!empty($historyBySystem[$sid])): ?>
            <a href="#" class="btn-delete" onclick="showHistory(<?= (int)$sid ?>);return false;">View</a>
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
      </tr>
      <?php
        $no++;
      endforeach; ?>
    </table>
</div>
    <!-- PAGINATION -->
    <div id="systemsPager"
         style="margin-top:8px;font-size:12px;display:flex;align-items:center;gap:8px;">
      <button id="systemsTablePrev" type="button"
              onclick="changePage('systemsTable', -1)">Prev</button>
      <span id="systemsTablePagerInfo">Page 1 / 1</span>
      <button id="systemsTableNext" type="button"
              onclick="changePage('systemsTable', 1)">Next</button>
    </div>

  </div>
</div>

<!-- MODAL HISTORY -->
<div class="modal-bg" id="historyModal" style="display:none;">
  <div class="modal">
    <h3>History Severity</h3>
    <div id="historyContent"
         style="max-height:300px;overflow-y:auto;font-size:13px;margin-bottom:12px;"></div>
    <button type="button" class="close" onclick="closeHistory()">Tutup</button>
  </div>
</div>

<script>
const scanHistory = <?php echo json_encode($historyBySystem); ?>;

function showHistory(systemId) {
  const hist = scanHistory[systemId];
  const modal = document.getElementById('historyModal');
  const content = document.getElementById('historyContent');

  if (!hist || hist.length === 0) {
    content.innerHTML = '<p>Tidak ada history scan.</p>';
  } else {
    const sevClassMap = {
      'CRITICAL': 'sev-critical',
      'HIGH'    : 'sev-high',
      'MEDIUM'  : 'sev-medium',
      'LOW'     : 'sev-low',
      'NONE'    : 'sev-none'
    };

    let html = '<table style="width:100%;border-collapse:collapse;font-size:12px;">';
    html += '<tr>' +
            '<th style="border-bottom:1px solid #ccc;padding:4px;text-align:left;">Severity</th>' +
            '<th style="border-bottom:1px solid #ccc;padding:4px;text-align:left;">Vuln</th>' +
            '<th style="border-bottom:1px solid #ccc;padding:4px;text-align:left;">Last Scan</th>' +
            '</tr>';

    hist.forEach(function (row) {
      const cls = sevClassMap[row.severity] || '';
      html += '<tr>' +
        '<td class="'+cls+'" style="border-bottom:1px solid #eee;padding:4px;">' + row.severity + '</td>' +
        '<td style="border-bottom:1px solid #eee;padding:4px;">' + row.vulnerabilities + '</td>' +
        '<td style="border-bottom:1px solid #eee;padding:4px;">' + row.scan_date + '</td>' +
        '</tr>';
    });

    html += '</table>';
    content.innerHTML = html;
  }

  modal.style.display = 'flex';
}

function closeHistory(){
  document.getElementById('historyModal').style.display = 'none';
}
</script>

<!-- CHART SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const barCtx = document.getElementById('vulnBarChart');
  const pieCtx = document.getElementById('vulnPieChart');
  if (!barCtx || !pieCtx) return;

  const sevData = <?php echo json_encode($severityAgg); ?>;
  const labels  = Object.keys(sevData);
  const data    = Object.values(sevData);

  const colorMap = {
    'CRITICAL': '#730505',
    'HIGH'    : '#c7281a',
    'MEDIUM'  : '#edcb66',
    'LOW'     : '#37bf3e',
    'NONE'    : '#10d6e0'
  };
  const bgColors = labels.map(l => colorMap[l] || '#999999');

  new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Asset Count',
        data: data,
        backgroundColor: bgColors,
        borderColor: bgColors,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: { y: { beginAtZero: true, ticks:{precision:0} } }
    }
  });

  new Chart(pieCtx, {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [{
        label: 'Asset Count',
        data: data,
        backgroundColor: bgColors,
        borderColor: '#ffffff',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });
});
</script>

<!-- SORTING + PAGINATION SCRIPT -->
<script src="module/index-pagination-sorting.js"></script>

</body>
</html>
