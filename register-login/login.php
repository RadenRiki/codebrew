<?php

// Include the connection file
include 'connection.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Login successful
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            header("Location: index.php");
            exit();
        } else {
            echo "Password salah!";
        }
    } else {
        echo "Username tidak ditemukan!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - CodeBrew</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="auth.css">
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

    <!-- Background blur elements -->
    <div class="blur-bg blur-1"></div>
    <div class="blur-bg blur-2"></div>
    <div class="blur-bg blur-3"></div>
    <div class="blur-bg blur-4"></div>

    <div class="auth-container">
        <!-- Logo -->
        <div class="logo-container">
            <img src="/assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" alt="CodeBrew Logo" class="logo">
        </div>

        <!-- Auth Form -->
        <div class="auth-form">
            <h1 class="auth-title">Selamat Datang Kembali!</h1>
            <p class="auth-subtitle">Lanjutkan petualangan coding-mu di CodeBrew</p>

            <!-- Social Login Buttons -->
            <div class="social-buttons">
                <button class="social-btn google-btn">
                    <img src="/assets/google-icon.png" alt="Google" class="social-icon">
                    <span>Lanjutkan dengan Google</span>
                </button>
                <button class="social-btn facebook-btn">
                    <img src="/assets/facebook-icon.png" alt="Facebook" class="social-icon">
                    <span>Lanjutkan dengan Facebook</span>
                </button>
            </div>

            <!-- Divider -->
            <div class="divider">
                <div class="divider-line"></div>
                <span class="divider-text">ATAU</span>
                <div class="divider-line"></div>
            </div>

            <!-- Login Form -->
            <form class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="loginEmail">E-Mail</label>
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

                <div class="form-options">
                    <label class="checkbox-label remember-me">
                        <input type="checkbox" id="rememberMe" name="rememberMe">
                        <span class="checkmark"></span>
                        Ingat saya
                    </label>
                    <a href="#" class="forgot-password">Lupa kata sandi?</a>
                </div>

                <button type="submit" class="auth-btn">Masuk</button>
            </form>

            <!-- Register Link -->
            <div class="auth-switch">
                <p>Belum punya akun? <a href="register.html" class="switch-link">Daftar di sini</a></p>
            </div>
        </div>
    </div>

    <script src="auth.js"></script>
</body>
</html>
