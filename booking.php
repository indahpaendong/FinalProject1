<?php
include 'db.php'; // koneksi database

// Proses update status jika ada form yang disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['new_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['new_status'];
    // Validasi status
    if (in_array($new_status, ['approved', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Ambil data bookings
$sql = "SELECT b.*, u.email FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        ORDER BY b.tanggal DESC, b.waktu_mulai ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin - Daftar Pemesanan</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f6f8;
        margin: 0; padding: 20px;
    }
    h1 {
        color: #333;
        margin-bottom: 20px;
        text-align: center;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    th, td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        text-align: center;
    }
    th {
        background: #667eea;
        color: white;
        text-transform: uppercase;
        font-size: 0.9rem;
    }
    tr:hover {
        background: #f0f0f8;
    }
    .status {
        padding: 5px 10px;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .status.pending {
        background: #f6ad55;
    }
    .status.approved {
        background: #48bb78;
    }
    .status.cancelled {
        background: #f56565;
    }
    form {
        margin: 0;
    }
    select {
        padding: 6px 8px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 0.9rem;
        cursor: pointer;
    }
    button {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        background: #667eea;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
        margin-left: 6px;
    }
    button:hover {
        background: #5a67d8;
    }
    @media (max-width: 768px) {
        th, td {
            font-size: 0.8rem;
            padding: 8px 10px;
        }
        button {
            padding: 5px 10px;
            font-size: 0.85rem;
        }
    }
</style>
</head>
<body>

<h1>Daftar Pemesanan Studio</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>No. HP</th>
            <th>Tanggal</th>
            <th>Waktu Mulai</th>
            <th>Waktu Selesai</th>
            <th>Jenis Studio</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                <td><?= $row['tanggal'] ?></td>
                <td><?= substr($row['waktu_mulai'], 0, 5) ?></td>
                <td><?= substr($row['waktu_selesai'], 0, 5) ?></td>
                <td><?= htmlspecialchars($row['jenis_studio']) ?></td>
                <td><span class="status <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?= $row['id'] ?>" />
                        <select name="new_status" aria-label="Ubah status pemesanan">
                            <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $row['status'] == 'approved' ? 'selected' : '' ?>>Approve</option>
                            <option value="cancelled" <?= $row['status'] == 'cancelled' ? 'selected' : '' ?>>Cancel</option>
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

<<<<<<< HEAD
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
=======
>>>>>>> 18bd6f052907c816321e3d78b8f8aaf6ccef3707
</body>
</html>
