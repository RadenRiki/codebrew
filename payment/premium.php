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
    <link rel = "icon" type = "image/png" href = "../assets/LogoIcon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="index.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Premium Page Specific CSS -->
    <style>
        :root {
            --primary: #652d86;
            --primary-light: #a367dc;
            --accent: #cf7dff;
            --purple-light: #e6baff;
            --dark-bg: #09020f;
            --card-bg: rgba(17, 7, 27, 0.75);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #fff;
            overflow-x: hidden;
        }

        /* Header Styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 15px 50px;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            border-bottom: 1px solid rgba(207, 125, 255, 0.2);
        }

        .logo img {
            height: 35px;
            width: auto;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: var(--accent);
        }

        .user-profile-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .greeting {
            color: var(--purple-light);
            font-weight: 500;
        }

        .profile-menu {
            position: relative;
        }

        .profile-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(207, 125, 255, 0.5);
        }

        .avatar {
            color: #fff;
            font-size: 18px;
        }

        .profile-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            background: rgba(26, 11, 46, 0.95);
            border: 1px solid var(--accent);
            border-radius: 10px;
            min-width: 200px;
            backdrop-filter: blur(10px);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .profile-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #fff;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .dropdown-item:hover {
            background: rgba(207, 125, 255, 0.2);
        }

        .dropdown-divider {
            height: 1px;
            background: rgba(207, 125, 255, 0.3);
            margin: 5px 0;
        }

        .logout-item {
            color: #ff6b6b;
        }

        /* Stars Animation */
        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        .star {
            position: absolute;
            border-radius: 50%;
            animation: twinkle 2s infinite alternate;
        }

        @keyframes twinkle {
            0% {
                opacity: 0.3;
                transform: scale(1);
            }

            100% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        /* Premium Page Styles */
        .premium-page {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary), var(--dark-bg) 77.4%);
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
            background: linear-gradient(180deg, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 0.01em;
            animation: titleGlow 3s ease-in-out infinite alternate;
        }

        @keyframes titleGlow {
            from {
                text-shadow: 0 0 20px rgba(207, 125, 255, 0.3);
            }

            to {
                text-shadow: 0 0 30px rgba(207, 125, 255, 0.6), 0 0 40px rgba(207, 125, 255, 0.4);
            }
        }

        .plans-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .plan-card {
            flex: 1 1 380px;
            max-width: 460px;
            min-height: 650px;
            border-radius: 30px;
            background: var(--card-bg);
            padding: 40px 30px;
            position: relative;
            display: flex;
            flex-direction: column;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(15px);
        }

        .plan-card:hover {
            transform: translateY(-10px) scale(1.02);
        }

        .free-plan {
            border: 2px solid #444;
        }

        .free-plan:hover {
            border-color: rgba(95, 80, 109, 0.75);
            box-shadow: 0 20px 40px rgba(163, 103, 220, 0.2);
        }

        .premium-plan {
            border: 2px solid var(--accent);
            box-shadow: 0 0 30px rgba(207, 125, 255, 0.2);
            position: relative;
            overflow: hidden; /* Penting untuk shine effect */
        }

        .premium-plan:hover {
            box-shadow: 0 20px 50px rgba(207, 125, 255, 0.4);
        }

        .premium-plan::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                rgba(207, 125, 255, 0.3),
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: rotate(45deg);
            animation: shine 3s infinite;
            pointer-events: none;
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

        .plan-name {
            font-size: 22px;
            font-weight: 600;
            color: var(--purple-light);
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .plan-price {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 8px;
            line-height: 1;
        }

        .free-price {
            color: #fff;
        }

        .premium-price {
            background: linear-gradient(180deg, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .plan-price-amount {
            font-size: 20px;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .price-period {
            font-size: 14px;
            color: #999;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .plan-description {
            margin-bottom: 25px;
            font-size: 15px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.85);
        }

        .features-list {
            padding-left: 25px;
            margin-bottom: 30px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: 500;
            color: #fff;
        }

        .feature-icon {
            font-size: 20px;
            color: #ff00ff;
            position: relative;
        }


        .plan-button {
            margin-top: auto;
            width: 100%;
            height: 60px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
            gap: 10px;
        }

        .free-button {
            background: transparent;
            border: 2px solid var(--accent);
            color: var(--purple-light);
        }

        .free-button:hover {
            background: rgba(207, 125, 255, 0.1);
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(207, 125, 255, 0.3);
        }

        .premium-button {
    background: linear-gradient(135deg, var(--primary-light), var(--accent));
    color: #fff;
    border: 2px solid var(--accent);
    box-shadow: 0 8px 25px rgba(207, 125, 255, 0.3);
    position: relative;
    overflow: hidden;
}

.premium-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.4),
        transparent
    );
    transition: left 0.5s;
}

