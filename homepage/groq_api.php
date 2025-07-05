<?php
// groq_api.php - Versi dengan relative path yang benar

// Gunakan relative path - karena groq_api.php ada di folder homepage
// dan .env ada di root project (satu level di atas)
$envFile = __DIR__ . '/../.env';

$envVars = [];
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    error_log('Successfully loaded .env file from: ' . realpath($envFile));
} else {
    error_log('ERROR: .env file not found at: ' . $envFile);
    error_log('Current directory: ' . __DIR__);
    error_log('Looking for file at: ' . realpath($envFile));
    die('Configuration error: .env file not found');
}

// Define constants
define('GEMINI_API_KEY', $envVars['GEMINI_API_KEY'] ?? '');
define('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent');
define('GROQ_API_KEY', $envVars['GROQ_API_KEY'] ?? '');
define('GROQ_ENDPOINT', $envVars['GROQ_ENDPOINT'] ?? 'https://api.groq.com/openai/v1/chat/completions');

// Verify API keys loaded
if (empty(GEMINI_API_KEY)) {
    error_log('WARNING: GEMINI_API_KEY is empty!');
}
if (empty(GROQ_API_KEY)) {
    error_log('WARNING: GROQ_API_KEY is empty!');
}

// Fungsi utama yang akan dipanggil
function get_explanation_from_groq($question_text, $correct_answer, $user_answer = '', $is_correct = false) {
    // Skip Groq, langsung ke Gemini karena Groq selalu gagal
    if (empty(GEMINI_API_KEY)) {
        return 'Error: API key tidak ditemukan. Pastikan file .env sudah benar.';
    }
    
    return call_gemini_api($question_text, $correct_answer, $user_answer, $is_correct);
}

// Fungsi untuk memanggil Gemini API
function call_gemini_api($question_text, $correct_answer, $user_answer = '', $is_correct = false) {
    $prompt = create_prompt($question_text, $correct_answer, $user_answer, $is_correct);

    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 150
        ]
    ];

    $url = GEMINI_ENDPOINT . '?key=' . GEMINI_API_KEY;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpStatus === 200) {
        $data = json_decode($response, true);
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($data['candidates'][0]['content']['parts'][0]['text']);
        }
    } else if ($httpStatus === 403) {
        return 'Error: API key tidak valid atau tidak memiliki akses ke Gemini API.';
    } else if ($httpStatus === 429) {
        return 'Error: Rate limit tercapai. Coba lagi nanti.';
    }
    
    return 'Penjelasan tidak tersedia saat ini.';
}

// Helper function untuk membuat prompt
function create_prompt($question_text, $correct_answer, $user_answer, $is_correct) {
    if ($is_correct) {
        return "Pertanyaan: $question_text\n" .
               "Jawaban benar: $correct_answer\n" .
               "Status: Benar\n\n" .
               "Jelaskan singkat (maks 3 kalimat) mengapa ini jawaban yang benar.";
    } else {
        $prompt = "Pertanyaan: $question_text\n" .
                  "Jawaban benar: $correct_answer\n";
        
        if (!empty($user_answer)) {
            $prompt .= "Jawaban user: $user_answer\n";
        } else {
            $prompt .= "Status: Tidak dijawab\n";
        }
        
        $prompt .= "\nJelaskan singkat (maks 3 kalimat) mengapa jawaban yang benar adalah '$correct_answer'.";
        
        return $prompt;
    }
}
?>