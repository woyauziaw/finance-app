<?php
session_start();
require "../config/database.php";

$userName = $_SESSION['user_name'] ?? 'User';
$userPhoto = $_SESSION['user_photo'] ?? null;
$uid = $_SESSION['user_id'] ?? 0;

$initial = strtoupper(substr($userName, 0, 2));

$colors = [
    'bg-blue-500',
    'bg-indigo-500',
    'bg-purple-500',
    'bg-pink-500',
    'bg-emerald-500',
    'bg-amber-500',
];

$color = $colors[$uid % count($colors)];

$photoFile = __DIR__ . '/../uploads/' . $userPhoto;
$photoUrl  = '/uploads/' . $userPhoto;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Apps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="/assets/js/app.js" defer></script>
</head>

<script>
window.addEventListener("load", function () {
    const loader = document.getElementById("global-loader");

    if (loader) {
        loader.style.opacity = "0";
        setTimeout(() => {
            loader.style.display = "none";
        }, 300);
    }

    if (typeof lucide !== "undefined") {
        lucide.createIcons();
    }
});
</script>

<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 min-h-screen flex">

    <div id="global-loader" class="fixed inset-0 bg-white dark:bg-slate-900 z-[9999] flex items-center justify-center transition-opacity duration-300">
        <div class="loader ease-linear rounded-full border-4 border-t-4 border-slate-200 h-12 w-12"></div>
    </div>

    <?php include 'sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 min-h-screen lg:pl-64">

        <header class="h-20 bg-white dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between px-6 sticky top-0 z-40">

            <button onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')" class="lg:hidden text-slate-500">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>

            <div class="font-semibold text-lg hidden sm:block">
                Dashboard Finansial
            </div>

            <div class="flex items-center gap-4 ml-auto">

                <button onclick="toggleDarkMode()" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:text-blue-500">
                    <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                </button>

                <!-- AVATAR FIX + CLICKABLE -->
                <a href="/pages/profile.php"
                   class="flex items-center gap-3 pl-2 border-l border-slate-200 dark:border-slate-700 hover:opacity-80 transition">

                    <?php if (!empty($userPhoto) && file_exists($photoFile)): ?>
                        <img src="<?= $photoUrl ?>"
                             class="w-10 h-10 rounded-full object-cover ring-2 ring-blue-500/20">
                    <?php else: ?>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm uppercase <?= $color ?>">
                            <?= $initial ?>
                        </div>
                    <?php endif; ?>

                    <span class="text-sm font-medium hidden md:block">
                        <?= htmlspecialchars($userName) ?>
                    </span>

                </a>

            </div>
        </header>

        <main class="p-6 flex-1">