<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../register-login/login.php");
    exit();
}

// Include database connection
include_once '../connection.php';

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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
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
        }
        
        /* Navbar Styles */
        .navbar {
            background-color: var(--darker);
            padding: 0.8rem 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .navbar-dark .navbar-nav .nav-link {
            color: var(--light-purple);
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }
        
        .navbar-dark .navbar-nav .nav-link:hover,
        .navbar-dark .navbar-nav .nav-link.active {
            color: var(--light);
        }
        
        .navbar-dark .navbar-nav .nav-link.premium {
            color: var(--accent);
        }
        
        .navbar .profile-img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--secondary);
        }
        
        /* Page Header */
        .page-header {
            background: var(--gradient);
            color: white;
            padding: 2rem 0;
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
        
        .category-pill:hover, .category-pill.active {
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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../homepage/index.php">
                <img src="../assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" alt="CodeBrew Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../homepage/index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="bank_materi.php">Materi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../quiz/index.php">Kuis</a>
                    </li>
                    <?php if ($_SESSION['is_premium']): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard/index.php">Dashboard</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link premium" href="../premium/index.php">
                            <i class="fas fa-crown"></i> Premium
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <?php if (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])): ?>
                                <img src="<?php echo $_SESSION['profile_pic']; ?>" alt="Profile" class="profile-img me-2">
                            <?php else: ?>
                                <i class="fas fa-user-circle me-2" style="font-size: 1.5rem;"></i>
                            <?php endif; ?>
                            <span class="d-none d-md-block"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../profile/index.php"><i class="fas fa-user me-2"></i> Profil</a></li>
                            <li><a class="dropdown-item" href="../settings/index.php"><i class="fas fa-cog me-2"></i> Pengaturan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

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
                        <a href="bank_materi.php" class="category-pill <?php echo empty($selectedCategory) ? 'active' : ''; ?>">
                            Semua
                        </a>
                        <?php foreach ($categories as $category): ?>
                            <a href="bank_materi.php?kategori=<?php echo urlencode($category); ?>" 
                               class="category-pill <?php echo $selectedCategory === $category ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($category); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Search Box -->
                <div class="col-md-4">
                    <h5 class="mb-3">Cari Materi:</h5>
                    <form action="bank_materi.php" method="GET" class="d-flex">
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
                                <div class="mb-2">
                                    <?php if (rand(0, 1) === 0 || !$_SESSION['is_premium']): ?>
                                    <span class="badge-free">
                                        <i class="fas fa-unlock"></i> Gratis
                                    </span>
                                    <?php else: ?>
                                    <span class="badge-premium">
                                        <i class="fas fa-crown"></i> Premium
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
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
                        <a href="bank_materi.php" class="btn btn-primary">
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
        
        <!-- Premium CTA Banner (for free users) -->
        <?php if (!$_SESSION['is_premium']): ?>
        <div class="card mt-5 mb-5">
            <div class="card-body p-4 text-center">
                <div class="row align-items-center">
                    <div class="col-md-8 text-md-start">
                        <h4 class="mb-3" style="color: var(--primary);"><i class="fas fa-crown text-warning me-2"></i>Akses Semua Materi Premium</h4>
                        <p class="mb-md-0">
                            Tingkatkan ke Premium sekarang untuk membuka semua materi eksklusif dan fitur tambahan!
                        </p>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <a href="../premium/index.php" class="btn btn-warning btn-lg fw-bold">
                            <i class="fas fa-crown me-2"></i> Upgrade ke Premium
                        </a>
                    </div>
                </div>
            </div>
        </div>
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
                    <li><a href="bank_materi.php?kategori=HTML">HTML</a></li>
                    <li><a href="bank_materi.php?kategori=CSS">CSS</a></li>
                    <li><a href="bank_materi.php?kategori=JavaScript">JavaScript</a></li>
                    <li><a href="bank_materi.php?kategori=Python">Python</a></li>
                    <li><a href="bank_materi.php?kategori=PHP">PHP</a></li>
                    <li><a href="bank_materi.php?kategori=MySQL">MySQL</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>PINTAR</h3>
                <ul>
                    <li><a href="../quiz/index.php">Kuis Gratis</a></li>
                    <li><a href="../premium/index.php">Paket Premium</a></li>
                    <li><a href="../dashboard/index.php">Dashboard Belajar</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">Copyright ©️ CodeBrew 2025</div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
