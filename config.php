<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'uber_cueillette');
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die('Erreur de connexion: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
session_start();
?>
