<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';

    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$nama', '$email', '$pass', '$role')";
    if ($conn->query($sql)) {
        header("Location: login.php");
    } else {
        echo "Gagal mendaftar: " . $conn->error;
    }
}
?>

<form method="POST">
  Nama: <input name="name"><br>
  Email: <input name="email"><br>
  Password: <input type="password" name="password"><br>
  <button type="submit">Daftar</button>
</form>

