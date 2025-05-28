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

                    <?php if (strpos($message, 'error:') === 0): ?>
                        <div class="alert alert-error">
                            <?php echo substr($message, 7); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <form method="POST" id="bookingForm">
                    <input type="hidden" name="package" value="<?php echo $selected_package; ?>">
                    <input type="hidden" name="waktu_mulai" id="hiddenWaktuMulai">
                    <input type="hidden" name="waktu_selesai" id="hiddenWaktuSelesai">
                    
                    <div class="form-group">
                        <label for="nama">Nama Lengkap:</label>
                        <input type="text" name="nama" id="nama" placeholder="Masukkan nama lengkap" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hp">Nomor HP:</label>
                        <input type="tel" name="hp" id="hp" placeholder="08xx-xxxx-xxxx" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal">Tanggal Booking:</label>
                        <input type="date" name="tanggal" id="tanggal" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="jenis_studio">Jenis Studio:</label>
                        <select name="jenis_studio" id="jenis_studio" required>
                            <select name="jenis_studio" id="jenis_studio" required>
                            <option value="">Pilih Jenis Studio</option>
                            <option value="indoor">Studio Indoor</option>
                            <option value="outdoor">Studio Outdoor</option>
                            <option value="vintage">Studio Vintage</option>
                            <option value="modern">Studio Modern</option>
                            <option value="minimalis">Studio Minimalis</option>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="10">Tidak ada pemesanan.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

                    </div>
                    
                    <!-- Time Selection Section -->
                    <div class="form-group time-selection">
                        <label>Pilih Waktu:</label>
                        <div class="time-slots-container" id="timeSlotsContainer" style="display: none;">
                            <div class="time-slots-header">
                                <h4 style="color: white; margin: 0;">Waktu Tersedia</h4>
                                <div class="legend">
                                    <div class="legend-item">
                                        <div class="legend-color legend-available"></div>
                                        <span>Tersedia</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color legend-unavailable"></div>
                                        <span>Tidak Tersedia</span>
                                    </div>
                                </div>
                            </div>
                            <div class="time-slots-grid" id="timeSlotsGrid">
                                <!-- Time slots will be loaded here -->
                            </div>
                        </div>
                        <div id="loadingMessage" style="display: none; text-align: center; color: rgba(255,255,255,0.8); padding: 1rem;">
                            <div class="loading-spinner"></div>
                            <span style="margin-left: 0.5rem;">Memuat waktu tersedia...</span>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit_booking" id="submitBtn" disabled>
                        Buat Booking
                    </button>
                </form>
            </div>

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

