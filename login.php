<?php
require 'config.php';

// Kalau sudah login, langsung ke index
if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}

// Generate CSRF token khusus login
if (empty($_SESSION['csrf_token_login'])) {
  $_SESSION['csrf_token_login'] = bin2hex(random_bytes(32));
}

$error = "";

// next parameter (jika ada)
$next = $_GET['next'] ?? 'index.php';

// Normalisasi / validasi next (mencegah open redirect)
if (!preg_match('/^[-\/\w\.]+$/', $next)) {
  $next = 'index.php';
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Ambil next dari POST
  $next = $_POST['next'] ?? $next;
  if (!preg_match('/^[-\/\w\.]+$/', $next)) {
    $next = 'index.php';
  }

  // Validasi CSRF token
  if (
    !isset($_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token_login']) ||
    !hash_equals($_SESSION['csrf_token_login'], $_POST['csrf_token'])
  ) {
    $error = "Invalid security token. Please refresh the page and try again.";
  } else {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
      $error = "Username dan password wajib diisi.";
    } else {

      // Prepared statement
      $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
      if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
          $user = $result->fetch_assoc();

          // Password sudah hashed SHA-256
          if (hash("sha256", $password) === $user['password']) {

            // Set session login
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            // Rotate CSRF token setelah login sukses
            $_SESSION['csrf_token_login'] = bin2hex(random_bytes(32));

            header("Location: " . $next);
            exit;
          }
        }

        $error = "Username atau password salah.";
        $stmt->close();
      } else {
        $error = "Terjadi kesalahan sistem. Coba lagi beberapa saat.";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - VulnCentral</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- PANGGIL STYLE.CSS -->
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

<div class="login-wrapper">
  <div class="login-card">

    <!-- LEFT PANEL -->
    <div class="login-left">
      <div>

 <div class="login-title">Centralized Vulnerability Intelligence Platform</div>
<br><br>
        <div class="brand-row">
        <!--   <div class="brand-logo"><img src="image/VC-Shield.png" alt="VulnCentral"></div> -->
          <div class="brand-name">Please Sign In</div>
        </div>

       <!--  <div class="login-title">Centralized Vulnerability Intelligence Platform</div> -->

        <?php if (!empty($error)): ?>
          <div class="error-alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <!-- CSRF token -->
          <input type="hidden" name="csrf_token"
                 value="<?= htmlspecialchars($_SESSION['csrf_token_login'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

          <!-- next param -->
          <input type="hidden" name="next"
                 value="<?= htmlspecialchars($next, ENT_QUOTES, 'UTF-8'); ?>">

          <div class="form-group">
            <label for="username">Username</label>
            <input
              type="text"
              id="username"
              name="username"
              class="input-control"
              autocomplete="username"
              required
            >
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input
              type="password"
              id="password"
              name="password"
              class="input-control"
              autocomplete="current-password"
              required
            >
          </div>

          <button type="submit" class="btn-submit">Login</button>
        </form>
      </div>

      <div class="login-footer">
        <span>@Codeparty</span>
        <div>🔒SECURITY FIRST</div>
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="login-right">
      <img src="img/VC-White.png" alt="CyberSecurity Specialist">
    </div>

  </div>
</div>

</body>
</html>
