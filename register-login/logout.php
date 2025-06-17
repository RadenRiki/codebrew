<?php
session_start(); // Memulai sesi

// Hapus semua data sesi
$_SESSION = array();

// Jika menggunakan cookie sesi, hapus juga cookie-nya
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Redirect ke beranda dengan notifikasi
header("Location: ../landing-page/index.php?notif=" . urlencode("Logout berhasil!"));
exit;
?>
