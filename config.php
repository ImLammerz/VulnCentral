<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// config.php cookie attribute and must https
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function get_csrf_token() {
  return $_SESSION['csrf_token'] ?? '';
}

function verify_csrf_token($token) {
  return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "vc_security";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}
?>
