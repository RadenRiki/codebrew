
<?php
  session_start();
  // Pastikan user sudah login (homepage hanya bisa diakses setelah login)
  if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
  }
  $user = htmlspecialchars($_SESSION['username']);
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
            <li><a href="belajar.php">Belajar</a></li>
            <li><a href="../ranking.php">Ranking</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
        </ul>
    </nav>

    <!-- User greeting and profile button -->
    <div class="user-profile-container">
        <?php if (isset($_SESSION['username'])): ?>
            <span class="greeting">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
        <!-- Profile button with dropdown -->
        <div class="profile-menu">
            <div class="profile-btn" id="profileBtn">
                <i class="fas fa-user avatar"></i>
            </div>
            <div class="profile-dropdown" id="profileDropdown">
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
        <?php else: ?>
            <a href="../register-login/login.php">
              <button class="btn">Daftar / Masuk </button>
            </a>
        <?php endif; ?>
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
                <h3 class="feature-title">Tunjukkan seberapa jauh kamu berkembang</h3>
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
                <button class="quiz-btn">Mulai Quiz</button>
            </div>

            <div class="language-card">
                <div class="language-icon">
                    <img src="../assets/Rectangle 16.png" alt="css" class="language-img" />
                </div>
                <h3 class="language-title">CSS</h3>
                <p class="language-desc">Transformasi biasa menjadi luar biasa dengan CSS! Berikan warna, layout, dan
                    animasi pada websitemu.</p>
                <button class="quiz-btn">Mulai Quiz</button>
            </div>

            <div class="language-card">
                <div class="language-icon">
                    <img src="../assets/th (1) 1.png" alt="js" />
                </div>
                <h3 class="language-title">JAVASCRIPT</h3>
                <p class="language-desc">Hidupkan web statis menjadi dinamis dengan JavaScript! Tambahkan logika dan
                    interaksi canggih.</p>
                <button class="quiz-btn">Mulai Quiz</button>
            </div>

            <div class="language-card">
                <div class="language-icon">
                    <img src="../assets/Rectangle 17.png" alt="python" class="language-img" />
                </div>
                <h3 class="language-title">PYTHON</h3>
                <p class="language-desc">Python menggabungkan kesederhanaan sintaks dengan kekuatan tak terbatas untuk
                    berbagai aplikasi.</p>
                <button class="quiz-btn">Mulai Quiz</button>
            </div>

            <div class="language-card">
                <div class="language-icon">
                    <img src="../assets/Rectangle 18.png" alt="php" class="language-img" />
                </div>
                <h3 class="language-title">PHP</h3>
                <p class="language-desc">PHP adalah bahasa scripting server-side untuk memproses data dan berkomunikasi
                    dengan database.</p>
                <button class="quiz-btn">Mulai Quiz</button>
            </div>

            <div class="language-card">
                <div class="language-icon">
                    <img src="../assets/Rectangle 19.png" alt="sql" class="language-img" />
                </div>
                <h3 class="language-title">SQL</h3>
                <p class="language-desc">Kuasai seni berbicara dengan database! SQL untuk mengelola dan memanipulasi
                    data relasional.</p>
                <button class="quiz-btn">Mulai Quiz</button>
            </div>
        </div>
    </section>

    <!-- Premium Section -->
    <section class="premium">
        <div class="premium-container">
            <h2 class="premium-title">Jadilah bagian dari komunitas yang lebih mendalam dengan <span
                    class="pintar-badge">PINTAR</span></h2>

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
        // Profile dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const profileBtn = document.getElementById('profileBtn');
            const profileDropdown = document.getElementById('profileDropdown');
            
            // Toggle dropdown when clicking profile button
            profileBtn.addEventListener('click', function(e) {
                profileDropdown.classList.toggle('show');
                e.stopPropagation();
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                    profileDropdown.classList.remove('show');
                }
            });

            // Stars animation
            createStars();
            
            // Smooth scrolling functionality
            const scrollButton = document.getElementById('scroll-to-quiz');
            
            if (scrollButton) {
                scrollButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetSection = document.getElementById('languages-section');
                    
                    if (targetSection) {
                        targetSection.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        
                        setTimeout(() => {
                            targetSection.style.transition = 'all 0.3s ease';
                            targetSection.style.transform = 'scale(1.02)';
                            
                            setTimeout(() => {
                                targetSection.style.transform = 'scale(1)';
                            }, 300);
                        }, 800);
                    }
                });
            }

            // Quiz buttons functionality
            const quizButtons = document.querySelectorAll('.quiz-btn');
            quizButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const languageTitle = this.closest('.language-card').querySelector('.language-title').textContent;
                    alert(`Quiz untuk ${languageTitle} akan segera dimulai!`);
                    // Here you would redirect to the specific quiz page
                });
            });
        });

        // Create animated stars in the background
        function createStars() {
            const starsContainer = document.getElementById('stars');
            const starCount = 150;

            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.classList.add('star');
                
                // Random position
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                
                // Random size
                const size = Math.random() * 3;
                
                // Random duration
                const duration = 3 + Math.random() * 7;
                
                // Random delay
                const delay = Math.random() * 5;
                
                star.style.left = `${posX}%`;
                star.style.top = `${posY}%`;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.setProperty('--duration', `${duration}s`);
                star.style.animationDelay = `${delay}s`;
                
                starsContainer.appendChild(star);
            }
        }
    </script>
</body>

</html>

