// ambil semua element sekali di awal
const modalBg     = document.getElementById('modal-bg');
const modalTitle  = document.getElementById('modalTitle');
const idField     = document.getElementById('idField');
const currentFile = document.getElementById('currentFile');
const nameField   = document.getElementById('nameField');
const webField    = document.getElementById('webField');
const critField   = document.getElementById('critField');
const highField   = document.getElementById('highField');
const medField    = document.getElementById('medField');
const lowField    = document.getElementById('lowField');
const sevPreview  = document.getElementById('sevPreview');
const vulnPreview = document.getElementById('vulnPreview');
const dateField   = document.getElementById('dateField');
const needField   = document.getElementById('needField');
const authField   = document.getElementById('authField');
const fileField   = document.getElementById('fileField');
const uploadGroup = document.getElementById('uploadGroup');
const removeFile  = document.getElementById('removeFile');

function recompute() {
  const c = parseInt(critField.value || '0', 10);
  const h = parseInt(highField.value || '0', 10);
  const m = parseInt(medField.value  || '0', 10);
  const l = parseInt(lowField.value  || '0', 10);

  let sev = 'NONE';
  if (c > 0)      sev = 'CRITICAL';
  else if (h > 0) sev = 'HIGH';
  else if (m > 0) sev = 'MEDIUM';
  else if (l > 0) sev = 'LOW';

  sevPreview.textContent  = sev;
  vulnPreview.textContent = (c + h + m + l);
}

function openModal() {
  modalTitle.textContent = 'Add Asset';
  idField.value = 0;
  currentFile.value = '';
  nameField.value = '';
  webField.value = '';
  critField.value = 0;
  highField.value = 0;
  medField.value  = 0;
  lowField.value  = 0;
  dateField.value = '';
  needField.value = 'YES';
  authField.value = 'YES';
  fileField.value = '';
  removeFile.checked = false;

  // tampilkan upload PDF di mode Tambah
  if (uploadGroup) uploadGroup.style.display = 'block';

  recompute();
  modalBg.style.display = 'flex';
}

function closeModal() {
  modalBg.style.display = 'none';
}

function editAsset(btn) {
  modalTitle.textContent = 'Edit Asset';
  idField.value = btn.dataset.id || '';
  currentFile.value = btn.dataset.file || '';
  nameField.value = btn.dataset.name || '';
  webField.value  = btn.dataset.web  || '';

  // convert 14/10/2025 → 2025-10-14
  const d = btn.dataset.date || '';
  if (d.includes('/')) {
    const parts = d.split('/');
    if (parts.length === 3) {
      dateField.value =
        parts[2] + '-' +
        parts[1].padStart(2,'0') + '-' +
        parts[0].padStart(2,'0');
    } else {
      dateField.value = '';
    }
  } else {
    dateField.value = '';
  }

  needField.value = btn.dataset.need || 'YES';
  authField.value = btn.dataset.auth || 'YES';

  critField.value = btn.dataset.crit || 0;
  highField.value = btn.dataset.high || 0;
  medField.value  = btn.dataset.med  || 0;
  lowField.value  = btn.dataset.low  || 0;

  removeFile.checked = false;
  fileField.value = '';
  
  // sembunyikan upload PDF di mode Edit
  if (uploadGroup) uploadGroup.style.display = 'none';

  recompute();
  modalBg.style.display = 'flex';
}

// pasang listener input severity
[critField, highField, medField, lowField].forEach(el => {
  el.addEventListener('input', recompute);
});

// === SEARCH TABLE ADMIN ===
(function() {
  const search = document.getElementById('adminTableSearch');
  const table  = document.getElementById('adminSystemsTable');

  if (!search || !table) return;

  search.addEventListener('input', function() {
    const filter = search.value.toLowerCase();
    const rows = Array.from(table.querySelectorAll('tbody tr'));

    rows.forEach(row => {
      const text = row.innerText.toLowerCase();
      if (filter === '' || text.indexOf(filter) !== -1) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });
})();

// === MODAL UPLOAD PDF ===
const uploadModalBg          = document.getElementById('upload-modal-bg');
const uploadIdField          = document.getElementById('uploadIdField');
const uploadCurrentFile      = document.getElementById('uploadCurrentFile');
const uploadSystemName       = document.getElementById('uploadSystemName');
const uploadCurrentFileLabel = document.getElementById('uploadCurrentFileLabel');
const uploadRemoveFile       = document.getElementById('uploadRemoveFile');

function openUploadModal(btn) {
  const id   = btn.dataset.id || '';
  const file = btn.dataset.file || '';
  const sys  = btn.dataset.system || '';

  uploadIdField.value     = id;
  uploadCurrentFile.value = file;
  uploadSystemName.textContent = sys ? ('System: ' + sys) : '';

  uploadRemoveFile.checked = false;

  if (file) {
    uploadCurrentFileLabel.textContent = file;
  } else {
    uploadCurrentFileLabel.textContent = 'Belum ada file ter-upload.';
  }

  uploadModalBg.style.display = 'flex';
}

function closeUploadModal() {
  uploadModalBg.style.display = 'none';
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

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initMobileSidebar);
} else {
  initMobileSidebar();
}
