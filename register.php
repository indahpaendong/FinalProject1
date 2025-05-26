<?php
include 'db.php';

$message = ''; // untuk pesan error/sukses

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';

    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$nama', '$email', '$pass', '$role')";
    if ($conn->query($sql)) {
        header("Location: login.php");
        exit;
    } else {
        $message = "Gagal mendaftar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register</title>
    <style>
    /* Reset dan Base */
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
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    /* Container dengan animasi */
    .auth-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 450px;
        text-align: center;
        position: relative;
        overflow: hidden;
        animation: fadeInUp 0.8s ease-out both;
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

    @keyframes fadeInUp {
        0% {
            opacity: 0;
            transform: translateY(30px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    h2 {
        color: #2d3748;
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .subtitle {
        color: #718096;
        margin-bottom: 30px;
        font-size: 1rem;
        transition: color 0.3s ease;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .input-group {
        text-align: left;
    }

    .input-group label {
        display: block;
        margin-bottom: 5px;
        color: #4a5568;
        font-weight: 600;
        font-size: 0.9rem;
        transition: color 0.3s ease;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        background: #f7fafc;
        transition: all 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        transform: scale(1.02);
    }

    button[type="submit"] {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 15px;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 10px;
    }

    button[type="submit"]:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }

    button[type="submit"]:active {
        transform: translateY(-1px);
    }

    p {
        margin-top: 25px;
        color: #718096;
        transition: color 0.3s ease;
    }

    p a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    p a:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    .error-message {
        background: linear-gradient(135deg, #fed7d7, #feb2b2);
        color: #c53030;
        padding: 12px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        border-left: 4px solid #e53e3e;
        font-weight: 500;
        animation: slideFadeIn 0.5s ease-in-out;
    }

    @keyframes slideFadeIn {
        0% {
            opacity: 0;
            transform: translateY(-15px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 480px) {
        .auth-container {
            padding: 30px 20px;
            margin: 10px;
        }
        h2 {
            font-size: 1.8rem;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 12px 15px;
        }
        button[type="submit"] {
            padding: 12px;
            font-size: 1rem;
        }
    }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2>Daftar Akun</h2>
        <p class="subtitle">Buat akun baru untuk mengakses layanan kami.</p>

        <?php if ($message): ?>
            <div class="error-message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label for="name">Nama</label>
                <input type="text" id="name" name="name" required />
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required />
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required />
            </div>
            <button type="submit">Daftar</button>
        </form>
    </div>
</body>
</html>
