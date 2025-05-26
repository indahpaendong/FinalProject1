<<<<<<< HEAD
=======
<?php
// Anda bisa menambahkan PHP logic di sini jika diperlukan
// Misalnya: session check, database connection, dll.
?>

>>>>>>> c211a72c6d5591e58dc4f8a7d63854fb712604b7
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Booking Studio Foto</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated Background Elements */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 118, 117, 0.3) 0%, transparent 50%);
            z-index: -1;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Container Styles */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
        }

        /* Hero Section */
        h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 700;
            color: #fff;
            margin-bottom: 1rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease-out;
            background: linear-gradient(45deg, #fff, #f8f9ff, #e8eaff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        p {
            font-size: clamp(1.1rem, 2.5vw, 1.3rem);
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 3rem;
            max-width: 600px;
            animation: fadeInUp 1s ease-out 0.2s both;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Button Group */
        .btn-group {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 4rem;
            animation: fadeInUp 1s ease-out 0.4s both;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            min-width: 140px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        /* Primary Button */
        .btn:not(.btn-secondary) {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            border: 2px solid transparent;
        }

        .btn:not(.btn-secondary):hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(255, 107, 107, 0.4);
            background: linear-gradient(135deg, #ff5252, #ff7979);
        }

        /* Secondary Button */
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            transform: translateY(-3px) scale(1.05);
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 15px 35px rgba(255, 255, 255, 0.2);
        }

        /* Footer */
        footer {
            position: relative;
            margin-top: auto;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        footer p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin: 0;
            padding: 2rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin-top: 3rem;
            padding: 1.5rem 2rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Floating Elements */
        .container::after {
            content: '';
            position: absolute;
            top: 10%;
            right: 10%;
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border-radius: 50%;
            animation: float 15s ease-in-out infinite reverse;
            z-index: -1;
        }

        .container::before {
            content: '';
            position: absolute;
            bottom: 20%;
            left: 5%;
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.2), rgba(255, 142, 142, 0.1));
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation: float 12s ease-in-out infinite;
            z-index: -1;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 1rem;
                width: 100%;
                max-width: 300px;
            }
            
            .btn {
                width: 100%;
                padding: 1rem 2rem;
            }
            
            footer p {
                font-size: 0.8rem;
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 2rem;
                margin-bottom: 0.5rem;
            }
            
            p {
                font-size: 1rem;
                margin-bottom: 2rem;
            }
            
            .btn {
                font-size: 1rem;
                padding: 0.8rem 1.5rem;
            }
        }

        /* Loading Animation */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        .btn:active {
            animation: pulse 0.3s ease-in-out;
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ff6b6b, #667eea);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #ff5252, #5a67d8);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Selamat Datang di Sistem Booking Studio Foto</h1>
        <p>Pesan studio foto dengan mudah dan cepat!</p>
        
        <div class="btn-group">
            <a href="register.php" class="btn">Daftar</a>
            <a href="login.php" class="btn btn-secondary">Login</a>
        </div>
        
        <footer>
            <p>Kelompok 5 - Indah Paendong, Yusi Meilany Kendek Allo & Afny Rewur</p>
        </footer>
    </div>
<<<<<<< HEAD
  </main>

  <footer>
    <p>Kelompok 5 - Indah Paendong & Yusi Meilany Kendek Allo & Afny Rewur</p>
  </footer>
=======
>>>>>>> c211a72c6d5591e58dc4f8a7d63854fb712604b7
</body>
</html>