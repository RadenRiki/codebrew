<?php
// get_explanation_ajax.php - Version dengan cache yang diperbaiki
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
    // CEK CACHE DULU
    $cache_key_answer_id = $user_answer_id ?? 0; // Use 0 for null answers
    
    $stmt_cache = $conn->prepare("
        SELECT explanation, created_at 
        FROM explanation_cache 
        WHERE question_id = ? 
        AND (user_answer_id = ? OR (user_answer_id IS NULL AND ? = 0))
        AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt_cache->bind_param("iii", $question_id, $cache_key_answer_id, $cache_key_answer_id);
    $stmt_cache->execute();
    $cache_result = $stmt_cache->get_result();
    
    if ($cache_result->num_rows > 0) {
        // CACHE HIT!
        $cache_data = $cache_result->fetch_assoc();
        $stmt_cache->close();
        
        // Process cached explanation
        $explanation = htmlspecialchars($cache_data['explanation'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $explanation = nl2br($explanation);
        
        echo json_encode([
            'success' => true,
            'explanation' => $explanation,
            'question_id' => $question_id,
            'from_cache' => true,
            'cache_time' => $cache_data['created_at']
        ]);
        exit;
    }
    $stmt_cache->close();
    
    // CACHE MISS - Generate new explanation
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
    $explanation_raw = get_explanation_from_groq(
        $question_text,
        $correct_answer_text,
        $user_answer_text,
        $is_correct
    );
    
    // Simpan ke cache SEBELUM di-escape
    $stmt_save_cache = $conn->prepare("
        INSERT INTO explanation_cache (question_id, user_answer_id, explanation, created_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE explanation = VALUES(explanation), created_at = NOW()
    ");
    $save_answer_id = $user_answer_id ?: null; // Convert 0 to NULL for database
    $stmt_save_cache->bind_param("iis", $question_id, $save_answer_id, $explanation_raw);
    $stmt_save_cache->execute();
    $stmt_save_cache->close();
    
    // Process untuk response
    $explanation = htmlspecialchars($explanation_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $explanation = nl2br($explanation);
    
    echo json_encode([
        'success' => true,
        'explanation' => $explanation,
        'question_id' => $question_id,
        'from_cache' => false
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_explanation_ajax.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'Failed to get explanation',
        'message' => 'Terjadi kesalahan saat mengambil penjelasan',
        'debug' => $e->getMessage()
    ]);
}
?>