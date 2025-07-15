<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../register-login/login.php");
    exit();
}

// Include database connection
include_once '../connection.php';

// Nonaktifkan akun
if (isset($_GET['nonaktifkan'])) {
    $id = (int)$_GET['nonaktifkan'];
    $conn->query("UPDATE user SET is_active = 0 WHERE user_id = $id");
    header("Location: users.php");
    exit();
}

// Aktifkan akun
if (isset($_GET['aktifkan'])) {
    $id = (int)$_GET['aktifkan'];
    $conn->query("UPDATE user SET is_active = 1 WHERE user_id = $id");
    header("Location: users.php");
    exit();
}

// Konfigurasi pagination
$limit = 15; // jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // ambil halaman dari URL
$offset = ($page - 1) * $limit; // hitung offset

// Ambil total user
$where = [];
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where[] = "(username LIKE '%$search%' OR email LIKE '%$search%')";
}
if (!empty($_GET['status'])) {
    if ($_GET['status'] == 'premium') {
        $where[] = "is_premium = 1";
    } elseif ($_GET['status'] == 'gratis') {
        $where[] = "is_premium = 0";
    }
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$total_users_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user $where_sql");
$total_users = mysqli_fetch_assoc($total_users_result)['total'];
$total_pages = ceil($total_users / $limit);

// Ambil data user sesuai halaman
$daftaruser = mysqli_query($conn, "SELECT * FROM user $where_sql LIMIT $limit OFFSET $offset");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kuis - Admin CodeBrew</title>
    <link rel = "icon" type = "image/png" href = "../assets/LogoIcon.png">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS (from materi_bank.php) -->

    <style>
        :root {
            --primary: #5D2E8E;
            --secondary: #A367DC;
            --accent: #FF7F50;
            --dark: #1A1A2E;
            --darker: #121225;
            --light: #F5F5F7;
            --light-purple: #D1C4E9;
            --gradient: linear-gradient(135deg, #5D2E8E 0%, #A367DC 100%);
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
            display: flex;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: var(--darker);
            color: var(--light);
            height: 100vh;
            position: fixed;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            background: var(--dark);
            text-align: center;
        }

        .sidebar-header img {
            max-width: 150px;
        }

        .sidebar-menu {
            padding: 20px 0;
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: var(--light-purple);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--primary);
            color: var(--light);
            border-left: 4px solid var(--accent);
        }

        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        .content-header {
            margin-bottom: 30px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 15px;
        }

        .content-header h1 {
            font-size: 1.8rem;
            color: var(--primary);
            font-weight: 600;
        }

        .content-header .breadcrumb {
            font-size: 0.85rem;
        }

        /* Card Styles */
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: none;
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 15px 20px;
            font-weight: 600;
            color: var(--primary);
        }

        .card-body {
            padding: 20px;
        }

        /* Form Styles */
        .form-label {
            font-weight: 500;
            color: #555;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
        }

        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(163, 103, 220, 0.25);
        }

        .btn-primary {
            background: var(--gradient);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(93, 46, 142, 0.3);
        }

        /* Table Styles */
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            background-color: #f8f9fa;
            color: var(--primary);
            font-weight: 600;
            border-bottom: 2px solid #e0e0e0;
        }

        .table-action-btn {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
        }

        /* Alert Styles */
        .alert {
            border-radius: 8px;
            padding: 15px 20px;
        }

        /* Badge Styles */
        .badge{
            padding: 6px 10px;
            border-radius: 5px;
            font-weight: 500;
        }

        .badge-admin {
            background-color: var(--primary);
            color: white;
            padding: 6px 10px;
            border-radius: 5px;
        }

        .badge-html {
            background-color: #E44D26;
            color: white;
        }

        .badge-css {
            background-color: #2965f1;
            color: white;
        }

        .badge-javascript {
            background-color: #F7DF1E;
            color: #333;
        }

        .badge-python {
            background-color: #306998;
            color: white;
        }

        .badge-php {
            background-color: #777BB3;
            color: white;
        }

        .badge-mysql {
            background-color: #00758F;
            color: white;
        }

        .badge-premium {
            background-color: var(--accent);
            color: white;
        }

        .badge-free {
            background-color: #28a745;
            color: white;
        }

        .pagination-container{
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        .pagination .page-link {
        color: #5D2E8E;
        }

        .pagination .page-link:hover {
        color: #5D2E8E;
        }

        .pagination .page-item.active .page-link {
        color: #ffff;
        background-color: #5D2E8E;
        border-color: #5D2E8E;
        }

        .pagination .page-link.prev, .pagination .page-link.next {
        color: #5D2E8E;
        }

        .pagination .page-link.prev:hover, .pagination .page-link.next:hover {
        color: #5D2E8E;
        }

        .angka-rapih {
            text-align: center;
            min-width: 80px;
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            letter-spacing: 1px;
        }


        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                text-align: center;
            }

            .sidebar-header img {
                max-width: 40px;
            }

            .sidebar-menu a span {
                display: none;
            }

            .sidebar-menu a i {
                margin-right: 0;
                font-size: 1.2rem;
            }

            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" alt="CodeBrew Logo">
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="materi_bank.php">
                    <i class="fas fa-book"></i> <span>Bank Materi</span>
                </a>
            </li>
            <li>
                <a href="manage_quiz.php">
                    <i class="fas fa-question-circle"></i> <span>Kelola Kuis</span>
                </a>
            </li>
            <li>
                <a href="users.php" class="active">
                    <i class="fas fa-users"></i> <span>Pengguna</span>
                </a>
            </li>
            <li>
                <a href="../register-login/logout.php" id="logout-link">
                    <i class="fas fa-sign-out-alt"></i> <span>Keluar</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Content Header -->
        <div class="content-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1>Kelola Pengguna</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" aria-current="page">Pengguna</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <span class="badge-admin">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Form Pencarian & Filter -->
        <form method="GET" class="row mb-4">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari username/email..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="premium" <?= (isset($_GET['status']) && $_GET['status'] == 'premium') ? 'selected' : '' ?>>Premium</option>
                    <option value="gratis" <?= (isset($_GET['status']) && $_GET['status'] == 'gratis') ? 'selected' : '' ?>>Gratis</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Cari</button>
            </div>
            <div class="col-md-2">
                <a href="users.php" class="btn btn-secondary w-100">Refresh</a>
            </div>
        </form>


        <!-- Data Table Card: Daftar Kuis -->
        <div class="card mb-4">
            <div class="card-header">
                Daftar Pengguna
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($daftaruser) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px; text-align:center;">No</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th style="width: 110px; text-align:center;">Status Akun</th>
                                    <th style="width: 110px; text-align:center;">Tipe</th>
                                    <th class="text-center" style="width: 100px;">Total XP</th>
                                    <th style="width: 180px; text-align:center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = $offset + 1;
                                while ($user = mysqli_fetch_assoc($daftaruser)):
                                    $badgeClass = $user['is_premium'] ? 'badge bg-success' : 'badge bg-secondary';
                                    $statusLabel = $user['is_premium'] ? 'Premium' : 'Gratis';
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="text-center">
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($user['is_premium']): ?>
                                                <span class="badge badge-premium">Premium</span>
                                            <?php else: ?>
                                                <span class="badge badge-free">Gratis</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="angka-rapih">
                                            <div><?php echo number_format($user['xp_total'], 0, ',', '.'); ?></div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <?php if ($user['is_active']): ?>
                                                    <a href="users.php?nonaktifkan=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Nonaktifkan akun ini?')">
                                                        <i class="fas fa-user-slash"></i> Nonaktifkan
                                                    </a>
                                                <?php else: ?>
                                                    <a href="users.php?aktifkan=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Aktifkan kembali akun ini?')">
                                                        <i class="fas fa-user-check"></i> Aktifkan
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> Belum ada pengguna yang terdaftar.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Pagination -->
        <div class="pagination-container mt-4 mb-4">
            <nav>
                <ul class="pagination justify-content-center">
                    <?php
                    $show = 2; // jumlah halaman sebelum/sesudah current
                    $start = max(1, $page - $show);
                    $end = min($total_pages, $page + $show);
                    if ($page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '">Sebelumnya</a></li>';
                    }
                    if ($start > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                        if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    for ($i = $start; $i <= $end; $i++) {
                        $active = $i == $page ? 'active' : '';
                        echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                    }
                    if ($end < $total_pages) {
                        if ($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                    }
                    if ($page < $total_pages) {
                        echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . '">Berikutnya</a></li>';
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>


    <script>
        function confirmDeleteUser(userId) {
            if (confirm("Apakah kamu yakin ingin menghapus pengguna ini?")) {
                window.location.href = "delete_user.php?user_id=" + userId;
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var logoutLink = document.getElementById('logout-link');
            if (logoutLink) {
                logoutLink.addEventListener('click', function(e) {
                    var yakin = confirm('Apakah Anda yakin ingin logout?');
                    if (!yakin) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>

</body>

</html>