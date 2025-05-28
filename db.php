<?php
// Aktifkan laporan error MySQLi saat pengembangan
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$user = "root";
$pass = "";
$db = "booking_studio";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4"); // set karakter encoding

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
