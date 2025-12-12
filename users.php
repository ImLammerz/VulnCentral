<?php
require 'config.php';

// Only ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
  header('Location: login.php');
  exit;
}

$role          = $_SESSION['role'];
$loginUserId   = $_SESSION['user_id'];
$usernameLogin = $_SESSION['username'] ?? '';

// DELETE USER
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];

  if ($id != $loginUserId) {
    $conn->query("DELETE FROM users WHERE id=$id");
  }
  header('Location: users.php');
  exit;
}

// CREATE / UPDATE USER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id       = (int)($_POST['id'] ?? 0);
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $urole    = $_POST['role'];

  if ($id > 0) {
    if ($password !== '') {
      $hash = hash('sha256', $password);
      $stmt = $conn->prepare("UPDATE users SET username=?, password=?, role=? WHERE id=?");
      $stmt->bind_param("sssi", $username,$hash,$urole,$id);
    } else {
      $stmt = $conn->prepare("UPDATE users SET username=?, role=? WHERE id=?");
      $stmt->bind_param("ssi", $username,$urole,$id);
    }
  } else {
    if ($password === '') {
      header('Location: users.php?err=Password%20required');
      exit;
    }
    $hash = hash('sha256', $password);
    $stmt = $conn->prepare("INSERT INTO users (username,password,role) VALUES (?,?,?)");
    $stmt->bind_param("sss", $username,$hash,$urole);
  }

  $stmt->execute();
  $stmt->close();

  // update session if self edit
  if ($id > 0 && $id == $loginUserId) {
    $_SESSION['username'] = $username;
    $_SESSION['role']     = $urole;
  }

  header('Location: users.php');
  exit;
}

// LIST USER
$users = $conn->query("SELECT id, username, role FROM users ORDER BY id ASC");

// set active menu layout
$activeMenu = 'users';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Management - VulnCentral</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'module/layout.php'; ?>

<div class="page">
  <div class="container">
    <h3>Management User</h3>

    <?php if (isset($_GET['err'])): ?>
      <div style="color:#d9534f;margin-bottom:10px;">
        <?= htmlspecialchars($_GET['err']) ?>
      </div>
    <?php endif; ?>

    <button onclick="openUserModal()">+ Add New User</button>
<div class="table-wrap">
    <table>
      <tr>
        <th>ID</th><th>Username</th><th>Role</th><th>Action</th>
      </tr>
      <?php while($u = $users->fetch_assoc()): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
	<td class="actions">
 		<button type="button" class="btn-edit"
    		onclick='openUserModal(<?= json_encode($u, JSON_HEX_APOS|JSON_HEX_TAG|JSON_HEX_AMP) ?>)'>
    		Edit
  		</button>

  		<?php if ($u['id'] != $loginUserId): ?>
   		<a class="btn-delete"
       		href="users.php?delete=<?= $u['id'] ?>"
       		onclick="return confirm('Delete this user?')">
       		Delete
    		</a>
  		<?php else: ?>
    		<span class="self-note">(YOU)</span>
  		<?php endif; ?>
	</td>

      </tr>
      <?php endwhile; ?>
    </table>
</div>
  </div>
</div>

<!-- MODAL USER -->
<div class="modal-bg" id="user-modal-bg">
  <div class="modal">
    <h3 id="user-modal-title">Tambah User Baru</h3>
    <form method="POST">
      <input type="hidden" name="id" id="user-id">

      <label>Username</label>
      <input type="text" name="username" id="user-username" required>

      <label>Password <span id="user-pass-hint" style="font-size:11px;color:#666;"></span></label>
      <input type="password" name="password" id="user-password">

      <label>Role</label>
      <select name="role" id="user-role">
        <option value="ADMIN">ADMIN</option>
        <option value="VIEW">VIEW</option>
      </select>

      <button type="submit">Simpan User</button>
      <button type="button" class="close" onclick="closeUserModal()">Batal</button>
    </form>
  </div>
</div>

<script>
function openUserModal(user) {
  const bg   = document.getElementById('user-modal-bg');
  const idF  = document.getElementById('user-id');
  const uF   = document.getElementById('user-username');
  const pF   = document.getElementById('user-password');
  const rF   = document.getElementById('user-role');
  const ttl  = document.getElementById('user-modal-title');
  const hint = document.getElementById('user-pass-hint');

  if (user) {
    ttl.innerText  = 'Edit User';
    idF.value      = user.id;
    uF.value       = user.username;
    rF.value       = user.role;
    pF.value       = '';
    hint.innerText = '(kosongkan jika tidak ingin mengganti password)';
  } else {
    ttl.innerText  = 'Tambah User Baru';
    idF.value      = 0;
    uF.value       = '';
    rF.value       = 'ADMIN';
    pF.value       = '';
    hint.innerText = '(wajib diisi untuk user baru)';
  }
  bg.style.display = 'flex';
}

function closeUserModal() {
  document.getElementById('user-modal-bg').style.display = 'none';
}

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

  sidebar.addEventListener('click', (e) => {
    const a = e.target.closest('.menu-item');
    if (a) {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    }
  });
}

document.addEventListener('DOMContentLoaded', function () {
  initMobileSidebar();
});

</script>

</body>
</html>
