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

// Fungsi untuk menghitung ulang dan memperbarui total_questions di tabel quizzes
function updateQuizTotalQuestions($conn, $quiz_id) {
    // Pastikan quiz_id valid
    if (!$quiz_id) {
        return;
    }

    $total_questions = 0;

    $stmt_count = $conn->prepare("SELECT COUNT(*) FROM questions WHERE quiz_id = ?");
    $stmt_count->bind_param("i", $quiz_id);
    $stmt_count->execute();
    $stmt_count->bind_result($total_questions);
    $stmt_count->fetch();
    $stmt_count->close();

    $stmt_update = $conn->prepare("UPDATE quizzes SET total_questions = ? WHERE quiz_id = ?");
    $stmt_update->bind_param("ii", $total_questions, $quiz_id);
    $stmt_update->execute();
    $stmt_update->close();
}


// Inisialisasi variabel
$quiz_id = $language = $topic = '';
$question_id = $question_text = '';
$answers_for_question = []; // Array untuk menyimpan jawaban saat edit pertanyaan
$isEditingQuiz = false;
$isEditingQuestion = false;
$alert = '';
$alertType = '';

// --- Proses CRUD untuk Kuis (quizzes) ---

// Proses hapus kuis
if (isset($_GET['delete_quiz']) && !empty($_GET['delete_quiz'])) {
    $deleteId = $_GET['delete_quiz'];
    // Hapus kuis dan semua pertanyaan/jawaban terkait (ON DELETE CASCADE di DB)
    $deleteQuery = "DELETE FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $deleteId);
    
    if ($stmt->execute()) {
        $alert = "Kuis berhasil dihapus beserta pertanyaan dan jawabannya!";
        $alertType = "success";
    } else {
        $alert = "Error: " . $stmt->error;
        $alertType = "danger";
    }
    $stmt->close();
}

// Proses edit kuis
if (isset($_GET['edit_quiz']) && !empty($_GET['edit_quiz'])) {
    $editId = $_GET['edit_quiz'];
    $editQuery = "SELECT * FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($editQuery);
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $quiz_id = $row['quiz_id'];
        $language = $row['language'];
        $topic = $row['topic'];
        $is_premium = $row['is_premium'];
        $isEditingQuiz = true;
    }
    $stmt->close();
}

// Proses form submit untuk Kuis
if (isset($_POST['submit_quiz'])) {
    $language = $_POST['language'];
    $topic = $_POST['topic'];
    $is_premium = isset($_POST['is_premium']) ? 1 : 0;
    $admin_id = $_SESSION['user_id'];
    
    if (empty($language) || empty($topic)) {
        $alert = "Bahasa dan Topik kuis harus diisi!";
        $alertType = "danger";
    } else {
        if (isset($_POST['quiz_id']) && !empty($_POST['quiz_id'])) {
            // Update Kuis
            $quiz_id = $_POST['quiz_id'];
            $updateQuery = "UPDATE quizzes SET language = ?, topic = ?, is_premium = ?, admin_id = ? WHERE quiz_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssiii", $language, $topic, $is_premium, $admin_id, $quiz_id);
            
            if ($stmt->execute()) {
                $alert = "Kuis berhasil diupdate!";
                $alertType = "success";
                // Reset form
                $quiz_id = $language = $topic = '';
                $isEditingQuiz = false;
            } else {
                $alert = "Error: " . $stmt->error;
                $alertType = "danger";
            }
        } else {
            // Insert Kuis baru
            $insertQuery = "INSERT INTO quizzes (language, topic, is_premium, admin_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ssii", $language, $topic, $is_premium, $admin_id);
            
            if ($stmt->execute()) {
                $alert = "Kuis berhasil ditambahkan!";
                $alertType = "success";
                // Reset form
                $language = $topic = '';
            } else {
                $alert = "Error: " . $stmt->error;
                $alertType = "danger";
            }
        }
        $stmt->close();
    }
}

// --- Proses CRUD untuk Pertanyaan (questions) dan Jawaban (answers) ---

