<?php
require_once 'config/database.php';

// Load PHPMailer secara manual (cocok untuk XAMPP tanpa composer)
require 'vendor/phpmailer/Exception.php';
require 'vendor/phpmailer/PHPMailer.php';
require 'vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if (isset($_POST['submit'])) {
    $email = sanitize($_POST['email']);
    
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        // Generate 6 Digit OTP
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));
        
        // Simpan ke database
        mysqli_query($conn, "UPDATE users SET otp_code = '$otp', otp_expiry = '$expiry' WHERE email = '$email'");
        
        // Kirim Email via SMTP Gmail (atau SMTP mailer XAMPP lainnya)
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'emailkamu@gmail.com'; // Ganti dengan Gmail kamu
            $mail->Password   = 'password_aplikasi_kamu'; // Ganti dengan Password Aplikasi Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('no-reply@keuangan.com', 'Sistem Keuangan');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Kode OTP Forgot Password';
            $mail->Body    = "Status keamanan akun Anda. Berikut adalah kode OTP Anda: <b>$otp</b><br>Kode ini berlaku selama 15 menit.";

            $mail->send();
            $_SESSION['reset_email'] = $email;
            header("Location: verify-otp.php");
            exit();
        } catch (Exception $e) {
            $message = "<div class='alert alert-danger'>Email gagal dikirim. Error: {$mail->ErrorInfo}</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Email tidak terdaftar di sistem kami!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container m-auto" style="max-width: 400px;">
        <div class="card p-4 shadow-sm">
            <h3 class="text-center mb-3">Lupa Password</h3>
            <?= $message; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label>Masukkan Email Terdaftar</label>
                    <input type="email" name="email" class="form-control" required placeholder="contoh@email.com">
                </div>
                <button type="submit" name="submit" class="btn btn-primary w-100">Kirim Kode OTP</button>
                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none">Kembali ke Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
