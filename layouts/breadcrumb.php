<?php
// Set nilai default jika variabel tidak didefinisikan di halaman utama
$menu_induk = isset($breadcrumb_parent) ? $breadcrumb_parent : 'Pages';
$halaman_aktif = isset($breadcrumb_active) ? $breadcrumb_active : 'Dashboard';
?>

<div class="bg-white/60 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-800/80 backdrop-blur-md sticky top-20 z-40">
    <div class="max-w-4xl mx-auto px-6 py-3">
        <nav class="flex text-sm font-medium text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="inline-flex items-center">
                    <a href="../pages/dashboard.php" class="inline-flex items-center hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <?= htmlspecialchars($menu_induk) ?>
                    </a>
                </li>
                
                <li>
                    <div class="flex items-center">
                        <a href="../<?= strtolower($menu_induk); ?>/<?= strtolower($halaman_aktif) ?>.php" class="inline-flex items-center hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                           <svg class="w-5 h-5 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span class="ml-1 md:ml-2 text-slate-800 dark:text-slate-200 font-semibold cursor-default">
                                <?= htmlspecialchars($halaman_aktif) ?>
                            </span>
                        </a>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>
