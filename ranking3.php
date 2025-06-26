<?php
include 'connection.php';
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
<html>
<head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="initial-scale=1, width=device-width">
  	
  	<link rel="stylesheet"  href="../ranking2.css"/>
  	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"/>
  	
  	
  	
</head>
<body>
  	
  	<div class="ranking">
    		<div class="ranking-child">
    		</div>
    		<div class="ranking-item">
    		</div>
    		<img class="pngtreewhite-light-star-twink" alt="" src="../assets/—Pngtree—white light star twinkle light_7487663 1.png">
    		
    		<img class="pngtreewhite-light-star-twink1" alt="" src="../assets/—Pngtree—white light star twinkle light_7487663 5.png">
    		
    		<img class="pngtreewhite-light-star-twink2" alt="" src="../assets/—Pngtree—white light star twinkle light_7487663 6.png">
    		
    		<img class="pngtreewhite-light-star-twink3" alt="" src="../assets/—Pngtree—white light star twinkle light_7487663 4.png">
    		
    		<img class="pngtreewhite-light-star-twink4" alt="" src="../assets/—Pngtree—white light star twinkle light_7487663 2.png">
    		
    		<img class="pngtreewhite-light-star-twink5" alt="" src="../assets/—Pngtree—white light star twinkle light_7487663 3.png">
    		
    		<img class="pngtreewhite-light-star-twink6" alt="" src="../assets/—Pngtree—white light star twinkle light_7487663 7.png">
    		
    		<img class="desain-tanpa-judul-7" alt="" src="Desain tanpa judul (7).png">
    		
    		<div class="ranking-inner">
    		</div>
    		<div class="ellipse-div">
    		</div>
    		<img class="cuplikan-layar-2025-04-17-2103-icon" alt="" src="Cuplikan_layar_2025-04-17_210300-removebg-preview 1.png">
    		
    		<div class="ranking-child1">
    		</div>
    		<div class="rectangle-div">
    		</div>
    		<img class="cuplikan-layar-2025-04-17-1957-icon" alt="" src="Cuplikan_layar_2025-04-17_195753-removebg-preview 1.png">
    		
    		<div class="ranking-child2">
    		</div>
    		<div class="pintar">PINTAR</div>
    		<div class="dashboar">Dashboar</div>
    		<div class="beranda">Beranda</div>
    		<div class="belajar">Belajar</div>
    		<div class="ranking1">Ranking</div>
    		<img class="image-1-icon" alt="" src="image 1.png">
    		
    		<img class="image-3-icon" alt="" src="image 3.png">
    		
    		<img class="image-5-icon" alt="" src="image 5.png">
    		
    		<img class="image-2-icon" alt="" src="image 2.png">
    		
    		<img class="image-4-icon" alt="" src="image 4.png">
    		
    		<img class="image-6-icon" alt="" src="image 6.png">
    		
    		<img class="ellipse-icon" alt="" src="Ellipse 31.png">
    		
    		<div class="ranking-parent">
      			<div class="ranking2">Ranking</div>
      			<div class="semakin-aktif-kamu">Semakin aktif kamu belajar, semakin tinggi posisimu. Naikkan peringkat dan buktikan kemampuanmu. Jawab lebih banyak kuis dan rebut posisi puncak!</div>
    		</div>
    		<div class="ranking-child3">
    		</div>
    		<div class="rectangle-parent">
                  <section class="ranking">
                    <table class="table table-primary table-borderless ">
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
    		</div>
    		<div class="footter">
      			<img class="pngtreewhite-light-star-twink7" alt="" src="—Pngtree—white light star twinkle light_7487663 8.png">
      			
      			<div class="company-parent">
        				<div class="company">COMPANY</div>
        				<div class="about">About</div>
        				<div class="blog">Blog</div>
        				<div class="help-center">Help Center</div>
        				<div class="pricing">Pricing</div>
      			</div>
      			<div class="languages-parent">
        				<div class="company">Languages</div>
        				<div class="about">HTML</div>
        				<div class="blog">CSS</div>
        				<div class="help-center">JavaScript</div>
        				<div class="pricing">Python</div>
        				<div class="php">PHP</div>
        				<div class="mysql">MySql</div>
      			</div>
      			<div class="pintar-parent">
        				<div class="pintar1">PINTAR</div>
        				<div class="join-here">Join here</div>
      			</div>
      			<div class="copyright-wasabi">Copyright © Wasabi 2025</div>
    		</div>
    		<div class="rectangle-group">
      			<div class="group-child4">
      			</div>
      			<div class="group-child5">
      			</div>
      			<div class="mulai-kerjakan">Mulai Kerjakan!</div>
      			<div class="dapatkan-poin-dari-container">
        				<p class="dapatkan-poin-dari">Dapatkan poin dari setiap studi yang kamu selesaikan.</p>
        				<p class="dapatkan-poin-dari"> Ingin lebih banyak poin? Aktifkan Fitur Pintar dan tingkatkan progresmu lebih cepat!</p>
      			</div>
      			<b class="dapatkan-point-dari">Dapatkan point dari soal </b>
    		</div>
    		<img class="cuplikan-layar-2025-04-17-2123-icon" alt="" src="Cuplikan_layar_2025-04-17_212327-removebg-preview 1.png">
    		
    		<img class="cuplikan-layar-2025-04-17-2128-icon" alt="" src="Cuplikan_layar_2025-04-17_212832-removebg-preview 1.png">
    		
  	</div>
  	
  	
  	
  	
</body>
</html>