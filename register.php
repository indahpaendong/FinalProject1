<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];  // name="nama" di form
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($nama) || empty($email) || empty($password)) {
        echo "Semua kolom wajib diisi.";
    } else {
        // Cek email sudah terdaftar atau belum
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "Email sudah terdaftar.";
        } else {
            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert data user baru dengan prepared statement
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $nama, $email, $hash);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                echo "Gagal mendaftar: " . $conn->error;
            }
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar</title>
    <link rel="stylesheet" type="text/css" href="register.css">
</head>
<body>
    <h2>Daftar Akun</h2>
    <form method="POST" action="">
        <input type="text" name="nama" placeholder="Nama Lengkap" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Daftar</button>
    </form>
    <p><a href="login.php">Sudah punya akun? Login</a></p>
</body>
</html>
