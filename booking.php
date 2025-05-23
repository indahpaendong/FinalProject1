<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$id_user = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $hp = $_POST['hp'];
    $tgl = $_POST['tanggal'];
    $mulai = $_POST['waktu_mulai'];
    $selesai = $_POST['waktu_selesai'];
    $studio = $_POST['jenis_studio'];

    $sql = "INSERT INTO bookings (user_id, nama_lengkap, no_hp, tanggal, waktu_mulai, waktu_selesai, jenis_studio)
            VALUES ('$id_user', '$nama', '$hp', '$tgl', '$mulai', '$selesai', '$studio')";
    if ($conn->query($sql)) {
        echo "Booking berhasil!";
    } else {
        echo "Gagal booking: " . $conn->error;
    }
}
?>

<form method="POST">
  Nama Lengkap: <input name="nama"><br>
  No HP: <input name="hp"><br>
  Tanggal Booking: <input type="date" name="tanggal"><br>
  Waktu Mulai: <input type="time" name="waktu_mulai"><br>
  Waktu Selesai: <input type="time" name="waktu_selesai"><br>
  Jenis Studio:
  <select name="jenis_studio">
    <option value="Studio A">Studio A</option>
    <option value="Studio B">Studio B</option>
  </select><br>
  <button type="submit">Booking</button>
</form>
