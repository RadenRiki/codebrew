<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Koneksi database
require_once '../connection.php';  // pastikan di connection.php ada: $conn = new mysqli(...);

// Ambil data user dari session
$userId = intval($_SESSION['user_id']);
$user   = htmlspecialchars($_SESSION['username']);

// Ambil metode & jumlah pembayaran (dikirim via GET dari halaman sebelumnya)
$paymentMethod = isset($_GET['method']) ? htmlspecialchars($_GET['method']) : '';
$amount        = isset($_GET['amount']) ? floatval($_GET['amount']) : 0.00;

// Generate order number dan detail payment
$orderNumber    = 'PINTAR-' . strtoupper(uniqid());
$qrCodeURL      = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($orderNumber);
$virtualAccount = 'VA-' . rand(1000000000, 9999999999);
$ovoNumber      = '0812-3456-7890';  // nomor OVO tujuan
$gopayNumber = '0812-3456-7890';

// set expiry time 30 menit dari sekarang
$expiryTimestamp = time() + 30 * 60;
$expiryTime      = date('H:i:s', $expiryTimestamp);

// Simpan record payment dengan status 'pending'
$stmt = $conn->prepare("
    INSERT INTO payments
      (user_id, order_number, payment_method, amount, payment_status)
    VALUES
      (?,        ?,            ?,              ?,      'pending')
");
$stmt->bind_param("issd", $userId, $orderNumber, $paymentMethod, $amount);
if (!$stmt->execute()) {
    die("Error inserting payment: " . $stmt->error);
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - PINTAR</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="index.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(180deg, #652d86, #09020f 77.4%);
            color: #fff;
            margin: 0;
            padding: 0;
        }
        
        .payment-confirmation {
            min-height: 100vh;
            padding: 80px 20px;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            color: #e6baff;
        }
        
        .order-details {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: left;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .order-details h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #cf7dff;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 10px;
        }
        
        .order-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .order-info .label {
            color: rgba(255,255,255,0.7);
        }
        
        .order-info .value {
            font-weight: 500;
        }
        
        .payment-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .payment-instruction {
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .qr-code {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px auto;
            width: 250px;
            height: 250px;
        }
        
        .qr-code img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 10px;
            border: 2px solid rgba(207, 125, 255, 0.5);
            padding: 10px;
            background: white;
        }
        
        .payment-number {
            background: rgba(207, 125, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 1px;
            margin: 15px 0;
        }
        
        .timer {
            font-size: 20px;
            margin: 25px 0;
        }
        
        .countdown {
            font-weight: 700;
            color: #ff7d7d;
        }
        
        .check-status {
            margin-top: 20px;
            padding: 12px 30px;
            border-radius: 30px;
            background: linear-gradient(90deg, #a367dc, #cf7dff);
            color: #fff;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-block;
            font-weight: 600;
            border: none;
            box-shadow: 0 4px 12px rgba(163, 103, 220, 0.3);
        }
        
        .check-status:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(163, 103, 220, 0.4);
            background: linear-gradient(90deg, #9760d0, #c16ef5);
        }
        
        .footer-note {
            margin-top: 30px;
            color: rgba(255,255,255,0.5);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="payment-confirmation">
        <h1>Konfirmasi Pembayaran PINTAR Premium</h1>
        
        <!-- Order Details Section -->
        <div class="order-details">
            <h3>Detail Pembelian</h3>
            <div class="order-info">
                <span class="label">No. Pesanan:</span>
                <span class="value"><?php echo $orderNumber; ?></span>
            </div>
            <div class="order-info">
                <span class="label">Nama Produk:</span>
                <span class="value">PINTAR Premium Lifetime</span>
            </div>
            <div class="order-info">
                <span class="label">Harga:</span>
                <span class="value">Rp100.000</span>
            </div>
            <div class="order-info">
                <span class="label">Status:</span>
                <span class="value" style="color: #ffde7d;">Menunggu Pembayaran</span>
            </div>
            <div class="order-info">
                <span class="label">Batas Pembayaran:</span>
                <span class="value"><?php echo $expiryTime; ?></span>
            </div>
        </div>
        
        <!-- Payment Method Section -->
        <div class="payment-container">
            <div class="payment-instruction">
                <?php if ($paymentMethod == 'qris'): ?>
                    <p>Silakan scan QR Code di bawah ini:</p>
                <?php elseif ($paymentMethod == 'gopay'): ?>
                    <p>Silakan transfer ke nomor GoPay:</p>
                <?php elseif ($paymentMethod == 'ovo'): ?>
                    <p>Silakan transfer ke nomor OVO:</p>
                <?php elseif ($paymentMethod == 'bankTransfer'): ?>
                    <p>Silakan transfer ke rekening berikut:</p>
                <?php else: ?>
                    <p>Metode pembayaran tidak valid.</p>
                <?php endif; ?>
            </div>
            
            <?php if ($paymentMethod == 'qris'): ?>
                <div class="qr-code">
                    <img src="<?php echo $qrCodeURL; ?>" alt="QRIS Code">
                </div>
                <p style="margin-top: -10px; color: rgba(255,255,255,0.7);">Arahkan kamera Anda ke QR Code di atas</p>
            <?php elseif ($paymentMethod == 'gopay'): ?>
                <div class="payment-number">
                    <?php echo $gopayNumber; ?>
                </div>
                <p style="color: rgba(255,255,255,0.7);">Gunakan nomor ini di aplikasi GoPay Anda</p>
            <?php elseif ($paymentMethod == 'ovo'): ?>
                <div class="payment-number">
                    <?php echo $ovoNumber; ?>
                </div>
                <p style="color: rgba(255,255,255,0.7);">Gunakan nomor ini di aplikasi OVO Anda</p>
            <?php elseif ($paymentMethod == 'bankTransfer'): ?>
                <div class="payment-number">
                    <?php echo $virtualAccount; ?>
                </div>
                <p style="color: rgba(255,255,255,0.7);">
                    <strong>Bank Mandiri</strong><br>
                    A/n: PT CodeBrew Indonesia
                </p>
            <?php endif; ?>
        </div>
        
        <div class="timer">
            Waktu tersisa: <span class="countdown" id="countdown">30:00</span>
        </div>
        
        <button class="check-status" id="checkStatus">CEK STATUS PEMBAYARAN</button>
        
        <div class="footer-note">
            Jika sudah melakukan pembayaran tetapi status belum berubah,<br>
            silakan klik tombol di atas atau hubungi admin.
        </div>
    </div>

    <script>
    // Countdown timer (30 minutes)
    let minutes = 29;
    let seconds = 59;
    const countdownElement = document.getElementById('countdown');

    function updateTimer() {
      countdownElement.textContent =
        `${minutes.toString().padStart(2,'0')}:${seconds.toString().padStart(2,'0')}`;

      if (seconds === 0) {
        if (minutes === 0) {
          clearInterval(timerInterval);
          showTimeExpired();
          return;
        }
        minutes--;
        seconds = 59;
      } else {
        seconds--;
      }
    }

    function showTimeExpired() {
      Swal.fire({
        title: 'Waktu Pembayaran Habis!',
        text: 'Silakan memulai proses pembayaran kembali.',
        icon: 'warning',
        confirmButtonColor: '#cf7dff',
        confirmButtonText: 'OK'
      }).then(() => {
        window.history.back();
      });
    }

    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer(); // tampilkan segera

    // Check payment status
    document.getElementById('checkStatus').addEventListener('click', () => {
      clearInterval(timerInterval);

      const xhr = new XMLHttpRequest();
      xhr.open("POST", "update_user_status.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onload = function() {
        if (xhr.status === 200) {
          let res;
          try {
            res = JSON.parse(xhr.responseText);
          } catch (e) {
            console.error("Invalid JSON:", xhr.responseText);
            return;
          }
          if (res.success) {
            Swal.fire({
              title: 'Pembayaran Berhasil!',
              html: `
                <div style="text-align:left; margin:20px 0;">
                  <p><strong>No. Pesanan:</strong> <?php echo $orderNumber; ?></p>
                  <p><strong>Produk:</strong> PINTAR Premium</p>
                  <p><strong>Status:</strong> <span style="color:#7dffaa;">Aktif</span></p>
                </div>
                <p>Terima kasih telah berlangganan PINTAR Premium!</p>
              `,
              icon: 'success',
              confirmButtonColor: '#cf7dff',
              confirmButtonText: 'Masuk ke Dashboard'
            }).then(() => {
              window.location.href = '../homepage/index.php';
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: res.message || 'Terjadi kesalahan saat memperbarui status. Silakan coba lagi.',
              icon: 'error',
              confirmButtonColor: '#cf7dff'
            });
          }
        } else {
          Swal.fire({
            title: 'Error Server!',
            text: 'Tidak dapat menghubungi server. Status: ' + xhr.status,
            icon: 'error',
            confirmButtonColor: '#cf7dff'
          });
        }
      };
      // kirim user_id + order_number
      xhr.send(
        'user_id=' + encodeURIComponent('<?php echo $userId; ?>') +
        '&order_number=' + encodeURIComponent('<?php echo $orderNumber; ?>')
      );
    });
  </script>
</body>
</html>
