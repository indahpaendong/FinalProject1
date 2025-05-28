<?php
// paket.php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Paket Studio Foto</title>
  <link rel="stylesheet" href="booking.css">
</head>
<body>

<div class="container">
  <h1>Daftar Paket Studio</h1>
  <div id="paketList" class="paket-container">

  <?php
  // Definisikan array paket sekali saja di luar []
  $paketList = [
    [
      'nama' => 'Basic Package',
      'deskripsi' => 'Paket basic dengan 5 foto terbaik dan editing sederhana',
      'harga' => 150000,
      'durasi' => '1 Jam',
      'fitur' => ['5 Foto Terbaik', 'Basic Editing', '1 Studio Pilihan', 'Digital Copy'],
      'icon' => '📸',
      'warna' => 'linear-gradient(135deg, #667eea, #764ba2)',
    ],
    [
      'nama' => 'Premium Package',
      'deskripsi' => 'Paket premium dengan 15 foto terbaik dan editing profesional',
      'harga' => 300000,
      'durasi' => '2 Jam',
      'fitur' => ['15 Foto Terbaik', 'Professional Editing', '2 Studio Pilihan', 'Digital + Print', 'Make Up Session'],
      'icon' => '⭐',
      'warna' => 'linear-gradient(135deg, #ff6b6b, #ff8e8e)',
    ],
    [
      'nama' => 'Ultimate Package',
      'deskripsi' => 'Paket ultimate dengan 30 foto terbaik, editing expert, dan akses semua studio',
      'harga' => 500000,
      'durasi' => '3 Jam',
      'fitur' => ['30 Foto Terbaik', 'Expert Editing', 'All Studio Access', 'Album Premium', 'Professional Make Up', 'Props Included'],
      'icon' => '👑',
      'warna' => 'linear-gradient(135deg, #f093fb, #f5576c)',
    ],
  ];

  foreach ($paketList as $paket) {
    echo '
    <div class="paket" style="background:' . $paket['warna'] . ';">
      <h3>' . htmlspecialchars($paket['nama']) . ' ' . $paket['icon'] . '</h3>
      <p>' . htmlspecialchars($paket['deskripsi']) . '</p>
      <p>Harga: Rp' . number_format($paket['harga'], 0, ',', '.') . ' / ' . $paket['durasi'] . '</p>
      <a href="booking.php?paket=' . urlencode($paket['nama']) . '">
        <button>Pilih Paket Ini</button>
      </a>
    </div>';
  }
  ?>

  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> Kelompok 5 - Indah, Yusi, Afny
</footer>

</body>
</html>
