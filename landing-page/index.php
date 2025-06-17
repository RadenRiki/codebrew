<?php
// Include the connection file
include '../connection.php';
session_start(); // Needed to access $_SESSION

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
    <style>
      :root {
        --primary: #5d2e8e;
        --primary-light: #a367dc;
        --accent: #ff84e8;
        --dark: #1a0b2e;
        --darker: #0d071b;
        --light: #ffffff;
        --light-purple: #c9b6e4;
        --gradient: linear-gradient(90deg, var(--primary-light), var(--accent));
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
      }

      body {
        background: linear-gradient(180deg, var(--darker), var(--dark));
        color: var(--light);
        overflow-x: hidden;
        min-height: 100vh;
        position: relative;
      }

      /* Animasi bintang */
      .stars {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: -1;
      }

      .star {
        position: absolute;
        background-color: white;
        border-radius: 50%;
        animation: twinkle var(--duration) ease-in-out infinite;
        opacity: 0;
      }

      @keyframes twinkle {
        0% {
          opacity: 0;
          transform: scale(0);
        }

        50% {
          opacity: 1;
          transform: scale(1);
        }

        100% {
          opacity: 0;
          transform: scale(0);
        }
      }

      /* Header */
      header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        padding: 1.5rem 5%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 100;
        backdrop-filter: blur(10px);
        background: rgba(26, 11, 46, 0.8);
        border-bottom: 1px solid rgba(93, 46, 142, 0.3);
      }

      .logo {
		width: 35%;
		padding-top: 0.5%;
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
        background: var(--gradient);
        transition: width 0.3s;
      }

      nav a:hover::after {
        width: 100%;
      }

      .btn {
        background: var(--gradient);
        color: var(--light);
        border: none;
        padding: 0.7rem 1.5rem;
        border-radius: 2rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
      }

      .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(163, 103, 220, 0.3);
      }

      /* Hero Section */
      .hero {
        padding: 15rem 5% 5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        min-height: 100vh;
        position: relative;
      }

      .hero h1 {
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        max-width: 900px;
        line-height: 1.2;
      }

      .hero p {
        font-size: 1.2rem;
        color: var(--light-purple);
        max-width: 700px;
        margin-bottom: 2.5rem;
        line-height: 1.6;
      }

      .cta {
        background: var(--gradient);
        color: var(--light);
        font-size: 1.1rem;
        font-weight: 600;
        padding: 1rem 2.5rem;
        border-radius: 2rem;
        text-decoration: none;
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
        overflow: hidden;
        z-index: 1;
      }

      .cta:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(163, 103, 220, 0.4);
      }

      .cta::after {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: 0.5s;
        z-index: -1;
      }

      .cta:hover::after {
        left: 100%;
      }

      .hero-img {
        max-width: 600px;
        margin-top: 3rem;
        animation: float 6s ease-in-out infinite;
      }

      @keyframes float {
        0%,
        100% {
          transform: translateY(0);
        }

        50% {
          transform: translateY(-30px);
        }
      }

      /* Features */
      .features {
        padding: 5rem 5%;
        background: rgba(13, 7, 27, 0.7);
      }

      .section-title {
        text-align: center;
        font-size: 2.5rem;
        margin-bottom: 1rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }

      .section-subtitle {
        text-align: center;
        color: var(--light-purple);
        margin-bottom: 4rem;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
      }

      .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
      }

      .feature-card {
        background: rgba(93, 46, 142, 0.2);
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        transition: transform 0.3s, background 0.3s;
        position: relative;
        overflow: hidden;
      }

      .feature-card:hover {
        transform: translateY(-10px);
        background: rgba(93, 46, 142, 0.3);
      }

      .feature-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--primary), var(--light-purple));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
      }

      .feature-card:hover::before {
        transform: scaleX(1);
      }

      .feature-icon {
        font-size: 3rem;
        margin-bottom: 1.5rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }

      .feature-card:hover {
        transform: translateY(-10px);
        border-color: var(--primary);
        box-shadow: 0 20px 40px rgba(139, 92, 246, 0.3);
      }

      .feature-icon::before {
        content: "";
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transform: rotate(45deg);
        animation: shine 3s infinite;
      }

      @keyframes shine {
        0% {
          transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }
        50% {
          transform: translateX(100%) translateY(100%) rotate(45deg);
        }
        100% {
          transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }
      }

      .feature-title {
        font-size: 1.5rem;
        margin-bottom: 1rem;
      }

      .feature-desc {
        color: var(--light-purple);
        line-height: 1.6;
      }

      /* Languages */
      .languages {
        padding: 5rem 5%;
        text-align: center;
      }

      .languages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
      }

      .language-card {
        background: rgba(93, 46, 142, 0.2);
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
      }

      .language-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        background: rgba(93, 46, 142, 0.3);
      }

      .language-icon {
        font-size: 3rem;
        margin-bottom: 1.5rem;
        color: var(--primary-light);
      }

      .language-title {
        font-size: 1.8rem;
        margin-bottom: 1rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }

      .language-desc {
        color: var(--light-purple);
        line-height: 1.6;
        margin-bottom: 1.5rem;
      }

      .quiz-btn {
        background: transparent;
        border: 2px solid var(--primary-light);
        color: var(--light);
        padding: 0.5rem 1.5rem;
        border-radius: 2rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
      }

      .quiz-btn:hover {
        background: var(--primary-light);
      }

      /* Premium Section */
	  .premium {
		position: relative;
		z-index: 2;
		max-width: 1200px;
		margin: 0 auto;
		padding: 4rem 2rem;
	}


	
	.premium-container {
		background: rgba(0, 0, 0, 0.6);
		backdrop-filter: blur(20px);
		border-radius: 25px;
		border: 2px solid var(--primary);
		padding: 3rem 2rem;
		text-align: center;
		position: relative;
		overflow: hidden;
	}

	.premium-title {
		font-size: 2.2rem;
		font-weight: 700;
		color: white;
		margin-bottom: 3rem;
		line-height: 1.2;
	}

	.pintar-badge {
		background: var(--primary);
		color: white;
		padding: 0.5rem 1.5rem;
		border-radius: 25px;
		font-size: 1rem;
		font-weight: 600;
	}

	.premium-main-features {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 3rem;
		margin-bottom: 3rem;
		max-width: 900px;
		margin-left: auto;
		margin-right: auto;
	}

	.main-feature {
		text-align: center;
	}

	.feature-header {
		margin-bottom: 1rem;
	}

	.feature-header h3 {
		font-size: 1.2rem;
		color: #ccc;
		margin: 0;
		line-height: 1.1;
	}

	.feature-brand {
		margin: 1.5rem 0;
	}

	.feature-brand h2 {
		font-size: 3rem;
		font-weight: 900;
		margin: 0;
		background: var(--gradient);
        color: var(--light);
		-webkit-background-clip: text;
		-webkit-text-fill-color: transparent;
		background-clip: text;
		letter-spacing: 2px;
	}

	.feature-description {
		color: #aaa;
		font-size: 1rem;
		line-height: 1.4;
		margin: 0;
	}

	.feature-points {
		display: flex;
		justify-content: center;
		gap: 3rem;
		margin-bottom: 3rem;
		flex-wrap: wrap;
	}

	.point {
		display: flex;
		align-items: center;
		gap: 0.8rem;
	}

	.point-circle {
		width: 12px;
		height: 12px;
		background: white;
		border-radius: 50%;
		flex-shrink: 0;
	}

	.point-text {
		color: white;
		font-size: 1rem;
	}

	.point-text .highlight {
		color: var(--light-purple);
		font-weight: 600;
	}

	.premium-cta {
        background: var(--gradient);
        color: var(--light);
		border: none;
		padding: 1rem 3rem;
		border-radius: 30px;
		font-size: 1.1rem;
		font-weight: 600;
		cursor: pointer;
		transition: all 0.3s ease;
		box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
	}

	.premium-cta:hover {
		transform: translateY(-3px);
		box-shadow: 0 15px 40px rgba(139, 92, 246, 0.4);
	}

	/* Responsive Design */
	@media (max-width: 768px) {
		.premium-main-features {
			grid-template-columns: 1fr;
			gap: 2rem;
		}
		
		.feature-points {
			flex-direction: column;
			gap: 1rem;
			align-items: center;
		}
		
		.premium-title {
			font-size: 1.8rem;
		}
		
		.feature-brand h2 {
			font-size: 2.5rem;
		}
		
		.container {
			padding: 1rem;
		}
	}

	  /*language page*/
	  .language-img {
		width: 100%;
	  }
      /* Footer */
      footer {
        padding: 5rem 5% 2rem;
        background: var(--darker);
      }

      .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 3rem;
        max-width: 1200px;
        margin: 0 auto 3rem;
      }

      .footer-col h3 {
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        color: var(--accent);
      }

      .footer-col ul {
        list-style: none;
      }

      .footer-col ul li {
        margin-bottom: 0.8rem;
      }

      .footer-col ul li a {
        color: var(--light-purple);
        text-decoration: none;
        transition: color 0.3s;
      }

      .footer-col ul li a:hover {
        color: var(--light);
      }

      .footer-col.pintar {
      }

      .pintar-badge {
        display: inline-block;
        background: var(--gradient);
        color: var(--dark);
        padding: 0.5rem 1.5rem;
        border-radius: 2rem;
        font-weight: 600;
        margin-top: -1rem;
        cursor: pointer;
      }

      .copyright {
        text-align: center;
        padding-top: 2rem;
        border-top: 1px solid rgba(93, 46, 142, 0.3);
        color: var(--light-purple);
        font-size: 0.9rem;
      }

      /* Added assets styles */
      .star-assets {
        position: absolute;
        z-index: -1;
        pointer-events: none;
      }

      .star1 {
        width: 500px;
        opacity: 50%;
      }

      .star2 {
        top: 15%;
        left: 70%;
        width: 700px;
      }

      .star3 {
        top: 25%;
        width: 500px;
      }

      .star4 {
        top: 40%;
        left: 50%;
        opacity: 40%;
        width: 500px;
      }

      .star5 {
        top: 60%;
        width: 500px;
      }

      .star6 {
        top: 70%;
        width: 500px;
      }

      .star7 {
        top: 85%;
        left: 20%;
        width: 500px;
      }

      /* Responsive */
      @media (max-width: 992px) {
        header {
          padding: 1.5rem 5%;
        }

        nav ul {
          gap: 1.5rem;
        }

        .hero h1 {
          font-size: 2.8rem;
        }
      }

      @media (max-width: 768px) {
        nav ul {
          display: none;
        }

        .hero {
          padding: 10rem 5% 3rem;
        }

        .hero h1 {
          font-size: 2.2rem;
        }

        .hero p {
          font-size: 1rem;
        }
      }

      @media (max-width: 480px) {
        .hero h1 {
          font-size: 1.8rem;
        }

        .section-title {
          font-size: 2rem;
        }

        .premium-title {
          font-size: 2rem;
        }

        .premium-subtitle {
          font-size: 1.5rem;
        }
      }
    </style>
  </head>

  <body>
    <!-- Background stars -->
    <div class="stars" id="stars"></div>

    <!-- Static star assets -->
    <img class="star-assets star1" src="/assets/—Pngtree—white light star twinkle light_7487663 1.png" alt="" />
    <img class="star-assets star2" src="/assets/—Pngtree—white light star twinkle light_7487663 2.png" alt="" />
    <img class="star-assets star3" src="/assets/—Pngtree—white light star twinkle light_7487663 3.png" alt="" />
    <img class="star-assets star4" src="/assets/—Pngtree—white light star twinkle light_7487663 4.png" alt="" />
    <img class="star-assets star5" src="/assets/—Pngtree—white light star twinkle light_7487663 5.png" alt="" />
    <img class="star-assets star6" src="/assets/—Pngtree—white light star twinkle light_7487663 6.png" alt="" />
    <img class="star-assets star7" src="/assets/—Pngtree—white light star twinkle light_7487663 7.png" alt="" />

    <!-- Header -->
    <header>
      <div class="logo">
		<img src="/assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" class="logo">
	</div>
      <nav>
        <ul>
          <li><a href="#">Beranda</a></li>
          <li><a href="#">Belajar</a></li>
          <li><a href="#">Ranking</a></li>
          <li><a href="#">Dashboard</a></li>
        </ul>
      </nav>
      <div class="action-buttons">
        <?php if (isset($_SESSION['username'])): ?>
            <!-- Tampilkan ikon profil dan nama pengguna jika login -->
            <div class="profile-menu">
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <img src="images/profile.png" alt="Profile Icon" class="profile-icon">
                <div class="dropdown-menu">
                    <a href="profile.php">Profil</a>
                    <a href="logout.php">Keluar</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Tampilkan teks "Daftar" jika belum login -->
            <button href="register.php" class="btn">Daftar / Masuk</button>
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
      <a href="#" class="cta">Klik disini untuk memulai!</a>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <img src="/assets/1907.i109.039.p.m004.c30.programming_development_isometric_icons-02-removebg-preview 1.png" alt="Belajar Fleksibel" class="feature-img" />
          </div>
          <h3 class="feature-title">Belajar sesuai ritmemu</h3>
          <p class="feature-desc">Mulai dari nol atau tingkatkan skill codingmu tanpa tekanan dan tanpa batas.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <img src="/assets/4203236-removebg-preview 1.png" alt="Belajar Fleksibel" class="feature-img" />
          </div>
          <h3 class="feature-title">Tunjukkan seberapa jauh kamu berkembang</h3>
          <p class="feature-desc">Sistem ranking bukan sekadar angka,tapi bukti progres belajarmu.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <img src="/assets/Cuplikan_layar_2025-04-16_160306-removebg-preview 1.png" alt="Belajar Fleksibel" class="feature-img" />
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
            <img src="/assets/Rectangle 15.png" alt="html" class="language-img" />
          </div>
          <h3 class="language-title">HTML</h3>
          <p class="language-desc">Bangun pondasi digitalmu dengan HTML! Bahasa markup sederhana untuk menyusun struktur konten web.</p>
          <button class="quiz-btn">Mulai Quiz</button>
        </div>

        <div class="language-card">
          <div class="language-icon">
            <img src="/assets/Rectangle 16.png" alt="css" class="language-img" />
          </div>
          <h3 class="language-title">CSS</h3>
          <p class="language-desc">Transformasi biasa menjadi luar biasa dengan CSS! Berikan warna, layout, dan animasi pada websitemu.</p>
          <button class="quiz-btn">Mulai Quiz</button>
        </div>

        <div class="language-card">
          <div class="language-icon">
            <img src="/assets/th (1) 1.png" alt="js" />
          </div>
          <h3 class="language-title">JAVASCRIPT</h3>
          <p class="language-desc">Hidupkan web statis menjadi dinamis dengan JavaScript! Tambahkan logika dan interaksi canggih.</p>
          <button class="quiz-btn">Mulai Quiz</button>
        </div>

        <div class="language-card">
          <div class="language-icon">
            <img src="/assets/Rectangle 17.png" alt="python" class="language-img" />
          </div>
          <h3 class="language-title">PYTHON</h3>
          <p class="language-desc">Python menggabungkan kesederhanaan sintaks dengan kekuatan tak terbatas untuk berbagai aplikasi.</p>
          <button class="quiz-btn">Mulai Quiz</button>
        </div>

        <div class="language-card">
          <div class="language-icon">
            <img src="/assets/Rectangle 18.png" alt="php" class="language-img" />
          </div>
          <h3 class="language-title">PHP</h3>
          <p class="language-desc">PHP adalah bahasa scripting server-side untuk memproses data dan berkomunikasi dengan database.</p>
          <button class="quiz-btn">Mulai Quiz</button>
        </div>

        <div class="language-card">
          <div class="language-icon">
            <img src="/assets/Rectangle 19.png" alt="sql" class="language-img" />
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

      // Button animation
      const ctaBtn = document.querySelector(".cta");
      ctaBtn.addEventListener("click", function (e) {
        e.preventDefault();

        this.textContent = "Memulai Petualangan...";
        this.style.pointerEvents = "none";

        setTimeout(() => {
          this.innerHTML = 'Petualangan Dimulai! <i class="fas fa-arrow-right"></i>';
          this.style.background = "linear-gradient(90deg, #ff84e8, #a367dc)";
        }, 1500);
      });

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
    </script>
  </body>
</html>