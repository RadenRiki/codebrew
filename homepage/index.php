<?php
session_start();
// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Tambahkan ini untuk cek status premium
require_once '../connection.php';
$user = htmlspecialchars($_SESSION['username']);

// Query untuk cek status premium
$stmt = $conn->prepare("SELECT is_premium FROM user WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$is_premium = $user_data['is_premium'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CodeBrew</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="index.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom Chatbot Styling -->
    <style>

        nav a.active {
            color: var(--light);
        }

        nav a.active::after {
            width: 100%;
        }
        
        /* Chatbot Container */
        .chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            font-family: 'Poppins', sans-serif;
        }

        /* Chatbot Button */
        .chatbot-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(90deg, var(--primary-light), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(163, 103, 220, 0.5);
            transition: all 0.3s;
        }

        .chatbot-button:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(163, 103, 220, 0.7);
        }

        .chatbot-icon {
            font-size: 24px;
            color: white;
        }

        /* Chatbox */
        .chatbox {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 350px;
            height: 450px;
            background: rgba(26, 11, 46, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            display: none;
            flex-direction: column;
            border: 1px solid rgba(93, 46, 142, 0.5);
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }

        .chatbox.active {
            display: flex;
            animation: fadeIn 0.3s forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.9);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Chatbox Header */
        .chatbox-header {
            padding: 15px 20px;
            background: linear-gradient(90deg, var(--primary-light), var(--accent));
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chatbox-title {
            color: white;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            align-items: center;
        }

        .bot-avatar {
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }

        .close-btn {
            color: white;
            cursor: pointer;
            font-size: 18px;
        }

        /* Chatbox Messages */
        .messages-container {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .message {
            margin-bottom: 12px;
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            font-size: 14px;
            line-height: 1.4;
        }

        .bot-message {
            background: rgba(93, 46, 142, 0.3);
            color: var(--light);
            align-self: flex-start;
            border-bottom-left-radius: 5px;
        }

        .user-message {
            background: rgba(255, 132, 232, 0.3);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 5px;
        }

        /* Typing Indicator */
        .typing-indicator {
            display: none;
            align-self: flex-start;
            margin-bottom: 12px;
        }

        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            margin-right: 5px;
            animation: typing 1s infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
            margin-right: 0;
        }

        @keyframes typing {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        /* Chatbox Input */
        .chatbox-input {
            display: flex;
            padding: 15px;
            background: rgba(13, 7, 27, 0.7);
            border-top: 1px solid rgba(93, 46, 142, 0.3);
        }

        .message-input {
            flex: 1;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            padding: 10px 15px;
            border-radius: 20px;
            color: white;
            font-size: 14px;
            outline: none;
        }

        .message-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .send-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(90deg, var(--primary-light), var(--accent));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .send-btn:hover {
            transform: scale(1.1);
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .chatbox {
                width: 300px;
                height: 400px;
                right: 0;
                bottom: 70px;
            }
        }

        /* languages */
        .language-js {
            padding-left: 5rem;
        }

        .language-title {
            font-weight: bold;
        }

        .hero h1 {
            font-weight: bold;
        }

        .hero p {
            font-weight: 300;
        }

        .section-title {
            font-weight: bold;
        }

        .feature-title {
            font-weight: bold;
        }

        .feature-title2 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            padding-top: 4rem;
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
                <li><a href="index.php" class="active">Beranda</a></li>
                <li><a href="../bank_materi/belajar.php">Belajar</a></li>
                <li><a href="kuis.php">Kuis</a></li>
                <li><a href="ranking.php">Ranking</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <?php if ($is_premium): ?>
                    <li><span class="premium-badge-nav">PREMIUM</span></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- User greeting and profile button -->
        <div class="user-profile-container">
            <?php if (isset($_SESSION['username'])): ?>
                <span class="greeting">
                    Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                    <?php if ($is_premium): ?>
                        <span class="premium-indicator">⭐</span>
                    <?php endif; ?>
                </span>
            <?php endif; ?>

            <!-- Profile button with dropdown -->
            <div class="profile-menu">
                <div class="profile-btn <?php echo $is_premium ? 'premium-profile' : ''; ?>" id="profileBtn">
                    <i class="fas fa-user avatar"></i>
                    <?php if ($is_premium): ?>
                        <div class="premium-crown">👑</div>
                    <?php endif; ?>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <?php if ($is_premium): ?>
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



    <!-- Hero Section -->
    <section class="hero">
        <h1>
            Mulai Petualangan <br />
            Coding-Mu dari Sini!
        </h1>
        <p>Selamat datang di dunia pemrograman, tempat di mana setiap baris kode adalah langkah baru menuju kreativitas
            tanpa batas! Di sini, kamu akan memulai perjalanan seru untuk menguasai bahasa digital masa depan!</p>

        <a href="#languages-section" class="cta" id="scroll-to-quiz">Ayo mulai mengerjakan kuis!</a>
    </section>


    <!-- Features Section -->
    <section class="features">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <img src="../assets/1907.i109.039.p.m004.c30.programming_development_isometric_icons-02-removebg-preview 1.png"
                        alt="Belajar Fleksibel" class="feature-img" />
                </div>
                <h3 class="feature-title">Belajar sesuai ritmemu</h3>
                <p class="feature-desc">Mulai dari nol atau tingkatkan skill codingmu tanpa tekanan dan tanpa batas.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <img src="../assets/4203236-removebg-preview 1.png" alt="Belajar Fleksibel" class="feature-img" />
                </div>
                <h3 class="feature-title2">Tunjukkan seberapa jauh kamu berkembang</h3>
                <p class="feature-desc">Sistem ranking bukan sekadar angka,tapi bukti progres belajarmu.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <img src="../assets/Cuplikan_layar_2025-04-16_160306-removebg-preview 1.png" alt="Belajar Fleksibel"
                        class="feature-img" />
                </div>
                <h3 class="feature-title">Bikin belajar jadi seru dan tidak membosankan</h3>
                <p class="feature-desc">Tes kemampuanmu setiap saat dengan kuis interaktif.</p>
            </div>
        </div>
    </section>

    <!-- Languages Section -->
    <section class="languages" id="languages-section">
        <h2 class="section-title">Pilih Salah Satu, Tes Skill Codingmu Sekarang!</h2>
        <p class="section-subtitle">Bukan sekedar quiz, tapi investasi pengetahuan</p>

        <div class="languages-grid">
            <div class="language-card">
                <div class="language-icon">
                    <img src="../assets/Rectangle 15.png" alt="html" class="language-img" />
                </div>
                <h3 class="language-title">HTML</h3>
                <p class="language-desc">Bangun pondasi digitalmu dengan HTML! Bahasa markup sederhana untuk menyusun
                    struktur konten web.</p>
                    <button class="quiz-btn" onclick="location.href='../homepage/kuis.php'">Mulai Quiz</button>
            </div>

            <div class="language-card">
                <div class="language-icon">
                    <img src="../assets/Rectangle 16.png" alt="css" class="language-img" />
                </div>
                <h3 class="language-title">CSS</h3>
                <p class="language-desc">Transformasi biasa menjadi luar biasa dengan CSS! Berikan warna, layout, dan
                    animasi pada websitemu.</p>
                    <button class="quiz-btn" onclick="location.href='../homepage/kuis.php'">Mulai Quiz</button>
            </div>

            <div class="language-card">
                <div class="language-icon">
                    <img src="../assets/th (1) 1.png" alt="js" class="language-js" />
                </div>
                <h3 class="language-title">JAVASCRIPT</h3>
                <p class="language-desc">Hidupkan web statis menjadi dinamis dengan JavaScript! Tambahkan logika dan
                    interaksi canggih.</p>
                    <button class="quiz-btn" onclick="location.href='../homepage/kuis.php'">Mulai Quiz</button>
            </div>

            <div class="language-card">
                <div class="language-icon">
                    <img src="../assets/Rectangle 17.png" alt="python" class="language-img" />
                </div>
                <h3 class="language-title">PYTHON</h3>
                <p class="language-desc">Python menggabungkan kesederhanaan sintaks dengan kekuatan tak terbatas untuk
                    berbagai aplikasi.</p>
                    <button class="quiz-btn" onclick="location.href='../homepage/kuis.php'">Mulai Quiz</button>
            </div>

            <div class="language-card">
                <div class="language-icon">
                    <img src="../assets/Rectangle 18.png" alt="php" class="language-img" />
                </div>
                <h3 class="language-title">PHP</h3>
                <p class="language-desc">PHP adalah bahasa scripting server-side untuk memproses data dan berkomunikasi
                    dengan database.</p>
                    <button class="quiz-btn" onclick="location.href='../homepage/kuis.php'">Mulai Quiz</button>
            </div>

            <div class="language-card">
                <div class="language-icon">
                    <img src="../assets/Rectangle 19.png" alt="sql" class="language-img" />
                </div>
                <h3 class="language-title">SQL</h3>
                <p class="language-desc">Kuasai seni berbicara dengan database! SQL untuk mengelola dan memanipulasi
                    data relasional.</p>
                    <button class="quiz-btn" onclick="location.href='../homepage/kuis.php'">Mulai Quiz</button>
            </div>
        </div>
    </section>

    <!-- Premium Section -->
    <?php if (!$is_premium): ?>
        <section class="premium">
            <div class="premium-container">
                <h2 class="premium-title">Jadilah bagian dari komunitas yang lebih mendalam dengan <span class="pintar-badge">PINTAR</span></h2>

                <div class="premium-main-features">
                    <div class="main-feature left">
                        <div class="feature-header">
                            <h3>Membuka level</h3>
                            <h3>latihan lebih tinggi</h3>
                        </div>
                        <div class="feature-brand">
                            <h2>++NAIK</h2>
                        </div>
                        <p class="feature-description">
                            Pengguna PINTAR dapat mengakses<br>
                            kunci ke-level selanjutnya.
                        </p>
                    </div>

                    <div class="main-feature right">
                        <div class="feature-header">
                            <h3>Manajemen</h3>
                            <h3>progress latihan</h3>
                        </div>
                        <div class="feature-brand">
                            <h2>++DASHBOARD</h2>
                        </div>
                        <p class="feature-description">
                            Pengguna PINTAR dapat memantau<br>
                            progress latihan mereka
                        </p>
                    </div>
                </div>

                <div class="feature-points">
                    <div class="point">
                        <div class="point-circle"></div>
                        <span class="point-text">Membuka level <span class="highlight">yang lebih tinggi</span></span>
                    </div>
                    <div class="point">
                        <div class="point-circle"></div>
                        <span class="point-text">Statistik <span class="highlight">perkembangan</span></span>
                    </div>
                    <div class="point">
                        <div class="point-circle"></div>
                        <span class="point-text">Nilai yang <span class="highlight">lebih maksimal</span></span>
                    </div>
                </div>

                <button class="premium-cta">Mulai dengan fitur PINTAR !</button>
            </div>
        </section>
    <?php else: ?>
        <!-- Premium User Welcome Section -->
        <section class="premium-welcome">
            <div class="premium-welcome-container">
                <div class="premium-crown-icon">👑</div>
                <h2 class="premium-welcome-title">Selamat datang, Member <span class="pintar-badge">PINTAR</span>!</h2>
                <p class="premium-welcome-desc">Kamu sekarang memiliki akses penuh ke semua fitur premium CodeBrew</p>

                <div class="premium-benefits">
                    <div class="benefit-item">
                        <i class="fas fa-unlock"></i>
                        <span>Level Lanjutan Terbuka</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard Analytics</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-trophy"></i>
                        <span>Skor Maksimal</span>
                    </div>
                </div>

                <a href="dashboard.php" class="premium-dashboard-btn">Lihat Dashboard Premium</a>
            </div>
        </section>
    <?php endif; ?>


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

    <!-- Chatbot Component -->
    <div class="chatbot-container">
        <div class="chatbot-button" id="chatbotButton">
            <i class="fas fa-robot chatbot-icon"></i>
        </div>
        <div class="chatbox" id="chatbox">
            <div class="chatbox-header">
                <div class="chatbox-title">
                    <div class="bot-avatar"><i class="fas fa-robot"></i></div>
                    <span>CodeBrew Assistant</span>
                </div>
                <div class="close-btn" id="closeChat"><i class="fas fa-times"></i></div>
            </div>
            <div class="messages-container" id="messagesContainer">
                <div class="message bot-message">Halo! Saya CodeBrew Assistant. Apa yang bisa saya bantu?</div>
                <div class="typing-indicator" id="typingIndicator">
                    <span></span><span></span><span></span>
                </div>
            </div>
            <div class="chatbox-input">
                <input type="text" id="messageInput" class="message-input" placeholder="Ketik pesan Anda..." />
                <div class="send-btn" id="sendMessage"><i class="fas fa-paper-plane"></i></div>
            </div>
        </div>
    </div>
    <!-- JavaScript for Functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // === Profile dropdown (jika masih ada di halaman) ===
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

            // === Smooth scroll ke quiz ===
            const scrollButton = document.getElementById('scroll-to-quiz');
            if (scrollButton) {
                scrollButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.getElementById('languages-section');
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        setTimeout(() => {
                            target.style.transition = 'all 0.3s ease';
                            target.style.transform = 'scale(1.02)';
                            setTimeout(() => target.style.transform = 'scale(1)', 300);
                        }, 800);
                    }
                });
            }

            // === Quiz buttons ===
            // document.querySelectorAll('.quiz-btn').forEach(btn => {
            //     btn.addEventListener('click', function() {
            //         const lang = this.closest('.language-card').querySelector('.language-title').textContent;
            //         alert(`Quiz untuk ${lang} akan segera dimulai!`);
            //     });
            // });

            // === Chatbot Functionality ===
            const chatbotButton = document.getElementById('chatbotButton');
            const chatbox = document.getElementById('chatbox');
            const closeChat = document.getElementById('closeChat');
            const sendMessage = document.getElementById('sendMessage');
            const messageInput = document.getElementById('messageInput');
            const messagesContainer = document.getElementById('messagesContainer');
            const typingIndicator = document.getElementById('typingIndicator');

            let sessionId = null; // simpan session di sini

            // Toggle chatbox + init session
            chatbotButton.addEventListener('click', async () => {
                if (!sessionId) {
                    try {
                        const resp = await fetch('create_chat_session.php');
                        const data = await resp.json();
                        sessionId = data.session_id || data.sessionId || null;
                    } catch (e) {
                        console.error('Gagal inisialisasi sesi:', e);
                    }
                }
                chatbox.classList.toggle('active');
                if (chatbox.classList.contains('active')) messageInput.focus();
            });

            // Close chatbox
            closeChat.addEventListener('click', () => {
                chatbox.classList.remove('active');
            });

            function addMessage(text, cls) {
                const msg = document.createElement('div');
                msg.classList.add('message', cls);
                msg.textContent = text;
                messagesContainer.appendChild(msg);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Kirim pesan ke API dengan guard empty response
            async function sendUserMessage() {
                const text = messageInput.value.trim();
                if (!text) return;
                addMessage(text, 'user-message');
                messageInput.value = '';
                typingIndicator.style.display = 'flex';

                try {
                    const res = await fetch('chatbot_api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            session_id: sessionId,
                            message: text
                        })
                    });

                    const raw = await res.text();
                    console.log('🍞 raw response:', raw);

                    if (!raw) {
                        // jika kosong, kemungkinan backend silent error
                        addMessage('Server tidak merespon. Periksa chatbot_api.php.', 'bot-message');
                        typingIndicator.style.display = 'none';
                        return;
                    }

                    let json;
                    try {
                        json = JSON.parse(raw);
                    } catch (err) {
                        console.error('Invalid JSON:', err);
                        addMessage('Terjadi kesalahan parsing response.', 'bot-message');
                        typingIndicator.style.display = 'none';
                        return;
                    }

                    typingIndicator.style.display = 'none';
                    if (json.session_id) sessionId = json.session_id;
                    if (json.message) addMessage(json.message, 'bot-message');
                    else addMessage('Maaf, tidak ada balasan.', 'bot-message');

                } catch (err) {
                    typingIndicator.style.display = 'none';
                    console.error('Fetch/parsing error:', err);
                    addMessage('Terjadi kesalahan koneksi.', 'bot-message');
                }
            }

            sendMessage.addEventListener('click', sendUserMessage);
            messageInput.addEventListener('keypress', e => {
                if (e.key === 'Enter') sendUserMessage();
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

        // Premium CTA (di luar DOMContentLoaded)
        const premiumButton = document.querySelector('.premium-cta');
        if (premiumButton) {
            premiumButton.addEventListener('click', () => {
                Swal.fire({
                    title: 'Beralih ke PINTAR!',
                    html: `<p>Dengan meng-upgrade ke PINTAR kamu akan mendapatkan:</p>
               <ul class="premium-features-list">
                 <li><i class="fas fa-check"></i> Akses ke level latihan lebih tinggi</li>
                 <li><i class="fas fa-check"></i> Dashboard statistik perkembangan</li>
                 <li><i class="fas fa-check"></i> Nilai yang lebih maksimal</li>
               </ul>`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--accent)',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Upgrade Sekarang!',
                    cancelButtonText: 'Nanti Saja'
                }).then(result => {
                    if (result.isConfirmed) window.location.href = '../payment/premium.php';
                });
            });
        }
    </script>

    <script>
    // Tangkap semua elemen logout
    document.querySelectorAll('.logout-item').forEach(function(element) {
        element.addEventListener('click', function(event) {
        const yakin = confirm("Apakah Anda yakin ingin logout?");
        if (!yakin) {
            event.preventDefault(); // Batalkan logout jika user membatalkan
        }
        });
    });
    </script>


</body>

</html>