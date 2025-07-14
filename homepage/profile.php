<?php
session_start();

// Redirect jika belum login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Koneksi ke database
// Menggunakan file connection.php untuk koneksi
require_once '../connection.php'; // Pastikan path ini benar

// Cek koneksi
if ($conn->connect_error) { // Menggunakan $conn dari connection.php
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data user dari tabel `user`
$username = $_SESSION['username'];
$sql = "SELECT * FROM user WHERE username = '$username'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
$user_id = $user['user_id']; // ID user dari tabel user
$is_premium = $user['is_premium'] ?? 0; // Ambil status premium

// Handle update profil
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = $_POST['password']; // Ambil password baru (belum di-escape)
    $confirm_password = $_POST['confirm_password']; // Ambil konfirmasi password

    // Validasi email
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } else {
        // Cek apakah email sudah digunakan oleh user lain
        $check_email_sql = "SELECT user_id FROM user WHERE email = '$new_email' AND user_id != '$user_id'";
        $check_email_result = mysqli_query($conn, $check_email_sql);
        if (mysqli_num_rows($check_email_result) > 0) {
            $error_message = "Email sudah digunakan oleh akun lain.";
        } else {
            $update_fields = [];
            $update_params = [];
            $param_types = '';

            // Update email jika berbeda
            if ($new_email !== $user['email']) {
                $update_fields[] = "email = ?";
                $update_params[] = $new_email;
                $param_types .= 's';
            }

            // Update password jika diisi dan cocok
            if (!empty($new_password)) {
                if ($new_password === $confirm_password) {
                    // Hashing password sebelum disimpan
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_fields[] = "password = ?";
                    $update_params[] = $hashed_password;
                    $param_types .= 's';
                } else {
                    $error_message = "Konfirmasi password tidak cocok.";
                }
            }

            if (!empty($update_fields) && empty($error_message)) {
                $update_sql = "UPDATE user SET " . implode(", ", $update_fields) . " WHERE user_id = ?";
                $update_params[] = $user_id;
                $param_types .= 'i'; // 'i' for integer user_id

                $stmt = mysqli_prepare($conn, $update_sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, $param_types, ...$update_params);
                    if (mysqli_stmt_execute($stmt)) {
                        $success_message = "Profil berhasil diperbarui!";
                        // Refresh user data setelah update
                        $result = mysqli_query($conn, $sql);
                        $user = mysqli_fetch_assoc($result);
                        $is_premium = $user['is_premium'] ?? 0; // Perbarui status premium setelah update
                    } else {
                        $error_message = "Error saat memperbarui: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error_message = "Error saat menyiapkan statement: " . mysqli_error($conn);
                }
            } elseif (empty($update_fields) && empty($error_message)) {
                $error_message = "Tidak ada perubahan yang disimpan.";
            }
        }
    }
}

// Ambil aktivitas kuis terakhir
$recent_sql = "SELECT q.topic as quiz_title, uq.score, uq.completed_at
               FROM user_quiz_scores uq
               JOIN quizzes q ON uq.quiz_id = q.quiz_id
               WHERE uq.user_id = '$user_id'
               ORDER BY uq.completed_at DESC
               LIMIT 5";


