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
            exit();
        } else {
            $error = "Password salah";
        }
    } else {
        $error = "Email tidak ditemukan";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Booking Studio</title>
    <link rel="stylesheet" href="stylelogin.css">
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST" action="">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
