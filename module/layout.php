<?php
// layout.php
$username = $_SESSION['username'] ?? '';
$role     = $_SESSION['role'] ?? '';
$active   = $activeMenu ?? ''; // 'dashboard', 'admin', 'users'
?>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-left">
    <!-- HAMBURGER (muncul di mobile via CSS) -->
    <button class="menu-toggle" id="menuToggle" type="button" aria-label="Toggle Menu">☰</button>

    <img src="img/VC-ShieldWhiteO.png" alt="VulnCentral" class="topbar-logo">
    <div class="topbar-title">VulnCentral Platform</div>
  </div>

  <div class="topbar-right">
    <div class="profile-info">
      👤 <?= htmlspecialchars($username) ?> (<?= htmlspecialchars($role) ?>)
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</div>

<!-- OVERLAY (buat nutup sidebar saat open di mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <div class="menu-title">MENU</div>
  <div class="menu-items">
    <a href="index.php"
       class="menu-item <?= $active==='dashboard'?'active':'' ?>">📊 Dashboard</a>

    <?php if ($role === 'ADMIN'): ?>
      <a href="admin.php"
         class="menu-item <?= $active==='admin'?'active':'' ?>">🛠 Asset Admin</a>

      <a href="users.php"
         class="menu-item <?= $active==='users'?'active':'' ?>">👥 User Management</a>
    <?php endif; ?>
  </div>
</div>
