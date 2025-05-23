<?php
include 'db.php';
session_start();
if ($_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit;
}

$result = $conn->query("SELECT * FROM bookings");

echo "<h2>Daftar Booking Studio</h2>";
while ($row = $result->fetch_assoc()) {
    echo "{$row['nama_lengkap']} - {$row['tanggal']} - {$row['jenis_studio']} <br>";
}
?>
