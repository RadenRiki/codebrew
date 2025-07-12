<?php
// fix_cached_explanations.php
// Script untuk memperbaiki format penjelasan yang sudah tersimpan di cache

require_once '../connection.php';

// Fungsi untuk memperbaiki format HTML dalam penjelasan
function fix_html_format($text) {
    // Pola untuk menemukan tag HTML yang hilang bracket-nya
    // Contoh: "h1" menjadi "<h1>", "h6" menjadi "<h6>"
    $patterns = [
        // Heading tags
        '/\b(h[1-6])\b(?![>])/' => '<$1>',
        '/\/(h[1-6])\b(?![>])/' => '</$1>',
        
        // Common HTML tags
        '/\b(p|div|span|br|hr|strong|em|i|b|u)\b(?![>])/' => '<$1>',
        '/\/(p|div|span|strong|em|i|b|u)\b(?![>])/' => '</$1>',
        
        // Self-closing tags
        '/\b(br|hr|img)\b(?![>\/])/' => '<$1>',
        
        // Fix backticks around tags
        '/`<([^`>]+)>`/' => '<$1>',
        '/`([^`<]+)`(?=\s*(hingga|sampai|dan|atau|,))/' => '<$1>',
    ];
    
    $fixed = $text;
    foreach ($patterns as $pattern => $replacement) {
        $fixed = preg_replace($pattern, $replacement, $fixed);
    }
    
    // Escape HTML entities untuk ditampilkan dengan benar
    $fixed = htmlspecialchars($fixed, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    return $fixed;
}

// Update semua penjelasan di cache
$query = "SELECT cache_id, explanation FROM explanation_cache";
$result = $conn->query($query);

$updated = 0;
$errors = 0;

if ($result->num_rows > 0) {
    $stmt_update = $conn->prepare("UPDATE explanation_cache SET explanation = ? WHERE cache_id = ?");
    
    while ($row = $result->fetch_assoc()) {
        $fixed_explanation = fix_html_format($row['explanation']);
        
        if ($fixed_explanation !== $row['explanation']) {
            $stmt_update->bind_param("si", $fixed_explanation, $row['cache_id']);
            if ($stmt_update->execute()) {
                $updated++;
                echo "Updated cache_id: " . $row['cache_id'] . "\n";
            } else {
                $errors++;
                echo "Error updating cache_id: " . $row['cache_id'] . "\n";
            }
        }
    }
    
    $stmt_update->close();
}

echo "\nSummary:\n";
echo "Total records: " . $result->num_rows . "\n";
echo "Updated: $updated\n";
echo "Errors: $errors\n";
echo "Unchanged: " . ($result->num_rows - $updated - $errors) . "\n";

$conn->close();
?>