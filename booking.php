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

<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Studio</title>
    <link rel="stylesheet" href="booking.css">
</head>
<body>
    <div class="booking-container">
        <h2>Form Booking Studio</h2>
        <form action="proses_booking.php" method="POST">
            <label for="tanggal">Tanggal Booking:</label>
            <input type="date" name="tanggal" required>

            <label for="waktu_mulai">Waktu Mulai:</label>
            <input type="time" name="waktu_mulai" required>

            <label for="waktu_selesai">Waktu Selesai:</label>
            <input type="time" name="waktu_selesai" required>

            <label for="jenis_studio">Jenis Studio:</label>
            <select name="jenis_studio" required>
                <option value="">-- Pilih Jenis Studio --</option>
                <option value="Studio A">Studio A</option>
                <option value="Studio B">Studio B</option>
                <option value="Studio C">Studio C</option>
            </select>

            <button type="submit">Booking Sekarang</button>
        </form>
    </div>
</body>
</html>

