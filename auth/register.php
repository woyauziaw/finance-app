<?php
require '../config/database.php';
$preview_image = $WEB_DOMAIN . "/assets/img/2.jpg"; 
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
                        showConfirmButton: false,
                        backdrop: `rgba(79,70,229,0.15)`
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
<html lang="id" class="overflow-x-hidden">
<head>
    <title><?php echo WEB_NAME; ?> | Register</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="<?= $WEB_NAME ?> | Register">
    <meta name="description" content="Kelola pemasukan, pengeluaran, target tabungan, dan laporan keuangan dalam satu dashboard modern.">
    <meta name="keywords" content="finance app, keuangan, budgeting, tabungan, target keuangan, laporan keuangan">
    <meta name="author" content="WOYCX">
    <meta name="robots" content="index, follow">
    <meta name="language" content="id">
    <meta name="theme-color" content="#4F46E5">
    
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $WEB_DOMAIN ?>">
    <meta property="og:title" content="<?= $WEB_NAME ?> | Register">
    <meta property="og:description" content="Kelola pemasukan, pengeluaran, target tabungan, dan laporan keuangan dalam satu dashboard modern.">
    <meta property="og:image" content="<?= $preview_image ?>">
    <meta property="og:image:secure_url" content="<?= $preview_image ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:site_name" content="<?= $WEB_NAME ?> | Register">
    <meta property="og:locale" content="id_ID">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $WEB_NAME ?> | Register">
    <meta name="twitter:description" content="Kelola pemasukan, pengeluaran, target tabungan, dan laporan keuangan dalam satu dashboard modern.">
    <meta name="twitter:image" content="<?= $preview_image ?>">
    
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="apple-touch-icon" href="/assets/img/favicon.png">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        primaryHover: '#4338CA',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        /* CSS yang identik dengan Landing Page */
        .gradient-bg { background: radial-gradient(100% 150% at 0% 0%, #EEF2FF 0%, #F8FAFC 100%); }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Background Gradient: Gelap */
        html.dark .gradient-bg { background: radial-gradient(100% 150% at 0% 0%, #0f172a 0%, #020617 100%); }
        
        .glow-effect { filter: blur(120px); }
        
        .input-seamless {
            transition: all 0.3s ease;
        }
        .input-seamless:focus {
            box-shadow: 0 4px 20px -2px rgba(79, 70, 229, 0.15);
            transform: translateY(-1px);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up {
            animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
    <script>
        (function () {
        const root = document.documentElement;
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.classList.toggle('dark', isDark);
    })();
    </script> 
</head>

<body class="bg-slate-50 text-slate-800 dark:bg-slate-950 bg-slate-50 dark:text-slate-200 antialiased overflow-x-hidden gradient-bg min-h-screen flex flex-col selection:bg-primary selection:text-white relative">

    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute top-0 -right-10 w-96 h-96 bg-indigo-200/50 dark:bg-indigo-900/30 rounded-full glow-effect"></div>
        <div class="absolute bottom-10 -left-10 w-[30rem] h-[30rem] bg-purple-200/40 dark:bg-purple-900/25 rounded-full glow-effect"></div>
    </div>

    <nav class="w-full z-50 top-0 pt-6 px-6 lg:px-12 absolute">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="../index.php" class="flex items-center gap-2 group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center border border-slate-100 dark:border-slate-700 overflow-hidden transform group-hover:rotate-12 transition duration-300">
                    <img src="/assets/img/app_icon_dark.png" alt="W"/>
                </div>
                <span class="font-bold text-2xl text-slate-900 dark:text-white tracking-tight"><?php echo WEB_NAME; ?></span>
            </a>
            <a href="../index.php" class="text-sm font-semibold text-slate-500 dark:text-slate-400 dark:hover:text-primary hover:text-primary transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Beranda
            </a>
        </div>
    </nav>

    <div class="flex-1 flex flex-col items-center justify-center px-4 py-24 relative z-10 w-full max-w-[100vw]">
        
        <div class="w-full max-w-md animate-fade-up">

            <div class="text-center mb-8">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Buat Akun Baru</h1>
                <p class="text-slate-500 dark:text-slate-400 font-medium">Mulai kelola keuanganmu dengan lebih pintar.</p>
            </div>

            <?php if($error): ?>
                <div class="bg-red-50/80 backdrop-blur-sm border border-red-100 text-red-500 px-4 py-3 rounded-2xl mb-6 text-sm font-semibold flex items-center gap-3 shadow-sm dark:bg-red-900/30 dark:border-red-800 dark:text-red-400">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="bg-white/60 backdrop-blur-xl border border-white p-8 sm:p-10 rounded-[2rem] shadow-[0_24px_48px_-15px_rgba(0,0,0,0.05)] dark:bg-slate-800/60 dark:border-slate-700 dark:shadow-[0_24px_48px_-15px_rgba(0,0,0,0.4)]">
                
                <form action="" method="POST" class="space-y-5">
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">Username</label>
                        <input type="text" name="name" required placeholder="Nama Anda" 
                               class="input-seamless w-full px-5 py-3.5 bg-white/80 border border-slate-200/80 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-slate-900/80 dark:border-slate-700 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">Email</label>
                        <input type="email" name="email" required placeholder="nama@email.com" 
                               class="input-seamless w-full px-5 py-3.5 bg-white/80 border border-slate-200/80 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-slate-900/80 dark:border-slate-700 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">Password</label>
                        <input type="password" name="password" required placeholder="Minimal 8 karakter" 
                               class="input-seamless w-full px-5 py-3.5 bg-white/80 border border-slate-200/80 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-slate-900/80 dark:border-slate-700 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 ml-1">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" required placeholder="Ulangi password Anda" 
                               class="input-seamless w-full px-5 py-3.5 bg-white/80 border border-slate-200/80 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-slate-900/80 dark:border-slate-700 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary">
                    </div>

                    <button type="submit" 
                            class="w-full py-4 mt-4 rounded-2xl font-bold text-lg text-white bg-primary hover:bg-primaryHover transition-all duration-300 shadow-xl shadow-primary/30 hover:shadow-primary/50 hover:-translate-y-1 flex justify-center items-center gap-2">
                        Daftar Sekarang
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>

            </div>

            <p class="text-center text-slate-500 dark:text-slate-400 mt-8 font-medium">
                Sudah punya akun? 
                <a href="/auth/login.php" class="text-primary font-bold hover:text-primaryHover transition ml-1 decoration-2 hover:underline underline-offset-4 dark:hover:text-primaryHover">Masuk di sini</a>
            </p>

        </div>
    </div>

</body>
</html>
