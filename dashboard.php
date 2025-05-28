<?php
include 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit;
}

$result = $conn->query("SELECT * FROM bookings");
?>

<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    if (in_array($status, ['pending', 'approved', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, is_seen = 0 WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: dashboard.php?success=1");
        exit;
    }
}


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit;
}

// Tangani update status jika ada form yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Validasi nilai status
    if (in_array($status, ['pending', 'approved', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();

        // Redirect agar tidak mengulang POST (dan menampilkan notifikasi)
        header("Location: dashboard.php?success=1");
        exit;
    }
}

// Ambil data booking
$result = $conn->query("SELECT * FROM bookings");
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - Daftar Booking</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom right, #667eea, #764ba2);
      color: #333;
      padding: 20px;
    }

    h2 {
      text-align: center;
      color: white;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    th {
      background-color: #667eea;
      color: white;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    select, button {
      padding: 5px 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .link {
      display: block;
      margin-top: 20px;
      text-align: center;
      color: white;
      text-decoration: none;
      font-weight: bold;
    }

    .notification {
  padding: 15px 20px;
  margin-bottom: 20px;
  border-radius: 5px;
  font-weight: bold;
  color: #155724;
  background-color: #d4edda;
  border: 1px solid #c3e6cb;
  text-align: center;
}
  </style>
</head>
<body>
<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
  <div class="notification" id="notification">Successfully updated!</div>
<?php endif; ?>

  <h2>Daftar Booking Studio</h2>

  <?php if ($result && $result->num_rows > 0): ?>
  <table>
    <tr>
      <th>Nama Lengkap</th>
      <th>Tanggal</th>
      <th>Jam Mulai</th>
      <th>Jam Selesai</th>
      <th>Studio</th>
      <th>Status</th>
      <th>Aksi</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
      <td><?= htmlspecialchars($row['tanggal']) ?></td>
      <td><?= htmlspecialchars($row['waktu_mulai']) ?></td>
      <td><?= htmlspecialchars($row['waktu_selesai']) ?></td>
      <td><?= htmlspecialchars($row['jenis_studio']) ?></td>
      <td><?= htmlspecialchars($row['status']) ?></td>
      <td>
          <form method="post" action="dashboard.php">
          <input type="hidden" name="id" value="<?= $row['id'] ?>">
          <select name="status">
            <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= $row['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="cancelled" <?= $row['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
          </select>
          <button type="submit">Update</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  <?php else: ?>
    <p style="color:white; text-align:center;">Belum ada data booking.</p>
  <?php endif; ?>

<script>
  const notif = document.getElementById('notification');
  if (notif) {
    setTimeout(() => {
      notif.style.display = 'none';
    }, 3000);
  }
</script>

</body>
</html>