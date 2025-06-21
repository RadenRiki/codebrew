<?php
  session_start();
  // Redirect to login if not logged in
  if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
  }
  $user = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeBrew Premium - PINTAR</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Premium Page Specific CSS -->
    <style>
        /* Premium Page Styles */
        .premium-page {
            min-height: 100vh;
            background: linear-gradient(180deg, #652d86, #09020f 77.4%);
            padding-top: 120px;
            padding-bottom: 50px;
            position: relative;
            overflow: hidden;
        }
        
        .premium-title {
            font-size: 60px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 50px;
            background: linear-gradient(180deg, #fff, #bf4ae6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 0.01em;
        }
        
        .plans-container {
            display: flex;
            justify-content: center;
            gap: 55px;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .plan-card {
            width: 460px;
            height: 600px;
            border-radius: 50px;
            background-color: rgba(17, 7, 27, 0.75);
            padding: 40px;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .free-plan {
            border: 2px solid #232323;
        }
        
        .premium-plan {
            border: 2px solid #cf7dff;
        }
        
        .plan-name {
            font-size: 20px;
            font-weight: 500;
            color: #e6baff;
            margin-bottom: 10px;
        }
        
        .plan-price {
            font-size: 60px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .free-price {
            color: #fff;
        }
        
        .premium-price {
            background: linear-gradient(180deg, #fff, #bf4ae6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .price-period {
            font-size: 12px;
            color: #6c6a6f;
            margin-bottom: 30px;
        }
        
        .plan-description {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .features-list {
            margin-bottom: 30px;
        }
        
        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            color: #fff;
            font-size: 16px;
            font-weight: 500;
        }
        
        .feature-icon {
            color: #e6baff;
            margin-right: 20px;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .plan-button {
            margin-top: auto;
            width: 360px;
            height: 80px;
            border-radius: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 500;
            color: #e6baff;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: auto auto 20px;
        }
        
        .free-button {
            border: 2px solid #cf7dff;
            background: transparent;
        }
        
        .premium-button {
            border: 2px solid #cf7dff;
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.93) 12.02%, rgba(164, 91, 189, 0.93));
            filter: drop-shadow(0px 4px 4px rgba(0, 0, 0, 0.25));
        }
        
        .plan-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(163, 103, 220, 0.3);
        }
        
        .button-icon {
            margin-left: 10px;
            font-size: 24px;
        }
        
        /* Background stars and decorations */
        .premium-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.5;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1100px) {
            .plans-container {
                flex-direction: column;
                align-items: center;
                gap: 30px;
            }
            
            .premium-title {
                font-size: 40px;
                padding: 0 20px;
            }
        }
        
        @media (max-width: 500px) {
            .plan-card {
                width: 90%;
                height: auto;
                min-height: 550px;
                padding: 30px 20px;
            }
            
            .plan-button {
                width: 90%;
            }
            
            .plan-price {
                font-size: 40px;
            }
        }
        
        /* Payment modal styles */
        .payment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .payment-modal.active {
            display: flex;
            animation: fadeIn 0.3s forwards;
        }
        
        .payment-content {
            background: rgba(26, 11, 46, 0.95);
            border: 2px solid #cf7dff;
            border-radius: 20px;
            width: 500px;
            max-width: 90%;
            padding: 30px;
            position: relative;
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .payment-title {
            font-size: 24px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 10px;
        }
        
        .payment-subtitle {
            font-size: 16px;
            color: #e6baff;
        }
        
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .payment-method {
            background: rgba(93, 46, 142, 0.2);
            border: 1px solid rgba(207, 125, 255, 0.3);
            border-radius: 10px;
            padding: 15px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .payment-method:hover, .payment-method.selected {
            background: rgba(93, 46, 142, 0.4);
            border-color: #cf7dff;
        }
        
        .method-icon {
            width: 40px;
            height: 40px;
            background: #fff;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .method-icon img {
            max-width: 30px;
            max-height: 30px;
        }
        
        .method-name {
            font-size: 16px;
            font-weight: 500;
            color: #fff;
        }
        
        .payment-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .payment-button {
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .cancel-btn {
            background: transparent;
            border: 1px solid #cf7dff;
            color: #e6baff;
        }
        
        .proceed-btn {
            background: linear-gradient(90deg, var(--primary-light), var(--accent));
            border: none;
            color: #fff;
        }
        
        .payment-button:hover {
            transform: translateY(-3px);
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #e6baff;
            font-size: 20px;
            cursor: pointer;
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
    
    <!-- Header -->
    <header>
        <!-- Logo -->
        <a href="index.php" class="logo">
            <img src="../assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" class="logo">
        </a>

        <!-- Navigasi -->
        <nav>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="belajar.php">Belajar</a></li>
                <li><a href="ranking.php">Ranking</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
        </nav>

        <!-- User greeting and profile button -->
        <div class="user-profile-container">
            <?php if (isset($_SESSION['username'])): ?>
                <span class="greeting">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <?php endif; ?>
            
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
            </div>
        </div>
    </header>

    <!-- Premium Page Content -->
    <div class="premium-page">
        <h1 class="premium-title">BERGABUNG UNTUK LEBIH!</h1>
        
        <div class="plans-container">
            <!-- Free Plan Card -->
            <div class="plan-card free-plan">
                <div class="plan-name">Penjelajah</div>
                <div class="plan-price free-price">GRATIS</div>
                <div class="price-period">/ Selamanya</div>
                
                <p class="plan-description">
                    Ayo mulai perjalanan belajar pemrograman kamu tanpa biaya! Dengan fitur Penjelajah Gratis, kamu punya kesempatan untuk mengeksplorasi dunia coding dengan santai dan menyenangkan, dengan fitur:
                </p>
                
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-star feature-icon"></i>
                        <span>Mengerjakan kuis</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-star feature-icon"></i>
                        <span>Mengakses link materi berbagai bahasa pemrograman</span>
                    </div>
                </div>
                
                <div class="plan-button free-button" id="freeButton">
                    Mulai secara gratis
                    <i class="fas fa-arrow-right button-icon"></i>
                </div>
            </div>
            
            <!-- Premium Plan Card -->
            <div class="plan-card premium-plan">
                <div class="plan-name">Premium</div>
                <div class="plan-price premium-price">PINTAR</div>
                <div class="price-period">/ Selamanya</div>
                <p class="plan-price-amount">Rp100.000</p>
                
                <p class="plan-description">
                    Dapatkan akses penuh ke berbagai fitur keren yang bakal membantu belajar pemrograman kamu lebih jauh lagi! Dengan berlangganan paket Premium PINTAR, kamu bisa menikmati:
                </p>
                
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-star feature-icon"></i>
                        <span>Mengakses kuis lanjutan</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-star feature-icon"></i>
                        <span>Memantau progress latihan</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-star feature-icon"></i>
                        <span>Mengakses link materi berbagai bahasa pemrograman</span>
                    </div>
                </div>
                
                <div class="plan-button premium-button" id="premiumButton">
                    Buka fitur premium PINTAR
                    <i class="fas fa-arrow-right button-icon"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Modal -->
    <div class="payment-modal" id="paymentModal">
        <div class="payment-content">
            <div class="close-modal" id="closeModal">
                <i class="fas fa-times"></i>
            </div>
            
            <div class="payment-header">
                <h2 class="payment-title">Upgrade ke PINTAR Premium</h2>
                <p class="payment-subtitle">Pilih metode pembayaran - Rp100.000 (sekali bayar)</p>
            </div>
            
            <div class="payment-methods">
                <div class="payment-method" data-method="qris">
                    <div class="method-icon">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/QRIS_logo.svg" alt="QRIS">
                    </div>
                    <span class="method-name">QRIS</span>
                </div>
                
                <div class="payment-method" data-method="gopay">
                    <div class="method-icon">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg" alt="GoPay">
                    </div>
                    <span class="method-name">GoPay</span>
                </div>
                
                <div class="payment-method" data-method="ovo">
                    <div class="method-icon">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg" alt="OVO">
                    </div>
                    <span class="method-name">OVO</span>
                </div>
                
                <div class="payment-method" data-method="bankTransfer">
                    <div class="method-icon">
                        <i class="fas fa-university" style="color: #5d2e8e; font-size: 24px;"></i>
                    </div>
                    <span class="method-name">Transfer Bank</span>
                </div>
            </div>
            
            <div class="payment-actions">
                <button class="payment-button cancel-btn" id="cancelPayment">Batal</button>
                <button class="payment-button proceed-btn" id="proceedPayment">Lanjutkan Pembayaran</button>
            </div>
        </div>
    </div>

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
            
            // === Payment Modal Controls ===
            const premiumButton = document.getElementById('premiumButton');
            const paymentModal = document.getElementById('paymentModal');
            const closeModal = document.getElementById('closeModal');
            const cancelPayment = document.getElementById('cancelPayment');
            const proceedPayment = document.getElementById('proceedPayment');
            const freeButton = document.getElementById('freeButton');
            const paymentMethods = document.querySelectorAll('.payment-method');
            
            // Show payment modal
            if (premiumButton && paymentModal) {
                premiumButton.addEventListener('click', () => {
                    paymentModal.classList.add('active');
                });
            }
            
            // Close payment modal
            if (closeModal && paymentModal) {
                closeModal.addEventListener('click', () => {
                    paymentModal.classList.remove('active');
                });
                
                cancelPayment.addEventListener('click', () => {
                    paymentModal.classList.remove('active');
                });
            }
            
            // Payment method selection
            paymentMethods.forEach(method => {
                method.addEventListener('click', () => {
                    // Remove selected class from all methods
                    paymentMethods.forEach(m => m.classList.remove('selected'));
                    // Add selected class to the clicked method
                    method.classList.add('selected');
                });
            });
            
            // Proceed with payment
if (proceedPayment) {
    proceedPayment.addEventListener('click', () => {
        const selectedMethod = document.querySelector('.payment-method.selected');
        
        if (!selectedMethod) {
            Swal.fire({
                title: 'Pilih Metode Pembayaran',
                text: 'Silakan pilih metode pembayaran terlebih dahulu',
                icon: 'warning',
                confirmButtonColor: 'var(--accent)'
            });
            return;
        }
        
        const method = selectedMethod.dataset.method;
        
        // Redirect to payment confirmation page
        window.location.href = `payment_confirmation.php?method=${method}`;
    });
}

            
            // Simulate successful payment (for demo)
            function simulatePaymentSuccess() {
                Swal.fire({
                    title: 'Pembayaran Berhasil!',
                    text: 'Selamat! Kamu telah berhasil meng-upgrade ke PINTAR Premium. Nikmati semua fitur eksklusif sekarang!',
                    icon: 'success',
                    confirmButtonColor: 'var(--accent)',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to dashboard or reload to update status
                        window.location.href = 'dashboard.php';
                    }
                });
            }
            
            // Free plan button action
            if (freeButton) {
                freeButton.addEventListener('click', () => {
                    Swal.fire({
                        title: 'Fitur Gratis Diaktifkan',
                        text: 'Kamu sudah menggunakan fitur gratis. Tingkatkan ke PINTAR Premium untuk akses penuh!',
                        icon: 'info',
                        confirmButtonColor: 'var(--accent)'
                    });
                });
            }
            
            // === Fungsi untuk membuat bintang animasi ===
            function createStars() {
                const container = document.getElementById('stars');
                if (!container) return;
                
                const colors = ['#a367dc','#ff84e8','#6d42e6','#ffffff'];
                for (let i = 0; i < 50; i++) {
                    const star = document.createElement('div');
                    star.classList.add('star');
                    const x = Math.random()*100, y = Math.random()*100;
                    const size = Math.random()*3+1;
                    const color = colors[Math.floor(Math.random()*colors.length)];
                    const dur = Math.random()*4+1, delay = Math.random()*5;
                    Object.assign(star.style, {
                        left: `${x}%`, top: `${y}%`,
                        width: `${size}px`, height: `${size}px`,
                        backgroundColor: color,
                        boxShadow: `0 0 ${size*2}px ${color}`,
                        animationDuration: `${dur}s`,
                        animationDelay: `${delay}s`
                    });
                    container.appendChild(star);
                }
            }
        });
    </script>
</body>
</html>
