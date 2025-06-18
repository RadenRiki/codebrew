<?php
// Include the connection file
include 'connection.php';
session_start(); // Needed to access $_SESSION
$notif = isset($_GET['notif']) ? $_GET['notif'] : '';

// Fetch all users from the database
$sql = "SELECT * FROM user";
$result = $conn->query($sql);

// Store data in $user array
$user = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $user[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CodeBrew</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="landing-page/index.css">
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

    <!-- Header -->
    <header>
      <div class="logo">
        <img src="../assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" class="logo">
      </div>
        <nav>
          <ul>
            <li><a href="#">Beranda</a></li>
            <li><a href="#">Belajar</a></li>
            <li><a href="../ranking.php">Ranking</a></li>
            <li><a href="#">Dashboard</a></li>
          </ul>
        </nav>
        <div class="action-buttons">
          <?php if (isset($_SESSION['username'])): ?>
              <!-- Tampilkan ikon profil dan nama pengguna jika login -->
              <?php if (isset($_SESSION['username'])): ?>
                <div class="profile-menu">
                    <span class="greeting">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="../register-login/logout.php" onclick="confirmLogout(event)" class="logout-btn">Logout</a>
                </div>
              <?php endif; ?>
          <?php else: ?>
            <!-- Tampilkan teks "Daftar" jika belum login -->
            <a href="../register-login/login.php">
              <button class="btn">Daftar / Masuk </button>
            </a>
          <?php endif; ?>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
      <h1>
        Mulai Petualangan <br />
        Coding-Mu dari Sini!
      </h1>
      <p>Selamat datang di dunia pemrograman, tempat di mana setiap baris kode adalah langkah baru menuju kreativitas tanpa batas! Di sini, kamu akan memulai perjalanan seru untuk menguasai bahasa digital masa depan!</p>
      <?php if (isset($_SESSION['username'])): ?>
        <a href="../quiz/start.php" class="cta">Ayo mulai mengerjakan kuis!</a>
      <?php else: ?>
        <a href="../register-login/login.php" class="cta">Klik di sini untuk memulai!</a>
      <?php endif; ?>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <img src="../assets/1907.i109.039.p.m004.c30.programming_development_isometric_icons-02-removebg-preview 1.png" alt="Belajar Fleksibel" class="feature-img" />
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
            <img src="../assets/Cuplikan_layar_2025-04-16_160306-removebg-preview 1.png" alt="Belajar Fleksibel" class="feature-img" />
          </div>
          <h3 class="feature-title">Bikin belajar jadi seru dan tidak membosankan</h3>
          <p class="feature-desc">Tes kemampuanmu setiap saat dengan kuis interaktif.</p>
        </div>
      </div>
    </section>

    <!-- Languages Section -->
    <section class="languages">
      <h2 class="section-title">Pilih Salah Satu, Tes Skill Codingmu Sekarang!</h2>
      <p class="section-subtitle">Bukan sekedar quiz, tapi investasi pengetahuan</p>

      <div class="languages-grid">
        <div class="language-card">
          <div class="language-icon">
            <img src="../assets/Rectangle 15.png" alt="html" class="language-img" />
          </div>
          <h3 class="language-title">HTML</h3>
          <p class="language-desc">Bangun pondasi digitalmu dengan HTML! Bahasa markup sederhana untuk menyusun struktur konten web.</p>
          <button class="quiz-btn">Mulai Quiz</button>
        </div>

        <div class="language-card">
          <div class="language-icon">
            <img src="../assets/Rectangle 16.png" alt="css" class="language-img" />
          </div>
          <h3 class="language-title">CSS</h3>
          <p class="language-desc">Transformasi biasa menjadi luar biasa dengan CSS! Berikan warna, layout, dan animasi pada websitemu.</p>
          <button class="quiz-btn">Mulai Quiz</button>
        </div>

        <div class="language-card">
          <div class="language-icon">
            <img src="../assets/th (1) 1.png" alt="js" />
          </div>
          <h3 class="language-title">JAVASCRIPT</h3>
          <p class="language-desc">Hidupkan web statis menjadi dinamis dengan JavaScript! Tambahkan logika dan interaksi canggih.</p>
          <button class="quiz-btn">Mulai Quiz</button>
        </div>

        <div class="language-card">
          <div class="language-icon">
            <img src="../assets/Rectangle 17.png" alt="python" class="language-img" />
          </div>
          <h3 class="language-title">PYTHON</h3>
          <p class="language-desc">Python menggabungkan kesederhanaan sintaks dengan kekuatan tak terbatas untuk berbagai aplikasi.</p>
          <button class="quiz-btn">Mulai Quiz</button>
        </div>

        <div class="language-card">
          <div class="language-icon">
            <img src="../assets/Rectangle 18.png" alt="php" class="language-img" />
          </div>
          <h3 class="language-title">PHP</h3>
          <p class="language-desc">PHP adalah bahasa scripting server-side untuk memproses data dan berkomunikasi dengan database.</p>
          <button class="quiz-btn">Mulai Quiz</button>
        </div>

        <div class="language-card">
          <div class="language-icon">
            <img src="../assets/Rectangle 19.png" alt="sql" class="language-img" />
          </div>
          <h3 class="language-title">SQL</h3>
          <p class="language-desc">Kuasai seni berbicara dengan database! SQL untuk mengelola dan memanipulasi data relasional.</p>
          <button class="quiz-btn">Mulai Quiz</button>
        </div>
      </div>
    </section>

    <!-- Premium Section -->
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

    <script>
      // Create stars
      const starsContainer = document.getElementById("stars");
      const starsCount = 100;

      for (let i = 0; i < starsCount; i++) {
        const star = document.createElement("div");
        star.classList.add("star");

        // Random position
        const x = Math.random() * 100;
        const y = Math.random() * 100;

        // Random size
        const size = Math.random() * 3 + 1;

        // Random animation duration
        const duration = Math.random() * 5 + 3;

        star.style.left = `${x}%`;
        star.style.top = `${y}%`;
        star.style.width = `${size}px`;
        star.style.height = `${size}px`;
        star.style.setProperty("--duration", `${duration}s`);
        star.style.animationDelay = `${Math.random() * 5}s`;

        starsContainer.appendChild(star);
      }

      // Quiz buttons
      const quizBtns = document.querySelectorAll(".quiz-btn");
      quizBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
          this.innerHTML = "Membuka Quiz...";

          setTimeout(() => {
            this.innerHTML = 'Mulai Quiz <i class="fas fa-play"></i>';
          }, 1500);
        });
      });

      // Scroll animation
      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              entry.target.classList.add("show");
            }
          });
        },
        {
          threshold: 0.1,
        }
      );

      const hiddenElements = document.querySelectorAll(".feature-card, .language-card, .premium-feature");
      hiddenElements.forEach((el) => {
        el.classList.add("hidden");
        observer.observe(el);
      });

      //konfirmasi logout atau tidak
    function confirmLogout(event) {
     event.preventDefault(); // Mencegah link langsung dijalankan

    const confirmLogout = confirm("Apakah kamu yakin ingin logout?");
      if (confirmLogout) {
        window.location.href = "../register-login/logout.php";
      }
    }
    </script>

      <?php if (!empty($notif)): ?>
        <script>
          Swal.fire({
            icon: 'success',
            title: '<?php echo addslashes($notif); ?>',
            showConfirmButton: false,
            timer: 1500
          });

          // Hapus parameter notif dari URL setelah menampilkan alert
          if (window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('notif');
            window.history.replaceState({}, document.title, url.pathname);
          }
        </script>
    <?php endif; ?>
  </body>
</html>