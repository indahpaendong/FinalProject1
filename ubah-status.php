<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $status = $_POST['status'];

    // Update status dan reset is_seen supaya user dapat notif
    $stmt = $conn->prepare("UPDATE bookings SET status = ?, is_seen = 0 WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    header("Location: dashboard.php?success=1");
    exit;
}
?>
