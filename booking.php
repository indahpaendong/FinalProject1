<?php
include 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$selected_package = isset($_GET['package']) ? htmlspecialchars($_GET['package']) : '';

// Package definitions
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

// Function to get booked time slots
function getBookedTimes($conn, $date, $studio) {
    $bookedTimes = [];
    $stmt = $conn->prepare("SELECT waktu_mulai, waktu_selesai FROM bookings WHERE tanggal = ? AND jenis_studio = ? AND status != 'cancelled'");
    if ($stmt) {
        $stmt->bind_param("ss", $date, $studio);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $bookedTimes[] = [
                'start' => $row['waktu_mulai'],
                'end' => $row['waktu_selesai']
            ];
        }
        $stmt->close();
    }
    return $bookedTimes;
}

// Function to check if a time slot is available
function isTimeAvailable($proposedStart, $proposedEnd, $bookedTimes) {
    $proposedStartTime = strtotime($proposedStart);
    $proposedEndTime = strtotime($proposedEnd);
    
    foreach ($bookedTimes as $booking) {
        $bookedStartTime = strtotime($booking['start']);
        $bookedEndTime = strtotime($booking['end']);
        
        // Check for overlap
        if (($proposedStartTime < $bookedEndTime) && ($proposedEndTime > $bookedStartTime)) {
            return false;
        }
    }
    return true;
}

// Function to generate available time slots
function generateAvailableSlots($conn, $date, $studio, $duration) {
    $bookedTimes = getBookedTimes($conn, $date, $studio);
    $availableSlots = [];
    $unavailableSlots = [];
    
    // Generate slots from 9:00 to 17:00
    for ($hour = 9; $hour <= 17 - $duration; $hour++) {
        $startTime = sprintf("%02d:00:00", $hour);
        $endTime = sprintf("%02d:00:00", $hour + $duration);
        
        if (isTimeAvailable($startTime, $endTime, $bookedTimes)) {
            $availableSlots[] = [
                'start' => substr($startTime, 0, 5),
                'end' => substr($endTime, 0, 5),
                'available' => true
            ];
        }
    }
    
    // Add booked slots for display
    foreach ($bookedTimes as $booking) {
        $unavailableSlots[] = [
            'start' => substr($booking['start'], 0, 5),
            'end' => substr($booking['end'], 0, 5),
            'available' => false
        ];
    }
    
    return [
        'available' => $availableSlots,
        'unavailable' => $unavailableSlots
    ];
}