// Proses hapus pertanyaan
if (isset($_GET['delete_question']) && !empty($_GET['delete_question'])) {
    $deleteId = $_GET['delete_question'];
    
    // Dapatkan quiz_id dari pertanyaan yang akan dihapus SEBELUM dihapus
    $target_quiz_id = null;
    $stmt_get_quiz_id = $conn->prepare("SELECT quiz_id FROM questions WHERE question_id = ?");
    $stmt_get_quiz_id->bind_param("i", $deleteId);
    $stmt_get_quiz_id->execute();
    $stmt_get_quiz_id->bind_result($target_quiz_id);
    $stmt_get_quiz_id->fetch();
    $stmt_get_quiz_id->close();

    $deleteQuery = "DELETE FROM questions WHERE question_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $deleteId);
    
    if ($stmt->execute()) {
        // Panggil fungsi untuk update total_questions setelah penghapusan
        if ($target_quiz_id) { // Pastikan quiz_id ditemukan
            updateQuizTotalQuestions($conn, $target_quiz_id);
        }
        $alert = "Pertanyaan berhasil dihapus beserta jawabannya!";
        $alertType = "success";
    } else {
        $alert = "Error: " . $stmt->error;
        $alertType = "danger";
    }
    $stmt->close();
}

// Proses edit pertanyaan (dan ambil jawabannya)
if (isset($_GET['edit_question']) && !empty($_GET['edit_question'])) {
    $editId = $_GET['edit_question'];
    
    // Ambil data pertanyaan
    $editQuestionQuery = "SELECT * FROM questions WHERE question_id = ?";
    $stmtQ = $conn->prepare($editQuestionQuery);
    $stmtQ->bind_param("i", $editId);
    $stmtQ->execute();
    $resultQ = $stmtQ->get_result();
    
    if ($resultQ->num_rows > 0) {
        $rowQ = $resultQ->fetch_assoc();
        $question_id = $rowQ['question_id'];
        $quiz_id = $rowQ['quiz_id']; // Pre-select the quiz for the question
        $question_text = $rowQ['question_text'];
        $isEditingQuestion = true;

        // Ambil jawaban terkait pertanyaan ini
        $editAnswersQuery = "SELECT * FROM answers WHERE question_id = ?";
        $stmtA = $conn->prepare($editAnswersQuery);
        $stmtA->bind_param("i", $question_id);
        $stmtA->execute();
        $resultA = $stmtA->get_result();
        while ($rowA = $resultA->fetch_assoc()) {
            $answers_for_question[] = $rowA;
        }
        $stmtA->close();
    }
    $stmtQ->close();
}

