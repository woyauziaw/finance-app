<?php
$breadcrumb_parent = "Pages";
$breadcrumb_active = "Changelogs";
include '../layouts/header.php';
/* =========================
   ROLE CHECK
========================= */
$isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

/* =========================
   HELPER FUNCTIONS
========================= */
function parseChangelogGrouped($text) {
    $lines = explode("\n", trim($text));
    $categories = ['new' => [], 'update' => [], 'fix' => [], 'other' => []];
    
    // Variabel untuk mengingat sedang berada di kategori mana
    $currentCategory = 'other'; 

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // 1. Cek apakah baris ini HANYA Prefix (Contoh: "ADD:" atau "FIX:")
        if (preg_match('/^(add|feat|new|\[add\])[\s\:\-]*$/i', $line)) {
            $currentCategory = 'new';
            continue; // Lanjut ke baris berikutnya
        } elseif (preg_match('/^(update|improve|\[update\])[\s\:\-]*$/i', $line)) {
            $currentCategory = 'update';
            continue;
        } elseif (preg_match('/^(fix|bug|\[fix\])[\s\:\-]*$/i', $line)) {
            $currentCategory = 'fix';
            continue;
        }

        // 2. Cek apakah baris ini Inline Prefix (Contoh: "ADD: Fitur login")
        if (preg_match('/^(add|feat|new|\[add\])[\s\:\-]+(.*)/i', $line, $matches)) {
            $currentCategory = 'new';
            $categories['new'][] = htmlspecialchars(trim($matches[2]), ENT_QUOTES, 'UTF-8');
            continue;
        } elseif (preg_match('/^(update|improve|\[update\])[\s\:\-]+(.*)/i', $line, $matches)) {
            $currentCategory = 'update';
            $categories['update'][] = htmlspecialchars(trim($matches[2]), ENT_QUOTES, 'UTF-8');
            continue;
        } elseif (preg_match('/^(fix|bug|\[fix\])[\s\:\-]+(.*)/i', $line, $matches)) {
            $currentCategory = 'fix';
            $categories['fix'][] = htmlspecialchars(trim($matches[2]), ENT_QUOTES, 'UTF-8');
            continue;
        }

        // 3. Jika tidak ada prefix di baris ini, masukkan ke kategori terakhir yang aktif
        $categories[$currentCategory][] = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
    }

    $html = '<div class="space-y-6">';

    // Render NEW / FEATURES
    if (!empty($categories['new'])) {
        $html .= '<div><h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-3"><span class="flex items-center justify-center w-5 h-5 rounded bg-emerald-100 dark:bg-emerald-500/20">✨</span> Fitur Baru</h4><ul class="space-y-2.5 text-sm text-slate-600 dark:text-slate-300 ml-1">';
        foreach ($categories['new'] as $item) { $html .= "<li class='flex items-start gap-3'><span class='mt-2 w-1 h-1 rounded-full bg-emerald-400 shrink-0'></span> <span class='leading-relaxed'>{$item}</span></li>"; }
        $html .= '</ul></div>';
    }

    // Render UPDATES
    if (!empty($categories['update'])) {
        $html .= '<div><h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 mb-3"><span class="flex items-center justify-center w-5 h-5 rounded bg-blue-100 dark:bg-blue-500/20">⚡</span> Peningkatan</h4><ul class="space-y-2.5 text-sm text-slate-600 dark:text-slate-300 ml-1">';
        foreach ($categories['update'] as $item) { $html .= "<li class='flex items-start gap-3'><span class='mt-2 w-1 h-1 rounded-full bg-blue-400 shrink-0'></span> <span class='leading-relaxed'>{$item}</span></li>"; }
        $html .= '</ul></div>';
    }

    // Render FIXES
    if (!empty($categories['fix'])) {
        $html .= '<div><h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-red-600 dark:text-red-400 mb-3"><span class="flex items-center justify-center w-5 h-5 rounded bg-red-100 dark:bg-red-500/20">🐛</span> Perbaikan Bug</h4><ul class="space-y-2.5 text-sm text-slate-600 dark:text-slate-300 ml-1">';
        foreach ($categories['fix'] as $item) { $html .= "<li class='flex items-start gap-3'><span class='mt-2 w-1 h-1 rounded-full bg-red-400 shrink-0'></span> <span class='leading-relaxed'>{$item}</span></li>"; }
        $html .= '</ul></div>';
    }

    // Render OTHERS (Tanpa Prefix sama sekali di awal)
    if (!empty($categories['other'])) {
        $html .= '<div><ul class="space-y-2.5 text-sm text-slate-600 dark:text-slate-300 ml-1">';
        foreach ($categories['other'] as $item) { $html .= "<li class='flex items-start gap-3'><span class='mt-2 w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600 shrink-0'></span> <span class='leading-relaxed'>{$item}</span></li>"; }
        $html .= '</ul></div>';
    }

    $html .= '</div>';
    return $html;
}

