// groq_api.php

<?php
require_once '../connection.php';
require_once '../vendor/autoload.php'; // Pastikan ini mengarah ke autoload.php Composer

// Memuat file .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../'); // Path ke .env
$dotenv->load();

// Pengaturan API Groq
$groq_api_key = getenv('GROQ_API_KEY');
$groq_endpoint = getenv('GROQ_ENDPOINT');

// Fungsi untuk mengirimkan permintaan ke API Groq
function get_explanation($question_id, $answer_id, $is_correct) {
    global $groq_api_key, $groq_endpoint; // Menggunakan variabel global

    $payload = [
        'model' => 'compound-beta',
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Tolong berikan penjelasan tentang jawaban ' . ($is_correct ? 'yang benar' : 'yang salah') . ' untuk pertanyaan dengan ID ' . $question_id . ' dan jawaban dengan ID ' . $answer_id . '.',
            ],
        ],
    ];

    $ch = curl_init($groq_endpoint);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $groq_api_key,
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);

    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        return 'Error: ' . $curlErr; // Error handling
    } elseif ($httpStatus !== 200) {
        return 'Error: ' . $response; // Error handling
    } else {
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Error: Invalid JSON response'; // Error handling
        } else {
            return $data['choices'][0]['message']['content'];
        }
    }
}

// Contoh penggunaan fungsi get_explanation
$question_id = 1; // Ganti dengan ID pertanyaan yang sesuai
$answer_id = 1; // Ganti dengan ID jawaban yang sesuai
$is_correct = true; // Ganti sesuai dengan jawaban yang benar atau salah

$explanation = get_explanation($question_id, $answer_id, $is_correct);
echo $explanation;
