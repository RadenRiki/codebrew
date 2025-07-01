<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
require_once '../connection.php';        // pastikan ini define $conn = mysqli_connect(...)

$userId      = intval($_POST['user_id']);
$orderNumber = $conn->real_escape_string($_POST['order_number']);

// 1) Tandai payment sebagai completed
$stmt = $conn->prepare("
    UPDATE payments
    SET payment_status = 'completed'
    WHERE user_id = ? AND order_number = ?
");
$stmt->bind_param("is", $userId, $orderNumber);
if (!$stmt->execute()) {
    http_response_code(500);
    echo "Error updating payment: ".$stmt->error;
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
    echo "Error updating user: ".$stmt->error;
    exit;
}
$stmt->close();

echo json_encode(['success' => true]);