<?php
session_start()
require_once 'config/database.php';

if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}

$message = "";
if (isset($_POST['reset'])) {
    $password_baru = sanitize($_POST['password']);
    $konfirmasi = sanitize($_POST['confirm_password']);
    $email = $_SESSION['reset_email'];
    
    if ($password_baru === $konfirmasi) {
        // Enkripsi MD5 agar cocok dengan setelan login XAMPP default bawaan database.sql
        $password_encrypt = md5($password_baru); 
        
        // Update password baru & bersihkan kolom OTP agar tidak bisa dipakai ulang
        $update = "UPDATE users SET password = '$password_encrypt', otp_code = NULL, otp_expiry = NULL WHERE email = '$email'";
        if (mysqli_query($conn, $update)) {
            session_destroy();
            echo "<script>alert('Password berhasil diubah! Silakan login kembali.'); window.location='login.php';</script>";
            exit();
        }
    } else {
        $message = "<div class='alert alert-danger'>Konfirmasi password tidak cocok!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container m-auto" style="max-width: 400px;">
        <div class="card p-4 shadow-sm">
            <h3 class="text-center mb-3">Password Baru</h3>
            <?= $message; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label>Password Baru</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                <button type="submit" name="reset" class="btn btn-primary w-100">Ubah Password</button>
            </form>
        </div>
    </div>
</body>
</html>
