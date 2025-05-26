<?php
include 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit;
}

// Ambil data booking
$result = $conn->query("SELECT * FROM bookings");

echo "<h2>Dashboard Admin - Daftar Booking Studio</h2>";
echo "<table border='1' cellpadding='8' cellspacing='0'>
<tr>
    <th>Nama Lengkap</th>
    <th>Tanggal</th>
    <th>Jam Mulai</th>
    <th>Jam Selesai</th>
    <th>Studio</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['nama_lengkap']}</td>
        <td>{$row['tanggal']}</td>
        <td>{$row['waktu_mulai']}</td>
        <td>{$row['waktu_selesai']}</td>
        <td>{$row['jenis_studio']}</td>
        <td>{$row['status']}</td>
        <td>
            <form method='post' action='ubah_status.php'>
                <input type='hidden' name='id' value='{$row['id']}'>
                <select name='status'>
                    <option value='pending' " . ($row['status'] == 'pending' ? 'selected' : '') . ">Pending</option>
                    <option value='approved' " . ($row['status'] == 'approved' ? 'selected' : '') . ">Approved</option>
                    <option value='cancelled' " . ($row['status'] == 'cancelled' ? 'selected' : '') . ">Cancelled</option>
                </select>
                <button type='submit'>Update</button>
            </form>
        </td>
    </tr>";
}
echo "</table>";

echo "<br><a href='tambah_paket.php'>➕ Tambah Paket Studio</a>";
?>