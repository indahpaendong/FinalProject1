<?php
// Koneksi database
$host = "localhost";
$user = "root";
$pass = "";
$db = "booking_studio";  // ganti sesuai nama dbmu

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Handle Tambah Paket
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_paket'];
    $harga = $_POST['harga'];
    $sql = "INSERT INTO paket (nama_paket, harga) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nama, $harga);
    $stmt->execute();
    header("Location: paket.php");
    exit;
}

// Handle Hapus Paket
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $sql = "DELETE FROM paket WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: paket.php");
    exit;
}

// Handle Edit Paket (update)
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama_paket'];
    $harga = $_POST['harga'];
    $sql = "UPDATE paket SET nama_paket=?, harga=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $nama, $harga, $id);
    $stmt->execute();
    header("Location: paket.php");
    exit;
}

// Ambil data paket (untuk tampil)
$result = $conn->query("SELECT * FROM paket");

// Untuk edit: ambil data paket berdasarkan id
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    // Pastikan $id adalah angka (integer)
    if (!is_numeric($id)) {
        $editData = null;
    } else {
        $stmt = $conn->prepare("SELECT * FROM paket WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resEdit = $stmt->get_result();

        if ($resEdit && $resEdit->num_rows > 0) {
            $editData = $resEdit->fetch_assoc();
        } else {
            $editData = null;
        }
        $stmt->close();
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Paket Studio</title>
    <link rel="stylesheet" href="css/style-paket.css">
    <style>
        table { border-collapse: collapse; width: 60%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        form { margin-bottom: 30px; }
        input[type=text], input[type=number] { padding: 6px; width: 200px; }
        input[type=submit], button { padding: 6px 12px; margin-top: 10px; cursor: pointer; }
        a { text-decoration: none; color: blue; margin-right: 10px; }
        a.delete { color: red; }
    </style>
</head>
<body>
<a href="dashboard.php">⬅ Kembali ke Dashboard</a>
<h2><?php echo $editData ? "Edit Paket" : "Tambah Paket"; ?></h2>

<form method="post" action="paket.php">
    <?php if ($editData): ?>
        <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
    <?php endif; ?>
    <label>Nama Paket:</label><br>
    <input type="text" name="nama_paket" required value="<?php echo $editData ? htmlspecialchars($editData['nama_paket']) : ''; ?>"><br><br>

    <label>Harga (Rp):</label><br>
    <input type="number" name="harga" required value="<?php echo $editData ? $editData['harga'] : ''; ?>"><br><br>

    <?php if ($editData): ?>
        <input type="submit" name="edit" value="Update Paket">
        <a href="paket.php">Batal</a>
    <?php else: ?>
        <input type="submit" name="tambah" value="Tambah Paket">
    <?php endif; ?>
</form>

<h2>Daftar Paket</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Paket</th>
            <th>Harga (Rp)</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['nama_paket']); ?></td>
            <td><?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
            <td>
                <a href="paket.php?edit=<?php echo $row['id']; ?>">Edit</a>
                <a href="paket.php?hapus=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Yakin ingin hapus paket ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>