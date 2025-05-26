<?php
include 'db.php'; // koneksi database

// Proses update status jika ada form yang disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['new_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['new_status'];
    // Validasi status
    if (in_array($new_status, ['approved', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Ambil data bookings
$sql = "SELECT b.*, u.email FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        ORDER BY b.tanggal DESC, b.waktu_mulai ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin - Daftar Pemesanan</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f6f8;
        margin: 0; padding: 20px;
    }
    h1 {
        color: #333;
        margin-bottom: 20px;
        text-align: center;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    th, td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        text-align: center;
    }
    th {
        background: #667eea;
        color: white;
        text-transform: uppercase;
        font-size: 0.9rem;
    }
    tr:hover {
        background: #f0f0f8;
    }
    .status {
        padding: 5px 10px;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .status.pending {
        background: #f6ad55;
    }
    .status.approved {
        background: #48bb78;
    }
    .status.cancelled {
        background: #f56565;
    }
    form {
        margin: 0;
    }
    select {
        padding: 6px 8px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 0.9rem;
        cursor: pointer;
    }
    button {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        background: #667eea;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
        margin-left: 6px;
    }
    button:hover {
        background: #5a67d8;
    }
    @media (max-width: 768px) {
        th, td {
            font-size: 0.8rem;
            padding: 8px 10px;
        }
        button {
            padding: 5px 10px;
            font-size: 0.85rem;
        }
    }
</style>
</head>
<body>

<h1>Daftar Pemesanan Studio</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>No. HP</th>
            <th>Tanggal</th>
            <th>Waktu Mulai</th>
            <th>Waktu Selesai</th>
            <th>Jenis Studio</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                <td><?= $row['tanggal'] ?></td>
                <td><?= substr($row['waktu_mulai'], 0, 5) ?></td>
                <td><?= substr($row['waktu_selesai'], 0, 5) ?></td>
                <td><?= htmlspecialchars($row['jenis_studio']) ?></td>
                <td><span class="status <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?= $row['id'] ?>" />
                        <select name="new_status" aria-label="Ubah status pemesanan">
                            <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $row['status'] == 'approved' ? 'selected' : '' ?>>Approve</option>
                            <option value="cancelled" <?= $row['status'] == 'cancelled' ? 'selected' : '' ?>>Cancel</option>
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

</body>
</html>
