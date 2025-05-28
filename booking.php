<?php
// booking.php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
  // Jika belum login, redirect ke login
  header("Location: login.php");
  exit;
}
$user_id = $_SESSION['user_id'];
$jenis_studio = $_GET['paket'] ?? '';
if (!$jenis_studio) {
  header("Location: pilih_paket.php");
  exit;
}
// Ambil booking milik user
$result = $conn->query("SELECT * FROM bookings WHERE user_id = $user_id");
$notification_message = "";

$notif_query = $conn->query("SELECT * FROM bookings WHERE user_id = $user_id");
if ($notif_query && $notif_query->num_rows > 0) {
    $row = $notif_query->fetch_assoc();

    if ($row['status'] == 'approved') {
        $notification_message = "Booking Anda telah disetujui.";
    } elseif ($row['status'] == 'cancelled') {
        $notification_message = "Booking Anda telah dibatalkan.";
    }

    // Tandai notifikasi sudah "dilihat"
$conn->query("UPDATE bookings SET is_seen = 1 WHERE id = " . $row['id']);

}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Booking Studio Foto</title>
  <link rel="stylesheet" href="booking.css">
</head>
<body>
<?php if ($notification_message): ?>
  <div style="background-color:#d4edda; color:#155724; padding:15px; border-radius:5px; font-weight:bold; margin-bottom:20px; text-align:center;">
    <?= htmlspecialchars($notification_message) ?>
  </div>
<?php endif; ?>

<div class="container">
  <h1>Booking Studio Foto</h1>

  <!-- Langsung tampilkan form booking tanpa daftar paket -->

    <form id="bookingForm">
  <input type="hidden" name="user_id" value="<?= $user_id ?>">

  <div class="form-group">
    <label for="nama_lengkap">Nama Lengkap</label>
    <input type="text" id="nama_lengkap" name="nama_lengkap" required>
  </div>

  <div class="form-group">
    <label for="no_hp">Nomor HP</label>
    <input type="tel" id="no_hp" name="no_hp" required>
  </div>

  <div class="form-group">
    <label for="tanggal">Tanggal Booking</label>
    <input type="date" id="tanggal" name="tanggal" required>
  </div>

  <div class="form-group">
    <label for="waktu_mulai">Waktu Mulai</label>
    <input type="time" id="waktu_mulai" name="waktu_mulai" required>
  </div>

  <div class="form-group">
    <label for="waktu_selesai">Waktu Selesai</label>
    <input type="time" id="waktu_selesai" name="waktu_selesai" required>
  </div>

  <div class="form-group">
    <label for="jenis_studio">Jenis Studio</label>
    <input type="text" id="jenis_studio" name="jenis_studio" value="<?= htmlspecialchars($jenis_studio) ?>" readonly>
  </div>

  <button type="submit">Kirim Booking</button>
</form>

  <div id="responseMessage"></div>
</div>

<script>
  // Kirim form booking
  document.getElementById("bookingForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const form = e.target;

    const data = {
      user_id: form.user_id.value,
      nama_lengkap: form.nama_lengkap.value,
      no_hp: form.no_hp.value,
      tanggal: form.tanggal.value,
      waktu_mulai: form.waktu_mulai.value,
      waktu_selesai: form.waktu_selesai.value,
      jenis_studio: form.jenis_studio.value,
    };

    fetch("simpan-booking.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    })
    .then(res => res.json())
    .then(res => {
      const msg = document.getElementById("responseMessage");
      if (res.status === "success") {
        msg.innerHTML = `<div class="success-message">${res.message}</div>`;
        form.reset();
      } else {
        msg.innerHTML = `<div class="error-message">${res.message}</div>`;
      }
    })
    .catch(() => {
      document.getElementById("responseMessage").innerHTML =
        `<div class="error-message">Terjadi kesalahan saat mengirim data.</div>`;
    });
  });
</script>

<?php if ($notification_message): ?>
  <div class="notification"><?= htmlspecialchars($notification_message) ?></div>
<?php endif; ?>

<h2>Daftar Booking Saya</h2>

<?php if ($result && $result->num_rows > 0): ?>
<table class="table-booking">
  <thead>
    <tr>
      <th>Tanggal</th>
      <th>Jenis Studio</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($booking = $result->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($booking['tanggal']) ?></td>
      <td><?= htmlspecialchars($booking['jenis_studio']) ?></td>
      <td><?= htmlspecialchars($booking['status']) ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<?php else: ?>
  <p>Belum ada data booking.</p>
<?php endif; ?>

<footer>
  &copy; <?= date('Y') ?> Kelompok 5 - Indah, Yusi, Afny
</footer>

</body>
</html>

