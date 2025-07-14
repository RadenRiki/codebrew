<?php
session_start();
include '../connection.php'; // Sesuaikan path jika berbeda

// Pastikan user sudah login dan memiliki role 'manager'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header('Location: ../register-login/login.php'); // Arahkan kembali ke login jika tidak berhak
    exit;
}

// --- Fungsi untuk mengambil data dashboard ---

// Fungsi untuk mendapatkan total pengguna
function getTotalUsers($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_users FROM user");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['total_users'];
}

// Fungsi untuk mendapatkan total pengguna periode sebelumnya
function getTotalUsersPrevious($conn, $filter = 'monthly', $year = null) {
    $query = "";
    
    if ($filter == 'daily') {
        // Kemarin
        $query = "SELECT COUNT(*) AS total_users FROM user WHERE joined_date < CURDATE()";
    } elseif ($filter == 'last7days') {
        // 14 hari yang lalu sampai 7 hari yang lalu
        $query = "SELECT COUNT(*) AS total_users FROM user WHERE joined_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($filter == 'monthly') {
        if ($year) {
            // Total user sebelum tahun yang dipilih
            $query = "SELECT COUNT(*) AS total_users FROM user WHERE joined_date < '$year-01-01'";
        } else {
            // Bulan lalu
            $query = "SELECT COUNT(*) AS total_users FROM user WHERE joined_date < DATE_FORMAT(NOW(), '%Y-%m-01')";
        }
    } elseif ($filter == 'yearly') {
        // Tahun lalu
        $query = "SELECT COUNT(*) AS total_users FROM user WHERE joined_date < DATE_FORMAT(NOW(), '%Y-01-01')";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['total_users'] ?? 0;
}

// Fungsi untuk mendapatkan total pengguna premium
function getTotalPremiumUsers($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_premium_users FROM user WHERE is_premium = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['total_premium_users'];
}

// Fungsi untuk mendapatkan total pengguna premium periode sebelumnya
function getTotalPremiumUsersPrevious($conn, $filter = 'monthly', $year = null) {
    $query = "";
    
    if ($filter == 'daily') {
        $query = "SELECT COUNT(*) AS total_premium_users FROM user WHERE is_premium = 1 AND joined_date < CURDATE()";
    } elseif ($filter == 'last7days') {
        $query = "SELECT COUNT(*) AS total_premium_users FROM user WHERE is_premium = 1 AND joined_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($filter == 'monthly') {
        if ($year) {
            $query = "SELECT COUNT(*) AS total_premium_users FROM user WHERE is_premium = 1 AND joined_date < '$year-01-01'";
        } else {
            $query = "SELECT COUNT(*) AS total_premium_users FROM user WHERE is_premium = 1 AND joined_date < DATE_FORMAT(NOW(), '%Y-%m-01')";
        }
    } elseif ($filter == 'yearly') {
        $query = "SELECT COUNT(*) AS total_premium_users FROM user WHERE is_premium = 1 AND joined_date < DATE_FORMAT(NOW(), '%Y-01-01')";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['total_premium_users'] ?? 0;
}

// Fungsi untuk mendapatkan total penghasilan
function getTotalRevenue($conn) {
    $stmt = $conn->prepare("SELECT SUM(amount) AS total_revenue FROM payments WHERE payment_status = 'completed'");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['total_revenue'] ?? 0;
}

// Fungsi untuk mendapatkan total penghasilan periode sebelumnya
function getTotalRevenuePrevious($conn, $filter = 'monthly', $year = null) {
    $query = "";
    
    if ($filter == 'daily') {
        $query = "SELECT SUM(amount) AS total_revenue FROM payments WHERE payment_status = 'completed' AND created_at < CURDATE()";
    } elseif ($filter == 'last7days') {
        $query = "SELECT SUM(amount) AS total_revenue FROM payments WHERE payment_status = 'completed' AND created_at < DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($filter == 'monthly') {
        if ($year) {
            $query = "SELECT SUM(amount) AS total_revenue FROM payments WHERE payment_status = 'completed' AND created_at < '$year-01-01'";
        } else {
            $query = "SELECT SUM(amount) AS total_revenue FROM payments WHERE payment_status = 'completed' AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')";
        }
    } elseif ($filter == 'yearly') {
        $query = "SELECT SUM(amount) AS total_revenue FROM payments WHERE payment_status = 'completed' AND created_at < DATE_FORMAT(NOW(), '%Y-01-01')";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['total_revenue'] ?? 0;
}

// Fungsi untuk menghitung persentase perubahan
function calculatePercentageChange($current, $previous) {
    if ($previous == 0) {
        return $current > 0 ? "+100.0%" : "0.0%";
    }
    
    $change = (($current - $previous) / $previous) * 100;
    $formatted = number_format(abs($change), 1);
    
    if ($change > 0) {
        return "+{$formatted}%";
    } elseif ($change < 0) {
        return "-{$formatted}%";
    } else {
        return "0.0%";
    }
}

// Fungsi untuk mendapatkan daftar tahun yang tersedia
function getAvailableYears($conn) {
    $years = [];
    
    // Get years from user table
    $stmt = $conn->prepare("SELECT DISTINCT YEAR(joined_date) as year FROM user WHERE joined_date IS NOT NULL ORDER BY year DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $years[] = $row['year'];
    }
    
    // Get years from payments table
    $stmt = $conn->prepare("SELECT DISTINCT YEAR(created_at) as year FROM payments WHERE created_at IS NOT NULL ORDER BY year DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['year'], $years)) {
            $years[] = $row['year'];
        }
    }
    
    rsort($years); // Sort descending
    return $years;
}

// Fungsi helper untuk mengisi tanggal yang kosong dengan nilai 0
function fillMissingDates($data, $days = 7) {
    $filled = [];
    
    // Buat array dengan semua tanggal dalam 7 hari terakhir
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $filled[$date] = 0;
    }
    
    // Isi dengan data yang ada
    foreach ($data as $item) {
        if (isset($filled[$item['period']])) {
            $filled[$item['period']] = $item['count'] ?? $item['total_amount'] ?? 0;
        }
    }
    
    // Convert kembali ke format array
    $result = [];
    foreach ($filled as $date => $value) {
        $result[] = [
            'period' => $date,
            'count' => isset($data[0]['count']) ? $value : null,
            'total_amount' => isset($data[0]['total_amount']) ? $value : null
        ];
    }
    
    return $result;
}

// Fungsi helper untuk mengisi bulan yang kosong dengan nilai 0
function fillMissingMonths($data, $year) {
    $filled = [];
    
    // Buat array dengan semua bulan dalam tahun yang dipilih
    for ($month = 1; $month <= 12; $month++) {
        $monthKey = sprintf('%s-%02d', $year, $month);
        $filled[$monthKey] = 0;
    }
    
    // Isi dengan data yang ada
    foreach ($data as $item) {
        if (isset($filled[$item['period']])) {
            $filled[$item['period']] = $item['count'] ?? $item['total_amount'] ?? 0;
        }
    }
    
    // Convert kembali ke format array
    $result = [];
    foreach ($filled as $date => $value) {
        $result[] = [
            'period' => $date,
            'count' => isset($data[0]['count']) ? $value : null,
            'total_amount' => isset($data[0]['total_amount']) ? $value : null
        ];
    }
    
    return $result;
}

// --- Fungsi untuk mendapatkan data grafik berdasarkan filter ---
function getMonthlyRegistrations($conn, $filter = 'monthly', $year = null) {
    $data = [];
    $query = "";
    $groupBy = "";

    if ($filter == 'yearly') {
        $query = "SELECT DATE_FORMAT(joined_date, '%Y') AS period, COUNT(*) AS count FROM user ";
        $groupBy = "GROUP BY period ORDER BY period ASC";
    } elseif ($filter == 'daily') {
        $query = "SELECT DATE_FORMAT(joined_date, '%Y-%m-%d') AS period, COUNT(*) AS count FROM user ";
        $groupBy = "GROUP BY period ORDER BY period ASC";
    } elseif ($filter == 'last7days') {
        $query = "SELECT DATE_FORMAT(joined_date, '%Y-%m-%d') AS period, COUNT(*) AS count FROM user WHERE joined_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ";
        $groupBy = "GROUP BY period ORDER BY period ASC";
    } else { // monthly
        if ($year) {
            $query = "SELECT DATE_FORMAT(joined_date, '%Y-%m') AS period, COUNT(*) AS count FROM user WHERE YEAR(joined_date) = '$year' ";
        } else {
            $query = "SELECT DATE_FORMAT(joined_date, '%Y-%m') AS period, COUNT(*) AS count FROM user ";
        }
        $groupBy = "GROUP BY period ORDER BY period ASC";
    }

    $stmt = $conn->prepare($query . $groupBy);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    // Jika filter adalah last7days, isi tanggal yang kosong dengan 0
    if ($filter == 'last7days') {
        $data = fillMissingDates($data, 7);
    }
    
    // Jika filter adalah monthly dengan tahun tertentu, isi bulan yang kosong dengan 0
    if ($filter == 'monthly' && $year) {
        $data = fillMissingMonths($data, $year);
    }
    
    return $data;
}

function getMonthlyRevenue($conn, $filter = 'monthly', $year = null) {
    $data = [];
    $query = "";
    $groupBy = "";

    if ($filter == 'yearly') {
        $query = "SELECT DATE_FORMAT(created_at, '%Y') AS period, SUM(amount) AS total_amount FROM payments WHERE payment_status = 'completed' ";
        $groupBy = "GROUP BY period ORDER BY period ASC";
    } elseif ($filter == 'daily') {
        $query = "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS period, SUM(amount) AS total_amount FROM payments WHERE payment_status = 'completed' ";
        $groupBy = "GROUP BY period ORDER BY period ASC";
    } elseif ($filter == 'last7days') {
        $query = "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS period, SUM(amount) AS total_amount FROM payments WHERE payment_status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ";
        $groupBy = "GROUP BY period ORDER BY period ASC";
    } else { // monthly
        if ($year) {
            $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS period, SUM(amount) AS total_amount FROM payments WHERE payment_status = 'completed' AND YEAR(created_at) = '$year' ";
        } else {
            $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS period, SUM(amount) AS total_amount FROM payments WHERE payment_status = 'completed' ";
        }
        $groupBy = "GROUP BY period ORDER BY period ASC";
    }

    $stmt = $conn->prepare($query . $groupBy);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    // Jika filter adalah last7days, isi tanggal yang kosong dengan 0
    if ($filter == 'last7days') {
        $data = fillMissingDates($data, 7);
    }
    
    // Jika filter adalah monthly dengan tahun tertentu, isi bulan yang kosong dengan 0
    if ($filter == 'monthly' && $year) {
        $data = fillMissingMonths($data, $year);
    }
    
    return $data;
}

// Ambil filter dari request, default ke 'monthly'
$currentFilter = $_GET['filter'] ?? 'monthly';
$selectedYear = $_GET['year'] ?? date('Y'); // Default ke tahun ini

// Get available years
$availableYears = getAvailableYears($conn);

// Ambil data untuk dashboard
$totalUsers = getTotalUsers($conn);
$totalPremiumUsers = getTotalPremiumUsers($conn);
$totalRevenue = getTotalRevenue($conn);

// Ambil data periode sebelumnya
$totalUsersPrevious = getTotalUsersPrevious($conn, $currentFilter, ($currentFilter == 'monthly' ? $selectedYear : null));
$totalPremiumUsersPrevious = getTotalPremiumUsersPrevious($conn, $currentFilter, ($currentFilter == 'monthly' ? $selectedYear : null));
$totalRevenuePrevious = getTotalRevenuePrevious($conn, $currentFilter, ($currentFilter == 'monthly' ? $selectedYear : null));

// Hitung persentase perubahan
$userChange = calculatePercentageChange($totalUsers, $totalUsersPrevious);
$premiumChange = calculatePercentageChange($totalPremiumUsers, $totalPremiumUsersPrevious);
$revenueChange = calculatePercentageChange($totalRevenue, $totalRevenuePrevious);

// Tentukan class untuk perubahan (positive/negative)
$userChangeClass = strpos($userChange, '+') !== false ? 'positive' : (strpos($userChange, '-') !== false ? 'negative' : '');
$premiumChangeClass = strpos($premiumChange, '+') !== false ? 'positive' : (strpos($premiumChange, '-') !== false ? 'negative' : '');
$revenueChangeClass = strpos($revenueChange, '+') !== false ? 'positive' : (strpos($revenueChange, '-') !== false ? 'negative' : '');

// Data untuk grafik berdasarkan filter
$monthlyRegistrations = getMonthlyRegistrations($conn, $currentFilter, ($currentFilter == 'monthly' ? $selectedYear : null));
$monthlyRevenue = getMonthlyRevenue($conn, $currentFilter, ($currentFilter == 'monthly' ? $selectedYear : null));

// Data untuk chart jenis pengguna
$freeUsers = $totalUsers - $totalPremiumUsers;
$premiumUsers = $totalPremiumUsers;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Manajer - CodeBrew</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Inter", sans-serif;
        }

        body {
            background: #f9f9f9;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: #ffffff;
            padding: 1.5rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo img {
            width: 150px;
        }

        .user-profile-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .greeting {
            font-weight: 500;
            font-size: 16px;
            color: #333;
            white-space: nowrap;
        }

        .profile-menu {
            position: relative;
        }

        .profile-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .profile-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.2);
        }

        .profile-btn .avatar {
            font-size: 22px;
            color: #ffffff;
        }

        .profile-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 12px;
            width: 180px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 0.8rem 0;
            display: none;
            z-index: 100;
        }

        .profile-dropdown.show {
            display: block;
        }

        .profile-dropdown::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 20px;
            width: 16px;
            height: 16px;
            background: #ffffff;
            transform: rotate(45deg);
            border-left: 1px solid #ddd;
            border-top: 1px solid #ddd;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: #333;
            text-decoration: none;
            transition: background-color 0.2s;
            gap: 10px;
        }

        .dropdown-item i {
            font-size: 16px;
            color: #007bff;
            width: 20px;
        }

        .dropdown-item:hover {
            background-color: #f1f1f1;
        }

        .dropdown-divider {
            height: 1px;
            background: #ddd;
            margin: 0.5rem 0;
        }

        .logout-item {
            color: #ff6a7a;
        }

        .logout-item i {
            color: #ff6a7a;
        }

        /* Main Content */
        .dashboard-container {
            flex-grow: 1;
            padding: 3rem 5%;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .dashboard-title {
            font-size: 2.5rem;
            margin-bottom: 3rem;
            text-align: center;
            font-weight: 300;
            letter-spacing: -0.02em;
        }

        .kpi-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .kpi-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .kpi-card h3 {
            font-size: 1rem;
            color: #666;
            margin-bottom: 1rem;
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .kpi-card .value {
            font-size: 2.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .kpi-card .change {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .kpi-card .change.positive {
            color: #4CAF50;
        }

        .kpi-card .change.negative {
            color: #F44336;
        }

        .chart-section {
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #ddd;
        }

        .chart-section h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 400;
        }

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
        }

        /* Filter Controls */
        .filter-controls {
            text-align: center;
            margin-bottom: 3rem;
        }

        .filter-controls button {
            background: transparent;
            color: #007bff;
            border: 1px solid #007bff;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 0.5rem;
            font-size: 0.875rem;
        }

        .filter-controls button:hover {
            background: #007bff;
            color: #ffffff;
        }

        .filter-controls button.active {
            background: #007bff;
            color: #ffffff;
        }

        /* Year selector */
        .year-selector {
            display: inline-block;
            margin-left: 1rem;
        }

        .year-selector select {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            border: 1px solid #007bff;
            background: transparent;
            color: #007bff;
            font-weight: 500;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .year-selector select:hover {
            background: #007bff;
            color: #ffffff;
        }

        .year-selector select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        /* Footer */
        footer {
            padding: 2rem 5%;
            background: #ffffff;
            text-align: center;
            color: #666;
            font-size: 0.875rem;
            margin-top: auto;
            border-top: 1px solid #ddd;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header {
                padding: 1rem 3%;
            }
            .logo img {
                width: 120px;
            }
            .greeting {
                display: none;
            }
            .dashboard-title {
                font-size: 2rem;
            }
            .kpi-cards {
                grid-template-columns: 1fr;
            }
            .chart-grid {
                grid-template-columns: 1fr;
            }
            .chart-container {
                height: 300px;
            }
            .filter-controls button {
                margin: 0.25rem;
                font-size: 0.75rem;
                padding: 0.5rem 1rem;
            }
            .year-selector {
                display: block;
                margin-top: 1rem;
                margin-left: 0;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 2rem 3%;
            }
            .kpi-card .value {
                font-size: 2rem;
            }
            .chart-section h3 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <a href="#" class="logo">
            <img src="../assets/logodm.jpg" alt="CodeBrew logo - modern tech-style text logo with code brackets icon" />
        </a>
        <div class="user-profile-container">
            <span class="greeting">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <div class="profile-menu">
                <div class="profile-btn" id="profileBtn">
                    <i class="fas fa-user avatar"></i>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="../register-login/logout.php" class="dropdown-item logout-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="dashboard-container">
        <!-- <h1 class="dashboard-title">Dashboard Manajer</h1> -->

        <div class="kpi-cards">
            <div class="kpi-card">
                <h3>Total Pengguna</h3>
                <div class="value"><?php echo number_format($totalUsers, 0, ',', '.'); ?></div>
                <div class="change <?php echo $userChangeClass; ?>"><?php echo $userChange; ?> dari periode sebelumnya</div>
            </div>
            <div class="kpi-card">
                <h3>Pengguna Premium</h3>
                <div class="value"><?php echo number_format($totalPremiumUsers, 0, ',', '.'); ?></div>
                <div class="change <?php echo $premiumChangeClass; ?>"><?php echo $premiumChange; ?> dari periode sebelumnya</div>
            </div>
            <div class="kpi-card">
                <h3>Total Penghasilan</h3>
                <div class="value">Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></div>
                <div class="change <?php echo $revenueChangeClass; ?>"><?php echo $revenueChange; ?> dari periode sebelumnya</div>
            </div>
        </div>

        <div class="filter-controls">
            <button class="<?php echo ($currentFilter == 'daily') ? 'active' : ''; ?>" onclick="window.location.href='dashboard.php?filter=daily'">Harian</button>
            <button class="<?php echo ($currentFilter == 'last7days') ? 'active' : ''; ?>" onclick="window.location.href='dashboard.php?filter=last7days'">7 Hari Terakhir</button>
            <button class="<?php echo ($currentFilter == 'monthly') ? 'active' : ''; ?>" onclick="window.location.href='dashboard.php?filter=monthly&year=<?php echo $selectedYear; ?>'">Bulanan</button>
            <button class="<?php echo ($currentFilter == 'yearly') ? 'active' : ''; ?>" onclick="window.location.href='dashboard.php?filter=yearly'">Tahunan</button>
            
            <?php if ($currentFilter == 'monthly' && !empty($availableYears)): ?>
            <div class="year-selector" id="yearSelector" style="display: inline-block;">
                <select id="yearSelect" onchange="changeYear(this.value)">
                    <?php foreach ($availableYears as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == $selectedYear) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <div class="chart-grid">
            <div class="chart-section">
                <h3>Pendaftar Akun <?php echo ($currentFilter == 'monthly') ? "- Tahun $selectedYear" : ''; ?></h3>
                <div class="chart-container">
                    <canvas id="registrationChart"></canvas>
                </div>
            </div>

            <div class="chart-section">
                <h3>Distribusi Jenis Pengguna</h3>
                <div class="chart-container">
                    <canvas id="userTypeChart"></canvas>
                </div>
            </div>

            <div class="chart-section" style="grid-column: span 2;">
                <h3>Pendapatan <?php echo ($currentFilter == 'monthly') ? "- Tahun $selectedYear" : ''; ?></h3>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="copyright">Copyright © Wasabi 2025</div>
    </footer>

    <script>
        // Profile dropdown functionality
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

        // Year change function
        function changeYear(year) {
            window.location.href = 'dashboard.php?filter=monthly&year=' + year;
        }

        // Logout confirmation
        document.querySelectorAll('.logout-item').forEach(function(element) {
            element.addEventListener('click', function(event) {
                const yakin = confirm("Apakah Anda yakin ingin logout?");
                if (!yakin) {
                    event.preventDefault();
                }
            });
        });

        // Chart.js Configuration
        document.addEventListener('DOMContentLoaded', function() {
            // Data dari PHP
            const monthlyRegistrationsData = <?php echo json_encode($monthlyRegistrations); ?>;
            const monthlyRevenueData = <?php echo json_encode($monthlyRevenue); ?>;
            const freeUsersCount = <?php echo $freeUsers; ?>;
            const premiumUsersCount = <?php echo $premiumUsers; ?>;
            const currentFilter = '<?php echo $currentFilter; ?>';

            // Global Chart.js configuration for light theme
            Chart.defaults.font.family = 'Inter';
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#333';

            // Format labels untuk monthly filter
            function formatMonthLabel(period) {
                if (currentFilter === 'monthly') {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
                    const [year, month] = period.split('-');
                    return months[parseInt(month) - 1];
                }
                return period;
            }

            // --- Chart Pendaftar Akun ---
            const registrationLabels = monthlyRegistrationsData.map(item => formatMonthLabel(item.period));
            const registrationCounts = monthlyRegistrationsData.map(item => item.count || 0);

            const registrationCtx = document.getElementById('registrationChart').getContext('2d');
            new Chart(registrationCtx, {
                type: 'line',
                data: {
                    labels: registrationLabels,
                    datasets: [{
                        label: 'Jumlah Pendaftar',
                        data: registrationCounts,
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        borderColor: '#007bff',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#007bff',
                        pointBorderColor: '#007bff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#333',
                                padding: 10
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#333',
                                padding: 10
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#333',
                                font: {
                                    weight: 500
                                },
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            bodyFont: {
                                size: 13
                            }
                        }
                    }
                }
            });

            // --- Chart Distribusi Jenis Pengguna ---
            const userTypeCtx = document.getElementById('userTypeChart').getContext('2d');
            new Chart(userTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pengguna Gratis', 'Pengguna Premium'],
                    datasets: [{
                        data: [freeUsersCount, premiumUsersCount],
                        backgroundColor: [
                            '#007bff',
                            '#28a745'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#333',
                                font: {
                                    weight: 500,
                                    size: 13
                                },
                                padding: 25,
                                boxWidth: 15,
                                boxHeight: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    const total = freeUsersCount + premiumUsersCount;
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    label += context.parsed + ' (' + percentage + '%)';
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // --- Chart Pendapatan ---
            const revenueLabels = monthlyRevenueData.map(item => formatMonthLabel(item.period));
            const revenueAmounts = monthlyRevenueData.map(item => item.total_amount || 0);

            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: revenueLabels,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: revenueAmounts,
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderColor: '#28a745',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#28a745',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#333',
                                padding: 10,
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#333',
                                padding: 10
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#333',
                                font: {
                                    weight: 500
                                },
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#333',
                            bodyColor: '#333',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

</body>
</html>

            