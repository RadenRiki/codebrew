<?php
// clean_explanation_cache.php
// Script untuk membersihkan cache penjelasan yang bermasalah

require_once '../connection.php';

// Opsi 1: Hapus semua cache untuk generate ulang
if (isset($_GET['action']) && $_GET['action'] === 'clear_all') {
    $query = "TRUNCATE TABLE explanation_cache";
    if ($conn->query($query)) {
        echo "Semua cache telah dihapus. Penjelasan akan di-generate ulang saat diakses.\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
    exit;
}

// Opsi 2: Hapus cache yang bermasalah (mengandung HTML tags yang tidak di-escape)
$problematic_patterns = [
    '<b>', '</b>', '<strong>', '</strong>',
    '<i>', '</i>', '<em>', '</em>',
    '<ul>', '</ul>', '<ol>', '</ol>', '<li>', '</li>',
    '<p>', '</p>', '<br>', '<blockquote>', '</blockquote>',
    '<cite>', '</cite>', '<code>', '</code>'
];

$where_conditions = [];
foreach ($problematic_patterns as $pattern) {
    $where_conditions[] = "explanation LIKE '%" . $conn->real_escape_string($pattern) . "%'";
}

$query = "SELECT cache_id, question_id, explanation FROM explanation_cache WHERE " . implode(' OR ', $where_conditions);
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " problematic cache entries.\n\n";
    
    // Delete problematic entries
    $delete_query = "DELETE FROM explanation_cache WHERE " . implode(' OR ', $where_conditions);
    
    if ($conn->query($delete_query)) {
        echo "Deleted " . $conn->affected_rows . " problematic cache entries.\n";
        echo "These explanations will be regenerated when accessed.\n";
    } else {
        echo "Error deleting entries: " . $conn->error . "\n";
    }
} else {
    echo "No problematic cache entries found.\n";
}

// Show statistics
$stats_query = "SELECT COUNT(*) as total FROM explanation_cache";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

echo "\nCache Statistics:\n";
echo "Total entries remaining: " . $stats['total'] . "\n";

$conn->close();
?>