// Proses form submit untuk Pertanyaan (dan Jawaban)
if (isset($_POST['submit_question'])) {
    $quiz_id = $_POST['quiz_id_for_question'];
    $question_text = $_POST['question_text'];
    $admin_id = $_SESSION['user_id'];
    $answer_texts = $_POST['answer_text']; // Array of answer texts
    $correct_answers = isset($_POST['is_correct']) ? $_POST['is_correct'] : []; // Array of correct answer indices
    
    // Validasi dasar
    if (empty($quiz_id) || empty($question_text)) {
        $alert = "Kuis dan teks pertanyaan harus diisi!";
        $alertType = "danger";
    } else if (empty($answer_texts) || count(array_filter($answer_texts, 'trim')) < 2) { // Minimal 2 jawaban non-kosong
        $alert = "Setidaknya harus ada dua pilihan jawaban yang tidak kosong!";
        $alertType = "danger";
    } else if (empty($correct_answers)) {
        $alert = "Setidaknya satu jawaban benar harus dipilih!";
        $alertType = "danger";
    } else {
        $conn->begin_transaction(); // Mulai transaksi
        try {
            if (isset($_POST['question_id']) && !empty($_POST['question_id'])) {
                // Update Pertanyaan
                $question_id = $_POST['question_id'];
                $updateQuestionQuery = "UPDATE questions SET quiz_id = ?, question_text = ?, admin_id = ? WHERE question_id = ?";
                $stmtQ = $conn->prepare($updateQuestionQuery);
                $stmtQ->bind_param("isii", $quiz_id, $question_text, $admin_id, $question_id);
                $stmtQ->execute();
                $stmtQ->close();

                // Hapus semua jawaban lama untuk pertanyaan ini
                $deleteAnswersQuery = "DELETE FROM answers WHERE question_id = ?";
                $stmtD = $conn->prepare($deleteAnswersQuery);
                $stmtD->bind_param("i", $question_id);
                $stmtD->execute();
                $stmtD->close();

            } else {
                // Insert Pertanyaan baru
                $insertQuestionQuery = "INSERT INTO questions (quiz_id, question_text, admin_id) VALUES (?, ?, ?)";
                $stmtQ = $conn->prepare($insertQuestionQuery);
                $stmtQ->bind_param("isi", $quiz_id, $question_text, $admin_id);
                $stmtQ->execute();
                $question_id = $conn->insert_id; // Ambil ID pertanyaan yang baru dibuat
                $stmtQ->close();
            }

            // Insert/Update Jawaban
            $insertAnswerQuery = "INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)";
            $stmtA = $conn->prepare($insertAnswerQuery);
            
            foreach ($answer_texts as $index => $text) {
                if (!empty(trim($text))) { // Pastikan teks jawaban tidak kosong
                    $is_correct = in_array($index, $correct_answers) ? 1 : 0;
                    $stmtA->bind_param("isi", $question_id, $text, $is_correct);
                    $stmtA->execute();
                }
            }
            $stmtA->close();

            $conn->commit(); // Commit transaksi

            // Panggil fungsi untuk update total_questions setelah penambahan/update pertanyaan
            updateQuizTotalQuestions($conn, $quiz_id);

            $alert = "Pertanyaan dan jawaban berhasil " . ($isEditingQuestion ? "diupdate" : "ditambahkan") . "!";
            $alertType = "success";
            // Reset form
            $question_id = $question_text = '';
            $answers_for_question = [];
            $isEditingQuestion = false;

        } catch (mysqli_sql_exception $e) {
            $conn->rollback(); // Rollback jika ada error
            $alert = "Error: " . $e->getMessage();
            $alertType = "danger";
        }
    }
}

// Fetch all quizzes for dropdowns and display
$quizzes_data = [];
$quizResult = $conn->query("SELECT quiz_id, language, topic, is_premium, total_questions FROM quizzes ORDER BY language, topic");
if ($quizResult) {
    while ($row = $quizResult->fetch_assoc()) {
        $quizzes_data[] = $row;
    }
}

