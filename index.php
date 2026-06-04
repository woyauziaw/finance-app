<?php
session_start();
require 'config/database.php';
$preview_image = WEB_DOMAIN . "/assets/img/2.jpg"; 
$page_title = WEB_NAME . ' — Financial App';
$current_page = basename($_SERVER['PHP_SELF']);
$page_desc = "Kelola pemasukan, pengeluaran, target tabungan, dan laporan keuangan dalam satu dashboard modern.";
/**
 * AUTO LOGIN (remember token)
 */
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {

    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$_COOKIE['remember_token']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_photo'] = $user['photo'];
        $_SESSION['role'] = $user['role'];
    }
}

$isLogin = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <meta name="title" content="<?= $WEB_NAME ?> | <?= $page_title ?>">
    <meta name="description" content="<?= $page_desc ?>">
    <meta name="keywords" content="finance app, keuangan, budgeting, tabungan, target keuangan, laporan keuangan">
    <meta name="author" content="WOYCX">
    <meta name="robots" content="index, follow">
    <meta name="language" content="id">
    <meta name="theme-color" content="#4F46E5">
    
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $WEB_DOMAIN ?>">
    <meta property="og:title" content="<?= $WEB_NAME ?> | <?= $page_title ?>">
    <meta property="og:description" content="<?= $page_desc ?>">
    <meta property="og:image" content="<?= $preview_image ?>">
    <meta property="og:image:secure_url" content="<?= $preview_image ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:site_name" content="<?= $WEB_NAME ?>">
    <meta property="og:locale" content="id_ID">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $WEB_NAME ?> | <?= $page_title ?>">
    <meta name="twitter:description" content="<?= $page_desc ?>">
    <meta name="twitter:image" content="<?= $preview_image ?>">
    
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="apple-touch-icon" href="/assets/img/favicon.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Script app.js yang mendeteksi mode gelap -->
    
    <script>
        tailwind.config = {
            darkMode: 'class', // <-- FITUR DARK MODE DIAKTIFKAN DI SINI
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        primaryHover: '#4338CA',
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts & Animate On Scroll (AOS) -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Background Gradient: Terang */
        .gradient-bg { background: radial-gradient(100% 150% at 0% 0%, #EEF2FF 0%, #F8FAFC 100%); }
        /* Background Gradient: Gelap */
        html.dark .gradient-bg { background: radial-gradient(100% 150% at 0% 0%, #0f172a 0%, #020617 100%); }
        
        .glow-effect { filter: blur(120px); }
    </style>
    <script>
        (function () {
        const root = document.documentElement;
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.classList.toggle('dark', isDark);
    })();
    </script> 
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased overflow-x-hidden transition-colors duration-300">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 top-0 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border-b border-slate-200/60 dark:border-slate-800/60 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-xl transform hover:rotate-12 transition">
                        <img src="/assets/img/app_icon_dark.png"/>
                    </div>
                    <span class="font-bold text-2xl text-slate-900 dark:text-white tracking-tight"><?= WEB_NAME ?></span>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#fitur" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary font-semibold transition">Fitur</a>
                    <a href="#testimoni" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary font-semibold transition">Testimoni</a>
                    <a href="#faq" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary font-semibold transition">FAQ</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="auth/login.php" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary font-semibold px-2 py-1.5 transition">Masuk</a>
                    <a href="auth/register.php" class="bg-primary hover:bg-primaryHover text-white px-3 py-2 rounded-xl font-semibold transition-all shadow-lg shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5">Daftar Gratis</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-24 lg:pt-44 lg:pb-36 overflow-hidden gradient-bg">
        <!-- Background Decorative Glows -->
        <div class="absolute top-20 right-0 w-96 h-96 bg-indigo-200/40 dark:bg-indigo-600/20 rounded-full glow-effect -z-10"></div>
        <div class="absolute bottom-0 left-10 w-96 h-96 bg-purple-200/30 dark:bg-purple-600/20 rounded-full glow-effect -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                <div class="text-center lg:text-left lg:col-span-6" data-aos="fade-right" data-aos-duration="1000">
                    <div class="inline-flex items-center gap-2 bg-indigo-50 dark:bg-indigo-900/30 text-primary dark:text-indigo-300 font-bold px-4 py-2 rounded-full mb-6 border border-indigo-100/80 dark:border-indigo-500/30 text-sm transition-colors">
                        <span>🚀</span> Finansial Lebih Baik Tahun Ini
                    </div>
                    <h1 class="text-5xl lg:text-6xl font-extrabold text-slate-900 dark:text-white leading-tight mb-6 tracking-tight transition-colors">
                        Pegang Kendali <br> <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-indigo-600 to-purple-600">Keuanganmu</span>
                    </h1>
                    <p class="text-lg text-slate-600 dark:text-slate-400 mb-8 max-w-xl mx-auto lg:mx-0 font-medium leading-relaxed transition-colors">
                        Kelola pemasukan, awasi pengeluaran, capai target tabungan, dan analisis laporan keuangan dalam satu dashboard modern yang super cepat.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="auth/register.php" class="bg-primary hover:bg-primaryHover text-white px-8 py-4 rounded-xl font-bold text-lg transition-all shadow-xl shadow-primary/30 hover:shadow-primary/50 flex items-center justify-center gap-2 hover:-translate-y-1">
                            Mulai Sekarang
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </a>
                        <a href="pages/dashboard.php" class="bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 px-8 py-4 rounded-xl font-bold text-lg transition shadow-sm flex items-center justify-center">
                            <i data-lucide="layout-dashboard"></i> Dashboard 
                        </a>
                    </div>
                </div>
                
                <!-- Interactive 3D Card Image -->
                <div class="relative hidden lg:block lg:col-span-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                    <div class="js-tilt bg-white dark:bg-slate-800 p-3 rounded-2xl shadow-[0_32px_64px_-16px_rgba(79,70,229,0.15)] dark:shadow-[0_32px_64px_-16px_rgba(0,0,0,0.5)] border border-slate-100 dark:border-slate-700 cursor-pointer transition-colors" data-tilt-max="10" data-tilt-speed="400">
                        <div class="bg-slate-50 dark:bg-slate-900 rounded-xl overflow-hidden aspect-[4/3] relative border border-slate-100 dark:border-slate-700">
                            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Dashboard Preview" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-slate-950/10 to-transparent"></div>
                            <div class="absolute bottom-8 left-8 text-white">
                                <span class="bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider mb-2 inline-block">SaaS Interface</span>
                                <p class="font-bold text-2xl">Dashboard Kontrol</p>
                                <p class="text-sm text-slate-200/90 mt-1">Semua data finansial real-time di satu layar.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Floating Widget -->
                    <div class="absolute -top-6 -left-6 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-slate-100/80 dark:border-slate-700/80 animate-bounce transition-colors" style="animation-duration: 4s;">
                        <div class="flex items-center gap-3">
                            <div class="bg-emerald-500 p-2 rounded-xl text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-semibold">Pemasukan Bulan Ini</p>
                                <p class="text-base font-bold text-slate-800 dark:text-white">+ Rp 14.250.000</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-28 bg-white dark:bg-slate-950 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20" data-aos="fade-up">
                <h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white mb-4 tracking-tight">Kenapa Memilih WOYCX Finance?</h2>
                <p class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto font-medium text-lg">Hentikan kebiasaan bocor halus pada dompetmu. Mulai kelola masa depan finansial yang kokoh hari ini.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Fitur 1 -->
                <div class="p-8 bg-slate-50/60 dark:bg-slate-900/60 rounded-3xl border border-slate-200/60 dark:border-slate-800 hover:bg-white dark:hover:bg-slate-900 hover:shadow-[0_24px_48px_-15px_rgba(0,0,0,0.05)] transition-all duration-300 group" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl flex items-center justify-center mb-8 group-hover:bg-primary group-hover:text-white transition-all duration-300 shadow-sm">
                        <svg class="w-7 h-7 text-primary dark:text-indigo-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3 group-hover:text-primary dark:group-hover:text-indigo-400 transition-colors">Laporan Otomatis</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed font-medium">Pantau arus kas bulanan dan tahunan dengan grafik interaktif yang bersih. Tidak perlu lagi pusing rumus Excel manual.</p>
                </div>
                
                <!-- Fitur 2 -->
                <div class="p-8 bg-slate-50/60 dark:bg-slate-900/60 rounded-3xl border border-slate-200/60 dark:border-slate-800 hover:bg-white dark:hover:bg-slate-900 hover:shadow-[0_24px_48px_-15px_rgba(0,0,0,0.05)] transition-all duration-300 group" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center mb-8 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300 shadow-sm">
                        <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Target Tabungan</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed font-medium">Setel impian finansialmu (gadget baru, dana darurat, investasi) dan pantau persentase progres pencapaiannya secara visual.</p>
                </div>

                <!-- Fitur 3 -->
                <div class="p-8 bg-slate-50/60 dark:bg-slate-900/60 rounded-3xl border border-slate-200/60 dark:border-slate-800 hover:bg-white dark:hover:bg-slate-900 hover:shadow-[0_24px_48px_-15px_rgba(0,0,0,0.05)] transition-all duration-300 group" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-14 h-14 bg-rose-50 dark:bg-rose-900/30 rounded-2xl flex items-center justify-center mb-8 group-hover:bg-rose-500 group-hover:text-white transition-all duration-300 shadow-sm">
                        <svg class="w-7 h-7 text-rose-600 dark:text-rose-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3 group-hover:text-rose-500 dark:group-hover:text-rose-400 transition-colors">Aman & Privat</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed font-medium">Privasi data adalah prioritas utama. Setiap enkripsi data keuangan dirancang berlapis agar Anda dapat mencatat dengan tenang.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Proof / Testimoni Section -->
    <!-- Bagian ini sudah bernuansa gelap (bg-slate-900), jadi akan tetap terlihat natural di Dark Mode -->
    <section id="testimoni" class="py-24 bg-slate-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-indigo-950/40 via-slate-950 to-slate-950 -z-10"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <span class="text-primary font-bold tracking-wider text-xs uppercase bg-indigo-500/10 px-4 py-1.5 rounded-full">Testimoni</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold mt-4 tracking-tight">Apa Kata Mereka yang Sudah Mencoba?</h2>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Testi 1 -->
                <div class="bg-white/5 backdrop-blur-sm p-8 rounded-3xl border border-white/10 hover:border-white/20 transition" data-aos="fade-up" data-aos-delay="100">
                    <p class="text-slate-300 mb-6 font-medium leading-relaxed">"Semenjak pakai aplikasi ini, saya baru sadar kalau bocor halus pengeluaran kopi harian saya bisa buat beli aset digital. Dashboardnya super responsif!"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-white">R</div>
                        <div>
                            <h4 class="font-bold text-sm">Rian Adi</h4>
                            <p class="text-xs text-slate-400">Software Engineer</p>
                        </div>
                    </div>
                </div>
                <!-- Testi 2 -->
                <div class="bg-white/5 backdrop-blur-sm p-8 rounded-3xl border border-white/10 hover:border-white/20 transition" data-aos="fade-up" data-aos-delay="200">
                    <p class="text-slate-300 mb-6 font-medium leading-relaxed">"Fitur target tabungannya bikin nagih. Kita jadi termotivasi terus buat nyisihin uang demi ngejar progress bar target impian sampai 100%."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center font-bold text-white">S</div>
                        <div>
                            <h4 class="font-bold text-sm">Sarah Amalia</h4>
                            <p class="text-xs text-slate-400">Freelancer</p>
                        </div>
                    </div>
                </div>
                <!-- Testi 3 -->
                <div class="bg-white/5 backdrop-blur-sm p-8 rounded-3xl border border-white/10 hover:border-white/20 transition" data-aos="fade-up" data-aos-delay="300">
                    <p class="text-slate-300 mb-6 font-medium leading-relaxed">"Aplikasi pencatat keuangan terbaik yang pernah saya temui. Tampilannya bersih, minimalis, gak ribet, dan fiturnya langsung to-the-point."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center font-bold text-white">D</div>
                        <div>
                            <h4 class="font-bold text-sm">Deni Setiawan</h4>
                            <p class="text-xs text-slate-400">Digital Marketer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section Accordion -->
    <section id="faq" class="py-28 bg-white dark:bg-slate-950 transition-colors duration-300">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Pertanyaan yang Sering Diajukan</h2>
            </div>
            <div class="space-y-4" data-aos="fade-up">
                <!-- Item 1 -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden transition bg-slate-50/50 dark:bg-slate-900/50">
                    <button class="w-full text-left p-6 font-bold text-slate-800 dark:text-slate-200 flex justify-between items-center focus:outline-none" onclick="toggleFaq(this)">
                        <span>Apakah aplikasi ini sepenuhnya gratis?</span>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                        <p class="p-6 pt-0 text-slate-500 dark:text-slate-400 font-medium leading-relaxed">Ya! Fitur utama seperti pencatatan transaksi pemasukan, pengeluaran harian, serta pembuatan target tabungan dapat digunakan secara gratis tanpa biaya.</p>
                    </div>
                </div>
                <!-- Item 2 -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden transition bg-slate-50/50 dark:bg-slate-900/50">
                    <button class="w-full text-left p-6 font-bold text-slate-800 dark:text-slate-200 flex justify-between items-center focus:outline-none" onclick="toggleFaq(this)">
                        <span>Bagaimana keamanan data keuangan saya?</span>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                        <p class="p-6 pt-0 text-slate-500 dark:text-slate-400 font-medium leading-relaxed">Data Anda disimpan menggunakan teknologi enkripsi terkini di server kami. Kami berkomitmen penuh untuk tidak membagikan atau menjual data personal finansial Anda ke pihak ketiga manapun.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-r from-primary to-indigo-700 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-white/10 to-transparent pointer-events-none"></div>
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10" data-aos="zoom-in">
            <h2 class="text-4xl font-extrabold text-white mb-6 tracking-tight">Siap Mengubah Kebiasaan Finansialmu?</h2>
            <p class="text-indigo-100/90 mb-10 text-lg font-medium max-w-xl mx-auto">Bergabunglah bersama ribuan pengguna lainnya dan rasakan ringannya mengatur uang tanpa stres.</p>
            <a href="auth/register.php" class="bg-white text-primary px-10 py-4 rounded-xl font-bold text-lg hover:bg-slate-50 transition shadow-2xl hover:shadow-white/20 inline-block hover:-translate-y-0.5">
                Buat Akun Gratis Sekarang
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 py-16 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold">
                        <img src="/assets/img/1.png"/>
                    </div>
                    <span class="font-bold text-xl text-white tracking-tight">WOYCX</span>
                </div>
                <div class="text-sm font-medium">
                    &copy; <?= date('Y'); ?> <?= WEB_NAME ?>. All rights reserved.
                </div>
                <div class="flex gap-6 text-sm font-medium">
                    <a href="#" class="hover:text-white transition">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-white transition">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts (AOS Animation & Vanilla-Tilt 3D Effect) -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.1/vanilla-tilt.min.js"></script>
    <script>
        // Inisialisasi Efek Animasi Muncul Pas Di-scroll
        AOS.init({
            once: true,
            duration: 800
        });

        // Inisialisasi Efek 3D Card Bergerak Mengikuti Mouse
        VanillaTilt.init(document.querySelectorAll(".js-tilt"), {
            max: 6,
            speed: 300,
            glare: true,
            "max-glare": 0.1,
        });

        // Logika Accordion FAQ (Klik buka/tutup)
        function toggleFaq(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('svg');
            
            if (content.style.maxHeight && content.style.maxHeight !== '0px') {
                content.style.maxHeight = '0px';
                icon.classList.remove('rotate-180');
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
                icon.classList.add('rotate-180');
            }
        }
    </script>
</body>
</html>
