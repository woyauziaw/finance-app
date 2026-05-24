<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}

$message = "";
if (isset($_POST['verify'])) {
    $otp = sanitize($_POST['otp']);
    $email = $_SESSION['reset_email'];
    
    $query = "SELECT * FROM users WHERE email = '$email' AND otp_code = '$otp' AND otp_expiry > NOW()";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['otp_verified'] = true;
        header("Location: reset-password.php");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Kode OTP salah atau telah kedaluwarsa!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Verifikasi OTP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container m-auto" style="max-width: 400px;">
        <div class="card p-4 shadow-sm">
            <h3 class="text-center mb-3">Verifikasi OTP</h3>
            <?= $message; ?>
            <p class="text-muted text-center text-sm">Kode OTP telah dikirimkan ke email anda.</p>
            <form action="" method="POST">
                <div class="mb-3">
                    <label>Masukkan 6 Digit OTP</label>
                    <input type="text" name="otp" class="form-control text-center" maxlength="6" required placeholder="000000">
                </div>
                <button type="submit" name="verify" class="btn btn-success w-100">Verifikasi</button>
            </form>
        </div>
    </div>
</body>
</html>
