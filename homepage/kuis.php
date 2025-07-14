<?php
session_start();

$current_page = 'kuis.php';

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: ../register-login/login.php'); // Sesuaikan path jika perlu
    exit;
}

require_once '../connection.php'; // Sesuaikan path ke connection.php

// Ambil data user untuk cek status premium
$user_id = $_SESSION['user_id'];
$stmt_user = $conn->prepare("SELECT username, is_premium FROM user WHERE user_id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$is_premium_user = $user_data['is_premium'] ?? 0;
$username = $user_data['username'];
$stmt_user->close();

// Ambil semua data kuis dari database
$quizzes_by_language = [];
$quiz_query = "SELECT quiz_id, language, topic, is_premium FROM quizzes ORDER BY language, topic";
$quiz_result = $conn->query($quiz_query);

if ($quiz_result->num_rows > 0) {
    while ($row = $quiz_result->fetch_assoc()) {
        $quizzes_by_language[$row['language']][] = $row;
    }
}

// Urutkan bahasa sesuai keinginan (opsional, jika tidak, akan urut abjad)
$language_order = ['HTML', 'CSS', 'JavaScript', 'Python', 'PHP', 'MySQL'];
$ordered_quizzes = [];
foreach ($language_order as $lang) {
    if (isset($quizzes_by_language[$lang])) {
        $ordered_quizzes[$lang] = $quizzes_by_language[$lang];
    }
}
// Tambahkan bahasa lain yang mungkin tidak ada di $language_order
foreach ($quizzes_by_language as $lang => $quizzes) {
    if (!isset($ordered_quizzes[$lang])) {
        $ordered_quizzes[$lang] = $quizzes;
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pilih Kuis - CodeBrew</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="index.css"> <!-- Menggunakan CSS yang sama -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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

        /* Custom styles for kuis.php */
        body {
            background: linear-gradient(180deg, var(--darker), var(--dark));
            color: var(--light);
            min-height: 100vh;
            padding-top: 100px; /* Adjust for fixed header */
        }

        .quiz-selection-section {
            padding: 3rem 5%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
        }

        .section-subtitle {
            text-align: center;
            color: var(--light-purple);
            margin-bottom: 3rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .language-category {
            margin-bottom: 3rem;
            background: rgba(26, 11, 46, 0.7);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(93, 46, 142, 0.5);
        }

        .language-category h2 {
            font-size: 2rem;
            color: var(--primary-light);
            margin-bottom: 1.5rem;
            border-bottom: 2px solid rgba(93, 46, 142, 0.3);
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .language-category h2 i {
            font-size: 1.8rem;
        }

        .quiz-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .quiz-card {
            background: rgba(93, 46, 142, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .quiz-card.locked {
            opacity: 0.6;
            cursor: not-allowed;
            filter: grayscale(100%);
        }

        .quiz-card.locked::after {
            content: 'PREMIUM';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffd700;
            font-size: 1.8rem;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
            z-index: 10;
        }

        .quiz-card h3 {
            font-size: 1.3rem;
            color: var(--light);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .quiz-card .badge-premium-quiz {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #1a0b2e;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin-left: 10px;
        }

        .quiz-card p {
            color: var(--light-purple);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            flex-grow: 1;
        }

        .quiz-card .start-quiz-btn {
            background: var(--gradient);
            color: var(--light);
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            width: 100%;
            text-align: center;
            text-decoration: none;
            display: block;
        }

        .quiz-card .start-quiz-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(163, 103, 220, 0.3);
        }

        .quiz-card.locked .start-quiz-btn {
            background: #6c757d;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .language-category h2 {
                font-size: 1.8rem;
            }
            .quiz-card h3 {
                font-size: 1.2rem;
            }
            .quiz-card.locked::after {
                font-size: 1.5rem;
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
                <li><a href="../homepage/kuis.php" class="<?= $current_page == 'kuis.php' ? 'active' : '' ?>">Kuis</a></li>                <li><a href="ranking.php">Ranking</a></li>
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
                    Halo, <?php echo htmlspecialchars($username); ?>!
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

    <main class="quiz-selection-section">
        <h1 class="section-title">Pilih Kuis yang Ingin Kamu Kerjakan</h1>
        <p class="section-subtitle">Asah kemampuan codingmu dengan berbagai topik kuis interaktif!</p>

        <?php if (empty($ordered_quizzes)): ?>
            <div class="alert alert-info text-center" role="alert">
                <i class="fas fa-info-circle"></i> Belum ada kuis yang tersedia saat ini. Silakan cek kembali nanti!
            </div>
        <?php else: ?>
            <?php foreach ($ordered_quizzes as $language => $quizzes): ?>
                <div class="language-category">
                    <h2>
                        <?php 
                            $icon_class = '';
                            switch ($language) {
                                case 'HTML': $icon_class = 'fab fa-html5'; break;
                                case 'CSS': $icon_class = 'fab fa-css3-alt'; break;
                                case 'JavaScript': $icon_class = 'fab fa-js-square'; break;
                                case 'Python': $icon_class = 'fab fa-python'; break;
                                case 'PHP': $icon_class = 'fab fa-php'; break;
                                case 'MySQL': $icon_class = 'fas fa-database'; break;
                                default: $icon_class = 'fas fa-code'; break;
                            }
                        ?>
                        <i class="<?php echo $icon_class; ?>"></i> <?php echo htmlspecialchars($language); ?>
                    </h2>
                    <div class="quiz-list">
                        <?php foreach ($quizzes as $quiz): 
                            $is_locked = ($quiz['is_premium'] && !$is_premium_user);
                            $quiz_link = $is_locked ? '#' : 'start_quiz.php?quiz_id=' . $quiz['quiz_id']; // Ganti start_quiz.php dengan halaman kuis sebenarnya
                            $quiz_class = $is_locked ? 'quiz-card locked' : 'quiz-card';
                        ?>
                            <div class="<?php echo $quiz_class; ?>" data-quiz-id="<?php echo $quiz['quiz_id']; ?>" data-is-premium="<?php echo $quiz['is_premium']; ?>">
                                <h3>
                                    <?php echo htmlspecialchars($quiz['topic']); ?>
                                    <?php if ($quiz['is_premium']): ?>
                                        <span class="badge-premium-quiz">PREMIUM</span>
                                    <?php endif; ?>
                                </h3>
                                <p>Kuis ini akan menguji pemahaman Anda tentang <?php echo htmlspecialchars($quiz['topic']); ?> dalam bahasa <?php echo htmlspecialchars($quiz['language']); ?>.</p>
                                <a href="<?php echo $quiz_link; ?>" class="start-quiz-btn <?php echo $is_locked ? 'disabled' : ''; ?>">
                                    Mulai Kuis <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
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

            // === Quiz card click handler for locked quizzes ===
            document.querySelectorAll('.quiz-card.locked').forEach(card => {
                card.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent default link behavior
                    Swal.fire({
                        title: 'Kuis Premium',
                        html: 'Kuis ini hanya tersedia untuk pengguna <strong>PREMIUM</strong>. Tingkatkan akun Anda untuk mengakses semua kuis!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: 'var(--accent)',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Upgrade Sekarang!',
                        cancelButtonText: 'Nanti Saja'
                    }).then(result => {
                        if (result.isConfirmed) {
                            window.location.href = '../payment/premium.php'; // Ganti dengan halaman upgrade premium Anda
                        }
                    });
                });
            });

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
