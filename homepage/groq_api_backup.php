<?php
// groq_api.php

// Include config yang sama dengan chatbot
require_once __DIR__ . '/config.php'; // Asumsi config.php ada di folder yang sama

// Fungsi untuk mendapatkan penjelasan dari Groq API
function get_explanation_from_groq($question_text, $correct_answer, $user_answer = '', $is_correct = false) {
    // Siapkan prompt yang lebih kontekstual
    if ($is_correct) {
        $prompt = "Untuk pertanyaan: \"$question_text\"\n";
        $prompt .= "Jawaban yang benar adalah: \"$correct_answer\"\n";
        $prompt .= "Pengguna menjawab dengan benar.\n\n";
        $prompt .= "Berikan penjelasan singkat (maksimal 3 kalimat) mengapa jawaban tersebut benar dalam bahasa Indonesia.";
    } else {
        $prompt = "Untuk pertanyaan: \"$question_text\"\n";
        $prompt .= "Jawaban yang benar adalah: \"$correct_answer\"\n";
        if (!empty($user_answer)) {
            $prompt .= "Pengguna menjawab: \"$user_answer\"\n\n";
            $prompt .= "Berikan penjelasan singkat (maksimal 3 kalimat) mengapa jawaban pengguna salah dan mengapa jawaban yang benar adalah \"$correct_answer\" dalam bahasa Indonesia.";
        } else {
            $prompt .= "Pengguna tidak menjawab pertanyaan ini.\n\n";
            $prompt .= "Berikan penjelasan singkat (maksimal 3 kalimat) mengapa jawaban yang benar adalah \"$correct_answer\" dalam bahasa Indonesia.";
        }
    }

    // System prompt untuk pembelajaran coding
    $systemPrompt = [
        'role' => 'system',
        'content' => 'Kamu adalah CodeBrew Assistant, AI khusus untuk membantu pembelajaran coding. ' .
                     'Berikan penjelasan yang jelas, ringkas, dan mudah dipahami dalam bahasa Indonesia.'
    ];

    $messages = [
        $systemPrompt,
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ];

    $payload = [
        'model' => 'compound-beta', // Gunakan model yang sama dengan chatbot
        'messages' => $messages
    ];

    $ch = curl_init(GROQ_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . GROQ_API_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_SSL_VERIFYPEER => false, // Untuk development
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Debug logging
    if ($curlErr) {
        error_log('Groq API CURL Error: ' . $curlErr);
        return 'Maaf, tidak dapat memuat penjelasan saat ini. Error: ' . $curlErr;
    }
    
    if ($httpStatus !== 200) {
        error_log('Groq API HTTP Error ' . $httpStatus . ': ' . $response);
        
        // Parse error response jika ada
        $errorData = json_decode($response, true);
        $errorMsg = $errorData['error']['message'] ?? 'Unknown error';
        
        return 'Maaf, tidak dapat memuat penjelasan saat ini. Error: ' . $errorMsg;
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Groq API JSON Error: ' . json_last_error_msg());
        return 'Maaf, tidak dapat memuat penjelasan saat ini. JSON Error.';
    }

    // Ambil response dari API
    $explanation = $data['choices'][0]['message']['content'] ?? 'Penjelasan tidak tersedia.';
    
    // Clean up response
    $explanation = trim($explanation);
    
    return $explanation;
}

// Fungsi lama untuk kompatibilitas
function get_explanation($question_id, $answer_id, $is_correct) {
    return 'Penjelasan untuk jawaban ini akan segera tersedia melalui AI.';
}
?>