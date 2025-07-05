<?php
// test_env.php - Letakkan di folder homepage untuk test

echo "<h3>Testing Environment Variables</h3>";

// Method 1: Direct file reading
$envFile = __DIR__ . '/../.env';
echo "1. Checking .env file:<br>";
echo "File path: $envFile<br>";
echo "File exists: " . (file_exists($envFile) ? 'YES' : 'NO') . "<br>";

if (file_exists($envFile)) {
    echo "File contents:<br>";
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'API_KEY') !== false) {
            // Sensor API key untuk keamanan
            $parts = explode('=', $line, 2);
            if (count($parts) == 2) {
                echo $parts[0] . "=" . substr($parts[1], 0, 10) . "...<br>";
            }
        }
    }
}

echo "<br>";

// Method 2: Using phpdotenv
echo "2. Testing phpdotenv:<br>";
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
        echo "phpdotenv loaded successfully<br>";
        
        // Test getenv
        echo "GEMINI_API_KEY (getenv): " . (getenv('GEMINI_API_KEY') ? 'SET (' . substr(getenv('GEMINI_API_KEY'), 0, 10) . '...)' : 'NOT SET') . "<br>";
        echo "GROQ_API_KEY (getenv): " . (getenv('GROQ_API_KEY') ? 'SET (' . substr(getenv('GROQ_API_KEY'), 0, 10) . '...)' : 'NOT SET') . "<br>";
        
        // Test $_ENV
        echo "GEMINI_API_KEY (\$_ENV): " . (isset($_ENV['GEMINI_API_KEY']) ? 'SET' : 'NOT SET') . "<br>";
        echo "GROQ_API_KEY (\$_ENV): " . (isset($_ENV['GROQ_API_KEY']) ? 'SET' : 'NOT SET') . "<br>";
        
    } catch (Exception $e) {
        echo "Error loading .env: " . $e->getMessage() . "<br>";
    }
} else {
    echo "Vendor autoload not found<br>";
}

echo "<br>";

// Method 3: Manual parsing (fallback)
echo "3. Manual parsing test:<br>";
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    echo "GEMINI_API_KEY: " . (isset($envVars['GEMINI_API_KEY']) ? 'FOUND' : 'NOT FOUND') . "<br>";
    echo "GROQ_API_KEY: " . (isset($envVars['GROQ_API_KEY']) ? 'FOUND' : 'NOT FOUND') . "<br>";
}

echo "<br>";
echo "4. PHP Error Log Location: " . ini_get('error_log') . "<br>";
?>