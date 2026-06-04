<?php $current = isset($_GET['route']) ? $_GET['route'] : 'dashboard'; ?>
<aside id="sidebar" class="w-64 bg-white dark:bg-slate-800 border-r border-slate-100 dark:border-slate-700 fixed inset-y-0 left-0 z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col justify-between">
    <div>
        <div class="h-20 flex items-center px-6 border-b border-slate-100 dark:border-slate-700 justify-between">
            <h1 class="text-2xl font-black text-blue-500 tracking-tight"><?= WEB_NAME ?><span class="text-slate-800 dark:text-white">.</span></h1>
            <button onclick="document.getElementById('sidebar').classList.add('-translate-x-full')" class="lg:hidden text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <nav class="p-4 space-y-1.5">
            <a href="../pages/dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all <?= $current === 'dashboard' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50' ?>"><i data-lucide="layout-dashboard"></i> Dashboard</a>
            <a href="../pages/transaksi.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all <?= $current === 'transaksi' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50' ?>"><i data-lucide="arrow-left-right"></i> Transaksi</a>
            <a href="../pages/kategori.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all <?= $current === 'kategori' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50' ?>"><i data-lucide="tags"></i> Kategori</a>
            <a href="../pages/importcsv.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium <?= $current === 'import' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>"><i data-lucide="file-up"></i> Import Data</a>
            
            <a href="../pages/changelogs.php"
   class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
   <?= $current === 'changelogs'
        ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
        : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50' ?>">

    <i data-lucide="settings"></i>
    Changelogs
</a>


            <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
<a href="../admin/index.php"
   class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all
   <?= $current === 'admin_home'
        ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/20'
        : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50' ?>">
    <i data-lucide="layout-dashboard"></i>
    Admin Dashboard
</a>
<?php endif; ?>


        </nav>
    </div>
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">
    <a href="../pages/profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all <?= $current === 'profile' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50' ?>"><i data-lucide="user"></i> Akun Profil</a>
    <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition-all"><i data-lucide="log-out"></i> Keluar</a></div>
</aside>

