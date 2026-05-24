<?php
include '../layouts/header.php';

// Proteksi admin
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// statistik
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_changelogs = $pdo->query("SELECT COUNT(*) FROM changelogs")->fetchColumn();
?>

<div class="flex min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col p-5">

        <h2 class="text-xl font-bold mb-6">⚙️ Admin Panel</h2>

        <nav class="flex flex-col gap-2 text-sm">

            <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/10">
                🏠 Dashboard
            </a>

            <a href="users.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10">
                👥 Manajemen User
            </a>

            <a href="changelogs.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/10">
                ⚙️ Changelogs
            </a>

        </nav>

        <div class="mt-auto pt-6">
            <a href="../logout.php"
               class="block text-center bg-red-600 hover:bg-red-700 text-white py-2 rounded-xl text-sm font-semibold">
                Logout
            </a>
        </div>

    </aside>

    <!-- MAIN -->
    <main class="flex-1 p-6">

        <!-- HEADER -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Selamat Datang, Admin</h1>
            <p class="text-slate-500 text-sm">
                Kelola sistem keuangan & pengguna dari panel ini.
            </p>
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow border border-slate-100 dark:border-slate-700">
                <p class="text-xs text-slate-400 font-bold uppercase">Total Users</p>
                <h2 class="text-2xl font-black text-blue-500 mt-1"><?= $total_users ?></h2>
                <p class="text-xs text-slate-400 mt-1">Pengguna terdaftar</p>
            </div>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow border border-slate-100 dark:border-slate-700">
                <p class="text-xs text-slate-400 font-bold uppercase">Changelogs</p>
                <h2 class="text-2xl font-black text-emerald-500 mt-1"><?= $total_changelogs ?></h2>
                <p class="text-xs text-slate-400 mt-1">Update sistem</p>
            </div>

        </div>

        <!-- QUICK ACTION -->
        <div class="mt-6 bg-gradient-to-r from-slate-900 to-slate-800 text-white p-6 rounded-2xl">
            <h3 class="font-bold text-lg mb-1">Admin Control Center</h3>
            <p class="text-sm text-slate-300">
                Gunakan menu sidebar untuk mengelola user, changelog, dan sistem.
            </p>
        </div>

    </main>

</div>

<?php include '../layouts/footer.php'; ?>