.premium-button:hover::before {
    left: 100%;
}

        .premium-button:hover {
            background: linear-gradient(135deg, var(--accent), #ff84e8);
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(207, 125, 255, 0.5);
        }

        .button-icon {
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .plan-button:hover .button-icon {
            transform: translateX(3px);
        }

        /* Payment Modal */
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
            backdrop-filter: blur(5px);
        }

        .payment-modal.active {
            display: flex;
            animation: fadeIn 0.3s forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .payment-content {
            background: var(--card-bg);
            border: 2px solid var(--accent);
            border-radius: 20px;
            width: 500px;
            max-width: 90%;
            padding: 30px;
            position: relative;
            backdrop-filter: blur(15px);
        }

        .payment-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .payment-title {
            font-size: 24px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 10px;
        }

        .payment-subtitle {
            font-size: 16px;
            color: var(--purple-light);
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
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            background: rgba(93, 46, 142, 0.4);
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .payment-method.selected {
            background: rgba(93, 46, 142, 0.6);
            border-color: var(--accent);
            box-shadow: 0 0 20px rgba(207, 125, 255, 0.3);
        }

        .method-icon {
            width: 40px;
            height: 40px;
            background: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .method-icon img {
            max-width: 28px;
            max-height: 28px;
        }

        .method-name {
            font-size: 16px;
            font-weight: 500;
            color: #fff;
        }

        .payment-actions {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .payment-button {
            flex: 1;
            padding: 12px 20px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .cancel-btn {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--purple-light);
        }

        .cancel-btn:hover {
            background: rgba(207, 125, 255, 0.1);
            color: #fff;
        }

        .proceed-btn {
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            color: #fff;
        }

        .proceed-btn:hover {
            background: linear-gradient(135deg, var(--accent), #ff84e8);
            transform: translateY(-2px);
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            color: var(--purple-light);
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 8px;
            border-radius: 50%;
        }

        .close-modal:hover {
            color: #fff;
            background: rgba(207, 125, 255, 0.2);
            transform: rotate(90deg);
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
                <li><a href="../homepage/index.php">Beranda</a></li>
                <li><a href="../bank_materi/belajar.php">Belajar</a></li>
                <li><a href="../homepage/kuis.php">Kuis</a></li>
                <li><a href="ranking.php">Ranking</a></li>
                <li><a href="premium.php">Dashboard</a></li>
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
                    <a href="../homepage/profile.php" class="dropdown-item">
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
                    Ayo mulai perjalanan belajar pemrograman kamu tanpa biaya!
                    Dengan fitur Penjelajah Gratis, kamu punya kesempatan untuk mengeksplorasi
                    dunia coding dengan santai dan menyenangkan, dengan fitur: </p>

                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-check feature-icon"></i>
                        <span>Mengerjakan kuis dasar</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check feature-icon"></i>
                        <span>Mengakses link materi berbagai bahasa pemrograman</span>
                    </div>
                </div>

                <button class="plan-button free-button" id="freeButton">
                    <span>Mulai secara gratis</span>
                    <i class="fas fa-arrow-right button-icon"></i>
                </button>
            </div>

            <!-- Premium Plan Card -->
            <div class="plan-card premium-plan">
                <div class="plan-name">Premium</div>
                <div class="plan-price premium-price">PINTAR</div>
                <div class="plan-price-amount">Rp 100.000</div>
                <div class="price-period">/ Sekali bayar selamanya</div>

                <p class="plan-description">
                    Dapatkan akses penuh ke berbagai fitur keren yang bakal membantu 
                    belajar pemrograman kamu lebih jauh lagi!
                    Dengan berlangganan paket Premium PINTAR, kamu bisa menikmati: </p>

                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-crown feature-icon"></i>
                        <span>Mengakses kuis lanjutan</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line feature-icon"></i>
                        <span>Memantau progress latihan</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-code feature-icon"></i>
                        <span>Mengakses link materi berbagai bahasa pemrograman</span>
                    </div>
                </div>

                <button class="plan-button premium-button" id="premiumButton">
                    <span>Upgrade ke PINTAR Premium</span>
                    <i class="fas fa-rocket button-icon"></i>
                </button>
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
                <p class="payment-subtitle">Pilih metode pembayaran - Rp 100.000 (sekali bayar)</p>
            </div>

            <div class="payment-methods">
                <div class="payment-method" data-method="qris">
                    <div class="method-icon">
                        <i class="fas fa-qrcode" style="color: #5d2e8e; font-size: 20px;"></i>
                    </div>
                    <span class="method-name">QRIS</span>
                </div>

                <div class="payment-method" data-method="gopay">
                    <div class="method-icon">
                        <i class="fas fa-mobile-alt" style="color: #00AED6; font-size: 20px;"></i>
                    </div>
                    <span class="method-name">GoPay</span>
                </div>

                <div class="payment-method" data-method="ovo">
                    <div class="method-icon">
                        <i class="fas fa-wallet" style="color: #4C3BCF; font-size: 20px;"></i>
                    </div>
                    <span class="method-name">OVO</span>
                </div>

                <div class="payment-method" data-method="bankTransfer">
                    <div class="method-icon">
                        <i class="fas fa-university" style="color: #5d2e8e; font-size: 20px;"></i>
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
                    <li><a href="../bank_materi/belajar.php">HTML</a></li>
                    <li><a href="../bank_materi/belajar.php">CSS</a></li>
                    <li><a href="../bank_materi/belajar.php">JavaScript</a></li>
                    <li><a href="../bank_materi/belajar.php">Python</a></li>
                    <li><a href="../bank_materi/belajar.php">PHP</a></li>
                    <li><a href="../bank_materi/belajar.php">MySQL</a></li>
                </ul>
            </div>

            <div class="footer-col pintar">
                <h3>PINTAR</h3>
                <div> <a class="pintar-badge" href="premium.php">Gabung di sini</a></div>
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

            // Click outside modal to close
            paymentModal.addEventListener('click', (e) => {
                if (e.target === paymentModal) {
                    paymentModal.classList.remove('active');
                }
            });

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
    </script>
</body>

</html>