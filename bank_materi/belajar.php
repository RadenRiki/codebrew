<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = 'belajar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../register-login/login.php");
    exit();
}

// Include database connection
include_once '../connection.php';

// cek user aktif
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cek_aktif = mysqli_query($conn, "SELECT is_active FROM user WHERE user_id = $user_id");
    if ($cek_aktif) {
        $data_aktif = mysqli_fetch_assoc($cek_aktif);
        if ($data_aktif['is_active'] == 0) {
            // Jika akun nonaktif, langsung logout dan redirect ke login
            session_destroy();
            header("Location: ../register-login/login.php?notif=nonaktif");
            exit();
        }
    }
}

// Cek apakah pengguna premium
$is_premium = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check_premium = mysqli_query($conn, "SELECT is_premium FROM user WHERE user_id = $user_id");
    if ($check_premium && $data = mysqli_fetch_assoc($check_premium)) {
        $is_premium = $data['is_premium'];
    }
}

// Get the selected category (if any)
$selectedCategory = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Get search query (if any)
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Build the SQL query based on filters
$sql = "SELECT * FROM materi_bank WHERE 1=1";

// Add category filter if selected
if (!empty($selectedCategory)) {
    $sql .= " AND kategori = '" . $conn->real_escape_string($selectedCategory) . "'";
}

// Add search filter if provided
if (!empty($searchQuery)) {
    $sql .= " AND (judul LIKE '%" . $conn->real_escape_string($searchQuery) . "%')";
}

// Order by category and title
$sql .= " ORDER BY kategori, judul";

// Execute the query
$result = $conn->query($sql);

