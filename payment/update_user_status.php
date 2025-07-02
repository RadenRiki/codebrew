<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
require_once '../connection.php';

$userId      = intval($_POST['user_id']);
$orderNumber = $conn->real_escape_string($_POST['order_number']);
$amount      = 100000.00; // atau ambil dari POST jika dikirim

// 1) Update payment dengan amount dan status completed
$stmt = $conn->prepare("
    UPDATE payments
    SET payment_status = 'completed', amount = ?
    WHERE user_id = ? AND order_number = ?
");
$stmt->bind_param("dis", $amount, $userId, $orderNumber);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating payment: '.$stmt->error]);
    exit;
}
$stmt->close();

// 2) Upgrade user ke premium
$stmt = $conn->prepare("
    UPDATE `user`
    SET is_premium = 1
    WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating user: '.$stmt->error]);
    exit;
}
$stmt->close();

echo json_encode(['success' => true]);
?>
