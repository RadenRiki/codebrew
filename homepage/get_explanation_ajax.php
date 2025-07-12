<?php
// get_explanation_ajax.php
session_start();
require_once '../connection.php';
require_once __DIR__ . '/openai_api.php';

header('Content-Type: application/json');

// Validasi user login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Validasi input
if (!isset($_POST['question_id']) || !isset($_POST['quiz_id'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$question_id = (int)$_POST['question_id'];
$quiz_id = (int)$_POST['quiz_id'];
$user_answer_id = isset($_POST['user_answer_id']) ? (int)$_POST['user_answer_id'] : null;

try {
    // Ambil data pertanyaan dan jawaban
    $stmt = $conn->prepare("
        SELECT q.question_text, a.answer_id, a.answer_text, a.is_correct 
        FROM questions q
        JOIN answers a ON q.question_id = a.question_id
        WHERE q.question_id = ? AND q.quiz_id = ?
        ORDER BY a.answer_id
    ");
    $stmt->bind_param("ii", $question_id, $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $question_text = '';
    $correct_answer_text = '';
    $user_answer_text = '';
    $correct_answer_id = null;
    
    while ($row = $result->fetch_assoc()) {
        $question_text = $row['question_text'];
        if ($row['is_correct']) {
            $correct_answer_text = $row['answer_text'];
            $correct_answer_id = $row['answer_id'];
        }
        if ($user_answer_id && $row['answer_id'] == $user_answer_id) {
            $user_answer_text = $row['answer_text'];
        }
    }
    $stmt->close();
    
    // Tentukan apakah jawaban benar
    $is_correct = ($user_answer_id && $user_answer_id == $correct_answer_id);
    
    // Dapatkan penjelasan dari OpenAI
    $explanation = get_explanation_from_groq(
        $question_text,
        $correct_answer_text,
        $user_answer_text,
        $is_correct
    );
    
    // Proses penjelasan untuk format yang konsisten
    // Escape ALL HTML tags terlebih dahulu
    $explanation = htmlspecialchars($explanation, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Kembalikan formatting yang diinginkan (newline menjadi <br>)
    $explanation = nl2br($explanation);
    
    // Cache penjelasan ke database untuk reuse
    $stmt_cache = $conn->prepare("
        INSERT INTO explanation_cache (question_id, user_answer_id, explanation, created_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE explanation = VALUES(explanation), created_at = NOW()
    ");
    $stmt_cache->bind_param("iis", $question_id, $user_answer_id, $explanation);
    $stmt_cache->execute();
    $stmt_cache->close();
    
    echo json_encode([
        'success' => true,
        'explanation' => $explanation,
        'question_id' => $question_id
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_explanation_ajax.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'Failed to get explanation',
        'message' => 'Terjadi kesalahan saat mengambil penjelasan'
    ]);
}
?>