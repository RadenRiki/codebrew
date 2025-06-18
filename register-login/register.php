<?php
// Include the connection file
include '../connection.php';

$notif = ''; // siapkan variabel notifikasi

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    // Cek inputan gak boleh kosong
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $notif = '⚠️ Semua field harus diisi!';
    }
    // Cek format email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $notif = '⚠️ Format email tidak valid!';
    }
    // Cek password sama atau nggak
    elseif ($password !== $confirmPassword) {
        $notif = '⚠️ Password dan konfirmasi tidak cocok!';
    }
    // Cek apakah username/email sudah dipakai
    else {
        $check_query = "SELECT * FROM user WHERE email = '$email' OR username = '$username'";
        $check_result = mysqli_query($conn, $check_query);

        if (!$check_result) {
            $notif = '⚠️ Terjadi kesalahan database!';
        } elseif (mysqli_num_rows($check_result) > 0) {
            $existing_user = mysqli_fetch_assoc($check_result);
            if ($existing_user['email'] == $email) {
                $notif = '⚠️ Email sudah terdaftar!';
            } else {
                $notif = '⚠️ Username sudah digunakan!';
            }
        } else {
            // Hash password dan insert data
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO user (username, email, password, is_premium, xp_total) 
                             VALUES ('$username', '$email', '$hashed_password', 0, 0)";

            if (mysqli_query($conn, $insert_query)) {
                echo "<script>alert('✅ Registrasi berhasil! Silakan login.'); window.location.href = '../register-login/login.php';</script>";
                exit();
            } else {
                $notif = '⚠️ Terjadi kesalahan saat registrasi: ' . mysqli_error($conn);
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
    <title>Daftar - CodeBrew</title>
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
            <h1 class="auth-title">Daftar Sekarang</h1>
            <p class="auth-subtitle">Mulai petualangan coding-mu bersama CodeBrew!</p>

            <!-- Registration Form -->
            <form class="register-form" id="registerForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required>
                </div>

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan e-mail Anda" required>
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Konfirmasi Kata Sandi</label>
                    <div class="password-input">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Konfirmasi kata sandi" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                            <i class="fas fa-eye" id="confirmPassword-eye"></i>
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

                <button type="submit" class="auth-btn">Daftar Sekarang</button>
            </form>

            <!-- Login Link -->
            <div class="auth-switch">
                <p>Sudah punya akun? <a href="../register-login/login.php" class="switch-link">Masuk di sini</a></p>
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

    <script src="auth.js"></script>

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

    // Validate form before submission
    function validateForm() {
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const terms = document.getElementById('terms').checked;

        // Check empty fields
        if (!username || !email || !password || !confirmPassword) {
            alert('⚠️ Semua field harus diisi!');
            return false;
        }

        // Check username length
        if (username.length < 3) {
            alert('⚠️ Username minimal 3 karakter!');
            return false;
        }

        // Check email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('⚠️ Format email tidak valid!');
            return false;
        }

        // Check password match
        if (password !== confirmPassword) {
            alert('⚠️ Password dan konfirmasi tidak cocok!');
            return false;
        }

        if (password !== confirmPassword) {
            alert('⚠️ Password dan konfirmasi tidak cocok!');
            document.getElementById('password').style.borderColor = 'red';
            document.getElementById('confirmPassword').style.borderColor = 'red';
            return false;
        } else {
            document.getElementById('password').style.borderColor = '';
            document.getElementById('confirmPassword').style.borderColor = '';
        }
    }
    </script>

    <?php
        if (!empty($notif)) {
            echo "<script>alert('" . addslashes($notif) . "');</script>";
        }
    ?>
</body>
</html>
