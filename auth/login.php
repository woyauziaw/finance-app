<?php
session_start();
require '../config/database.php';
$preview_image = $WEB_DOMAIN . "/assets/img/2.jpg"; 
$error = '';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {

    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE remember_token = ?"
    );

    $stmt->execute([$_COOKIE['remember_token']]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_photo'] = $user['photo'];
        $_SESSION['role'] = $user['role'];
    }
}
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='../pages/dashboard.php';</script>";
    exit();
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
   
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_photo'] = $user['photo'];
        $_SESSION['role'] = $user['role'];
        // Remember Login 30 Hari
        $token = bin2hex(random_bytes(32)); 
        
        $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmt->execute([$token, $user['id']]); 
        setcookie(
          'remember_token',
          $token,
          time() + (86400 * 30),
          '/',
          '',
          false,
          true
        );
        
        echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Selamat datang kembali!', timer: 1200, showConfirmButton: false}).then(() => { window.location.href = '../pages/dashboard.php'; }); });</script>";
    } else { $error = 'Email atau Password tidak valid!!'; }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title><?php echo WEB_NAME; ?> | Login</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="<?= $WEB_NAME ?> | Login">
    <meta name="description" content="Kelola pemasukan, pengeluaran, target tabungan, dan laporan keuangan dalam satu dashboard modern.">
    <meta name="keywords" content="finance app, keuangan, budgeting, tabungan, target keuangan, laporan keuangan">
    <meta name="author" content="WOYCX">
    <meta name="robots" content="index, follow">
    <meta name="language" content="id">
    <meta name="theme-color" content="#4F46E5">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $WEB_DOMAIN ?>">
    <meta property="og:title" content="<?= $WEB_NAME ?> | Login">
    <meta property="og:description" content="Kelola pemasukan, pengeluaran, target tabungan, dan laporan keuangan dalam satu dashboard modern.">
    <meta property="og:image" content="<?= $preview_image ?>">
    <meta property="og:image:secure_url" content="<?= $preview_image ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:site_name" content="<?= $WEB_NAME ?> | Login">
    <meta property="og:locale" content="id_ID">
    
    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $WEB_NAME ?> | Login">
    <meta name="twitter:description" content="Kelola pemasukan, pengeluaran, target tabungan, dan laporan keuangan dalam satu dashboard modern.">
    <meta name="twitter:image" content="<?= $preview_image ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="apple-touch-icon" href="/assets/img/favicon.png">
    
    <!-- Tailwind Configuration -->
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
        
        /* Input styling yang halus */
        .input-seamless {
            transition: all 0.3s ease;
        }
        .input-seamless:focus {
            box-shadow: 0 4px 20px -2px rgba(79, 70, 229, 0.15);
            transform: translateY(-1px);
        }

        /* Animasi masuk */
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
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased overflow-x-hidden gradient-bg h-screen flex flex-col selection:bg-primary selection:text-white relativetransition-colors duration-300">

    <!-- SOLUSI BUG: Background Decorative Glows dibungkus dalam div fixed dan overflow-hidden -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute top-0 -right-10 w-96 h-96 bg-indigo-200/50 dark:bg-indigo-900/30 rounded-full glow-effect"></div>
        <div class="absolute bottom-10 -left-10 w-[30rem] h-[30rem] bg-purple-200/40 dark:bg-indigo-900/25 rounded-full glow-effect"></div>
    </div>

    <!-- Navbar Minimalis -->
    <nav class="w-full z-50 top-0 pt-6 px-6 lg:px-12 absolute">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="../index.php" class="flex items-center gap-2 group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-xl transform group-hover:rotate-12 transition">
                    <img src="/assets/img/app_icon_dark.png"/>
                </div>
                <span class="font-bold text-2xl text-slate-900 dark:text-slate-200 tracking-tight">WOYCX</span>
            </a>
            <a href="../index.php" class="text-sm font-semibold text-slate-500 dark:text-slate-300 hover:text-primary transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Beranda
            </a>
        </div>
    </nav>
     
    <!-- Main Content Container -->
    <div class="flex-1 flex items-center justify-center px-4 relative z-10 w-full max-w-[100vw]">
        
        <div class="w-full max-w-md animate-fade-up">
            
            <!-- Judul -->
            <div class="text-center mb-8">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight mb-2">Selamat Datang Kembali</h1>
                <p class="text-slate-500 dark:text-slate-400 font-medium">Lanjutkan progres finansialmu hari ini.</p>
            </div>

            <!-- Error Alert -->
            <?php if($error): ?>
            <div class="bg-red-50/80 backdrop-blur-sm border border-red-100 text-red-500 px-4 py-3 rounded-2xl mb-6 text-sm font-semibold flex items-center gap-3 shadow-sm dark:bg-red-900/30 dark:border-red-800 dark:text-red-400">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= $error ?>
            </div>
            <?php endif; ?>

            <!-- Form Wrapper -->
            <div class="bg-white/60 backdrop-blur-xl border border-white p-8 sm:p-10 rounded-[2rem] shadow-[0_24px_48px_-15px_rgba(0,0,0,0.05)] dark:bg-slate-800/60 dark:border-slate-700 dark:shadow-[0_24px_48px_-15px_rgba(0,0,0,0.4)]"> 
                
                <form action="" method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2 ml-1">Email</label>
                        <input type="email" name="email" required placeholder="nama@email.com" 
                               class="input-seamless w-full px-5 py-4 bg-white/80 border-slate-200/80 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-slate-900/80 dark:border-slate-700 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary">
                    </div>
                    
                    <div>
                        <div class="flex justify-between items-center mb-2 ml-1">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">Password</label>
                            <a href="#" class="text-xs font-bold text-primary dark:text-slate-200 hover:text-primaryHover transition">Lupa password?</a>
                        </div>
                        <input type="password" name="password" required placeholder="••••••••" 
                               class="input-seamless w-full px-5 py-4 bg-white/80 border border-slate-200/80 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-slate-900/80 dark:border-slate-700 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary"> 
                    </div>
                    
                    <!-- Tombol -->
                    <button type="submit" 
                            class="w-full py-4 mt-2 rounded-2xl font-bold text-lg text-white bg-primary hover:bg-primaryHover transition-all duration-300 shadow-xl shadow-primary/30 hover:shadow-primary/50 hover:-translate-y-1 flex justify-center items-center gap-2">
                        Masuk
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>

            </div>

            <p class="text-center text-slate-500 dark:text-slate-400 mt-8 font-medium">
                Belum bergabung? 
                <a href="/auth/register.php" class="text-primary font-bold hover:text-primaryHover transition ml-1 decoration-2 hover:underline underline-offset-4 dark:hover:text-primaryHover">Buat akun sekarang</a>
            </p>
            
        </div>
    </div>
</body>
</html>

