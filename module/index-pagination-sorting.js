/* ===== SORTING TABLE (DENGAN CUSTOM URUTAN SEVERITY) ===== */

const severityRank = {
  'CRITICAL': 5,
  'HIGH'    : 4,
  'MEDIUM'  : 3,
  'LOW'     : 2,
  'NONE'    : 1
};

function sortTable(th, type = "text") {
  const table = th.closest("table");
  if (!table) return;

  const tbody = table.tBodies[0] || table;

  const rows = Array.from(tbody.querySelectorAll("tr"))
    .filter(r => r.querySelector("td"));

  const colIndex = Array.from(th.parentNode.children).indexOf(th);

  let asc = th.dataset.asc === "true" ? false : true;
  th.dataset.asc = asc;

  rows.sort((a, b) => {
    let A = a.children[colIndex]?.innerText.trim() || "";
    let B = b.children[colIndex]?.innerText.trim() || "";

    if (type === "severity") {
      A = severityRank[A.toUpperCase()] || 0;
      B = severityRank[B.toUpperCase()] || 0;
    } else if (type === "number") {
      A = parseFloat(A.replace(/[^0-9.-]/g, "")) || 0;
      B = parseFloat(B.replace(/[^0-9.-]/g, "")) || 0;
    } else {
      A = A.toLowerCase();
      B = B.toLowerCase();
    }

    if (A < B) return asc ? 1 : -1;
    if (A > B) return asc ? -1 : 1;
    return 0;
  });

  rows.forEach(r => tbody.appendChild(r));

  if (paginationState[table.id]) {
    paginationState[table.id].currentPage = 1;
    applyPagination(table.id);
  }
}

/* ===== FILTER + PAGINATION ===== */

const paginationState = {
  'systemsTable': { pageSize: 20, currentPage: 1 }
};

function filterTable(tableId, inputId) {
  const table = document.getElementById(tableId);
  const input = document.getElementById(inputId);
  if (!table || !input) return;

  const filter = input.value.toLowerCase();
  const rows = Array.from(table.querySelectorAll('tr')).slice(1);

  rows.forEach(function(row) {
    const text = row.innerText.toLowerCase();
    row.dataset.matched = (filter === '' || text.indexOf(filter) !== -1) ? '1' : '0';
  });

  if (paginationState[tableId]) {
    paginationState[tableId].currentPage = 1;
  }
  applyPagination(tableId);
}

function changePage(tableId, delta) {
  const state = paginationState[tableId];
  if (!state) return;
  state.currentPage += delta;
  applyPagination(tableId);
}

function changePageSize(tableId, size) {
  const state = paginationState[tableId] || { pageSize: 20, currentPage: 1 };
  state.pageSize = parseInt(size, 10) || 20;
  state.currentPage = 1;
  paginationState[tableId] = state;
  applyPagination(tableId);
}

function applyPagination(tableId) {
  const table = document.getElementById(tableId);
  if (!table) return;

  const state = paginationState[tableId] || { pageSize: 20, currentPage: 1 };
  let pageSize = state.pageSize;
  if (pageSize <= 0) pageSize = 20;

  const allRows = Array.from(table.querySelectorAll('tr')).slice(1);
  const matchedRows = allRows.filter(function(row) {
    return (row.dataset.matched || '1') === '1';
  });

  let totalPages = Math.ceil(matchedRows.length / pageSize);
  if (totalPages < 1) totalPages = 1;

  if (state.currentPage > totalPages) state.currentPage = totalPages;
  if (state.currentPage < 1) state.currentPage = 1;

  const currentPage = state.currentPage;
  paginationState[tableId] = state;

  allRows.forEach(function(row) {
    row.style.display = 'none';
  });

  const start = (currentPage - 1) * pageSize;
  const end   = start + pageSize;

  matchedRows.forEach(function(row, idx) {
    if (idx >= start && idx < end) {
      row.style.display = '';
    }
  });

  let no = start + 1;
  matchedRows.forEach(function(row, idx) {
    if (idx >= start && idx < end && row.style.display !== 'none') {
      row.cells[0].innerText = no++;
    }
  });

  updatePagerControls(tableId, totalPages);
}

function updatePagerControls(tableId, totalPages) {
  const state = paginationState[tableId] || { currentPage: 1 };
  const info = document.getElementById(tableId + 'PagerInfo');
  const prev = document.getElementById(tableId + 'Prev');
  const next = document.getElementById(tableId + 'Next');

  if (info) {
    info.textContent = 'Page ' + state.currentPage + ' / ' + totalPages;
  }
  if (prev) {
    prev.disabled = state.currentPage <= 1;
  }
  if (next) {
    next.disabled = state.currentPage >= totalPages;
  }
}

/* ===== MOBILE SIDEBAR TOGGLE ===== */
function initMobileSidebar() {
  const btn = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');

  if (!btn || !sidebar || !overlay) return;

  function toggle() {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
  }

  btn.addEventListener('click', toggle);
  overlay.addEventListener('click', toggle);

  // auto close kalau klik menu item
  sidebar.addEventListener('click', (e) => {
    const a = e.target.closest('.menu-item');
    if (a) {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    }
  });
}


/* ===== INIT ===== */
document.addEventListener('DOMContentLoaded', function () {

initMobileSidebar();

  const table = document.getElementById('systemsTable');
  if (table) {
    const rows = Array.from(table.querySelectorAll('tr')).slice(1);
    rows.forEach(function(row) {
      row.dataset.matched = '1';
    });
  }

  const search = document.getElementById('tableSearch');
  if (search) {
    search.addEventListener('input', function() {
      filterTable('systemsTable', 'tableSearch');
    });
  }

  applyPagination('systemsTable');
});

