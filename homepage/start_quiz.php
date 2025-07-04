<?php
session_start();
// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../register-login/login.php');
    exit;
}

require_once '../connection.php';

$user_id = $_SESSION['user_id'];

// Ambil data user untuk cek status premium
// Ambil data user untuk cek status premium
$stmt_user = $conn->prepare("SELECT username, is_premium, xp_total FROM user WHERE user_id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$is_premium_user = $user_data['is_premium'] ?? 0;
$current_xp = $user_data['xp_total'] ?? 0; // Ganti xp menjadi xp_total
$stmt_user->close();

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if ($quiz_id === 0) {
    header('Location: kuis.php'); // Redirect jika quiz_id tidak valid
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
$correct_answers_map = []; // Map untuk menyimpan jawaban benar dari DB

// Proses submit kuis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $quiz_completed = true;
    $correct_count = 0;
    $wrong_count = 0;
    $unanswered_count = 0;
    $total_points_earned = 0;
    $points_per_question = 10; // Poin per soal benar

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

    foreach ($questions_with_answers as $q_id => $q_data) {
        $user_selected_answer_id = isset($_POST['question_' . $q_id]) ? (int)$_POST['question_' . $q_id] : null;
        $user_answers[$q_id] = $user_selected_answer_id; // Simpan jawaban user

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
    // Dapatkan nilai tertinggi sebelumnya untuk kuis ini oleh user ini
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
        // Update atau insert nilai tertinggi baru
        $stmt_update_score = $conn->prepare("
            INSERT INTO user_quiz_scores (user_id, quiz_id, score, completed_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = VALUES(completed_at)
        ");
        $stmt_update_score->bind_param("iii", $user_id, $quiz_id, $total_points_earned);
        $stmt_update_score->execute();
        $stmt_update_score->close();
    }

    // Update XP user (placeholder)
    // Ini akan diimplementasikan lebih lanjut untuk XP dan ranking
   // Update XP user
    $new_xp = $current_xp + $total_points_earned; // Ganti xp menjadi xp_total
    $stmt_update_xp = $conn->prepare("UPDATE user SET xp_total = ? WHERE user_id = ?"); // Ganti xp menjadi xp_total
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
    <title>Kuis: <?php echo htmlspecialchars($quiz_details['topic']); ?> - CodeBrew</title>
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
            padding-top: 100px; /* Adjust for fixed header */
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
            accent-color: var(--primary-light); /* Warna radio button */
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

        /* Quiz Result Summary */
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

    <header>
        <!-- Logo -->
        <a href="index.php" class="logo">
            <img src="../assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" class="logo">
        </a>

        <!-- Navigasi -->
        <nav>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="../bank_materi/belajar.php">Belajar</a></li>
                <li><a href="kuis.php" class="active">Kuis</a></li>
                <li><a href="ranking.php">Ranking</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <?php if ($is_premium_user): ?>
                    <li><span class="premium-badge-nav">PREMIUM</span></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- User greeting and profile button -->
        <div class="user-profile-container">
            <?php if (isset($_SESSION['username'])): ?>
                <span class="greeting">
                    Halo, <?php echo htmlspecialchars($user_data['username']); ?>!
                    <?php if ($is_premium_user): ?>
                        <span class="premium-indicator">⭐</span>
                    <?php endif; ?>
                </span>
            <?php endif; ?>

            <!-- Profile button with dropdown -->
            <div class="profile-menu">
                <div class="profile-btn <?php echo $is_premium_user ? 'premium-profile' : ''; ?>" id="profileBtn">
                    <i class="fas fa-user avatar"></i>
                    <?php if ($is_premium_user): ?>
                        <div class="premium-crown">👑</div>
                    <?php endif; ?>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <?php if ($is_premium_user): ?>
                        <div class="premium-status">
                            <i class="fas fa-crown"></i>
                            <span>Status Premium</span>
                        </div>
                        <div class="dropdown-divider"></div>
                    <?php endif; ?>
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user-circle"></i>
                        <span>Profil</span>
                    </a>
                    <a href="settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Pengaturan</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="../register-login/logout.php" class="dropdown-item logout-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="quiz-container">
        <div class="quiz-header">
            <h1>Kuis: <?php echo htmlspecialchars($quiz_details['topic']); ?></h1>
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
                        <div class="explanation">
                            <strong>Penjelasan:</strong>
                            <!-- Placeholder for Groq API explanation -->
                            <p>Penjelasan untuk jawaban ini akan segera tersedia melalui AI.</p>
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

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-col">
                <h3>COMPANY</h3>
                <ul>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Pricing</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>LANGUAGE</h3>
                <ul>
                    <li><a href="#">HTML</a></li>
                    <li><a href="#">CSS</a></li>
                    <li><a href="#">JavaScript</a></li>
                    <li><a href="#">Python</a></li>
                    <li><a href="#">PHP</a></li>
                    <li><a href="#">MySQL</a></li>
                </ul>
            </div>

            <div class="footer-col pintar">
                <h3>PINTAR</h3>
                <div class="pintar-badge">Gabung di sini</div>
            </div>
        </div>

        <div class="copyright">Copyright ©️ Wasabi 2025</div>
    </footer>

    <!-- JavaScript for Functionality -->
    <script>
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
