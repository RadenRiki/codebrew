<?php
session_start();
// Redirect to login if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
$user = htmlspecialchars($_SESSION['username']);

// Get the selected payment method from the previous page
$paymentMethod = isset($_GET['method']) ? htmlspecialchars($_GET['method']) : '';

// Generate payment details
$qrCode = 'https://api.qrserver.com/v1/create-qr-code/?data=' . uniqid('QRIS-', true);
$virtualAccount = 'VA-' . rand(1000000000, 9999999999);
$gopayNumber = '0812-' . rand(1000, 9999) . '-' . rand(1000, 9999);
$ovoNumber = '0812-' . rand(1000, 9999) . '-' . rand(1000, 9999);
$orderNumber = 'PINTAR-' . rand(1000, 9999) . '-' . date('Ymd');
$expiryTime = date('H:i', strtotime('+30 minutes'));
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
                    <img src="<?php echo $qrCode; ?>" alt="QRIS Code">
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
            countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
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
        updateTimer(); // Initial call to display immediately

        // Check payment status
        document.getElementById('checkStatus').addEventListener('click', () => {
            clearInterval(timerInterval);
            Swal.fire({
                title: 'Selamat Pembayaran Berhasil!',
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
                window.location.href = 'dashboard.php'; // Redirect to dashboard
            });
        });
    </script>
</body>
</html>