/* =========================
   ADD (ADMIN ONLY)
========================= */
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $version = trim($_POST['version']);
    $date = trim($_POST['release_date']);
    $desc = trim($_POST['description']);

    if (!empty($version) && !empty($date) && !empty($desc)) {
        $stmt = $pdo->prepare("INSERT INTO changelogs (version, release_date, description) VALUES (?, ?, ?)");
        $stmt->execute([$version, $date, $desc]);
        echo "<script>window.location.href='../pages/changelogs.php?success=added';</script>";
        exit;
    }
}

/* =========================
   DELETE (ADMIN ONLY)
========================= */
if ($isAdmin && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM changelogs WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>window.location.href='../pages/changelogs.php?success=deleted';</script>";
    exit;
}

/* =========================
   GET DATA
========================= */
$logs = $pdo->query("SELECT * FROM changelogs ORDER BY release_date DESC, id DESC")->fetchAll();

// SETTING LIMIT VISUAL
$visibleLimit = 5; 
?>

<div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 selection:bg-indigo-500/30">

    <div class="relative overflow-hidden border-b border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 via-white to-white dark:from-indigo-950/20 dark:via-slate-900 dark:to-slate-900 pointer-events-none"></div>
        <div class="max-w-4xl mx-auto px-6 py-16 relative z-10">
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-5xl">
                Changelog & <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-cyan-500">Release Notes</span>
            </h1>
            <p class="mt-4 max-w-2xl text-lg text-slate-500 dark:text-slate-400">
                Pembaruan terbaru, perbaikan bug, dan peningkatan fitur.
            </p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-12">

        <?php if ($isAdmin): ?>
            <div class="mb-12 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl p-6 md:p-8 rounded-3xl shadow-sm border border-slate-200/60 dark:border-slate-800 transition-all hover:shadow-md">
                <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Publish New Update
                </h2>
                <form method="POST" action="changelogs.php" class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <input type="hidden" name="action" value="add">
                    <div class="md:col-span-1">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Version</label>
                        <input type="text" name="version" placeholder="e.g. v2.1.0" required class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500/50 outline-none dark:text-white">
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Date</label>
                        <input type="date" name="release_date" value="<?= date('Y-m-d') ?>" required class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500/50 outline-none dark:text-white">
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                            Release Notes <span class="text-[10px] normal-case font-normal">(Gunakan prefix: ADD: / FIX: / UPDATE:)</span>
                        </label>
                        <textarea name="description" rows="5" required placeholder="ADD: Fitur pembayaran otomatis via gateway&#10;FIX: Masalah session terputus&#10;UPDATE: Refactor logika module" class="w-full p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500/50 outline-none resize-y dark:text-white font-mono text-sm"></textarea>
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-medium shadow-lg shadow-indigo-500/30">Publish Changelog</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if (count($logs) > 0): ?>
            <div class="relative border-l-[3px] border-slate-200 dark:border-slate-800 ml-4 md:ml-6 space-y-6 pb-10">
                <?php foreach ($logs as $index => $row): 
                    $isOpen = $index === 0;
                    $isHidden = $index >= $visibleLimit;
                ?>
                    <div class="relative group <?= $isHidden ? 'hidden-log hidden' : '' ?>">
                        
                        <div class="absolute -left-[11px] top-6 w-5 h-5 rounded-full bg-white dark:bg-slate-950 border-[4px] <?= $isOpen ? 'border-indigo-500' : 'border-slate-300 dark:border-slate-600' ?> ring-4 ring-white dark:ring-slate-950 transition-colors duration-300 dot-indicator"></div>

                        <div class="ml-10 md:ml-12">
                            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden transition-all hover:border-indigo-300 dark:hover:border-indigo-500/50">
                                
                                <button type="button" class="toggle-log-btn w-full p-5 md:p-6 flex items-center justify-between text-left focus:outline-none" aria-expanded="<?= $isOpen ? 'true' : 'false' ?>">
                                    <div class="flex flex-col sm:flex-row sm:items-baseline gap-2 sm:gap-4">
                                        <h3 class="text-xl md:text-2xl font-bold text-slate-900 dark:text-white">
                                            <?= htmlspecialchars($row['version']) ?>
                                        </h3>
                                        <time class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                            <?= date('d M Y', strtotime($row['release_date'])) ?>
                                        </time>
                                    </div>
                                    
                                    <div class="flex items-center gap-3 text-sm font-semibold text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                        <span class="toggle-text hidden sm:inline-block">
                                            <?= $isOpen ? 'Sembunyikan' : 'Tampilkan' ?>
                                        </span>
                                        <div class="bg-slate-50 dark:bg-slate-800 p-2 rounded-full">
                                            <svg class="w-5 h-5 transform transition-transform duration-300 chevron-icon <?= $isOpen ? 'rotate-180 text-indigo-500' : '' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </button>

                                <div class="changelog-body grid transition-all duration-300 ease-in-out <?= $isOpen ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0' ?>">
                                    <div class="overflow-hidden">
                                        <div class="px-5 md:px-6 pb-6 pt-2 border-t border-slate-100 dark:border-slate-800/60">
                                            <div class="mt-4">
                                                <?= parseChangelogGrouped($row['description']) ?>
                                            </div>

                                            <?php if ($isAdmin): ?>
                                                <div class="mt-8 pt-4 border-t border-red-100 dark:border-red-500/10 flex justify-end">
                                                    <form method="POST" action="changelogs.php" onsubmit="return confirm('Hapus versi <?= htmlspecialchars($row['version']) ?>?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 bg-red-50 dark:bg-red-500/10 px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            Hapus Log Ini
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>


            </div>
        <?php endif; ?>
        
        <?php if (count($logs) > $visibleLimit): ?>
            <div class="relative ml-10 md:ml-12 pt-6 flex justify-center">
                <div class="absolute -left-[24px] md:-left-[24px] top-0 bottom-1/2 border-l-[3px] border-slate-200 dark:border-slate-800"></div>
                        
                <button id="loadMoreBtn" data-showing="false" class="relative z-10 inline-flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:border-indigo-400 dark:hover:border-indigo-500 text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 px-6 py-2.5 rounded-full text-sm font-semibold shadow-sm transition-all">
                    <span>Tampilkan Riwayat Lama (<?= count($logs) - $visibleLimit ?>)</span>
                    <svg class="w-4 h-4 icon-load" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
        <?php endif; ?>


    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. LOGIC UNTUK ACCORDION (Buka/Tutup Changelog)
    const toggleBtns = document.querySelectorAll('.toggle-log-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.group');
            const body = this.nextElementSibling;
            const chevron = this.querySelector('.chevron-icon');
            const toggleText = this.querySelector('.toggle-text');
            const dot = card.querySelector('.dot-indicator');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                this.setAttribute('aria-expanded', 'false');
                body.classList.remove('grid-rows-[1fr]', 'opacity-100');
                body.classList.add('grid-rows-[0fr]', 'opacity-0');
                chevron.classList.remove('rotate-180', 'text-indigo-500');
                if(toggleText) toggleText.textContent = 'Tampilkan';
                dot.classList.remove('border-indigo-500');
                dot.classList.add('border-slate-300', 'dark:border-slate-600');
            } else {
                this.setAttribute('aria-expanded', 'true');
                body.classList.remove('grid-rows-[0fr]', 'opacity-0');
                body.classList.add('grid-rows-[1fr]', 'opacity-100');
                chevron.classList.add('rotate-180', 'text-indigo-500');
                if(toggleText) toggleText.textContent = 'Sembunyikan';
                dot.classList.remove('border-slate-300', 'dark:border-slate-600');
                dot.classList.add('border-indigo-500');
            }
        });
    });

    // 2. LOGIC UNTUK LOAD MORE (Sembunyikan/Tampilkan Riwayat Lama)
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const hiddenLogs = document.querySelectorAll('.hidden-log');
            const isShowingAll = this.getAttribute('data-showing') === 'true';
            const icon = this.querySelector('.icon-load');
            const textSpan = this.querySelector('span');

            if (isShowingAll) {
                // Tutup (Sembunyikan kembali)
                hiddenLogs.forEach(log => {
                    log.classList.add('hidden');
                    
                    // Opsional: Tutup accordion-nya juga jika terbuka
                    const btn = log.querySelector('.toggle-log-btn');
                    if(btn.getAttribute('aria-expanded') === 'true') {
                        btn.click(); 
                    }
                });
                textSpan.textContent = 'Tampilkan Riwayat Lama (<?= count($logs) - $visibleLimit ?>)';
                icon.classList.remove('rotate-180');
                this.setAttribute('data-showing', 'false');
                
                // Scroll kembali ke atas sedikit supaya user tidak tersesat di bawah
                loadMoreBtn.scrollIntoView({ behavior: 'smooth', block: 'end' });
            } else {
                // Buka (Tampilkan semua)
                hiddenLogs.forEach(log => {
                    log.classList.remove('hidden');
                    // Tambahkan efek fade-in sederhana
                    log.style.opacity = '0';
                    setTimeout(() => {
                        log.style.transition = 'opacity 0.5s ease';
                        log.style.opacity = '1';
                    }, 50);
                });
                textSpan.textContent = 'Sembunyikan Riwayat Lama';
                icon.classList.add('rotate-180');
                this.setAttribute('data-showing', 'true');
            }
        });
    }
});
</script>

<?php include '../layouts/footer.php'; ?>
