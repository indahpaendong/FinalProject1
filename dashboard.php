<?php
include 'db.php';
session_start();
if ($_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit;
}

$result = $conn->query("SELECT * FROM bookings");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard Admin - Daftar Booking Studio</title>
<style>
/* Reset dan box-sizing */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.auth-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.auth-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
}

h2 {
    color: #2d3748;
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 30px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.booking-list {
    text-align: left;
    font-size: 1.1rem;
    color: #4a5568;
    line-height: 1.6;
    max-height: 400px;
    overflow-y: auto;
    padding-right: 10px;
}

/* Scrollbar style */
.booking-list::-webkit-scrollbar {
    width: 8px;
}

.booking-list::-webkit-scrollbar-thumb {
    background-color: #667eea;
    border-radius: 10px;
}

.booking-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

@media (max-width: 480px) {
    .auth-container {
        padding: 30px 20px;
        margin: 10px;
        max-width: 100%;
    }
    h2 {
        font-size: 1.8rem;
    }
    .booking-list {
        font-size: 1rem;
    }
}
</style>
</head>
<body>
    <div class="auth-container">
        <h2>Daftar Booking Studio</h2>
        <div class="booking-list">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo htmlspecialchars($row['nama_lengkap']) . " - " . 
                         htmlspecialchars($row['tanggal']) . " - " . 
                         htmlspecialchars($row['jenis_studio']) . "<br>";
                }
            } else {
                echo "Belum ada booking.";
            }
            ?>
        </div>
    </div>
</body>
</html>
