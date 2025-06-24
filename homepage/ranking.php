<?php
include '../connection.php';
session_start();

// Ambil data ranking
$ranking = mysqli_query($conn, "SELECT * FROM user ORDER BY xp_total DESC LIMIT 10");

// Deteksi apakah user premium (misal dari database, disini diasumsikan session atau default false)
$is_premium = false;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $result = mysqli_query($conn, "SELECT is_premium FROM user WHERE username = '$username'");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $is_premium = $row['is_premium'] == 1;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CodeBrew - Ranking</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="../landing-page/index.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .user-profile-container {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .greeting {
      color: white;
      font-size: 16px;
      font-weight: 600;
    }
    .premium-indicator {
      color: gold;
      margin-left: 5px;
    }
    .profile-menu {
      position: relative;
    }
    .profile-btn {
      background: linear-gradient(45deg, #da6aff, #a66bff);
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: white;
      position: relative;
    }
    .premium-crown {
      position: absolute;
      top: -5px;
      right: -5px;
      font-size: 14px;
    }
    .profile-dropdown {
  display: none;
  position: absolute;
  top: 50px;
  right: 0;
  background-color: #2a0d45;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
  min-width: 160px;
  z-index: 100;
  overflow: hidden;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  color: #f0f0f0;
  text-decoration: none;
  font-weight: 500;
  background-color: transparent;
  transition: background-color 0.2s ease;
}

.dropdown-item i {
  color: #d5cfff;
  font-size: 16px;
}

.dropdown-item:hover {
  background-color: #3c1862;
}

.logout-item {
  color: #ff7e94;
  border-top: 1px solid #4b2d68;
  margin-top: 4px;
}

.logout-item i {
  color: #ff7e94;
}

.premium-status {
  color: gold;
  background-color: #381564;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.dropdown-divider {
  height: 1px;
  background-color: #4b2d68;
  margin: 0 8px;
}

  </style>
</head>

<body>
  <div class="stars" id="stars"></div>

  <!-- Header -->
  <header>
    <div class="logo">
      <img src="../assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" class="logo">
    </div>
    <nav>
      <ul>
        <li><a href="index.php">Beranda</a></li>
        <li><a href="#">Belajar</a></li>
        <li><a href="../ranking.php">Ranking</a></li>
        <li><a href="#">Dashboard</a></li>
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
                <a href="settings.php" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="../register-login/logout.php" class="dropdown-item logout-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
  </header>

  <section class="ranking">
    <h1>Ranking</h1>
    <table>
      <thead>
        <tr>
          <th>Rank</th>
          <th>Nama</th>
          <th>XP Total</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $rank = 1;
        while ($row = mysqli_fetch_assoc($ranking)) {
          echo "<tr>";
          echo "<td>" . $rank . "</td>";
          echo "<td>" . $row['username'] . "</td>";
          echo "<td>" . $row['xp_total'] . ' point' . "</td>";
          echo "</tr>";
          $rank++;
        }
        ?>
      </tbody>
    </table>
  </section>

  <script>
    // Toggle dropdown
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    profileBtn.addEventListener('click', () => {
      profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
    });

    // Close dropdown on outside click
    window.addEventListener('click', function(e) {
      if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.style.display = 'none';
      }
    });
  </script>
</body>
</html>
