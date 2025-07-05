<?php
// openai_api.php - Simplified Version untuk GPT-4.1 Nano

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

// Main function
function get_explanation_from_groq($question_text, $correct_answer, $user_answer = '', $is_correct = false) {
    if (empty(OPENAI_API_KEY)) {
        return 'Error: OpenAI API key tidak ditemukan.';
    }
    
    $prompt = create_prompt($question_text, $correct_answer, $user_answer, $is_correct);
    
    $messages = [
        [
            'role' => 'system',
            'content' => 'Anda adalah asisten pembelajaran programming. Berikan penjelasan singkat dan jelas dalam bahasa Indonesia.'
        ],
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ];

    $payload = [
        'model' => 'gpt-4.1-nano', // Nama model yang benar
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 150
    ];

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
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpStatus === 200) {
        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }
    } else if ($httpStatus === 401) {
        return 'Error: API key tidak valid.';
    } else if ($httpStatus === 429) {
        return 'Error: Rate limit tercapai.';
    } else if ($httpStatus === 400) {
        // Jika model tidak ditemukan, coba gpt-4o-mini
        return call_openai_fallback($messages);
    }
    
    return 'Penjelasan tidak tersedia.';
}

// Fallback function
function call_openai_fallback($messages) {
    $payload = [
        'model' => 'gpt-4o-mini', // Fallback model
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 150
    ];

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
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpStatus === 200) {
        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }
    }
    
    return 'Penjelasan tidak tersedia.';
}

// Helper function
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