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

// Inisialisasi variabel
$id = $kategori = $judul = $url = '';
$isEditing = false;
$alert = '';
$alertType = '';

// Buat tabel materi_bank jika belum ada
$createTableQuery = "CREATE TABLE IF NOT EXISTS materi_bank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori VARCHAR(50) NOT NULL,
    judul VARCHAR(255) NOT NULL,
    url TEXT NOT NULL,
    admin_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES user(user_id)
)";

if ($conn->query($createTableQuery) !== TRUE) {
    $alert = "Error creating table: " . $conn->error;
    $alertType = "danger";
}

// Proses hapus data
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $deleteQuery = "DELETE FROM materi_bank WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $deleteId);
    
    if ($stmt->execute()) {
        $alert = "Materi berhasil dihapus!";
        $alertType = "success";
    } else {
        $alert = "Error: " . $stmt->error;
        $alertType = "danger";
    }
    $stmt->close();
}

// Proses edit data
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editId = $_GET['edit'];
    $editQuery = "SELECT * FROM materi_bank WHERE id = ?";
    $stmt = $conn->prepare($editQuery);
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $kategori = $row['kategori'];
        $judul = $row['judul'];
        $url = $row['url'];
        $isEditing = true;
    }
    $stmt->close();
}

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kategori = $_POST['kategori'];
    $judul = $_POST['judul'];
    $url = $_POST['url'];
    $admin_id = $_SESSION['user_id'];
    
    // Validasi input
    if (empty($kategori) || empty($judul) || empty($url)) {
        $alert = "Semua field harus diisi!";
        $alertType = "danger";
    } else {
        // Validasi URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $alert = "URL tidak valid!";
            $alertType = "danger";
        } else {
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                // Update data
                $id = $_POST['id'];
                $updateQuery = "UPDATE materi_bank SET kategori = ?, judul = ?, url = ?, admin_id = ? WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("sssii", $kategori, $judul, $url, $admin_id, $id);
                
                if ($stmt->execute()) {
                    $alert = "Materi berhasil diupdate!";
                    $alertType = "success";
                    // Reset form
                    $id = $kategori = $judul = $url = '';
                    $isEditing = false;
                } else {
                    $alert = "Error: " . $stmt->error;
                    $alertType = "danger";
                }
            } else {
                // Insert data baru
                $insertQuery = "INSERT INTO materi_bank (kategori, judul, url, admin_id) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insertQuery);
                $stmt->bind_param("sssi", $kategori, $judul, $url, $admin_id);
                
                if ($stmt->execute()) {
                    $alert = "Materi berhasil ditambahkan!";
                    $alertType = "success";
                    // Reset form
                    $kategori = $judul = $url = '';
                } else {
                    $alert = "Error: " . $stmt->error;
                    $alertType = "danger";
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Materi - Admin CodeBrew</title>
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
        .badge {
            padding: 6px 10px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .badge-html {
            background-color: #E44D26;
            color: white;
        }
        
        .badge-css {
            background-color: #2965f1;
            color: white;
        }
        
        .badge-js {
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
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="materi_bank.php" class="active">
                    <i class="fas fa-book"></i> <span>Bank Materi</span>
                </a>
            </li>
            <li>
                <a href="manage_quiz.php">
                    <i class="fas fa-question-circle"></i> <span>Kelola Kuis</span>
                </a>
            </li>
            <li>
                <a href="users.php">
                    <i class="fas fa-users"></i> <span>Pengguna</span>
                </a>
            </li>
            <li>
                <a href="settings.php">
                    <i class="fas fa-cog"></i> <span>Pengaturan</span>
                </a>
            </li>
            <li>
                <a href="../logout.php">
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
                    <h1>Kelola Bank Materi</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bank Materi</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <span class="badge bg-primary">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Alert Message -->
        <?php if (!empty($alert)): ?>
        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
            <?php echo $alert; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- Form Card -->
        <div class="card">
            <div class="card-header">
                <?php echo $isEditing ? 'Edit Materi' : 'Tambah Materi Baru'; ?>
            </div>
            <div class="card-body">
                <form method="POST" action="materi_bank.php">
                    <?php if ($isEditing): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select class="form-select" id="kategori" name="kategori" required>
                                <option value="" disabled <?php echo empty($kategori) ? 'selected' : ''; ?>>Pilih Kategori</option>
                                <option value="HTML" <?php echo $kategori == 'HTML' ? 'selected' : ''; ?>>HTML</option>
                                <option value="CSS" <?php echo $kategori == 'CSS' ? 'selected' : ''; ?>>CSS</option>
                                <option value="JavaScript" <?php echo $kategori == 'JavaScript' ? 'selected' : ''; ?>>JavaScript</option>
                                <option value="Python" <?php echo $kategori == 'Python' ? 'selected' : ''; ?>>Python</option>
                                <option value="PHP" <?php echo $kategori == 'PHP' ? 'selected' : ''; ?>>PHP</option>
                                <option value="MySQL" <?php echo $kategori == 'MySQL' ? 'selected' : ''; ?>>MySQL</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="judul" class="form-label">Judul Materi</label>
                            <input type="text" class="form-control" id="judul" name="judul" placeholder="Masukkan judul materi" value="<?php echo htmlspecialchars($judul); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="url" class="form-label">URL Materi</label>
                        <input type="url" class="form-control" id="url" name="url" placeholder="https://www.w3schools.com/..." value="<?php echo htmlspecialchars($url); ?>" required>
                        <div class="form-text">Masukkan URL valid ke materi dari W3Schools atau sumber lainnya.</div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $isEditing ? 'Update Materi' : 'Simpan Materi'; ?>
                        </button>
                        <?php if ($isEditing): ?>
                            <a href="materi_bank.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Data Table Card -->
        <div class="card">
            <div class="card-header">
                Daftar Materi
            </div>
            <div class="card-body">
                <?php
                $query = "SELECT * FROM materi_bank ORDER BY kategori, judul";
                $result = $conn->query($query);
                ?>
                
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kategori</th>
                                    <th>Judul Materi</th>
                                    <th>URL</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($row = $result->fetch_assoc()): 
                                    // Menentukan badge style berdasarkan kategori
                                    $badgeClass = 'badge-';
                                    switch ($row['kategori']) {
                                        case 'HTML':
                                            $badgeClass .= 'html';
                                            break;
                                        case 'CSS':
                                            $badgeClass .= 'css';
                                            break;
                                        case 'JavaScript':
                                            $badgeClass .= 'js';
                                            break;
                                        case 'Python':
                                            $badgeClass .= 'python';
                                            break;
                                        case 'PHP':
                                            $badgeClass .= 'php';
                                            break;
                                        case 'MySQL':
                                            $badgeClass .= 'mysql';
                                            break;
                                        default:
                                            $badgeClass .= 'primary';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['kategori']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank" title="<?php echo htmlspecialchars($row['url']); ?>">
                                            <?php 
                                            // Menampilkan URL yang diperpendek jika terlalu panjang
                                            $displayUrl = strlen($row['url']) > 40 ? substr($row['url'], 0, 40) . '...' : $row['url'];
                                            echo htmlspecialchars($displayUrl); 
                                            ?>
                                            <i class="fas fa-external-link-alt ms-1"></i>
                                        </a>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="materi_bank.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning table-action-btn">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn btn-sm btn-danger table-action-btn">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> Belum ada data materi. Silakan tambahkan materi baru.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Konfirmasi sebelum menghapus
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus materi ini?')) {
                window.location.href = 'materi_bank.php?delete=' + id;
            }
        }
    </script>
</body>
</html>
