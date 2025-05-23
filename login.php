<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            header("Location: " . ($row['role'] === 'admin' ? "dashboard.php" : "booking.php"));
        } else {
            echo "Password salah";
        }
    } else {
        echo "Email tidak ditemukan";
    }
}
?>

<form method="POST">
  Email: <input name="email"><br>
  Password: <input type="password" name="password"><br>
  <button type="submit">Login</button>
</form>

$password = md5($_POST['password']);