$recent_result = mysqli_query($conn, $recent_sql);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profil Saya - CodeBrew</title>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="index.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* Additional styles specific to profile page */
    .profile-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 2rem;
    }

    .profile-header {
      display: flex;
      align-items: center;
      gap: 2rem;
      margin-bottom: 3rem;
    }

    .profile-avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: var(--gradient);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      color: var(--light);
      box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
      border: 3px solid var(--light);
    }

    .profile-name {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      background: var(--gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .profile-username {
      color: var(--light-purple);
      font-size: 1.2rem;
      margin-bottom: 1rem;
    }

    .profile-bio {
      color: var(--light);
      max-width: 600px;
    }

    .profile-tabs {
      display: flex;
      margin-bottom: 2rem;
      border-bottom: 1px solid rgba(93, 46, 142, 0.3);
    }

    .profile-tab {
      padding: 1rem 2rem;
      color: var(--light-purple);
      cursor: pointer;
      position: relative;
      transition: color 0.3s;
    }

    .profile-tab.active {
      color: var(--light);
    }

    .profile-tab.active::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 3px;
      background: var(--gradient);
      border-radius: 3px 3px 0 0;
    }

    .profile-content {
      display: none;
    }

    .profile-content.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 3rem;
    }

    .stat-card {
      background: rgba(93, 46, 142, 0.2);
      border-radius: 15px;
      padding: 1.5rem;
      text-align: center;
      transition: transform 0.3s;
    }

    .stat-card:hover {
      transform: translateY(-5px);
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      background: var(--gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .stat-label {
      color: var(--light-purple);
    }


    .recent-activity {
      background: rgba(93, 46, 142, 0.2);
      border-radius: 15px;
      padding: 1.5rem;
      margin-bottom: 3rem;
    }

    .activity-title {
      font-size: 1.5rem;
      margin-bottom: 1.5rem;
      color: var(--light);
    }

    .activity-item {
      display: flex;
      justify-content: space-between;
      padding: 1rem 0;
      border-bottom: 1px solid rgba(93, 46, 142, 0.3);
    }

    .activity-item:last-child {
      border-bottom: none;
    }

    .activity-quiz {
      color: var(--light);
      font-weight: 500;
    }

    .activity-score {
      background: var(--gradient);
      padding: 0.3rem 1rem;
      border-radius: 20px;
      color: var(--light);
      font-weight: 600;
    }

    .activity-date {
      color: var(--light-purple);
      font-size: 0.9rem;
    }

    .edit-profile-form {
      background: rgba(93, 46, 142, 0.2);
      border-radius: 15px;
      padding: 2rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      color: var(--light);
    }

    .form-input {
      width: 100%;
      padding: 0.8rem 1rem;
      background: rgba(13, 7, 27, 0.6);
      border: 1px solid rgba(93, 46, 142, 0.5);
      border-radius: 8px;
      color: var(--light);
      transition: border-color 0.3s;
    }

    .form-input:focus {
      outline: none;
      border-color: var(--primary-light);
    }

    .form-textarea {
      min-height: 120px;
      resize: vertical;
    }

    .form-submit {
      background: var(--gradient);
      color: var(--light);
      border: none;
      padding: 0.8rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .form-submit:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
    }

    .alert {
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
    }

    .alert-success {
      background: rgba(16, 185, 129, 0.2);
      border: 1px solid rgba(16, 185, 129, 0.5);
      color: #10b981;
    }

    .alert-error {
      background: rgba(239, 68, 68, 0.2);
      border: 1px solid rgba(239, 68, 68, 0.5);
      color: #ef4444;
    }

    .badge {
      display: inline-block;
      margin-right: 0.5rem;
      margin-bottom: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      background: rgba(93, 46, 142, 0.3);
      color: var(--light-purple);
      font-size: 0.9rem;
    }

    .badge-primary {
      background: var(--gradient);
      color: var(--light);
    }
  </style>
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

  <!-- Header -->
  <header>
    <!-- Logo -->
    <a href="index.php" class="logo">
      <img src="../assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" class="logo">
    </a>

    <!-- Navigasi -->
    <nav>
      <ul>
        <li><a href="index.php">Beranda</a></li>
        <li><a href="../bank_materi/belajar.php">Belajar</a></li>
        <li><a href="kuis.php">Kuis</a></li>
        <li><a href="ranking.php">Ranking</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
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

  <!-- Main Content -->
  <main style="padding-top: 120px; min-height: calc(100vh - 300px);">
    <div class="profile-container">
      <!-- Profile Header -->
      <section class="profile-header">
        <div class="profile-avatar">
          <?php
            // Display first letter of username if no avatar
            echo strtoupper(substr($username, 0, 1));
          ?>
        </div>
        <div class="profile-info">
          <h1 class="profile-name">
            <?php echo htmlspecialchars($username); // Hanya menampilkan username ?>
          </h1>
          <div class="profile-username">@<?php echo htmlspecialchars($username); ?>
            <?php echo isset($user['is_premium']) && $user['is_premium'] ? '<span class="badge badge-primary"><i class="fas fa-star"></i> PINTAR</span>' : ''; ?>
          </div>
          <p class="profile-bio">
            <?php echo isset($user['email']) ? 'Email: ' . htmlspecialchars($user['email']) : 'Email belum diatur.'; ?>
          </p>
        </div>
      </section>

      <!-- Profile Tabs -->
      <div class="profile-tabs">
        <div class="profile-tab active" data-tab="overview">Ringkasan</div>
        <div class="profile-tab" data-tab="edit-profile">Edit Profil</div>
        <!-- <div class="profile-tab" data-tab="achievements">Prestasi</div> -->
      </div>

      <!-- Success or Error Messages -->
      <?php if (!empty($success_message)): ?>
      <div class="alert alert-success">
        <?php echo $success_message; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($error_message)): ?>
      <div class="alert alert-error">
        <?php echo $error_message; ?>
      </div>
      <?php endif; ?>
      <!-- Profile Content - Overview -->
      <div class="profile-content active" id="overview">

        <!-- Recent Activity -->
        <div class="recent-activity">
          <h3 class="activity-title">Aktivitas Terbaru</h3>

          <?php if (mysqli_num_rows($recent_result) > 0): ?>
          <?php while ($activity = mysqli_fetch_assoc($recent_result)): ?>
          <div class="activity-item">
            <div>
              <div class="activity-quiz"><?php echo htmlspecialchars($activity['quiz_title']); ?></div>
              <div class="activity-date"><?php echo date('d M Y H:i', strtotime($activity['completed_at'])); ?></div>
            </div>
            <div class="activity-score"><?php echo $activity['score']; ?></div>
          </div>
          <?php endwhile; ?>
          <?php else: ?>
          <p class="text-center text-gray-400 py-4">Belum ada aktivitas kuis. Mulai kuis pertamamu sekarang!</p>
          <?php endif; ?>
        </div>

        <!-- Skills -->
        <div class="recent-activity">
          <h3 class="activity-title">Bahasa Pemrograman</h3>
          <div class="p-3">
            <span class="badge">HTML</span>
            <span class="badge">CSS</span>
            <span class="badge">JavaScript</span>
            <span class="badge">Python</span>
            <span class="badge">PHP</span>
            <span class="badge">SQL</span>
          </div>
        </div>

        <!-- Premium CTA -->
        <?php if (!isset($user['is_premium']) || !$user['is_premium']): ?>
        <div class="mt-8 text-center">
          <p class="text-light-purple mb-4">Tingkatkan pengalaman belajarmu dengan fitur PINTAR!</p>
          <button class="btn premium-cta-profile">Gabung PINTAR Sekarang</button>
        </div>
        <?php endif; ?>

      </div>

      <!-- Profile Content - Edit Profile -->
      <div class="profile-content" id="edit-profile">
        <form class="edit-profile-form" method="POST" action="">
          <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-input" type="email" id="email" name="email"
              value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>"
              placeholder="Masukkan email baru">
          </div>

          <div class="form-group">
            <label class="form-label" for="password">Password Baru</label>
            <input class="form-input" type="password" id="password" name="password"
              placeholder="Kosongkan jika tidak ingin mengubah password">
          </div>

          <div class="form-group">
            <label class="form-label" for="confirm_password">Konfirmasi Password Baru</label>
            <input class="form-input" type="password" id="confirm_password" name="confirm_password"
              placeholder="Konfirmasi password baru Anda">
          </div>

          <button type="submit" name="update_profile" class="form-submit">Simpan Perubahan</button>
        </form>
      </div>

      <!-- Profile Content - Achievements -->
      <!-- <div class="profile-content" id="achievements">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="stat-card flex items-center p-6">
            <div class="mr-4 text-4xl text-purple-500">
              <i class="fas fa-trophy"></i>
            </div>
            <div>
              <h3 class="text-xl font-semibold text-white mb-1">Pemula HTML</h3>
              <p class="text-light-purple">Selesaikan kuis HTML pertamamu</p>
            </div>
          </div>

          <div class="stat-card flex items-center p-6 opacity-50">
            <div class="mr-4 text-4xl text-purple-500">
              <i class="fas fa-medal"></i>
            </div>
            <div>
              <h3 class="text-xl font-semibold text-white mb-1">Ninja CSS</h3>
              <p class="text-light-purple">Dapatkan skor 100 di kuis CSS</p>
            </div>
          </div>

          <div class="stat-card flex items-center p-6 opacity-50">
            <div class="mr-4 text-4xl text-purple-500">
              <i class="fas fa-code"></i>
            </div>
            <div>
              <h3 class="text-xl font-semibold text-white mb-1">JavaScript Master</h3>
              <p class="text-light-purple">Selesaikan semua kuis JavaScript</p>
            </div>
          </div>

          <div class="stat-card flex items-center p-6 opacity-50">
            <div class="mr-4 text-4xl text-purple-500">
              <i class="fas fa-fire"></i>
            </div>
            <div>
              <h3 class="text-xl font-semibold text-white mb-1">Streak 7 Hari</h3>
              <p class="text-light-purple">Menyelesaikan kuis 7 hari berturut-turut</p>
            </div> -->
  </main>

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

  <!-- JavaScript -->
  <script>
    // Profile dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
      const profileBtn = document.getElementById('profileBtn');
      const profileDropdown = document.getElementById('profileDropdown');
      // Toggle dropdown when clicking profile button
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
      // Stars animation
      createStars();
      // Tab switching
      const tabs = document.querySelectorAll('.profile-tab');
      const contents = document.querySelectorAll('.profile-content');
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          const target = tab.dataset.tab;
          // Update active tab
          tabs.forEach(t => t.classList.remove('active'));
          tab.classList.add('active');
          // Show relevant content
          contents.forEach(content => {
            content.classList.remove('active');
            if (content.id === target) {
              content.classList.add('active');
            }
          });
        });
      });
      // Auto-hide alerts after 5 seconds
      const alerts = document.querySelectorAll('.alert');
      if (alerts.length > 0) {
        setTimeout(() => {
          alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = 0;
            setTimeout(() => alert.style.display = 'none', 500);
          });
        }, 5000);
      }
      // Sweet Alert for Premium button
      const premiumBtn = document.querySelector('.premium-cta-profile'); // Ubah selector
      if (premiumBtn) {
        premiumBtn.addEventListener('click', function() {
          Swal.fire({
            title: 'Beralih ke PINTAR!',
            html: `<p>Dengan meng-upgrade ke PINTAR kamu akan mendapatkan:</p>
               <ul class="premium-features-list">
                 <li><i class="fas fa-check"></i> Akses ke level latihan lebih tinggi</li>
                 <li><i class="fas fa-check"></i> Dashboard statistik perkembangan</li>
                 <li><i class="fas fa-check"></i> Nilai yang lebih maksimal</li>
               </ul>`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: 'var(--accent)',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Upgrade Sekarang!',
            cancelButtonText: 'Nanti Saja'
          }).then((result) => {
            if (result.isConfirmed) {
              // Redirect to premium signup
              window.location.href = '../payment/premium.php'; // Sesuaikan path jika perlu
            }
          });
        });
      }
    });
    // Create animated stars in the background
    function createStars() {
      const starsContainer = document.getElementById('stars');
      const starCount = 150;
      for (let i = 0; i < starCount; i++) {
        const star = document.createElement('div');
        star.classList.add('star');
        // Random position
        const posX = Math.random() * 100;
        const posY = Math.random() * 100;
        // Random size
        const size = Math.random() * 3;
        // Random duration
        const duration = 3 + Math.random() * 7;
        // Random delay
        const delay = Math.random() * 5;
        star.style.left = `${posX}%`;
        star.style.top = `${posY}%`;
        star.style.width = `${size}px`;
        star.style.height = `${size}px`;
        star.style.setProperty('--duration', `${duration}s`);
        star.style.animationDelay = `${delay}s`;
        starsContainer.appendChild(star);
      }
    }

    // Logout confirmation
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
