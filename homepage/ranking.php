<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../connection.php';

$current_page = 'rangking.php';

// Cek apakah pengguna premium
$is_premium = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check_premium = mysqli_query($conn, "SELECT is_premium FROM user WHERE user_id = $user_id");
    if ($check_premium && $data = mysqli_fetch_assoc($check_premium)) {
        $is_premium = $data['is_premium'];
    }
}

// Ambil ranking
$ranking = mysqli_query($conn, "SELECT username, xp_total FROM user ORDER BY xp_total DESC LIMIT 10");
?>

<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Ranking - CodeBrew</title>
    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

        .ranking-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            margin: 12rem auto 5rem;
            gap: 2rem;
            max-width: 1200px;
            padding: 0 5%;
        }

        .ranking-table {
            flex: 2;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 2rem;
        }

        .ranking-table h1 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            font-weight: bold;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
        }

        thead {
            background: rgba(163, 103, 220, 0.4);
        }

        tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.02);
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .sidebar h2 {
            font-size: 1rem;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .cta-button {
            background: var(--gradient);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            color: white;
            font-weight: bold;
            display: inline-block;
            margin-top: 1rem;
            text-align: center;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(163, 103, 220, 0.3);
        }

        /* Profile Button */
        .profile-menu {
            position: relative;
        }

        .profile-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            border: 2px solid var(--light);
        }

        .profile-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(163, 103, 220, 0.5);
        }

        .profile-btn .avatar {
            font-size: 22px;
            color: var(--light);
        }

        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            background: rgba(26, 11, 46, 0.95);
            border: 1px solid rgba(93, 46, 142, 0.5);
            border-radius: 12px;
            width: 180px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            padding: 0.8rem 0;
            display: none;
            z-index: 100;
            animation: fadeInDown 0.3s ease;
        }

        /* Premium Profile Button */
        .premium-profile {
            background: linear-gradient(45deg, #ffd700, #ff84e8);
            border: 2px solid #ffd700;
            position: relative;
        }

        .premium-crown {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 16px;
            background: #ffd700;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--light);
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-dropdown::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 20px;
            width: 16px;
            height: 16px;
            background: rgba(26, 11, 46, 0.95);
            transform: rotate(45deg);
            border-left: 1px solid rgba(93, 46, 142, 0.5);
            border-top: 1px solid rgba(93, 46, 142, 0.5);
        }

        .profile-dropdown.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: var(--light);
            text-decoration: none;
            transition: background-color 0.2s;
            gap: 10px;
        }

        .dropdown-item i {
            font-size: 16px;
            color: var(--light-purple);
            width: 20px;
        }

        .dropdown-item:hover {
            background-color: rgba(93, 46, 142, 0.3);
        }

        .dropdown-divider {
            height: 1px;
            background: rgba(93, 46, 142, 0.5);
            margin: 0.5rem 0;
        }

        .logout-item {
            color: #ff6a7a;
        }

        .logout-item i {
            color: #ff6a7a;
        }
    </style>
</head>

<body>
    <!-- Background bintang -->
    <div class="stars" id="stars"></div>
    <img class="star-assets star1" src="../assets/—Pngtree—white light star twinkle light_7487663 1.png" alt="">
    <img class="star-assets star2" src="../assets/—Pngtree—white light star twinkle light_7487663 2.png" alt="">
    <img class="star-assets star3" src="../assets/—Pngtree—white light star twinkle light_7487663 3.png" alt="">

    <!-- Header -->
    <header>
        <a href="index.php" class="logo">
            <img src="../assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" alt="CodeBrew Logo"
                class="logo" />
        </a>
        <!-- Navigasi -->
        <nav>
            <ul>
                <li>
                    <a href="index.php" class="<?= $current_page == 'index.php' ? 'text-purple-400 border-b-2 border-purple-400 font-semibold pb-1' : 'text-white hover:text-purple-400' ?>">Beranda</a>
                </li>
                <li>
                    <a href="../bank_materi/belajar.php" class="<?= $current_page == 'belajar.php' ? 'text-purple-400 border-b-2 border-purple-400 font-semibold pb-1' : 'text-white hover:text-purple-400' ?>">Belajar</a>
                </li>
                <li>
                    <a href="kuis.php" class="<?= $current_page == 'kuis.php' ? 'text-purple-400 border-b-2 border-purple-400 font-semibold pb-1' : 'text-white hover:text-purple-400' ?>">Kuis</a>
                </li>
                <li>
                    <a href="../homepage/ranking.php" class="<?= $current_page == 'ranking.php' ? 'active' : '' ?>">Ranking</a>
                </li>
                <li>
                    <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'text-purple-400 border-b-2 border-purple-400 font-semibold pb-1' : 'text-white hover:text-purple-400' ?>">Dashboard</a>
                </li>

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

    <!-- Konten Ranking -->
    <div class="ranking-container">
        <div class="ranking-table">
            <h1>🏆 Ranking</h1>
            <p class="mb-4 text-light-purple">Semakin aktif kamu belajar, semakin tinggi posisimu. Naikkan peringkat dan buktikan kemampuanmu!</p>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Total XP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rank = 1;
                    if (mysqli_num_rows($ranking) > 0) {
                        while ($row = mysqli_fetch_assoc($ranking)) {
                            echo "<tr>";
                            echo "<td>" . $rank . "</td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['xp_total']) . " point</td>";
                            echo "</tr>";
                            $rank++;
                        }
                    } else {
                        echo "<tr><td colspan='3'>Belum ada data ranking.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="sidebar">
            <h2>Dapatkan point dari soal</h2>
            <p>Dapatkan poin dari setiap studi yang kamu selesaikan.<br>Ingin lebih banyak poin? Aktifkan Fitur Pintar dan tingkatkan progresmu lebih cepat!</p>
            <a href="kuis.php" class="cta-button">Mulai Kerjakan!</a>
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

    <!-- JavaScript untuk dropdown profil -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const profileBtn = document.getElementById("profileBtn");
            const profileDropdown = document.getElementById("profileDropdown");

            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener("click", function(e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle("show");
                });

                document.addEventListener("click", function(e) {
                    if (!profileDropdown.contains(e.target) && !profileBtn.contains(e.target)) {
                        profileDropdown.classList.remove("show");
                    }
                });
            }
        });
    </script>
</body>

</html>