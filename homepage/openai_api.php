<?php
// openai_api.php - Optimized Version dengan Cache

require_once '../connection.php';

// Load .env file
$envFile = __DIR__ . '/../.env';
$envVars = [];

if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
} else {
    die('Configuration error: .env file not found');
}

// Define constants
define('OPENAI_API_KEY', $envVars['OPENAI_API_KEY'] ?? '');
define('OPENAI_ENDPOINT', 'https://api.openai.com/v1/chat/completions');

// Check cache first - FIXED VERSION
function check_explanation_cache($question_id, $user_answer_id = null) {
    global $conn;
    
    // Handle NULL answer_id properly
    if ($user_answer_id === null || $user_answer_id === 0) {
        $stmt = $conn->prepare("
            SELECT explanation 
            FROM explanation_cache 
            WHERE question_id = ? 
            AND user_answer_id IS NULL
            AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            LIMIT 1
        ");
        $stmt->bind_param("i", $question_id);
    } else {
        $stmt = $conn->prepare("
            SELECT explanation 
            FROM explanation_cache 
            WHERE question_id = ? 
            AND user_answer_id = ?
            AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            LIMIT 1
        ");
        $stmt->bind_param("ii", $question_id, $user_answer_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['explanation'];
    }
    
    $stmt->close();
    return null;
}

// Main function with cache check
function get_explanation_from_groq($question_text, $correct_answer, $user_answer = '', $is_correct = false, $question_id = null, $user_answer_id = null) {
    // Check cache first if question_id provided
    if ($question_id !== null) {
        $cached = check_explanation_cache($question_id, $user_answer_id);
        if ($cached !== null) {
            return $cached;
        }
    }
    
    if (empty(OPENAI_API_KEY)) {
        return 'Error: OpenAI API key tidak ditemukan.';
    }
    
    $prompt = create_prompt($question_text, $correct_answer, $user_answer, $is_correct);
    
    $messages = [
        [
            'role' => 'system',
            'content' => 'Anda adalah asisten pembelajaran programming. Berikan penjelasan singkat dan jelas dalam bahasa Indonesia. 

ATURAN PENTING untuk menyebutkan tag HTML:
- SELALU tulis tag HTML lengkap dengan kurung sudut, contoh: <h1>, </h1>, <p>, <ul>, <li>
- JANGAN tulis tag HTML tanpa kurung sudut
- JANGAN gunakan markdown bold (**) atau italic (*) - gunakan kata-kata untuk penekanan
- Contoh BENAR: "Tag <b> digunakan untuk..."
- Contoh SALAH: "Tag b digunakan untuk..." atau "Tag **b** digunakan untuk..."'
        ],
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ];

    $payload = [
        'model' => 'gpt-4o-mini', // Use stable model
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 150
    ];

    // Retry logic
    $maxRetries = 3;
    $retryDelay = 1; // seconds
    
    for ($i = 0; $i < $maxRetries; $i++) {
        $ch = curl_init(OPENAI_ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . OPENAI_API_KEY,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10, // Reduced timeout
            CURLOPT_CONNECTTIMEOUT => 5
        ]);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpStatus === 200) {
            $data = json_decode($response, true);
            if (isset($data['choices'][0]['message']['content'])) {
                return trim($data['choices'][0]['message']['content']);
            }
        } else if ($httpStatus === 429) {
            // Rate limit - wait and retry
            sleep($retryDelay);
            $retryDelay *= 2;
            continue;
        } else if ($httpStatus === 401) {
            return 'Error: API key tidak valid.';
        } else if (!empty($curlError)) {
            error_log("CURL Error: " . $curlError);
            // Continue to retry
        }
        
        if ($i < $maxRetries - 1) {
            sleep($retryDelay);
        }
    }
    
    // Fallback message jika semua retry gagal
    return generate_fallback_explanation($question_text, $correct_answer, $user_answer, $is_correct);
}

// Fallback explanation generator
function generate_fallback_explanation($question_text, $correct_answer, $user_answer, $is_correct) {
    if ($is_correct) {
        return "Jawaban Anda benar! '$correct_answer' adalah jawaban yang tepat untuk pertanyaan ini.";
    } else {
        $base = "Jawaban yang benar adalah '$correct_answer'.";
        if (!empty($user_answer)) {
            $base .= " Jawaban Anda '$user_answer' kurang tepat.";
        }
        return $base . " Silakan pelajari kembali materi terkait untuk pemahaman yang lebih baik.";
    }
}

// Helper function dengan prompt yang lebih jelas
function create_prompt($question_text, $correct_answer, $user_answer, $is_correct) {
    $format_rules = "FORMAT RULES:
- Tulis tag HTML dengan kurung sudut lengkap: <tag>, bukan 'tag' atau **tag**
- Jangan gunakan markdown formatting (** atau *)
- Contoh: 'Tag <b> digunakan untuk membuat teks tebal'";
    
    if ($is_correct) {
        return "Pertanyaan: $question_text\n" .
               "Jawaban benar: $correct_answer\n" .
               "Status: Benar\n\n" .
               "$format_rules\n\n" .
               "Jelaskan singkat (maks 3 kalimat) mengapa ini jawaban yang benar.";
    } else {
        $prompt = "Pertanyaan: $question_text\n" .
                  "Jawaban benar: $correct_answer\n";
        
        if (!empty($user_answer)) {
            $prompt .= "Jawaban user: $user_answer\n";
        } else {
            $prompt .= "Status: Tidak dijawab\n";
        }
        
        $prompt .= "\n$format_rules\n\n";
        $prompt .= "Jelaskan singkat (maks 3 kalimat) mengapa jawaban yang benar adalah '$correct_answer'.";
        
        return $prompt;
    }
}
?>