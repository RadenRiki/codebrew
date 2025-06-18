<?php
include 'connection.php';

session_start();

$ranking = mysqli_query($conn, "SELECT * FROM user ORDER BY xp_total DESC LIMIT 10");


mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CodeBrew</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../landing-page/index.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      <div class="logo">
        <img src="../assets/Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png" class="logo">
      </div>
        <nav>
          <ul>
            <li><a href="index.php">Beranda</a></li>
            <li><a href="#">Belajar</a></li>
            <li><a href="ranking.php">Ranking</a></li>
            <li><a href="#">Dashboard</a></li>
          </ul>
        </nav>
        <div class="action-buttons">
          <?php if (isset($_SESSION['username'])): ?>
              <!-- Tampilkan ikon profil dan nama pengguna jika login -->
              <?php if (isset($_SESSION['username'])): ?>
                <div class="profile-menu">
                    <span class="greeting">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="../register-login/logout.php" onclick="confirmLogout(event)" class="logout-btn">Logout</a>
                </div>
              <?php endif; ?>
          <?php else: ?>
            <!-- Tampilkan teks "Daftar" jika belum login -->
            <a href="../register-login/login.php">
              <button class="btn">Daftar / Masuk </button>
            </a>
          <?php endif; ?>
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
            echo "<td>" . $row['xp_total'] . ' point' ."</td>";
            echo "</tr>";
            $rank++;
          }
          ?>
        </tbody>
      </table>
    </section>

</body>