<?php
require '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim(sanitize($_POST['name']));
    $email = trim(sanitize($_POST['email']));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validasi nama
    if (strlen($username) < 3) {
        $error = 'Nama minimal 3 karakter.';
    }

    // Validasi email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    }

    // Password minimal 8 karakter
    elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    }

    // Harus ada huruf besar
    elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password harus mengandung huruf besar.';
    }

    // Harus ada huruf kecil
    elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password harus mengandung huruf kecil.';
    }

    // Harus ada angka
    elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password harus mengandung angka.';
    }

    // Password tidak boleh sama dengan username
    elseif (strtolower($password) === strtolower($username)) {
        $error = 'Password tidak boleh sama dengan username.';
    }

    // Password tidak boleh sama dengan email
    elseif (strtolower($password) === strtolower($email)) {
        $error = 'Password tidak boleh sama dengan email.';
    }

    // Konfirmasi password
    elseif ($password !== $confirmPassword) {
        $error = 'Konfirmasi password tidak cocok.';
    }

    else {

        // Cek email sudah terdaftar atau belum
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {

            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $insert = $pdo->prepare("
                INSERT INTO users (name, email, password)
                VALUES (?, ?, ?)
            ");

            if ($insert->execute([$username, $email, $hashed])) {
                echo "
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registrasi Berhasil!',
                        text: 'Silakan login',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '/auth/login.php';
                    });
                });
                </script>";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - <?php echo WEB_NAME; ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            background:
            radial-gradient(circle at top left, #2563eb33, transparent 40%),
            radial-gradient(circle at bottom right, #7c3aed33, transparent 40%),
            #0f172a;
        }

        .glass {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.06);
        }

        .input {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
            outline: none;
        }
    </style>
</head>

<body class="flex items-center justify-center px-4 text-white">

    <!-- glow -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-72 h-72 bg-blue-500/20 blur-3xl rounded-full -top-20 -left-20"></div>
        <div class="absolute w-72 h-72 bg-purple-500/20 blur-3xl rounded-full -bottom-20 -right-20"></div>
    </div>

    <!-- card -->
    <div class="glass w-full max-w-md rounded-3xl p-8 shadow-2xl relative">

        <!-- logo -->
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 rounded-2xl from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-xl shadow-lg">
                <img src='../assets/img/1.png'/>
            </div>
        </div>

        <!-- title -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold"><?php echo WEB_NAME; ?></h1>
            <p class="text-slate-400 text-sm mt-1">Buat akun baru</p>
        </div>

        <!-- error -->
        <?php if($error): ?>
            <div class="bg-red-900/50 text-red-400 p-3 rounded-xl mb-4 text-sm text-center font-medium">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- form -->
        <form action="" method="POST" class="space-y-4">

            <div>
                <label class="block text-sm text-slate-300 mb-1.5">Username</label>
                <input type="text" name="name" required class="input w-full px-4 py-3 rounded-xl text-white">
            </div>

            <div>
                <label class="block text-sm text-slate-300 mb-1.5">Email</label>
                <input type="email" name="email" required class="input w-full px-4 py-3 rounded-xl text-white">
            </div>

            <div>
                <label class="block text-sm text-slate-300 mb-1.5">Password</label>
                <input type="password" name="password" required class="input w-full px-4 py-3 rounded-xl text-white">
            </div>
            
            <div>
                <label class="block text-sm text-slate-300 mb-1.5">Confirm Password</label>
                <input type="password" name="confirm_password" required class="input w-full px-4 py-3 rounded-xl text-white">
            </div>

            <button type="submit"
                class="w-full py-3 rounded-xl font-semibold bg-gradient-to-r from-blue-600 to-indigo-600 hover:scale-[1.01] active:scale-[0.98] transition">
                Daftar
            </button>
        </form>

        <!-- footer -->
        <p class="text-center text-sm text-slate-400 mt-6">
            Udah punya akun?
            <a href="/auth/login.php" class="text-blue-400 font-semibold hover:underline">
                Masuk
            </a>
        </p>

    </div>

</body>
</html>