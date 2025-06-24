<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');

// 1) Cek user login
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User belum login']);
    exit;
}

// 2) Include config & koneksi DB
require_once __DIR__ . '/config.php';       // => /homepage/config.php
require_once __DIR__ . '/../connection.php'; // => one level up

if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection error']);
    exit;
}

// 3) Baca dan validasi input JSON
$raw = file_get_contents('php://input');
if (empty($raw)) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty request body']);
    exit;
}
$input = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}

$session_id   = $input['session_id'] ?? null;
$user_message = trim($input['message'] ?? '');
$user_id      = $_SESSION['user_id'];

// 4) Buat chat session baru jika perlu
if (!$session_id) {
    $session_id = uniqid('chat_');
    $stmt = $conn->prepare(
        "INSERT INTO chat_sessions (session_id, user_id, start_time)
         VALUES (?, ?, NOW())"
    );
    $stmt->bind_param('si', $session_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// 5) Simpan pesan user ke DB
$stmt = $conn->prepare(
    "INSERT INTO chat_messages (session_id, user_id, content, sender, timestamp)
     VALUES (?, ?, ?, 'user', NOW())"
);
$stmt->bind_param('sis', $session_id, $user_id, $user_message);
$stmt->execute();
$stmt->close();

// 6) Ambil riwayat chat untuk context
$history = [];
$stmt = $conn->prepare(
    "SELECT sender, content
       FROM chat_messages
      WHERE session_id = ?
      ORDER BY timestamp ASC"
);
$stmt->bind_param('s', $session_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $history[] = [
        'role'    => $row['sender'] === 'user' ? 'user' : 'assistant',
        'content' => $row['content']
    ];
}
$stmt->close();

// 7) System prompt untuk persona CodeBrew Assistant
$systemPrompt = [
    'role'    => 'system',
    'content' =>
        "Kamu adalah CodeBrew Assistant, AI khusus untuk membantu pertanyaan seputar " .
        "coding (HTML, CSS, JavaScript, Python, PHP, MySQL). " .
        "Jangan jawab di luar topik dan selalu tunjukkan persona ramah dan ringkas."
];

// Gabungkan system prompt + history
$messages = array_merge([$systemPrompt], $history);

// 8) Panggil Groq API dengan model yang valid
$payload = [
    'model'    => 'compound-beta',
    'messages' => $messages
];

$ch = curl_init(GROQ_ENDPOINT);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER    => [
        'Authorization: Bearer ' . GROQ_API_KEY,
        'Content-Type: application/json'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
]);
$response   = curl_exec($ch);
$curlErr     = curl_error($ch);
$httpStatus  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErr) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL Error: ' . $curlErr]);
    exit;
}
if ($httpStatus !== 200) {
    http_response_code($httpStatus);
    echo json_encode(['error' => 'API Error: ' . $response]);
    exit;
}

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid API response JSON: ' . json_last_error_msg()]);
    exit;
}
$ai_text = $data['choices'][0]['message']['content']
           ?? 'Maaf, terjadi kesalahan saat memproses.';

// 9) Simpan balasan bot ke DB
$stmt = $conn->prepare(
    "INSERT INTO chat_messages (session_id, user_id, content, sender, timestamp)
     VALUES (?, ?, ?, 'bot', NOW())"
);
$stmt->bind_param('sis', $session_id, $user_id, $ai_text);
$stmt->execute();
$stmt->close();

// 10) Kembalikan JSON ke frontend
echo json_encode([
    'session_id' => $session_id,
    'message'    => $ai_text
]);
