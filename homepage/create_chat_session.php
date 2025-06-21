<?php
// Mengaktifkan error reporting untuk debugging (matikan di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Menerima data dari AJAX request
header('Content-Type: application/json');
$requestData = json_decode(file_get_contents('php://input'), true);

// Koneksi ke database
$servername = "localhost";
$username = "root"; // Ganti dengan username MySQL Anda
$password = "root"; // Ganti dengan password MySQL Anda
$dbname = "codebrew_db"; // Ganti dengan nama database Anda

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Gagal terhubung ke database: ' . $conn->connect_error
    ]);
    exit;
}

// Ambil user_id dari request
$user_id = isset($requestData['user_id']) ? $requestData['user_id'] : 1;

// Buat session_id baru
$session_id = uniqid('chat_');

// Simpan sesi baru ke database
$stmt = $conn->prepare("INSERT INTO chat_sessions (session_id, user_id, start_time) VALUES (?, ?, NOW())");
$stmt->bind_param("si", $session_id, $user_id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'session_id' => $session_id
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Gagal membuat sesi chat: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