// Fetch all questions for display (with associated quiz info)
$questions_data = [];
$questionResult = $conn->query("
    SELECT 
        q.question_id, 
        q.question_text, 
        qz.language, 
        qz.topic,
        GROUP_CONCAT(CASE WHEN a.is_correct = 1 THEN a.answer_text ELSE NULL END SEPARATOR ' || ') AS correct_answers_text,
        COUNT(a.answer_id) AS total_answers
    FROM questions q 
    JOIN quizzes qz ON q.quiz_id = qz.quiz_id 
    LEFT JOIN answers a ON q.question_id = a.question_id
    GROUP BY q.question_id
    ORDER BY qz.language, qz.topic, q.question_id
");
if ($questionResult) {
    while ($row = $questionResult->fetch_assoc()) {
        $questions_data[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kuis - Admin CodeBrew</title>
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
        
        /* button ceklis kuis premium */
        .premium-check {
            display: flex;
            align-items: center;
            border: 2px solid rgb(200, 218, 230); /* border abu-abu */
            border-radius: 6px;
            background-color: #f8f9fa;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .premium-check:hover {
            background-color: #f0f4ff; /* hover lembut */
        }

        /* Saat checkbox dicentang: border & teks jadi biru */
        .premium-check input[type="checkbox"]:checked + span {
            color: #007bff; /* teks biru */
        }

        .premium-check:has(input[type="checkbox"]:checked) {
            border-color: #007bff; /* border biru */
        }

        /* Saat checkbox dicentang, ubah border jadi biru */
        .premium-check input[type="checkbox"]:checked + span {
            border-color: #007bff;
        }

        .premium-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #007bff; 
            margin-right: 8px;
            cursor: pointer;
        }

        .premium-check span {
            font-size: 1rem;
            font-weight: 600;
            color:rgb(77, 160, 212);
            transition: all 0.3s ease;
        }

        .premium-note {
            font-size: 0.9rem;
            color: #555;
            font-style: italic;
            margin-top: 8px;
            padding-bottom: 10px;
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
        
        .badge-html { background-color: #E44D26; color: white; }
        .badge-css { background-color: #2965f1; color: white; }
        .badge-javascript { background-color: #F7DF1E; color: #333; }
        .badge-python { background-color: #306998; color: white; }
        .badge-php { background-color: #777BB3; color: white; }
        .badge-mysql { background-color: #00758F; color: white; }
        .badge-premium { background-color: var(--accent); color: white; }
        .badge-free { background-color: #28a745; color: white; }

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
                <a href="materi_bank.php">
                    <i class="fas fa-book"></i> <span>Bank Materi</span>
                </a>
            </li>
            <li>
                <a href="manage_quiz.php" class="active">
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
                <a href="../register-login/logout.php">
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
                    <h1>Kelola Kuis</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Kelola Kuis</li>
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
        
        <!-- Form Card: Kelola Kuis (Topik) -->
        <div class="card mb-4">
            <div class="card-header">
                <?php echo $isEditingQuiz ? 'Edit Kuis' : 'Tambah Kuis Baru'; ?> (Topik)
            </div>
            <div class="card-body">
                <form method="POST" action="manage_quiz.php">
                    <?php if ($isEditingQuiz): ?>
                        <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="language" class="form-label">Bahasa</label>
                            <select class="form-select" id="language" name="language" required>
                                <option value="" disabled <?php echo empty($language) ? 'selected' : ''; ?>>Pilih Bahasa</option>
                                <option value="HTML" <?php echo $language == 'HTML' ? 'selected' : ''; ?>>HTML</option>
                                <option value="CSS" <?php echo $language == 'CSS' ? 'selected' : ''; ?>>CSS</option>
                                <option value="JavaScript" <?php echo $language == 'JavaScript' ? 'selected' : ''; ?>>JavaScript</option>
                                <option value="Python" <?php echo $language == 'Python' ? 'selected' : ''; ?>>Python</option>
                                <option value="PHP" <?php echo $language == 'PHP' ? 'selected' : ''; ?>>PHP</option>
                                <option value="MySQL" <?php echo $language == 'MySQL' ? 'selected' : ''; ?>>MySQL</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="topic" class="form-label">Topik Kuis</label>
                            <input type="text" class="form-control" id="topic" name="topic" placeholder="Contoh: HTML Basics, CSS Layout" value="<?php echo htmlspecialchars($topic); ?>" required>
                        </div>
                    </div>
                    
                    <label class="premium-check">
                        <input type="checkbox" id="is_premium" name="is_premium" 
                            <?php echo (isset($is_premium) && $is_premium) ? 'checked' : ''; ?>>
                        <span>💡 Kuis Premium (Hanya untuk Pengguna Premium)</span>
                    </label>
                    <div class="premium-note">
                        *Jika unggahan kuis ini premium, silakan ceklis tombol di atas.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="submit_quiz" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $isEditingQuiz ? 'Update Kuis' : 'Simpan Kuis'; ?>
                        </button>
                        <?php if ($isEditingQuiz): ?>
                            <a href="manage_quiz.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table Card: Daftar Kuis -->
        <div class="card mb-4">
            <div class="card-header">
                Daftar Kuis (Topik)
            </div>
            <div class="card-body">
                <?php if (!empty($quizzes_data)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Bahasa</th>
                                    <th>Topik Kuis</th>
                                    <th>Tipe</th>
                                    <th>Jumlah Soal</th> <!-- Kolom baru -->
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($quizzes_data as $quiz_row): 
                                    $badgeClass = 'badge-';
                                    switch ($quiz_row['language']) {
                                        case 'HTML': $badgeClass .= 'html'; break;
                                        case 'CSS': $badgeClass .= 'css'; break;
                                        case 'JavaScript': $badgeClass .= 'javascript'; break;
                                        case 'Python': $badgeClass .= 'python'; break;
                                        case 'PHP': $badgeClass .= 'php'; break;
                                        case 'MySQL': $badgeClass .= 'mysql'; break;
                                        default: $badgeClass .= 'primary';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($quiz_row['language']); ?></span></td>
                                    <td><?php echo htmlspecialchars($quiz_row['topic']); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($quiz_row['is_premium'] ? 'badge-premium' : 'badge-free'); ?>">
                                            <?php echo ($quiz_row['is_premium'] ? 'Premium' : 'Gratis'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($quiz_row['total_questions']); ?></td> <!-- Menampilkan jumlah soal -->
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="manage_quiz.php?edit_quiz=<?php echo $quiz_row['quiz_id']; ?>" class="btn btn-sm btn-warning table-action-btn">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" onclick="confirmDeleteQuiz(<?php echo $quiz_row['quiz_id']; ?>)" class="btn btn-sm btn-danger table-action-btn">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> Belum ada kuis yang ditambahkan.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form Card: Kelola Pertanyaan (dan Jawaban) -->
        <div class="card mb-4">
            <div class="card-header">
                <?php echo $isEditingQuestion ? 'Edit Pertanyaan' : 'Tambah Pertanyaan Baru'; ?>
            </div>
            <div class="card-body">
                <form method="POST" action="manage_quiz.php">
                    <?php if ($isEditingQuestion): ?>
                        <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="quiz_id_for_question" class="form-label">Pilih Kuis</label>
                        <select class="form-select" id="quiz_id_for_question" name="quiz_id_for_question" required>
                            <option value="" disabled <?php echo empty($quiz_id) ? 'selected' : ''; ?>>Pilih Kuis untuk Pertanyaan ini</option>
                            <?php foreach ($quizzes_data as $q_data): ?>
                                <option value="<?php echo $q_data['quiz_id']; ?>" <?php echo ($quiz_id == $q_data['quiz_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($q_data['language'] . ' - ' . $q_data['topic']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="question_text" class="form-label">Teks Pertanyaan</label>
                        <textarea class="form-control" id="question_text" name="question_text" rows="3" placeholder="Masukkan teks pertanyaan..." required><?php echo htmlspecialchars($question_text); ?></textarea>
                    </div>
                    
                    <hr>
                    <h5>Pilihan Jawaban (Minimal 2, Maksimal 5)</h5>
                    <div id="answers-container">
                        <?php if ($isEditingQuestion && !empty($answers_for_question)): ?>
                            <?php foreach ($answers_for_question as $idx => $answer): ?>
                                <div class="input-group mb-2 answer-item">
                                    <div class="input-group-text">
                                        <!-- Value checkbox harus sesuai dengan index array answer_text -->
                                        <input class="form-check-input mt-0" type="checkbox" name="is_correct[]" value="<?php echo $idx; ?>" <?php echo $answer['is_correct'] ? 'checked' : ''; ?>>
                                    </div>
                                    <input type="text" class="form-control" name="answer_text[]" placeholder="Teks Jawaban <?php echo $idx + 1; ?>" value="<?php echo htmlspecialchars($answer['answer_text']); ?>" required>
                                    <button class="btn btn-outline-danger remove-answer-btn" type="button"><i class="fas fa-times"></i></button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Default 4 answer fields for new questions -->
                            <?php for ($i = 0; $i < 4; $i++): ?>
                                <div class="input-group mb-2 answer-item">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0" type="checkbox" name="is_correct[]" value="<?php echo $i; ?>">
                                    </div>
                                    <input type="text" class="form-control" name="answer_text[]" placeholder="Teks Jawaban <?php echo $i + 1; ?>" <?php echo ($i < 2) ? 'required' : ''; ?>>
                                    <button class="btn btn-outline-danger remove-answer-btn" type="button"><i class="fas fa-times"></i></button>
                                </div>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" id="add-answer-btn" class="btn btn-info btn-sm mb-3"><i class="fas fa-plus"></i> Tambah Pilihan Jawaban</button>
                    <div class="form-text mb-3">*Ceklis kotak di samping teks jawaban untuk menandai jawaban yang benar.</div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="submit_question" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $isEditingQuestion ? 'Update Pertanyaan' : 'Simpan Pertanyaan'; ?>
                        </button>
                        <?php if ($isEditingQuestion): ?>
                            <a href="manage_quiz.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table Card: Daftar Pertanyaan -->
        <div class="card mb-4">
            <div class="card-header">
                Daftar Pertanyaan
            </div>
            <div class="card-body">
                <?php if (!empty($questions_data)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kuis (Bahasa - Topik)</th>
                                    <th>Teks Pertanyaan</th>
                                    <th>Jawaban Benar</th>
                                    <th>Jumlah Opsi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($questions_data as $q_row): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($q_row['language'] . ' - ' . $q_row['topic']); ?></td>
                                    <td><?php echo htmlspecialchars($q_row['question_text']); ?></td>
                                    <td><?php echo htmlspecialchars($q_row['correct_answers_text'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($q_row['total_answers']); ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="manage_quiz.php?edit_question=<?php echo $q_row['question_id']; ?>" class="btn btn-sm btn-warning table-action-btn">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" onclick="confirmDeleteQuestion(<?php echo $q_row['question_id']; ?>)" class="btn btn-sm btn-danger table-action-btn">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> Belum ada pertanyaan yang ditambahkan.
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
        // Konfirmasi sebelum menghapus Kuis
        function confirmDeleteQuiz(id) {
            if (confirm('Apakah Anda yakin ingin menghapus KUIS ini? Ini akan menghapus semua pertanyaan dan jawaban terkait!')) {
                window.location.href = 'manage_quiz.php?delete_quiz=' + id;
            }
        }

        // Konfirmasi sebelum menghapus Pertanyaan
        function confirmDeleteQuestion(id) {
            if (confirm('Apakah Anda yakin ingin menghapus PERTANYAAN ini? Ini akan menghapus semua jawaban terkait!')) {
                window.location.href = 'manage_quiz.php?delete_question=' + id;
            }
        }

        // Logika untuk menambah/menghapus pilihan jawaban secara dinamis
        $(document).ready(function() {
            let answerCount = $('#answers-container .answer-item').length;
            const maxAnswers = 5;
            const minAnswers = 2;

            function updateAnswerPlaceholders() {
                $('#answers-container .answer-item').each(function(index) {
                    $(this).find('input[type="text"]').attr('placeholder', 'Teks Jawaban ' + (index + 1));
                    // Update value for correct answer index. This is crucial for correct_answers[] to work.
                    // If editing, the checkbox value might already be set from DB, so only update if it's a new field.
                    if (!$(this).find('input[type="checkbox"]').data('original-value')) { // Check if it's an original loaded value
                         $(this).find('input[type="checkbox"]').val(index);
                    }
                   
                    // Set required for first two answers
                    if (index < minAnswers) {
                        $(this).find('input[type="text"]').prop('required', true);
                    } else {
                        $(this).find('input[type="text"]').prop('required', false);
                    }
                });
            }

            // Initial update for existing answers on edit
            // Store original values for checkboxes if editing, to prevent re-indexing issues on initial load
            $('#answers-container .answer-item').each(function(index) {
                let checkbox = $(this).find('input[type="checkbox"]');
                if (checkbox.is(':checked')) {
                    checkbox.data('original-value', checkbox.val());
                }
            });
            updateAnswerPlaceholders();

            $('#add-answer-btn').on('click', function() {
                if (answerCount < maxAnswers) {
                    const newAnswerHtml = `
                        <div class="input-group mb-2 answer-item">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="checkbox" name="is_correct[]" value="${answerCount}">
                            </div>
                            <input type="text" class="form-control" name="answer_text[]" placeholder="Teks Jawaban ${answerCount + 1}">
                            <button class="btn btn-outline-danger remove-answer-btn" type="button"><i class="fas fa-times"></i></button>
                        </div>
                    `;
                    $('#answers-container').append(newAnswerHtml);
                    answerCount++;
                    updateAnswerPlaceholders(); // Re-index and update placeholders
                    toggleAddButton();
                }
            });

            $(document).on('click', '.remove-answer-btn', function() {
                if (answerCount > minAnswers) {
                    $(this).closest('.answer-item').remove();
                    answerCount--;
                    updateAnswerPlaceholders(); // Re-index and update placeholders
                    toggleAddButton();
                } else {
                    alert('Minimal harus ada ' + minAnswers + ' pilihan jawaban.');
                }
            });

            function toggleAddButton() {
                if (answerCount >= maxAnswers) {
                    $('#add-answer-btn').hide();
                } else {
                    $('#add-answer-btn').show();
                }
            }

            toggleAddButton(); // Initial check for add button visibility
        });
    </script>
</body>
</html>