// AJAX endpoint for getting available times
if (isset($_GET['action']) && $_GET['action'] == 'get_available_times') {
    $date = $_GET['date'] ?? '';
    $studio = $_GET['studio'] ?? '';
    $packageType = $_GET['package'] ?? '';
    
    if (isset($packages[$packageType])) {
        $duration = $packages[$packageType]['duration_hours'];
        $slots = generateAvailableSlots($conn, $date, $studio, $duration);
        
        header('Content-Type: application/json');
        echo json_encode($slots);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid package']);
    }
    exit();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['select_package']) && $step == 1) {
        $package = $_POST['package'] ?? '';
        if (isset($packages[$package])) {
            header("Location: ?step=2&package=" . urlencode($package));
            exit();
        } else {
            $message = 'error: Paket tidak valid.';
        }
    } elseif (isset($_POST['submit_booking']) && $step == 2) {
        // Validate and sanitize input
        $nama = trim($_POST['nama'] ?? '');
        $hp = trim($_POST['hp'] ?? '');
        $tanggal = $_POST['tanggal'] ?? '';
        $waktu_mulai = $_POST['waktu_mulai'] ?? '';
        $waktu_selesai = $_POST['waktu_selesai'] ?? '';
        $jenis_studio = $_POST['jenis_studio'] ?? '';
        $package = $_POST['package'] ?? '';
        
        // Validation
        if (empty($nama) || empty($hp) || empty($tanggal) || empty($waktu_mulai) || empty($waktu_selesai) || empty($jenis_studio) || empty($package)) {
            $message = 'error: Semua field wajib diisi.';
        } elseif (!isset($packages[$package])) {
            $message = 'error: Paket tidak valid.';
        } elseif (strtotime($tanggal) <= strtotime('today')) {
            $message = 'error: Tanggal booking harus minimal besok.';
        } else {
            // Validate phone number
            $hp_clean = preg_replace('/[^0-9]/', '', $hp);
            if (!preg_match('/^(08|628|8)[0-9]{8,11}$/', $hp_clean)) {
                $message = 'error: Format nomor HP tidak valid.';
            } else {
                // Check time availability
                $bookedTimes = getBookedTimes($conn, $tanggal, $jenis_studio);
                if (!isTimeAvailable($waktu_mulai, $waktu_selesai, $bookedTimes)) {
                    $message = 'error: Waktu yang dipilih sudah dibooking. Silakan pilih jam lain.';
                } else {
                    // Insert booking
                    $stmt = $conn->prepare("INSERT INTO bookings (user_id, nama_lengkap, no_hp, tanggal, waktu_mulai, waktu_selesai, jenis_studio, package_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                    
                    if ($stmt) {
                        $stmt->bind_param("isssssss", $user_id, $nama, $hp, $tanggal, $waktu_mulai, $waktu_selesai, $jenis_studio, $package);
                        
                        if ($stmt->execute()) {
                            $step = 3;
                            $message = 'success: Booking berhasil dibuat!';
                        } else {
                            $message = 'error: Gagal membuat booking: ' . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $message = 'error: Gagal mempersiapkan query: ' . $conn->error;
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Studio Foto</title>
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
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
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

        select option {
            background: #333;
            color: white;
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

        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid rgba(76, 175, 80, 0.3);
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
            
            <?php if (strpos($message, 'error:') === 0): ?>
                <div class="alert alert-error">
                    <?php echo substr($message, 7); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
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
                            
                            <button type="submit" name="select_package" value="<?php echo $key; ?>" class="select-package-btn">
                                Pilih Paket Ini
                            </button>
                            <input type="hidden" name="package" value="<?php echo $key; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </form>

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
                            <select name="jenis_studio" id="jenis_studio" required>
                            <option value="">Pilih Jenis Studio</option>
                            <option value="indoor">Studio Indoor</option>
                            <option value="outdoor">Studio Outdoor</option>
                            <option value="vintage">Studio Vintage</option>
                            <option value="modern">Studio Modern</option>
                            <option value="minimalis">Studio Minimalis</option>
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

                    </div>
                    
                    <!-- Time Selection Section -->
                    <div class="form-group time-selection">
                        <label>Pilih Waktu:</label>
                        <div class="time-slots-container" id="timeSlotsContainer" style="display: none;">
                            <div class="time-slots-header">
                                <h4 style="color: white; margin: 0;">Waktu Tersedia</h4>
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
                                <!-- Time slots will be loaded here -->
                            </div>
                        </div>
                        <div id="loadingMessage" style="display: none; text-align: center; color: rgba(255,255,255,0.8); padding: 1rem;">
                            <div class="loading-spinner"></div>
                            <span style="margin-left: 0.5rem;">Memuat waktu tersedia...</span>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit_booking" id="submitBtn" disabled>
                        Buat Booking
                    </button>
                </form>
            </div>

        <?php elseif ($step == 3): ?>
            <!-- Success Page -->
            <div class="success-container">
                <span class="success-icon">🎉</span>
                <h2 class="success-title">Booking Berhasil!</h2>
                <p class="success-message">
                    Terima kasih! Booking Anda telah berhasil dibuat.<br>
                    Kami akan menghubungi Anda segera untuk konfirmasi.
                </p>
                
                <div class="nav-buttons">
                    <a href="dashboard.php" class="nav-btn">Kembali ke Dashboard</a>
                    <a href="my_bookings.php" class="nav-btn">Lihat Booking Saya</a>
                    <a href="?step=1" class="nav-btn">Booking Lagi</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Set minimum date to tomorrow
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('tanggal');
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            const minDate = tomorrow.toISOString().split('T')[0];
            dateInput.setAttribute('min', minDate);
            
            // Add event listeners
            const tanggalInput = document.getElementById('tanggal');
            const studioSelect = document.getElementById('jenis_studio');
            
            if (tanggalInput && studioSelect) {
                tanggalInput.addEventListener('change', loadTimeSlots);
                studioSelect.addEventListener('change', loadTimeSlots);
            }
        });

        let selectedTimeSlot = null;

        function loadTimeSlots() {
            const tanggal = document.getElementById('tanggal').value;
            const studio = document.getElementById('jenis_studio').value;
            const packageType = '<?php echo $selected_package; ?>';
            
            if (!tanggal || !studio) {
                document.getElementById('timeSlotsContainer').style.display = 'none';
                return;
            }
            
            // Show loading
            document.getElementById('loadingMessage').style.display = 'block';
            document.getElementById('timeSlotsContainer').style.display = 'none';
            
            // Fetch available times
            fetch(`?action=get_available_times&date=${tanggal}&studio=${studio}&package=${packageType}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loadingMessage').style.display = 'none';
                    
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    
                    displayTimeSlots(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loadingMessage').style.display = 'none';
                    alert('Gagal memuat waktu tersedia');
                });
        }

        function displayTimeSlots(data) {
            const container = document.getElementById('timeSlotsGrid');
            const timeSlotsContainer = document.getElementById('timeSlotsContainer');
            
            container.innerHTML = '';
            
            // Display available slots
            if (data.available && data.available.length > 0) {
                data.available.forEach(slot => {
                    const slotElement = createTimeSlotElement(slot, true);
                    container.appendChild(slotElement);
                });
                
                timeSlotsContainer.style.display = 'block';
            } else {
                container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: rgba(255,255,255,0.8); padding: 2rem;">Tidak ada waktu tersedia untuk tanggal dan studio yang dipilih.</div>';
                timeSlotsContainer.style.display = 'block';
            }
            
            // Display unavailable slots for reference
            if (data.unavailable && data.unavailable.length > 0) {
                data.unavailable.forEach(slot => {
                    const slotElement = createTimeSlotElement(slot, false);
                    container.appendChild(slotElement);
                });
            }
            
            // Sort slots by time
            const slots = Array.from(container.children);
            slots.sort((a, b) => {
                const timeA = a.dataset.startTime;
                const timeB = b.dataset.startTime;
                return timeA.localeCompare(timeB);
            });
            
            container.innerHTML = '';
            slots.forEach(slot => container.appendChild(slot));
        }

        function createTimeSlotElement(slot, isAvailable) {
            const slotElement = document.createElement('div');
            slotElement.className = `time-slot ${isAvailable ? 'available' : 'unavailable'}`;
            slotElement.innerHTML = `${slot.start} - ${slot.end}`;
            slotElement.dataset.startTime = slot.start;
            slotElement.dataset.endTime = slot.end;
            
            if (isAvailable) {
                slotElement.addEventListener('click', function() {
                    selectTimeSlot(this, slot.start, slot.end);
                });
            }
            
            return slotElement;
        }

        function selectTimeSlot(element, startTime, endTime) {
            // Remove previous selection
            document.querySelectorAll('.time-slot.selected').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Add selection to clicked element
            element.classList.add('selected');
            
            // Store selected time
            selectedTimeSlot = {
                start: startTime + ':00',
                end: endTime + ':00'
            };
            
            // Update hidden inputs
            document.getElementById('hiddenWaktuMulai').value = selectedTimeSlot.start;
            document.getElementById('hiddenWaktuSelesai').value = selectedTimeSlot.end;
            
            // Enable submit button
            document.getElementById('submitBtn').disabled = false;
        }

        // Form validation
        document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
            if (!selectedTimeSlot) {
                e.preventDefault();
                alert('Silakan pilih waktu booking terlebih dahulu');
                return false;
            }
            
            // Validate phone number
            const hp = document.getElementById('hp').value;
            const hpClean = hp.replace(/[^0-9]/g, '');
            const hpPattern = /^(08|628|8)[0-9]{8,11}$/;
            
            if (!hpPattern.test(hpClean)) {
                e.preventDefault();
                alert('Format nomor HP tidak valid. Gunakan format: 08xx-xxxx-xxxx');
                return false;
            }
            
            // Confirm booking
            const confirmMessage = `Konfirmasi Booking:\n\nNama: ${document.getElementById('nama').value}\nHP: ${hp}\nTanggal: ${document.getElementById('tanggal').value}\nWaktu: ${selectedTimeSlot.start.substr(0,5)} - ${selectedTimeSlot.end.substr(0,5)}\nStudio: ${document.getElementById('jenis_studio').value}\n\nLanjutkan booking?`;
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }
        });

        // Format phone number input
        document.getElementById('hp')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            
            if (value.length > 0) {
                if (value.length <= 4) {
                    value = value;
                } else if (value.length <= 8) {
                    value = value.substr(0, 4) + '-' + value.substr(4);
                } else {
                    value = value.substr(0, 4) + '-' + value.substr(4, 4) + '-' + value.substr(8, 4);
                }
            }
            
            e.target.value = value;
        });

        // Auto-focus next input
        document.getElementById('nama')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('hp').focus();
            }
        });

        document.getElementById('hp')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('tanggal').focus();
            }
        });
    </script>
</body>
</html>

<?php
// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>