<?php
session_start();
// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../register-login/login.php');
    exit;
}

require_once '../connection.php';
// require_once 'groq_api.php'; // Include file groq_api.php
require_once __DIR__ . '/openai_api.php';

// // Debug - hapus setelah selesai
// error_log('=== START QUIZ DEBUG ===');
// error_log('GEMINI_API_KEY defined: ' . (defined('GEMINI_API_KEY') ? 'YES' : 'NO'));
// error_log('GEMINI_API_KEY empty: ' . (empty(GEMINI_API_KEY) ? 'YES' : 'NO'));

$user_id = $_SESSION['user_id'];

// Ambil data user untuk cek status premium
$stmt_user = $conn->prepare("SELECT username, is_premium, xp_total FROM user WHERE user_id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$is_premium_user = $user_data['is_premium'] ?? 0;
$current_xp = $user_data['xp_total'] ?? 0;
$stmt_user->close();

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if ($quiz_id === 0) {
    header('Location: kuis.php');
    exit;
}

// Ambil detail kuis
$stmt_quiz = $conn->prepare("SELECT language, topic, is_premium FROM quizzes WHERE quiz_id = ?");
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();
$quiz_details = $result_quiz->fetch_assoc();
$stmt_quiz->close();

// Validasi kuis ditemukan
if (!$quiz_details) {
    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Kuis tidak ditemukan.'];
    header('Location: kuis.php');
    exit;
}

// Validasi akses premium
if ($quiz_details['is_premium'] && !$is_premium_user) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Kuis ini hanya tersedia untuk pengguna PREMIUM. Silakan upgrade akun Anda!'];
    header('Location: kuis.php');
    exit;
}

$questions = [];
$quiz_completed = false;
$score_summary = [];
$user_answers = [];
$correct_answers_map = [];
$explanations = []; // Array untuk menyimpan penjelasan

