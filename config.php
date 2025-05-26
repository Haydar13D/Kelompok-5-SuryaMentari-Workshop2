<?php
// config/config.php
// Ubah kredensial sesuai lingkungan Anda
$host = 'localhost';
$db   = 'servicemobil';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>
