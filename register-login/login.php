<?php
// Include the connection file
include '../connection.php';
session_start();

// Redirect if already logged in
// Periksa role juga, agar admin/manager tidak langsung redirect ke homepage user
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/materi_bank.php");
    } elseif ($_SESSION['role'] === 'manager') {
        header("Location: ../manager/dashboard.php");
    } else {
        header("Location: ../homepage/index.php");
    }
    exit();
}

// Notifikasi
$notif = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi input tidak boleh kosong
    if (empty($email) || empty($password)) {
        $notif = "⚠️ Email dan password harus diisi!";
    } else {
        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $notif = "⚠️ Format email tidak valid!";
        } else {
            // Gunakan prepared statement untuk keamanan
            // Tambahkan kolom 'role' dalam SELECT
            $stmt = $conn->prepare("SELECT user_id, username, email, password, is_premium, xp_total, role, is_active FROM user WHERE email = ?");

            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();

                    // Verifikasi password
                    if ($user && password_verify($password, $user['password'])) {
                        if ($user['is_active'] == 0) {
                            // Akun nonaktif
                            $notif = "Akun Anda telah dinonaktifkan oleh admin.";
                        } else {
                            // Akun aktif, lanjutkan login
                            $_SESSION['user_id'] = $user['user_id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['email'] = $user['email'];
                            $_SESSION['is_premium'] = $user['is_premium'];
                            $_SESSION['xp_total'] = $user['xp_total'];
                            $_SESSION['role'] = $user['role']; // Simpan role di session

                            // Alert sukses dan redirect berdasarkan role
                            if ($user['role'] === 'admin') {
                                echo "<script>
                                    alert('✅ Login berhasil! Selamat datang, Admin " . htmlspecialchars($user['username']) . "!');
                                    window.location.href = '../admin/materi_bank.php'; // Arahkan ke dashboard admin
                                </script>";
                            } elseif ($user['role'] === 'manager') {
                                echo "<script>
                                    alert('✅ Login berhasil! Selamat datang, Manager " . htmlspecialchars($user['username']) . "!');
                                    window.location.href = '../manager/dashboard.php'; // Arahkan ke dashboard manager
                                </script>";
                            } else { // Default ke user biasa
                                echo "<script>
                                    alert('✅ Login berhasil! Selamat datang, " . htmlspecialchars($user['username']) . "!');
                                    window.location.href = '../homepage/index.php';
                                </script>";
                            }
                            exit();
                        }
                    } else {
                        // Password salah
                        $notif = "⚠️ Password salah!";
                    }
                } else {
                    // Email tidak ditemukan
                    $notif = "⚠️ Akun dengan email tersebut tidak ditemukan!";
                }
                $stmt->close();
            } else {
                $notif = "⚠️ Terjadi kesalahan database!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - CodeBrew</title>
    <link rel = "icon" type = "image/png" href = "../assets/LogoIcon.png">
    <style>
        footer {
            flex-shrink: 0;
            padding: 3rem 5% 2rem;
            background: var(--darker);
            margin-top: 3rem;
            position: relative;
            z-index: 1;
            color: var(--light-purple);
            font-size: 0.9rem;
        }


        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto 2rem;
        }

        .footer-col h3 {
            font-size: 1.2rem;
            margin-bottom: 1.2rem;
            color: var(--accent);
            font-weight: 600;
        }

        .footer-col ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .footer-col ul li {
            margin-bottom: 0.6rem;
        }

        .footer-col ul li a {
            color: var(--light-purple);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-col ul li a:hover {
            color: var(--light);
            text-decoration: underline;
        }

        .footer-col.pintar .pintar-badge {
            display: inline-block;
            background: var(--gradient);
            color: var(--light);
            padding: 0.6rem 1.2rem;
            border-radius: 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .footer-col.pintar .pintar-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(163, 103, 220, 0.3);
        }

        .copyright {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(93, 46, 142, 0.3);
            color: var(--light-purple);
            font-size: 0.85rem;
            opacity: 0.8;
        }

        /* Responsive */

        @media (max-width: 768px) {
            footer {
                padding: 2rem 5% 1.5rem;
                margin-top: 2rem;
            }

            .footer-content {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .footer-col h3 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 480px) {
            footer {
                padding: 1.5rem 1rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 1rem;
            }

            .footer-col.pintar .pintar-badge {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
        }
    </style>
    </style>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="auth.css">
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

    <!-- Background blur elements -->
    <div class="blur-bg blur-1"></div>
    <div class="blur-bg blur-2"></div>
    <div class="blur-bg blur-3"></div>
    <div class="blur-bg blur-4"></div>

    <div class="auth-container">
        <!-- Logo -->
        <div class="logo-container">
            <img src="../assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" alt="CodeBrew Logo" class="logo">
        </div>

        <!-- Auth Form -->
        <div class="auth-form">
            <h1 class="auth-title">Selamat Datang Kembali!</h1>
            <p class="auth-subtitle">Lanjutkan petualangan coding-mu di CodeBrew</p>

            <!-- Login Form -->
            <form class="login-form" id="loginForm" method="POST" onsubmit="return validateLoginForm()">
                <div class="form-group">
                    <label for="loginEmail">E-mail</label>
                    <input type="email" id="loginEmail" name="email" placeholder="Masukkan e-mail Anda" required>
                </div>

                <div class="form-group">
                    <label for="loginPassword">Kata Sandi</label>
                    <div class="password-input">
                        <input type="password" id="loginPassword" name="password" placeholder="Masukkan kata sandi" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('loginPassword')">
                            <i class="fas fa-eye" id="loginPassword-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Divider -->
                <div class="divider">
                    <div class="divider-line"></div>
                    <span class="divider-text">ATAU</span>
                    <div class="divider-line"></div>
                </div>

                <!-- Social Login Buttons -->
                <div class="social-buttons">
                    <button class="social-btn google-btn">
                        <img src="../assets/google-icon.png" alt="Google" class="social-icon">
                        <span>Lanjutkan dengan Google</span>
                    </button>
                    <button class="social-btn facebook-btn">
                        <img src="../assets/facebook-icon.png" alt="Facebook" class="social-icon">
                        <span>Lanjutkan dengan Facebook</span>
                    </button>
                </div>

                <button type="submit" class="auth-btn">Masuk</button>
            </form>

            <!-- Register Link -->
            <div class="auth-switch">
                <p>Belum punya akun? <a href="../register-login/register.php" class="switch-link">Daftar di sini</a></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <!-- <footer>
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
    </footer> -->

    <script src="auth.js"></script>
    <?php
    if (!empty($notif)) {
        echo "<script>alert('" . addslashes($notif) . "');</script>";
    }
    ?>


    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '-eye');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Validate login form
        function validateLoginForm() {
            const email = document.getElementById('loginEmail').value.trim();
            const password = document.getElementById('loginPassword').value;

            // Check empty fields
            if (!email || !password) {
                alert('⚠️ Email dan password harus diisi!');
                return false;
            }

            // Check email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('⚠️ Format email tidak valid!');
                return false;
            }

            return true;
        }
    </script>
</body>

</html>