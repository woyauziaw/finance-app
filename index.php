<?php
session_start();
require_once 'config/database.php';

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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Finance App</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">

<!-- NAVBAR -->
<div class="flex justify-between items-center px-8 py-6">
    <h1 class="text-xl font-bold tracking-wide">💰 FinanceApp</h1>

    <div class="space-x-3">
        <?php if ($isLogin): ?>
            <a href="/?route=dashboard" class="bg-emerald-500 px-4 py-2 rounded-lg hover:bg-emerald-600">
                Dashboard
            </a>
        <?php else: ?>
            <a href="/?route=login" class="px-4 py-2 hover:underline">Login</a>
            <a href="/?route=register" class="bg-blue-500 px-4 py-2 rounded-lg hover:bg-blue-600">
                Register
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- HERO -->
<div class="text-center mt-20 px-6">
    <h2 class="text-4xl md:text-5xl font-bold leading-tight">
        Kelola Keuangan Kamu <br>
        Lebih <span class="text-emerald-400">Cerdas & Simple</span>
    </h2>

    <p class="text-slate-300 mt-4 max-w-xl mx-auto">
        Catat pemasukan, pengeluaran, dan lihat grafik keuangan kamu secara real-time dalam satu dashboard modern.
    </p>

    <div class="mt-8 space-x-4">
        <?php if ($isLogin): ?>
            <a href="/?route=dashboard" class="bg-emerald-500 px-6 py-3 rounded-xl font-semibold hover:bg-emerald-600">
                Buka Dashboard
            </a>
        <?php else: ?>
            <a href="/?route=register" class="bg-emerald-500 px-6 py-3 rounded-xl font-semibold hover:bg-emerald-600">
                Mulai Sekarang
            </a>
            <a href="/?route=login" class="border border-white px-6 py-3 rounded-xl hover:bg-white hover:text-black">
                Login
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- FEATURES -->
<div class="grid md:grid-cols-3 gap-6 px-10 mt-24 max-w-6xl mx-auto">

    <div class="bg-white/10 p-6 rounded-xl backdrop-blur">
        <h3 class="text-xl font-semibold">📊 Tracking Keuangan</h3>
        <p class="text-slate-300 mt-2">Catat pemasukan & pengeluaran dengan mudah.</p>
    </div>

    <div class="bg-white/10 p-6 rounded-xl backdrop-blur">
        <h3 class="text-xl font-semibold">📈 Grafik Real-time</h3>
        <p class="text-slate-300 mt-2">Lihat perkembangan keuangan kamu setiap bulan.</p>
    </div>

    <div class="bg-white/10 p-6 rounded-xl backdrop-blur">
        <h3 class="text-xl font-semibold">🔐 Aman & Private</h3>
        <p class="text-slate-300 mt-2">Data kamu tersimpan aman di database.</p>
    </div>

</div>

<!-- FOOTER -->
<div class="text-center text-slate-500 mt-20 mb-10 text-sm">
    © <?= date('Y') ?> FinanceApp — Built with ❤️
</div>

</body>
</html>