// Get all available categories for the filter dropdown
$categoriesSql = "SELECT DISTINCT kategori FROM materi_bank ORDER BY kategori";
$categoriesResult = $conn->query($categoriesSql);
$categories = [];
if ($categoriesResult && $categoriesResult->num_rows > 0) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['kategori'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Materi - CodeBrew</title>
    <link rel = "icon" type = "image/png" href = "../assets/LogoIcon.png">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #5d2e8e;
            --primary-light: #a367dc;
            --accent: #ff84e8;
            --dark: #1a0b2e;
            --darker: #0d071b;
            --light: #ffffff;
            --light-purple: #c9b6e4;
            --gradient: linear-gradient(135deg, #5D2E8E 0%, #A367DC 100%);
            --gradient2: linear-gradient(90deg, var(--primary-light), var(--accent));
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
            min-height: 100vh;
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

        .logout-item {
            color: #ff6a7a;
        }

        .logout-item i {
            color: #ff6a7a;
        }

        /* Premium Navigation Badge */
        .premium-badge-nav {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #1a0b2e;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            animation: premiumGlow 2s ease-in-out infinite alternate;
        }

        @keyframes premiumGlow {
            from {
                box-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
            }

            to {
                box-shadow: 0 0 15px rgba(255, 215, 0, 0.8);
            }
        }

        /* Premium Profile Button */
        .premium-profile {
            background: linear-gradient(45deg, #ffd700, #ff84e8) !important;
            border: 2px solid #ffd700 !important;
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

        /* Premium Indicator in Greeting */
        .premium-indicator {
            color: #ffd700;
            margin-left: 5px;
            animation: sparkle 1.5s ease-in-out infinite;
        }

        @keyframes sparkle {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }
        }

        /* Premium Status in Dropdown */
        .premium-status {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: #ffd700;
            font-weight: 600;
            gap: 10px;
            background: rgba(255, 215, 0, 0.1);
        }

        .premium-status i {
            color: #ffd700;
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

        /* User Section */
        .user-profile-container {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .greeting {
            color: var(--light);
            font-weight: 500;
            font-size: 1rem;
        }

        .premium-indicator {
            color: #FFD700;
            margin-left: 0.5rem;
            filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.5));
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

        /* Profile Menu */
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


        /* Content area for demo */
        .content {
            padding: 2rem 5%;
            text-align: center;
        }

        .content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header {
                padding: 1rem 3%;
            }

            nav ul {
                gap: 1.5rem;
            }

            .greeting {
                display: none;
            }

            .logo {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            nav ul {
                gap: 1rem;
            }

            nav a {
                font-size: 0.9rem;
            }
        }

        /* Page Header */
        .page-header {
            background: var(--gradient);
            color: white;
            padding-top: 8rem;
            padding-bottom: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .page-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        /* Category Pills */
        .category-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .category-pill {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: var(--dark);
            background: #f0f0f0;
            border: 1px solid #e0e0e0;
        }

        .category-pill:hover,
        .category-pill.active {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .category-pill.active {
            color: white;
            background: var(--primary);
            border-color: var(--primary);
        }

        /* Course Cards */
        .material-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
            height: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .material-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .material-card .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            font-weight: 600;
            background: white;
        }

        .material-card .card-body {
            padding: 20px;
        }

        .material-card .material-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.5;
            height: 3rem;
        }

        .material-card .material-category {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .material-card .material-desc {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 4.5rem;
        }

        .material-card-footer {
            padding: 15px 20px;
            background: #f9f9f9;
            border-top: 1px solid #f0f0f0;
        }

        .material-link {
            display: inline-block;
            padding: 8px 20px;
            background: var(--gradient);
            color: white;
            border-radius: 5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
        }

        .material-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(93, 46, 142, 0.3);
            color: white;
        }

        /* Category Colors */
        .category-html {
            background-color: #E44D26;
            color: white;
        }

        .category-css {
            background-color: #2965f1;
            color: white;
        }

        .category-javascript {
            background-color: #F7DF1E;
            color: #333;
        }

        .category-python {
            background-color: #306998;
            color: white;
        }

        .category-php {
            background-color: #777BB3;
            color: white;
        }

        .category-mysql {
            background-color: #00758F;
            color: white;
        }

        /* Pagination */
        .pagination {
            margin-top: 30px;
            justify-content: center;
        }

        .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
        }

        .page-link {
            color: var(--primary);
        }

        /* Badge Styles */
        .badge-premium {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #333;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-free {
            background-color: #28a745;
            color: white;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state img {
            max-width: 250px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .empty-state p {
            color: #666;
            max-width: 500px;
            margin: 0 auto 20px;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .no-results h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .no-results p {
            color: #666;
            max-width: 500px;
            margin: 0 auto 20px;
        }

        /* Footer */
        footer {
            background: var(--darker);
            color: var(--light-purple);
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-col h3 {
            color: var(--accent);
            margin-bottom: 1.2rem;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .footer-col ul {
            list-style: none;
            padding: 0;
        }

        .footer-col ul li {
            margin-bottom: 0.8rem;
        }

        .footer-col ul li a {
            color: var(--light-purple);
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-col ul li a:hover {
            color: var(--light);
            padding-left: 5px;
        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--light-purple);
            font-size: 0.9rem;
            opacity: 0.7;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem 0;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .category-pills {
                justify-content: center;
            }

            .footer-content {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }
    </style>
</head>

<body>
    <!-- Stars animation background -->
    <div class="stars" id="stars"></div>

    <header>
        <a href="index.php" class="logo">
            <img src="../assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" alt="CodeBrew Logo"
                class="logo" />
        </a>
        <!-- Navigasi -->
        <nav>
            <ul>
                <li>
                    <a href="../homepage/index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Beranda</a>
                </li>
                <li>
                    <a href="../bank_materi/belajar.php" class="<?= $current_page == 'belajar.php' ? 'active' : '' ?>">Belajar</a>
                </li>
                <li>
                    <a href="../homepage/kuis.php" class="<?= $current_page == 'kuis.php' ? 'active' : '' ?>">Kuis</a>
                </li>
                <li>
                    <a href="../homepage/ranking.php" class="<?= $current_page == 'ranking.php' ? 'text-white hover:text-purple-400' : '' ?>">Ranking</a>
                </li>
                <li>
                    <a href="../homepage/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'text-white hover:text-purple-400' : '' ?>">Dashboard</a>
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
                    <a href="../homepage/" class="dropdown-item">
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

    <!-- Page Header -->
    <div class="page-header text-center">
        <div class="container">
            <h1>Bank Materi CodeBrew</h1>
            <p>Jelajahi berbagai referensi dan tutorial terbaik untuk meningkatkan kemampuan pemrograman Anda.</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row">
                <!-- Category Pills -->
                <div class="col-md-8 mb-3 mb-md-0">
                    <h5 class="mb-3">Filter Kategori:</h5>
                    <div class="category-pills">
                        <a href="belajar.php" class="category-pill <?php echo empty($selectedCategory) ? 'active' : ''; ?>">
                            Semua
                        </a>
                        <?php foreach ($categories as $category): ?>
                            <a href="belajar.php?kategori=<?php echo urlencode($category); ?>"
                                class="category-pill <?php echo $selectedCategory === $category ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($category); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="col-md-4">
                    <h5 class="mb-3">Cari Materi:</h5>
                    <form action="belajar.php" method="GET" class="d-flex">
                        <?php if (!empty($selectedCategory)): ?>
                            <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                        <?php endif; ?>
                        <input type="text" name="search" class="form-control me-2" placeholder="Cari judul materi..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Materials Section -->
        <div class="row">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()):
                    // Determine category class
                    $categoryClass = 'category-';
                    switch (strtolower($row['kategori'])) {
                        case 'html':
                            $categoryClass .= 'html';
                            $categoryIcon = 'fa-html5';
                            break;
                        case 'css':
                            $categoryClass .= 'css';
                            $categoryIcon = 'fa-css3-alt';
                            break;
                        case 'javascript':
                            $categoryClass .= 'javascript';
                            $categoryIcon = 'fa-js';
                            break;
                        case 'python':
                            $categoryClass .= 'python';
                            $categoryIcon = 'fa-python';
                            break;
                        case 'php':
                            $categoryClass .= 'php';
                            $categoryIcon = 'fa-php';
                            break;
                        case 'mysql':
                            $categoryClass .= 'mysql';
                            $categoryIcon = 'fa-database';
                            break;
                        default:
                            $categoryClass .= 'primary';
                            $categoryIcon = 'fa-code';
                    }
                ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card material-card">
                            <div class="card-body">
                                <span class="material-category <?php echo $categoryClass; ?>">
                                    <i class="fab <?php echo $categoryIcon; ?> me-1"></i>
                                    <?php echo htmlspecialchars($row['kategori']); ?>
                                </span>

                                <h3 class="material-title"><?php echo htmlspecialchars($row['judul']); ?></h3>

                                <p class="material-desc">
                                    Tutorial lengkap tentang <?php echo htmlspecialchars($row['judul']); ?> untuk membantu Anda memperdalam pemahaman tentang <?php echo htmlspecialchars($row['kategori']); ?>.
                                </p>

                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <!-- <div class="mb-2">
                                    <?php if (rand(0, 1) === 0 || !$_SESSION['is_premium']): ?>
                                    <span class="badge-free">
                                        <i class="fas fa-unlock"></i> Gratis
                                    </span>
                                    <?php else: ?>
                                    <span class="badge-premium">
                                        <i class="fas fa-crown"></i> Premium
                                    </span>
                                    <?php endif; ?>
                                </div> -->

                                    <a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank" class="material-link">
                                        <i class="fas fa-external-link-alt me-1"></i> Buka Materi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- No Results Found -->
                <div class="col-12">
                    <div class="no-results">
                        <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h3>Materi Tidak Ditemukan</h3>
                        <p>
                            Tidak ada materi yang sesuai dengan kriteria pencarian Anda.
                            Silakan coba dengan kata kunci lain atau kategori yang berbeda.
                        </p>
                        <a href="belajar.php" class="btn btn-primary">
                            <i class="fas fa-undo me-1"></i> Reset Filter
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination (if needed for future development) -->
        <?php if (isset($result) && $result->num_rows > 9): ?>
            <nav aria-label="Pagination">
                <ul class="pagination">
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        <?php endif; ?>


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
                <a href="../payment/premium.php" class="pintar-badge">Gabung di sini</a>
            </div>
        </div>
        <div class="copyright">Copyright ©️ CodeBrew 2025</div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Navbar -->
    <script>
        // Stars animation
        function createStars() {
            const starsContainer = document.getElementById('stars');
            const numberOfStars = 100;

            for (let i = 0; i < numberOfStars; i++) {
                const star = document.createElement('div');
                star.className = 'star';

                // Random position
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';

                // Random size
                const size = Math.random() * 3 + 1;
                star.style.width = size + 'px';
                star.style.height = size + 'px';

                // Random animation duration
                const duration = Math.random() * 3 + 2;
                star.style.setProperty('--duration', duration + 's');

                // Random delay
                star.style.animationDelay = Math.random() * 5 + 's';

                starsContainer.appendChild(star);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Create stars
            createStars();

            // Profile dropdown functionality
            const profileBtn = document.getElementById('profileBtn');
            const profileDropdown = document.getElementById('profileDropdown');

            if (profileBtn && profileDropdown) {
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

                // Prevent dropdown from closing when clicking inside
                profileDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });
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