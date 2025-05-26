<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['user_id'];
$message = '';
$step = isset($_GET['step']) ? $_GET['step'] : 1;
$selected_package = isset($_GET['package']) ? $_GET['package'] : '';

// Definisi paket
$packages = [
    'basic' => [
        'name' => 'Basic Package',
        'price' => 'Rp 150.000',
        'duration' => '1 Jam',
        'duration_hours' => 1,
        'features' => ['5 Foto Terbaik', 'Basic Editing', '1 Studio Pilihan', 'Digital Copy'],
        'icon' => '📸',
        'color' => 'linear-gradient(135deg, #667eea, #764ba2)'
    ],
    'premium' => [
        'name' => 'Premium Package',
        'price' => 'Rp 300.000',
        'duration' => '2 Jam',
        'duration_hours' => 2,
        'features' => ['15 Foto Terbaik', 'Professional Editing', '2 Studio Pilihan', 'Digital + Print', 'Make Up Session'],
        'icon' => '⭐',
        'color' => 'linear-gradient(135deg, #ff6b6b, #ff8e8e)'
    ],
    'ultimate' => [
        'name' => 'Ultimate Package',
        'price' => 'Rp 500.000',
        'duration' => '3 Jam',
        'duration_hours' => 3,
        'features' => ['30 Foto Terbaik', 'Expert Editing', 'All Studio Access', 'Album Premium', 'Professional Make Up', 'Props Included'],
        'icon' => '👑',
        'color' => 'linear-gradient(135deg, #f093fb, #f5576c)'
    ]
];

// Function untuk mendapatkan jam yang sudah dibooking
function getBookedTimes($conn, $date, $studio) {
    $bookedTimes = [];
    $sql = "SELECT waktu_mulai, waktu_selesai FROM bookings WHERE tanggal = ? AND jenis_studio = ? AND status != 'cancelled'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $date, $studio);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $bookedTimes[] = [
            'start' => $row['waktu_mulai'],
            'end' => $row['waktu_selesai']
        ];
    }
    
    return $bookedTimes;
}

// Function untuk check apakah jam tersedia
function isTimeAvailable($proposedStart, $proposedEnd, $bookedTimes) {
    foreach ($bookedTimes as $booking) {
        // Convert ke format yang bisa dibandingkan
        $proposedStartTime = strtotime($proposedStart);
        $proposedEndTime = strtotime($proposedEnd);
        $bookedStartTime = strtotime($booking['start']);
        $bookedEndTime = strtotime($booking['end']);
        
        // Check overlap
        if (($proposedStartTime < $bookedEndTime) && ($proposedEndTime > $bookedStartTime)) {
            return false;
        }
    }
    return true;
}