// Proses submit kuis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $quiz_completed = true;
    $correct_count = 0;
    $wrong_count = 0;
    $unanswered_count = 0;
    $total_points_earned = 0;
    $points_per_question = 10;

    // Ambil semua pertanyaan dan jawaban benar dari database untuk kuis ini
    $stmt_all_questions = $conn->prepare("
        SELECT q.question_id, q.question_text, a.answer_id, a.answer_text, a.is_correct 
        FROM questions q
        JOIN answers a ON q.question_id = a.question_id
        WHERE q.quiz_id = ?
        ORDER BY q.question_id, a.answer_id
    ");
    $stmt_all_questions->bind_param("i", $quiz_id);
    $stmt_all_questions->execute();
    $result_all_questions = $stmt_all_questions->get_result();

    $questions_with_answers = [];
    while ($row = $result_all_questions->fetch_assoc()) {
        $questions_with_answers[$row['question_id']]['question_text'] = $row['question_text'];
        $questions_with_answers[$row['question_id']]['answers'][] = [
            'answer_id' => $row['answer_id'],
            'answer_text' => $row['answer_text'],
            'is_correct' => $row['is_correct']
        ];
        if ($row['is_correct']) {
            $correct_answers_map[$row['question_id']] = $row['answer_id'];
        }
    }
    $stmt_all_questions->close();

    // Proses jawaban user TANPA generate penjelasan
    foreach ($questions_with_answers as $q_id => $q_data) {
        $user_selected_answer_id = isset($_POST['question_' . $q_id]) ? (int)$_POST['question_' . $q_id] : null;
        $user_answers[$q_id] = $user_selected_answer_id;

        $is_correct_answer = false;
        if ($user_selected_answer_id !== null) {
            if (isset($correct_answers_map[$q_id]) && $user_selected_answer_id === $correct_answers_map[$q_id]) {
                $is_correct_answer = true;
            }
        }

        if ($is_correct_answer) {
            $correct_count++;
            $total_points_earned += $points_per_question;
        } elseif ($user_selected_answer_id === null) {
            $unanswered_count++;
        } else {
            $wrong_count++;
        }
    }

    $score_summary = [
        'correct' => $correct_count,
        'wrong' => $wrong_count,
        'unanswered' => $unanswered_count,
        'total_questions' => count($questions_with_answers),
        'points' => $total_points_earned
    ];

    // Simpan nilai tertinggi
    $stmt_get_high_score = $conn->prepare("SELECT score FROM user_quiz_scores WHERE user_id = ? AND quiz_id = ?");
    $stmt_get_high_score->bind_param("ii", $user_id, $quiz_id);
    $stmt_get_high_score->execute();
    $result_get_high_score = $stmt_get_high_score->get_result();
    $previous_high_score = 0;
    if ($result_get_high_score->num_rows > 0) {
        $previous_high_score = $result_get_high_score->fetch_assoc()['score'];
    }
    $stmt_get_high_score->close();

    if ($total_points_earned > $previous_high_score) {
        $stmt_update_score = $conn->prepare("
            INSERT INTO user_quiz_scores (user_id, quiz_id, score, completed_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = VALUES(completed_at)
        ");
        $stmt_update_score->bind_param("iii", $user_id, $quiz_id, $total_points_earned);
        $stmt_update_score->execute();
        $stmt_update_score->close();
    }

    // Simpan setiap percobaan kuis ke tabel quiz_attempts
    $stmt_save_attempt = $conn->prepare("
        INSERT INTO quiz_attempts (user_id, quiz_id, score, total_questions, correct_answers)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt_save_attempt->bind_param(
        "iiiii",
        $user_id,
        $quiz_id,
        $total_points_earned,
        $score_summary['total_questions'],
        $score_summary['correct']
    );
    $stmt_save_attempt->execute();
    $stmt_save_attempt->close();

    // Update XP user
    $new_xp = $current_xp + $total_points_earned;
    $stmt_update_xp = $conn->prepare("UPDATE user SET xp_total = ? WHERE user_id = ?");
    $stmt_update_xp->bind_param("ii", $new_xp, $user_id);
    $stmt_update_xp->execute();
    $stmt_update_xp->close();

    // Set questions untuk review
    $questions = $questions_with_answers;

} else {
    // Ambil pertanyaan untuk kuis (jika belum disubmit)
    $stmt_questions = $conn->prepare("
        SELECT q.question_id, q.question_text, a.answer_id, a.answer_text, a.is_correct 
        FROM questions q
        JOIN answers a ON q.question_id = a.question_id
        WHERE q.quiz_id = ?
        ORDER BY RAND()
    ");
    $stmt_questions->bind_param("i", $quiz_id);
    $stmt_questions->execute();
    $result_questions = $stmt_questions->get_result();

    while ($row = $result_questions->fetch_assoc()) {
        $questions[$row['question_id']]['question_text'] = $row['question_text'];
        $questions[$row['question_id']]['answers'][] = [
            'answer_id' => $row['answer_id'],
            'answer_text' => $row['answer_text'],
            'is_correct' => $row['is_correct']
        ];
        if ($row['is_correct']) {
            $correct_answers_map[$row['question_id']] = $row['answer_id'];
        }
    }
    $stmt_questions->close();

    if (empty($questions)) {
        $_SESSION['alert'] = ['type' => 'info', 'message' => 'Belum ada pertanyaan untuk kuis ini.'];
        header('Location: kuis.php');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kuis <?php echo htmlspecialchars($quiz_details['topic']); ?> - CodeBrew</title>
    <link rel = "icon" type = "image/png" href = "../assets/LogoIcon.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="index.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(180deg, var(--darker), var(--dark));
            color: var(--light);
            min-height: 100vh;
            padding-top: 100px;
        }

/* Tambahkan CSS ini di start_quiz.php dalam tag <style> */

.explanation p {
    line-height: 1.6;
    word-wrap: break-word;
}

/* Style untuk tag HTML dalam penjelasan */
.explanation code {
    background-color: rgba(163, 103, 220, 0.2);
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.9em;
    color: #ff84e8;
    border: 1px solid rgba(163, 103, 220, 0.3);
    white-space: nowrap;
}

/* Cache indicator */
.cache-indicator {
    display: inline-block;
    margin-left: 8px;
    font-size: 0.8em;
    opacity: 0.7;
}

.cache-indicator i {
    margin-right: 3px;
}

.cache-indicator.new {
    color: #ffc107;
}

.cache-indicator:not(.new) {
    color: #28a745;
}

.explanation small.text-muted {
    display: block;
    margin-top: 5px;
    font-size: 0.75em;
    opacity: 0.6;
}

/* Animasi loading yang lebih smooth */
.explanation .fa-spinner {
    color: var(--primary-light);
    margin-right: 8px;
}

/* Error state */
.explanation .text-warning,
.explanation .text-danger {
    font-style: italic;
    opacity: 0.8;
}

/* Reset any unintended list styling */
.explanation ul,
.explanation ol,
.explanation li {
    display: inline;
    list-style: none;
    padding: 0;
    margin: 0;
}

        .quiz-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(26, 11, 46, 0.7);
            border-radius: 15px;
            border: 1px solid rgba(93, 46, 142, 0.5);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        .quiz-header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(93, 46, 142, 0.3);
            padding-bottom: 1.5rem;
        }

        .quiz-header h1 {
            font-size: 2.5rem;
            color: var(--light);
            margin-bottom: 0.5rem;
        }

        .quiz-header p {
            font-size: 1.1rem;
            color: var(--light-purple);
        }

        .question-card {
            background: rgba(93, 46, 142, 0.2);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(93, 46, 142, 0.3);
        }

        .question-card h4 {
            font-size: 1.2rem;
            color: var(--light);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .options-list {
            list-style: none;
            padding: 0;
        }

        .options-list li {
            margin-bottom: 0.8rem;
        }

        .options-list label {
            display: block;
            background: rgba(255, 255, 255, 0.08);
            padding: 12px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s;
            border: 1px solid transparent;
            color: var(--light-purple);
        }

        .options-list label:hover {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-light);
        }

        .options-list input[type="radio"] {
            margin-right: 10px;
            accent-color: var(--primary-light);
        }

        .quiz-actions {
            text-align: center;
            margin-top: 2rem;
        }

        .submit-quiz-btn {
            background: var(--gradient);
            color: var(--light);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 2rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .submit-quiz-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(163, 103, 220, 0.3);
        }

        .quiz-summary {
            background: rgba(93, 46, 142, 0.3);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
            border: 1px solid var(--primary-light);
        }

        .quiz-summary h3 {
            font-size: 1.8rem;
            color: var(--light);
            margin-bottom: 1rem;
        }

        .summary-stats {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-item {
            flex: 1;
            min-width: 120px;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            color: var(--light);
        }

        .stat-item .value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.3rem;
        }

        .stat-item .label {
            font-size: 0.9rem;
            color: var(--light-purple);
        }

        .stat-item.correct .value { color: #28a745; }
        .stat-item.wrong .value { color: #dc3545; }
        .stat-item.unanswered .value { color: #ffc107; }
        .stat-item.points .value { color: var(--accent); }

        .review-section {
            margin-top: 2rem;
            border-top: 1px solid rgba(93, 46, 142, 0.3);
            padding-top: 2rem;
        }

        .review-section h3 {
            font-size: 1.8rem;
            color: var(--light);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .review-question-card {
            background: rgba(93, 46, 142, 0.2);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(93, 46, 142, 0.3);
        }

        .review-question-card.correct { border-color: #28a745; }
        .review-question-card.wrong { border-color: #dc3545; }
        .review-question-card.unanswered { border-color: #ffc107; }

        .review-question-card h4 {
            font-size: 1.2rem;
            color: var(--light);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .review-options-list {
            list-style: none;
            padding: 0;
        }

        .review-options-list li {
            margin-bottom: 0.5rem;
            padding: 8px 12px;
            border-radius: 6px;
            color: var(--light-purple);
            position: relative;
        }

        .review-options-list li.correct-answer {
            background-color: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: var(--light);
        }

        .review-options-list li.user-selected-wrong {
            background-color: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: var(--light);
        }

        .review-options-list li.user-selected-correct {
            background-color: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: var(--light);
        }

        .review-options-list li .icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
        }

        .review-options-list li.correct-answer .icon { color: #28a745; }
        .review-options-list li.user-selected-wrong .icon { color: #dc3545; }
        .review-options-list li.user-selected-correct .icon { color: #28a745; }

        .explanation {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border-left: 4px solid var(--accent);
            color: var(--light-purple);
            font-size: 0.95rem;
        }

        .explanation strong {
            color: var(--light);
        }

        .play-again-btn {
            background: var(--primary-light);
            color: var(--light);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 2rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 2rem;
        }

        .play-again-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(163, 103, 220, 0.3);
        }

        .back-btn {
            @apply bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded;
        }
    </style>
</head>
<body>
    <!-- Background stars -->
    <div class="stars" id="stars"></div>

    <!-- Static star assets -->
    <img class="star-assets star1" src="../assets/—Pngtree—white light star twinkle light_7487663 1.png" alt="" />
    <img class="star-assets star2" src="../assets/—Pngtree—white light star twinkle light_7487663 2.png" alt="" />
    <img class="star-assets star3" src="../assets/—Pngtree—white light star twinkle light_7487663 3.png" alt="" />
    <img class="star-assets star4" src="../assets/—Pngtree—white light star twinkle light_7487663 4.png" alt="" />
    <img class="star-assets star5" src="../assets/—Pngtree—white light star twinkle light_7487663 5.png" alt="" />
    <img class="star-assets star6" src="../assets/—Pngtree—white light star twinkle light_7487663 6.png" alt="" />
    <img class="star-assets star7" src="../assets/—Pngtree—white light star twinkle light_7487663 7.png" alt="" />


    <main class="quiz-container">
        <a href="#" onclick="history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="quiz-header">
            <h1><b><?php echo htmlspecialchars($quiz_details['topic']); ?></b></h1>
            <p>Bahasa: <?php echo htmlspecialchars($quiz_details['language']); ?> 
            <?php if ($quiz_details['is_premium']): ?>
                <span class="badge-premium-quiz">PREMIUM</span>
            <?php endif; ?>
            </p>
        </div>

        <?php if ($quiz_completed): ?>
            <!-- Ringkasan Hasil Kuis -->
            <div class="quiz-summary">
                <h3>Hasil Kuis Anda!</h3>
                <div class="summary-stats">
                    <div class="stat-item correct">
                        <div class="value"><?php echo $score_summary['correct']; ?></div>
                        <div class="label">Benar</div>
                    </div>
                    <div class="stat-item wrong">
                        <div class="value"><?php echo $score_summary['wrong']; ?></div>
                        <div class="label">Salah</div>
                    </div>
                    <div class="stat-item unanswered">
                        <div class="value"><?php echo $score_summary['unanswered']; ?></div>
                        <div class="label">Tidak Dijawab</div>
                    </div>
                    <div class="stat-item points">
                        <div class="value"><?php echo $score_summary['points']; ?></div>
                        <div class="label">Total Poin</div>
                    </div>
                </div>
                <p class="text-center text-white">Nilai tertinggi Anda untuk kuis ini telah diperbarui!</p>
                <p class="text-center text-white">XP Anda saat ini: <?php echo $new_xp; ?></p>
                <a href="kuis.php" class="play-again-btn">Pilih Kuis Lain</a>
            </div>

            <!-- Review Soal -->
<div class="review-section">
    <h3>Review Jawaban Anda</h3>
    <?php foreach ($questions as $q_id => $q_data): 
        $user_ans_id = $user_answers[$q_id] ?? null;
        $correct_ans_id = $correct_answers_map[$q_id];
        
        $card_class = 'unanswered';
        if ($user_ans_id !== null) {
            $card_class = ($user_ans_id === $correct_ans_id) ? 'correct' : 'wrong';
        }
    ?>
        <div class="review-question-card <?php echo $card_class; ?>">
            <h4><?php echo htmlspecialchars($q_data['question_text']); ?></h4>
            <ul class="review-options-list">
                <?php foreach ($q_data['answers'] as $answer): 
                    $li_class = '';
                    $icon = '';
                    if ($answer['answer_id'] === $correct_ans_id) {
                        $li_class = 'correct-answer';
                        $icon = '<i class="fas fa-check icon"></i>';
                    }
                    if ($user_ans_id !== null && $answer['answer_id'] === $user_ans_id) {
                        if ($user_ans_id === $correct_ans_id) {
                            $li_class = 'user-selected-correct';
                            $icon = '<i class="fas fa-check icon"></i>';
                        } else {
                            $li_class = 'user-selected-wrong';
                            $icon = '<i class="fas fa-times icon"></i>';
                        }
                    }
                ?>
                    <li class="<?php echo $li_class; ?>">
                        <?php echo htmlspecialchars($answer['answer_text']); ?>
                        <?php echo $icon; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Container untuk penjelasan dengan data attributes -->
            <div class="explanation" 
                 data-question-id="<?php echo $q_id; ?>"
                 data-quiz-id="<?php echo $quiz_id; ?>"
                 data-user-answer-id="<?php echo $user_ans_id; ?>">
                <strong>Penjelasan:</strong>
                <p><i class="fas fa-spinner fa-spin"></i> Memuat penjelasan...</p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
        <?php else: ?>
            <!-- Form Kuis -->
            <form method="POST" action="start_quiz.php?quiz_id=<?php echo $quiz_id; ?>">
                <?php $q_num = 1; ?>
                <?php foreach ($questions as $q_id => $q_data): ?>
                    <div class="question-card">
                        <h4><?php echo $q_num++; ?>. <?php echo htmlspecialchars($q_data['question_text']); ?></h4>
                        <ul class="options-list">
                            <?php 
                            // Acak urutan jawaban untuk setiap pertanyaan
                            shuffle($q_data['answers']); 
                            foreach ($q_data['answers'] as $answer): 
                            ?>
                                <li>
                                    <label>
                                        <input type="radio" name="question_<?php echo $q_id; ?>" value="<?php echo $answer['answer_id']; ?>">
                                        <?php echo htmlspecialchars($answer['answer_text']); ?>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>

                <div class="quiz-actions">
                    <button type="submit" name="submit_quiz" class="submit-quiz-btn">Akhiri Kuis</button>
                </div>
            </form>
        <?php endif; ?>
    </main>

    <!-- JavaScript for Functionality -->
    <script>
// Tambahkan di bagian JavaScript start_quiz.php setelah quiz selesai

// Fungsi untuk load penjelasan secara asynchronous dengan prioritas
function loadExplanations() {
    const explanationElements = document.querySelectorAll('.explanation');
    const loadQueue = [];
    
    // Collect all explanation requests
    explanationElements.forEach((element) => {
        const questionId = element.dataset.questionId;
        const userAnswerId = element.dataset.userAnswerId || null;
        const quizId = element.dataset.quizId;
        
        loadQueue.push({
            element,
            questionId,
            userAnswerId,
            quizId
        });
    });
    
    // Load explanations in parallel with limited concurrency
    const maxConcurrent = 3; // Load 3 at a time
    let currentIndex = 0;
    
    function loadNext() {
        if (currentIndex >= loadQueue.length) return;
        
        const item = loadQueue[currentIndex];
        currentIndex++;
        
        // Show loading spinner
        item.element.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> Memuat penjelasan...</p>';
        
        fetchExplanation(item.questionId, item.quizId, item.userAnswerId, item.element)
            .finally(() => {
                // Load next item regardless of success/failure
                loadNext();
            });
    }
    
    // Start loading with max concurrent requests
    for (let i = 0; i < Math.min(maxConcurrent, loadQueue.length); i++) {
        loadNext();
    }
}

// Fungsi untuk fetch penjelasan dari server
async function fetchExplanation(questionId, quizId, userAnswerId, element) {
    const startTime = Date.now();
    
    try {
        const formData = new FormData();
        formData.append('question_id', questionId);
        formData.append('quiz_id', quizId);
        if (userAnswerId) {
            formData.append('user_answer_id', userAnswerId);
        }
        
        const response = await fetch('get_explanation_ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        const loadTime = Date.now() - startTime;
        
        if (data.success) {
            // Process explanation
            let processedExplanation = data.explanation;
            
            // Detect and format escaped HTML tags with <code>
            processedExplanation = processedExplanation.replace(/&lt;(\/?[^&]+?)&gt;/g, '<code>&lt;$1&gt;</code>');
            
            // Add cache indicator
            const cacheIndicator = data.from_cache 
                ? '<span class="cache-indicator" title="Dari cache"><i class="fas fa-bolt"></i></span>' 
                : '<span class="cache-indicator new" title="Baru di-generate"><i class="fas fa-magic"></i></span>';
            
            element.innerHTML = `
                <strong>Penjelasan:</strong> ${cacheIndicator}
                <p>${processedExplanation}</p>
                ${data.from_cache ? `<small class="text-muted"></small>` : ''}
            `;
        } else {
            element.innerHTML = `
                <strong>Penjelasan:</strong>
                <p class="text-warning">Penjelasan tidak dapat dimuat. ${data.message || ''}</p>
            `;
        }
    } catch (error) {
        console.error('Error fetching explanation:', error);
        element.innerHTML = `
            <strong>Penjelasan:</strong>
            <p class="text-danger">Terjadi kesalahan saat memuat penjelasan.</p>
        `;
    }
}

// Auto-load explanations saat halaman hasil quiz ditampilkan
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($quiz_completed): ?>
        // Load penjelasan setelah halaman selesai render
        setTimeout(loadExplanations, 500); // Reduced delay
    <?php endif; ?>
});
        document.addEventListener('DOMContentLoaded', function() {
            // === Profile dropdown ===
            const profileBtn = document.getElementById('profileBtn');
            const profileDropdown = document.getElementById('profileDropdown');
            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener('click', e => {
                    profileDropdown.classList.toggle('show');
                    e.stopPropagation();
                });
                document.addEventListener('click', e => {
                    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.remove('show');
                    }
                });
            }

            // === Stars animation ===
            createStars();

            // === Fungsi untuk membuat bintang animasi ===
            function createStars() {
                const container = document.getElementById('stars');
                const colors = ['#a367dc', '#ff84e8', '#6d42e6', '#ffffff'];
                for (let i = 0; i < 50; i++) {
                    const star = document.createElement('div');
                    star.classList.add('star');
                    const x = Math.random() * 100,
                        y = Math.random() * 100;
                    const size = Math.random() * 3 + 1;
                    const color = colors[Math.floor(Math.random() * colors.length)];
                    const dur = Math.random() * 4 + 1,
                        delay = Math.random() * 5;
                    Object.assign(star.style, {
                        left: `${x}%`,
                        top: `${y}%`,
                        width: `${size}px`,
                        height: `${size}px`,
                        backgroundColor: color,
                        boxShadow: `0 0 ${size*2}px ${color}`,
                        animationDuration: `${dur}s`,
                        animationDelay: `${delay}s`
                    });
                    container.appendChild(star);
                }
            }
        });

        // Logout confirmation
        document.querySelectorAll('.logout-item').forEach(function(element) {
            element.addEventListener('click', function(event) {
                const yakin = confirm("Apakah Anda yakin ingin logout?");
                if (!yakin) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>