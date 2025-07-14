<?php
session_start();
require_once '../connection.php'; // Sesuaikan path ke connection.php

$current_page = 'dashboard.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../register-login/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt_user = $conn->prepare("SELECT username, is_premium, xp_total FROM user WHERE user_id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$is_premium_user = $user_data['is_premium'] ?? 0;
$username = $user_data['username'];
$xp_total = $user_data['xp_total'] ?? 0;
$stmt_user->close();

// Redirect jika bukan premium user
if (!$is_premium_user) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Fitur dashboard hanya untuk pengguna PREMIUM. Silakan upgrade akun Anda!'];
    header('Location: kuis.php'); // Atau ke halaman upgrade premium
    exit;
}

// --- Data untuk Dashboard ---

// 1. Ringkasan Performa
$total_quizzes_completed = 0;
$average_score = 0;
$total_possible_score = 0; // Untuk menghitung rata-rata skor berdasarkan total poin yang mungkin

$stmt_summary = $conn->prepare("
    SELECT COUNT(DISTINCT qa.quiz_id) AS total_completed_quizzes,
           SUM(qa.score) AS total_earned_score,
           SUM(q.total_questions * 10) AS total_possible_score_sum -- Asumsi 10 poin per soal
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.quiz_id
    WHERE qa.user_id = ?
");
$stmt_summary->bind_param("i", $user_id);
$stmt_summary->execute();
$result_summary = $stmt_summary->get_result();
$summary_data = $result_summary->fetch_assoc();
$stmt_summary->close();

$total_quizzes_completed = $summary_data['total_completed_quizzes'] ?? 0;
$total_earned_score = $summary_data['total_earned_score'] ?? 0;
$total_possible_score_sum = $summary_data['total_possible_score_sum'] ?? 0;

if ($total_possible_score_sum > 0) {
    $average_score = round(($total_earned_score / $total_possible_score_sum) * 100, 2);
}


// 2. Progres Kuis per Bahasa (untuk grafik batang)
$quiz_progress_by_language = [];
$stmt_lang_progress = $conn->prepare("
    SELECT q.language,
           COUNT(DISTINCT q.quiz_id) AS total_quizzes_in_lang,
           COUNT(DISTINCT uqs.quiz_id) AS completed_quizzes_in_lang
    FROM quizzes q
    LEFT JOIN user_quiz_scores uqs ON q.quiz_id = uqs.quiz_id AND uqs.user_id = ?
    GROUP BY q.language
    ORDER BY completed_quizzes_in_lang DESC
    LIMIT 5
");

$stmt_lang_progress->bind_param("i", $user_id);
$stmt_lang_progress->execute();
$result_lang_progress = $stmt_lang_progress->get_result();
while ($row = $result_lang_progress->fetch_assoc()) {
    $quiz_progress_by_language[] = $row;
}
$stmt_lang_progress->close();

// Data untuk Chart.js
$chart_labels = [];
$chart_data_completed = [];
$chart_data_total = [];
foreach ($quiz_progress_by_language as $lang_data) {
    $chart_labels[] = $lang_data['language'];
    $chart_data_completed[] = $lang_data['completed_quizzes_in_lang'];
    $chart_data_total[] = $lang_data['total_quizzes_in_lang'];
}


// 3. Grafik Progres Skor (untuk grafik garis)
$score_history = [];
$stmt_score_history = $conn->prepare("
    SELECT qa.attempt_date, qa.score, q.topic
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.quiz_id
    WHERE qa.user_id = ?
    ORDER BY qa.attempt_date ASC
    LIMIT 20 -- Ambil 20 percobaan terakhir untuk grafik
");
$stmt_score_history->bind_param("i", $user_id);
$stmt_score_history->execute();
$result_score_history = $stmt_score_history->get_result();
while ($row = $result_score_history->fetch_assoc()) {
    $score_history[] = $row;
}
$stmt_score_history->close();

$score_chart_labels = [];
$score_chart_data = [];
foreach ($score_history as $history_item) {
    $score_chart_labels[] = date('d M', strtotime($history_item['attempt_date'])) . ' - ' . $history_item['topic'];
    $score_chart_data[] = $history_item['score'];
}


// 4. Riwayat Kuis Terbaru
$recent_quizzes = [];
$stmt_recent_quizzes = $conn->prepare("
    SELECT qa.attempt_date, q.language, q.topic, qa.score, qa.total_questions, qa.correct_answers
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.quiz_id
    WHERE qa.user_id = ?
    ORDER BY qa.attempt_date DESC
    LIMIT 5
");
$stmt_recent_quizzes->bind_param("i", $user_id);
$stmt_recent_quizzes->execute();
$result_recent_quizzes = $stmt_recent_quizzes->get_result();
while ($row = $result_recent_quizzes->fetch_assoc()) {
    $recent_quizzes[] = $row;
}
$stmt_recent_quizzes->close();


// 5. Ranking Global (Top 10 XP)
$global_ranking = [];
$stmt_ranking = $conn->prepare("
    SELECT username, xp_total
    FROM user
    ORDER BY xp_total DESC
    LIMIT 10
");
$stmt_ranking->execute();
$result_ranking = $stmt_ranking->get_result();
while ($row = $result_ranking->fetch_assoc()) {
    $global_ranking[] = $row;
}
$stmt_ranking->close();

// Temukan posisi user saat ini di ranking
$user_rank = 0;
$stmt_user_rank = $conn->prepare("
    SELECT COUNT(*) + 1 AS user_rank
    FROM user
    WHERE xp_total > (SELECT xp_total FROM user WHERE user_id = ? LIMIT 1)
");
$stmt_user_rank->bind_param("i", $user_id);
$stmt_user_rank->execute();
$result_user_rank = $stmt_user_rank->get_result();
$user_rank_data = $result_user_rank->fetch_assoc();
$user_rank = $user_rank_data['user_rank'] ?? 0; // Menggunakan alias yang baru
$stmt_user_rank->close();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Pengguna - CodeBrew</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="index.css"> <!-- Menggunakan CSS yang sama -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Custom styles for dashboard.php */
        body {
            background: linear-gradient(180deg, var(--darker), var(--dark));
            color: var(--light);
            min-height: 100vh;
            padding-top: 100px; /* Adjust for fixed header */
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 2.5rem;
        }

        nav a {
            color: var(--light-purple);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
            
        }

        nav a:hover {
            color: var(--light);
        }

        nav a::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient2);
            transition: width 0.3s;
        }

        nav a:hover::after {
            width: 100%;
        }

        nav a.active {
            color: #fff !important;
            position: relative;
        }
        nav a.active::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 100%;
            height: 2px;
            border-radius: 2px;
            background: linear-gradient(90deg, #a367dc, #ff84e8);
            transition: width 0.3s;
            z-index: 2;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(26, 11, 46, 0.7);
            border-radius: 15px;
            border: 1px solid rgba(93, 46, 142, 0.5);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(93, 46, 142, 0.3);
            padding-bottom: 1.5rem;
        }

        .dashboard-header h1 {
            font-size: 2.8rem;
            color: var(--light);
            margin-bottom: 0.5rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
        }

        .dashboard-header p {
            font-size: 1.1rem;
            color: var(--light-purple);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(93, 46, 142, 0.2);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(93, 46, 142, 0.3);
        }

        .stat-card .value {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }

        .stat-card .label {
            font-size: 1rem;
            color: var(--light-purple);
        }

        .chart-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            position: relative;
            height: 400px;         /* ketinggian chart tetap */
            overflow-x: auto;      /* scroll horizontal di dalam chart saja */
            overflow-y: visible;
            padding: 1.5rem;
            background: rgba(93, 46, 142, 0.2);
            border-radius: 10px;
            border: 1px solid rgba(93, 46, 142, 0.3);

        }

        .chart-scroll-container {
            width: 100%;
            height: calc(100% - 2rem);  /* sisakan ruang 2rem untuk label */
            overflow: auto;
            }
            .chart-scroll-container canvas {
            min-width: 800px;            /* sesuai kebutuhan */
            height: 100% !important;
            }

        .chart-card h3 {
            font-size: 1.5rem;
            color: var(--light);
            margin-bottom: 1rem;
            text-align: center;
        }

        .recent-quizzes-section, .ranking-section {
            background: rgba(93, 46, 142, 0.2);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(93, 46, 142, 0.3);
        }

        .recent-quizzes-section h3, .ranking-section h3 {
            font-size: 1.5rem;
            color: var(--light);
            margin-bottom: 1rem;
            text-align: center;
        }

        .quiz-list-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .quiz-list-table th, .quiz-list-table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid rgba(93, 46, 142, 0.2);
            color: var(--light-purple);
        }

        .quiz-list-table th {
            color: var(--light);
            font-weight: 600;
            background: rgba(93, 46, 142, 0.1);
        }

        .quiz-list-table tr:last-child td {
            border-bottom: none;
        }

        .quiz-list-table td.score {
            font-weight: bold;
            color: var(--accent);
        }

        .ranking-list {
            list-style: none;
            padding: 0;
        }

        .ranking-list li {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(93, 46, 142, 0.2);
            color: var(--light-purple);
        }

        .ranking-list li:last-child {
            border-bottom: none;
        }

        .ranking-list li .rank-num {
            font-weight: bold;
            color: var(--light);
            width: 30px;
        }

        .ranking-list li .username {
            flex-grow: 1;
            color: var(--light);
        }

        .ranking-list li .xp {
            font-weight: bold;
            color: var(--accent);
        }

        .user-rank-info {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 1.1rem;
            color: var(--light);
        }

        .user-rank-info span {
            font-weight: bold;
            color: var(--accent);
        }

        

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
            .dashboard-header h1 {
                font-size: 2rem;
            }
            .stats-grid, .chart-section {
                grid-template-columns: 1fr;
            }
            .stat-card .value {
                font-size: 2rem;
            }
            .chart-card {
                padding: 1rem;
            }
            .chart-card h3 {
                font-size: 1.3rem;
            }
            .quiz-list-table th, .quiz-list-table td {
                font-size: 0.9rem;
                padding: 0.6rem;
            }
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
                <li><a href="kuis.php">Kuis</a></li>
                <li><a href="ranking.php">Ranking</a></li>
                <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                <?php if ($is_premium_user): ?>
                    <li><span class="premium-badge-nav">PREMIUM</span></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- User greeting and profile button -->
        <div class="user-profile-container">
            <?php if (isset($_SESSION['username'])): ?>
                <span class="greeting">
                Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!
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
                    <!-- <a href="settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Pengaturan</span>
                    </a> -->
                    <div class="dropdown-divider"></div>
                    <a href="../register-login/logout.php" class="dropdown-item logout-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="dashboard-container">
        <div class="dashboard-header">
            <h1>Dashboard Pengguna</h1>
            <p>Selamat datang kembali, <?php echo htmlspecialchars($username); ?>! Pantau progres belajarmu di sini.</p>
        </div>

        <!-- Ringkasan Performa -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value"><?php echo $xp_total; ?></div>
                <div class="label">Total XP</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo $total_quizzes_completed; ?></div>
                <div class="label">Kuis Selesai</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo $average_score; ?>%</div>
                <div class="label">Skor Rata-rata</div>
            </div>
            <div class="stat-card">
                <div class="value">#<?php echo $user_rank; ?></div>
                <div class="label">Peringkat Anda</div>
            </div>
        </div>

        <!-- Grafik Progres -->
        <div class="chart-section">
            <div class="chart-card">
                <h3>Progres Kuis per Bahasa</h3>
                <canvas id="languageProgressChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Riwayat Skor Kuis Terbaru</h3>
                <canvas id="scoreHistoryChart"></canvas>
            </div>
        </div>

        <!-- Riwayat Kuis Terbaru -->
        <div class="recent-quizzes-section">
            <h3>Riwayat Kuis Terbaru</h3>
            <?php if (empty($recent_quizzes)): ?>
                <p class="text-center text-light-purple">Belum ada kuis yang diselesaikan. Ayo mulai kerjakan kuis!</p>
            <?php else: ?>
                <table class="quiz-list-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Bahasa</th>
                            <th>Topik</th>
                            <th>Skor</th>
                            <th>Benar</th>
                            <th>Total Soal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_quizzes as $quiz): ?>
                            <tr>
                                <td><?php echo date('d M Y H:i', strtotime($quiz['attempt_date'])); ?></td>
                                <td><?php echo htmlspecialchars($quiz['language']); ?></td>
                                <td><?php echo htmlspecialchars($quiz['topic']); ?></td>
                                <td class="score"><?php echo htmlspecialchars($quiz['score']); ?></td>
                                <td><?php echo htmlspecialchars($quiz['correct_answers']); ?></td>
                                <td><?php echo htmlspecialchars($quiz['total_questions']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Ranking Global -->
        <div class="ranking-section">
            <h3>Top 10 Peringkat Global</h3>
            <ul class="ranking-list">
                <?php if (empty($global_ranking)): ?>
                    <li class="text-center text-light-purple">Belum ada data peringkat.</li>
                <?php else: ?>
                    <?php $rank_num = 1; ?>
                    <?php foreach ($global_ranking as $rank_user): ?>
                        <li>
                            <span class="rank-num">#<?php echo $rank_num++; ?></span>
                            <span class="username"><?php echo htmlspecialchars($rank_user['username']); ?></span>
                            <span class="xp"><?php echo htmlspecialchars($rank_user['xp_total']); ?> XP</span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <p class="user-rank-info">Posisi Anda: <span class="xp">#<?php echo $user_rank; ?></span> dengan <span class="xp"><?php echo $xp_total; ?> XP</span></p>
        </div>

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

            // === Chart.js for Language Progress ===
const languageProgressCtx = document.getElementById('languageProgressChart').getContext('2d');
new Chart(languageProgressCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Kuis Selesai',
            data: <?php echo json_encode($chart_data_completed); ?>,
            backgroundColor: 'rgba(163, 103, 220, 0.7)', // primary-light
            borderColor: 'rgba(163, 103, 220, 1)',
            borderWidth: 1
        }, {
            label: 'Total Kuis',
            data: <?php echo json_encode($chart_data_total); ?>,
            backgroundColor: 'rgba(255, 132, 232, 0.3)', // accent with transparency
            borderColor: 'rgba(255, 132, 232, 0.7)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: 'white'
                },
                grid: {
                    color: 'rgba(93, 46, 142, 0.2)'
                }
            },
            x: {
                ticks: {
                    color: 'white'
                },
                grid: {
                    color: 'rgba(93, 46, 142, 0.2)'
                },
                barPercentage: 0.5, // Atur lebar batang
                categoryPercentage: 0.5 // Atur lebar kategori
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: 'white'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(26, 11, 46, 0.9)',
                titleColor: 'white',
                bodyColor: 'white',
                borderColor: 'white',
                borderWidth: 1
            }
        }
    }
});


            // === Chart.js for Score History ===
            const scoreHistoryCtx = document.getElementById('scoreHistoryChart').getContext('2d');
            new Chart(scoreHistoryCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($score_chart_labels); ?>,
                    datasets: [{
                        label: 'Skor Kuis',
                        data: <?php echo json_encode($score_chart_data); ?>,
                        fill: true,
                        backgroundColor: 'rgba(155, 78, 141, 0.2)', // accent with transparency
                        borderColor: 'rgb(184, 85, 165)',
                        tension: 0.3,
                        pointBackgroundColor: 'rgb(159, 90, 146)',
                        pointBorderColor: 'rgb(199, 76, 176)',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100, // Assuming max score is 100
                            ticks: {
                                color: 'white'
                            },
                            grid: {
                                color: 'rgba(93, 46, 142, 0.2)'
                            }
                        },
                        x: {
                            ticks: {
                                color: 'white'
                            },
                            grid: {
                                color: 'rgba(93, 46, 142, 0.2)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: 'white'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(26, 11, 46, 0.9)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: 'white',
                            borderWidth: 1
                        }
                    }
                }
            });
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