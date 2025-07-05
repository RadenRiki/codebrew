<?php
// test_gemini.php - Test koneksi ke Gemini API

// Parse .env manually - fix path
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    // Coba path alternatif
    $envFile = '/Applications/MAMP/htdocs/codebrew/.env';
}

echo "Looking for .env at: $envFile<br>";
echo "File exists: " . (file_exists($envFile) ? 'YES' : 'NO') . "<br><br>";

$apiKey = '';
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    $apiKey = $envVars['GEMINI_API_KEY'] ?? '';
} else {
    echo "<strong>ERROR: .env file not found!</strong><br>";
    echo "Please check the file location.<br><br>";
}

echo "<h3>Testing Gemini API</h3>";
echo "API Key found: " . (!empty($apiKey) ? 'YES' : 'NO') . "<br>";
echo "API Key prefix: " . substr($apiKey, 0, 10) . "...<br><br>";

// Test simple request
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

$payload = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Hello, just testing. Reply with "OK" if you receive this.']
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_VERBOSE => true
]);

echo "Sending request...<br>";
$response = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

echo "HTTP Status: $httpStatus<br>";
echo "CURL Error: " . ($curlErr ?: 'None') . "<br>";
echo "Response Time: " . $curlInfo['total_time'] . " seconds<br><br>";

if ($httpStatus === 200) {
    $data = json_decode($response, true);
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        echo "<strong>Success!</strong> API Response: " . $data['candidates'][0]['content']['parts'][0]['text'] . "<br>";
    } else {
        echo "Unexpected response format:<br>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
    }
} else {
    echo "<strong>Error Response:</strong><br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}
?>