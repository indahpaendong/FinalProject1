<?php
session_start();
include 'db.php'; // jika di sini sudah ada koneksi $conn, gunakan itu

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['status'=>'error','message'=>'User belum login']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
    exit;
}

$nama_lengkap  = $data['nama_lengkap'] ?? '';
$no_hp         = $data['no_hp'] ?? '';
$tanggal       = $data['tanggal'] ?? '';
$waktu_mulai   = $data['waktu_mulai'] ?? '';
$waktu_selesai = $data['waktu_selesai'] ?? '';
$jenis_studio  = $data['jenis_studio'] ?? '';

if (
    !$nama_lengkap || !$no_hp || !$tanggal ||
    !$waktu_mulai || !$waktu_selesai || !$jenis_studio
) {
    echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO bookings (user_id, nama_lengkap, no_hp, tanggal, waktu_mulai, waktu_selesai, jenis_studio) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssss", $user_id, $nama_lengkap, $no_hp, $tanggal, $waktu_mulai, $waktu_selesai, $jenis_studio);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Booking berhasil disimpan']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan booking: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
