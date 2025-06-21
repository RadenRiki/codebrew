<?php
// homepage/config.php

/**
 * Parse file .env sederhana: KEY=VALUE
 */
function loadEnv(string $path): array {
  if (!file_exists($path)) return [];
  $lines = file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
  $env = [];
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    [$key, $val] = array_map('trim', explode('=', $line, 2));
    $env[$key] = $val;
  }
  return $env;
}

// Muat .env dari root (satu level di atas)
$env = loadEnv(__DIR__ . '/../.env');

// Database
define('DB_SERVER',   $env['DB_SERVER']   ?? 'localhost');
define('DB_USERNAME', $env['DB_USERNAME'] ?? '');
define('DB_PASSWORD', $env['DB_PASSWORD'] ?? '');
define('DB_NAME',     $env['DB_NAME']     ?? '');

// Groq AI
define('GROQ_API_KEY',  $env['GROQ_API_KEY']  ?? '');
define('GROQ_ENDPOINT', $env['GROQ_ENDPOINT'] ?? '');