// AJAX endpoint untuk mendapatkan jam yang tersedia
if (isset($_GET['action']) && $_GET['action'] == 'get_available_times') {
    $date = $_GET['date'];
    $studio = $_GET['studio'];
    $packageType = $_GET['package'];
    
    $duration = $packages[$packageType]['duration_hours'];
    $bookedTimes = getBookedTimes($conn, $date, $studio);
    
    // Generate slot waktu (09:00 - 17:00)
    $availableSlots = [];
    for ($hour = 9; $hour <= 17 - $duration; $hour++) {
        $startTime = sprintf("%02d:00", $hour);
        $endTime = sprintf("%02d:00", $hour + $duration);
        
        if (isTimeAvailable($startTime, $endTime, $bookedTimes)) {
            $availableSlots[] = [
                'start' => $startTime,
                'end' => $endTime,
                'available' => true
            ];
        }
    }
    
    // Juga kirim jam yang sudah dibooking untuk ditampilkan
    $unavailableSlots = [];
    foreach ($bookedTimes as $booking) {
        $unavailableSlots[] = [
            'start' => substr($booking['start'], 0, 5),
            'end' => substr($booking['end'], 0, 5),
            'available' => false
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'available' => $availableSlots,
        'unavailable' => $unavailableSlots
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $step == 2) {
    $nama = $_POST['nama'];
    $hp = $_POST['hp'];
    $tgl = $_POST['tanggal'];
    $mulai = $_POST['waktu_mulai'];
    $selesai = $_POST['waktu_selesai'];
    $studio = $_POST['jenis_studio'];
    $package = $_POST['package'];
    
    // Validasi ketersediaan waktu sebelum insert
    $bookedTimes = getBookedTimes($conn, $tgl, $studio);
    if (!isTimeAvailable($mulai, $selesai, $bookedTimes)) {
        $message = 'error: Waktu yang dipilih sudah dibooking oleh orang lain. Silakan pilih waktu lain.';
    } else {
        $sql = "INSERT INTO bookings (user_id, nama_lengkap, no_hp, tanggal, waktu_mulai, waktu_selesai, jenis_studio, package_type, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";
       $nama_customer = "John Doe";
$tanggal_booking = "2025-05-27";
$waktu_booking = "14:00";
$layanan = "Potong Rambut";

$stmt = $conn->prepare("INSERT INTO bookings (nama_customer, tanggal, waktu, layanan) VALUES (?, ?, ?, ?)");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ssss", $nama_customer, $tanggal_booking, $waktu_booking, $layanan);
$stmt->execute();

// 2. CONTOH: Mengambil data booking berdasarkan ID
$user_id = 123;

$stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// 3. CONTOH: Update status booking
$booking_id = 456;
$status_baru = "confirmed";

$stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("si", $status_baru, $booking_id);
$stmt->execute();

// 4. CONTOH: Hapus booking
$booking_id = 789;

$stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $booking_id);
$stmt->execute();

// 5. CONTOH: Cek slot waktu yang tersedia
$tanggal = "2025-05-27";
$waktu = "14:00";

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE tanggal = ? AND waktu = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ss", $tanggal, $waktu);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    echo "Slot waktu sudah terisi!";
} else {
    echo "Slot waktu masih tersedia";
}
        $stmt->bind_param("isssssss", $id_user, $nama, $hp, $tgl, $mulai, $selesai, $studio, $package);
        
        if ($stmt->execute()) {
            $step = 3;
            $message = 'success';
        } else {
            $message = 'error: ' . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Studio Foto - Pilih Paket</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
            position: relative;
        }

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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Progress Bar */
        .progress-bar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-50%);
            z-index: 1;
        }

        .progress-fill {
            position: absolute;
            top: 50%;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #ff6b6b, #ff8e8e);
            transform: translateY(-50%);
            transition: width 0.5s ease;
            z-index: 2;
            border-radius: 2px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            z-index: 3;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            border-color: white;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .step.completed .step-circle {
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            border-color: white;
        }

        .step-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            font-weight: 600;
            text-align: center;
        }

        .step.active .step-label {
            color: white;
        }

        /* Package Selection */
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .package-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .package-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--package-color);
        }

        .package-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .package-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .package-name {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .package-price {
            color: #ff6b6b;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .package-duration {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .package-features {
            list-style: none;
            text-align: left;
            margin-bottom: 2rem;
        }

        .package-features li {
            color: rgba(255, 255, 255, 0.9);
            padding: 0.5rem 0;
            position: relative;
            padding-left: 1.5rem;
        }

        .package-features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #4CAF50;
            font-weight: bold;
        }

        .select-package-btn {
            background: var(--package-color);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 1rem;
        }

        .select-package-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        /* Form Styles */
        .booking-form {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .selected-package-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .selected-package-info h3 {
            color: white;
            margin-bottom: 0.5rem;
        }

        .selected-package-info p {
            color: rgba(255, 255, 255, 0.8);
        }

        h2 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            background: linear-gradient(45deg, #fff, #f8f9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        input, select {
            width: 100%;
            padding: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        input:focus, select:focus {
            outline: none;
            border-color: rgba(255, 107, 107, 0.8);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 20px rgba(255, 107, 107, 0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Time Slots */
        .time-selection {
            margin-bottom: 2rem;
        }

        .time-slots-container {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .time-slots-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .legend {
            display: flex;
            gap: 1rem;
            font-size: 0.9rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }

        .legend-available { background: #4CAF50; }
        .legend-unavailable { background: #f44336; }

        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.8rem;
        }

        .time-slot {
            padding: 0.8rem;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .time-slot.available {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border-color: rgba(76, 175, 80, 0.3);
        }

        .time-slot.available:hover {
            background: rgba(76, 175, 80, 0.3);
            transform: translateY(-2px);
        }

        .time-slot.unavailable {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .time-slot.selected {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e) !important;
            color: white !important;
            border-color: white;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        button {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: white;
            border: none;
            padding: 1.2rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 107, 107, 0.4);
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        /* Success Page */
        .success-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 4rem 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .success-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            display: block;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-30px); }
            60% { transform: translateY(-15px); }
        }

        .success-title {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .success-message {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .nav-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            body { padding: 1rem; }
            .packages-grid { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            .progress-steps { flex-direction: column; gap: 1rem; }
            .progress-steps::before, .progress-fill { display: none; }
            .nav-buttons { flex-direction: column; }
            .time-slots-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
            .legend { flex-direction: column; gap: 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Progress Bar -->
        <div class="progress-bar">
            <div class="progress-steps">
                <div class="progress-fill" style="width: <?php echo ($step-1) * 50; ?>%;"></div>
                
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                    <div class="step-circle"><?php echo $step > 1 ? '✓' : '1'; ?></div>
                    <div class="step-label">Pilih Paket</div>
                </div>
                
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                    <div class="step-circle"><?php echo $step > 2 ? '✓' : '2'; ?></div>
                    <div class="step-label">Detail Booking</div>
                </div>
                
                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                    <div class="step-circle"><?php echo $step >= 3 ? '✓' : '3'; ?></div>
                    <div class="step-label">Konfirmasi</div>
                </div>
            </div>
        </div>

        <?php if ($step == 1): ?>
            <!-- Package Selection -->
            <h2 style="text-align: center; margin-bottom: 3rem;">Pilih Paket Foto Anda</h2>
            
            <div class="packages-grid">
                <?php foreach ($packages as $key => $package): ?>
                    <div class="package-card" style="--package-color: <?php echo $package['color']; ?>">
                        <span class="package-icon"><?php echo $package['icon']; ?></span>
                        <h3 class="package-name"><?php echo $package['name']; ?></h3>
                        <div class="package-price"><?php echo $package['price']; ?></div>
                        <div class="package-duration"><?php echo $package['duration']; ?></div>
                        
                        <ul class="package-features">
                            <?php foreach ($package['features'] as $feature): ?>
                                <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <button class="select-package-btn" onclick="selectPackage('<?php echo $key; ?>')">
                            Pilih Paket Ini
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($step == 2): ?>
            <!-- Booking Form -->
            <div class="booking-form">
                <div class="form-header">
                    <h2>Detail Booking</h2>
                    
                    <?php if ($selected_package && isset($packages[$selected_package])): ?>
                        <div class="selected-package-info">
                            <h3><?php echo $packages[$selected_package]['icon']; ?> <?php echo $packages[$selected_package]['name']; ?></h3>
                            <p><?php echo $packages[$selected_package]['price']; ?> - <?php echo $packages[$selected_package]['duration']; ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (strpos($message, 'error:') === 0): ?>
                        <div class="alert alert-error">
                            <?php echo substr($message, 7); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <form method="POST" id="bookingForm">
                    <input type="hidden" name="package" value="<?php echo $selected_package; ?>">
                    <input type="hidden" name="waktu_mulai" id="hiddenWaktuMulai">
                    <input type="hidden" name="waktu_selesai" id="hiddenWaktuSelesai">
                    
                    <div class="form-group">
                        <label for="nama">Nama Lengkap:</label>
                        <input type="text" name="nama" id="nama" placeholder="Masukkan nama lengkap" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hp">Nomor HP:</label>
                        <input type="tel" name="hp" id="hp" placeholder="08xx-xxxx-xxxx" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal">Tanggal Booking:</label>
                        <input type="date" name="tanggal" id="tanggal" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="jenis_studio">Jenis Studio:</label>
                        <select name="jenis_studio" id="jenis_studio" required>
                            <option value="">-- Pilih Jenis Studio --</option>
                            <option value="Studio A">Studio A - Portrait & Fashion</option>
                            <option value="Studio B">Studio B - Product Photography</option>
                            <option value="Studio C">Studio C - Group Photography</option>
                        </select>
                    </div>
                    
                    <div class="time-selection">
                        <label>Pilih Waktu:</label>
                        <div class="time-slots-container" id="timeSlotsContainer" style="display: none;">
                            <div class="time-slots-header">
                                <h4 style="color: white; margin: 0;">Jam Tersedia</h4>
                                <div class="legend">
                                    <div class="legend-item">
                                        <div class="legend-color legend-available"></div>
                                        <span>Tersedia</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color legend-unavailable"></div>
                                        <span>Tidak Tersedia</span>
                                    </div>
                                </div>
                            </div>
                            <div class="time-slots-grid" id="timeSlotsGrid">
                                <div class="loading-spinner"></div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" id="submitBtn" disabled>🎯 Konfirmasi Booking</button>
                </form>

                </div>

        <?php else: ?>
            <!-- Success Page -->
            <div class="success-container">
                <span class="success-icon">🎉</span>
                <h2 class="success-title">Booking Berhasil!</h2>
                <p class="success-message">
                    Terima kasih! Booking Anda telah berhasil disimpan.<br>
                    Kami akan menghubungi Anda segera untuk konfirmasi.
                </p>
                
                <div class="nav-buttons">
                    <a href="dashboard.php" class="nav-btn">📊 Dashboard</a>
                    <a href="history.php" class="nav-btn">📝 Riwayat Booking</a>
                    <a href="booking.php" class="nav-btn">➕ Booking Lagi</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        let selectedTimeSlot = null;
        let currentPackage = '<?php echo $selected_package; ?>';
        
        function selectPackage(packageType) {
            window.location.href = '?step=2&package=' + packageType;
        }

        // Set minimum date to today
        if (document.getElementById('tanggal')) {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('tanggal').min = tomorrow.toISOString().split('T')[0];
        }

        // Function to load available time slots
        function loadTimeSlots() {
            const dateInput = document.getElementById('tanggal');
            const studioSelect = document.getElementById('jenis_studio');
            const timeSlotsContainer = document.getElementById('timeSlotsContainer');
            const timeSlotsGrid = document.getElementById('timeSlotsGrid');
            const submitBtn = document.getElementById('submitBtn');
            
            if (!dateInput.value || !studioSelect.value || !currentPackage) {
                timeSlotsContainer.style.display = 'none';
                submitBtn.disabled = true;
                return;
            }
            
            // Show loading
            timeSlotsContainer.style.display = 'block';
            timeSlotsGrid.innerHTML = '<div class="loading-spinner"></div><span style="color: rgba(255,255,255,0.8); margin-left: 10px;">Memuat jam tersedia...</span>';
            
            // Fetch available times
            fetch(?action=get_available_times&date=${dateInput.value}&studio=${studioSelect.value}&package=${currentPackage})
                .then(response => response.json())
                .then(data => {
                    displayTimeSlots(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    timeSlotsGrid.innerHTML = '<p style="color: #f44336;">Gagal memuat jam tersedia. Silakan refresh halaman.</p>';
                });
        }

        function displayTimeSlots(data) {
            const timeSlotsGrid = document.getElementById('timeSlotsGrid');
            timeSlotsGrid.innerHTML = '';
            
            // Combine available and unavailable slots
            const allSlots = [...data.available, ...data.unavailable];
            
            // Sort by start time
            allSlots.sort((a, b) => a.start.localeCompare(b.start));
            
            if (allSlots.length === 0) {
                timeSlotsGrid.innerHTML = '<p style="color: rgba(255,255,255,0.8); grid-column: 1/-1; text-align: center; padding: 2rem;">Tidak ada slot waktu tersedia untuk tanggal dan studio yang dipilih.</p>';
                return;
            }
            
            allSlots.forEach(slot => {
                const slotElement = document.createElement('div');
                slotElement.className = time-slot ${slot.available ? 'available' : 'unavailable'};
                slotElement.innerHTML = `
                    <div style="font-size: 0.9rem; font-weight: bold;">${slot.start} - ${slot.end}</div>
                    <div style="font-size: 0.8rem; opacity: 0.8;">${slot.available ? 'Tersedia' : 'Terbooked'}</div>
                `;
                
                if (slot.available) {
                    slotElement.addEventListener('click', () => selectTimeSlot(slotElement, slot));
                }
                
                timeSlotsGrid.appendChild(slotElement);
            });
        }

        function selectTimeSlot(element, slot) {
            // Remove previous selection
            if (selectedTimeSlot) {
                selectedTimeSlot.classList.remove('selected');
            }
            
            // Select new slot
            element.classList.add('selected');
            selectedTimeSlot = element;
            
            // Update hidden inputs
            document.getElementById('hiddenWaktuMulai').value = slot.start + ':00';
            document.getElementById('hiddenWaktuSelesai').value = slot.end + ':00';
            
            // Enable submit button
            document.getElementById('submitBtn').disabled = false;
        }

        // Event listeners
        document.getElementById('tanggal').addEventListener('change', () => {
            selectedTimeSlot = null;
            document.getElementById('submitBtn').disabled = true;
            loadTimeSlots();
        });
        
        document.getElementById('jenis_studio').addEventListener('change', () => {
            selectedTimeSlot = null;
            document.getElementById('submitBtn').disabled = true;
            loadTimeSlots();
        });

        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            if (!selectedTimeSlot) {
                e.preventDefault();
                alert('Silakan pilih waktu booking terlebih dahulu!');
                return false;
            }
            
            const nama = document.getElementById('nama').value.trim();
            const hp = document.getElementById('hp').value.trim();
            
            if (!nama || !hp) {
                e.preventDefault();
                alert('Mohon lengkapi semua data yang diperlukan!');
                return false;
            }
            
            // Validate phone number format
            const phoneRegex = /^(\+62|62|0)8[1-9][0-9]{6,9}$/;
            if (!phoneRegex.test(hp.replace(/[-\s]/g, ''))) {
                e.preventDefault();
                alert('Format nomor HP tidak valid! Gunakan format: 08xx-xxxx-xxxx');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<div class="loading-spinner" style="display: inline-block; margin-right: 10px;"></div> Memproses...';
            submitBtn.disabled = true;
        });

        // Auto-format phone number
        document.getElementById('hp').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('0')) {
                value = value.substring(1);
            }
            if (value.length > 0) {
                if (value.length <= 4) {
                    value = '08' + value;
                } else if (value.length <= 8) {
                    value = '08' + value.substring(2, 4) + '-' + value.substring(4);
                } else {
                    value = '08' + value.substring(2, 4) + '-' + value.substring(4, 8) + '-' + value.substring(8, 12);
                }
            }
            e.target.value = value;
        });

        // Real-time availability check
        setInterval(() => {
            if (document.getElementById('timeSlotsContainer').style.display === 'block') {
                loadTimeSlots();
            }
        }, 30000); // Refresh every 30 seconds

        // Notification for booking conflicts
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = alert alert-${type};
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.style.minWidth = '300px';
            notification.style.animation = 'slideIn 0.3s ease';
            notification.innerHTML = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        }

        // Add CSS for notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Check for booking conflicts when page loads
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('conflict')) {
                showNotification('⚠ Waktu yang Anda pilih sudah dibooking oleh orang lain. Silakan pilih waktu lain.', 'error');
            }
        });

        // Keyboard navigation for time slots
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && selectedTimeSlot) {
                selectedTimeSlot.classList.remove('selected');
                selectedTimeSlot = null;
                document.getElementById('submitBtn').disabled = true;
                document.getElementById('hiddenWaktuMulai').value = '';
                document.getElementById('hiddenWaktuSelesai').value = '';
            }
        });

        // Prevent double submission
        let isSubmitting = false;
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            isSubmitting = true;
        });

        // Auto-refresh when tab becomes visible (to check for new bookings)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && document.getElementById('timeSlotsContainer').style.display === 'block') {
                setTimeout(loadTimeSlots, 1000);
            }
        });
    </script>
</body>
</html>
