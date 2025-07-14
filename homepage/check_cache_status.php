<?php
// check_cache_status.php
// Script untuk melihat status cache

require_once '../connection.php';

// Get quiz_id from parameter
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

echo "<h2>Cache Status Check</h2>";

// Overall cache statistics
$query = "SELECT COUNT(*) as total_cache, 
          COUNT(DISTINCT question_id) as unique_questions,
          MIN(created_at) as oldest_cache,
          MAX(created_at) as newest_cache
          FROM explanation_cache";
$result = $conn->query($query);
$stats = $result->fetch_assoc();

echo "<h3>Overall Statistics:</h3>";
echo "<ul>";
echo "<li>Total cache entries: " . $stats['total_cache'] . "</li>";
echo "<li>Unique questions cached: " . $stats['unique_questions'] . "</li>";
echo "<li>Oldest cache: " . $stats['oldest_cache'] . "</li>";
echo "<li>Newest cache: " . $stats['newest_cache'] . "</li>";
echo "</ul>";

// Quiz-specific cache status
if ($quiz_id > 0) {
    echo "<h3>Cache Status for Quiz ID: $quiz_id</h3>";
    
    $query = "SELECT 
        q.question_id,
        q.question_text,
        COUNT(DISTINCT ec.user_answer_id) as cached_variations,
        MAX(ec.created_at) as last_cached
    FROM questions q
    LEFT JOIN explanation_cache ec ON q.question_id = ec.question_id
    WHERE q.quiz_id = ?
    GROUP BY q.question_id
    ORDER BY q.question_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr>
            <th>Question ID</th>
            <th>Question (truncated)</th>
            <th>Cached Variations</th>
            <th>Last Cached</th>
          </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['question_id'] . "</td>";
        echo "<td>" . substr(htmlspecialchars($row['question_text']), 0, 50) . "...</td>";
        echo "<td>" . $row['cached_variations'] . "</td>";
        echo "<td>" . ($row['last_cached'] ?: 'Never') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    $stmt->close();
    
    // Show cache details
    echo "<h3>Detailed Cache Entries:</h3>";
    $query = "SELECT 
        ec.question_id,
        ec.user_answer_id,
        LENGTH(ec.explanation) as explanation_length,
        ec.created_at,
        SUBSTRING(ec.explanation, 1, 100) as explanation_preview
    FROM explanation_cache ec
    JOIN questions q ON ec.question_id = q.question_id
    WHERE q.quiz_id = ?
    ORDER BY ec.created_at DESC
    LIMIT 20";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr>
            <th>Question ID</th>
            <th>Answer ID</th>
            <th>Length</th>
            <th>Created</th>
            <th>Preview</th>
          </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['question_id'] . "</td>";
        echo "<td>" . ($row['user_answer_id'] ?: 'NULL') . "</td>";
        echo "<td>" . $row['explanation_length'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . htmlspecialchars($row['explanation_preview']) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
    $stmt->close();
}

// Test specific cache lookup
if (isset($_GET['test_question_id'])) {
    $test_qid = (int)$_GET['test_question_id'];
    $test_aid = isset($_GET['test_answer_id']) ? (int)$_GET['test_answer_id'] : 0;
    
    echo "<h3>Testing Cache Lookup:</h3>";
    echo "<p>Question ID: $test_qid, Answer ID: " . ($test_aid ?: 'NULL') . "</p>";
    
    $stmt = $conn->prepare("
        SELECT explanation, created_at 
        FROM explanation_cache 
        WHERE question_id = ? 
        AND (user_answer_id = ? OR (user_answer_id IS NULL AND ? = 0))
    ");
    $stmt->bind_param("iii", $test_qid, $test_aid, $test_aid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo "<p style='color: green;'>CACHE HIT!</p>";
        echo "<p>Created at: " . $data['created_at'] . "</p>";
        echo "<p>Explanation: " . htmlspecialchars(substr($data['explanation'], 0, 200)) . "...</p>";
    } else {
        echo "<p style='color: red;'>CACHE MISS!</p>";
    }
    $stmt->close();
}

$conn->close();
?>