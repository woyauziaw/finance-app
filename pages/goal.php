<?php
$breadcrumb_parent = "Pages";
$breadcrumb_active = "Goal";
include '../layouts/header.php';
$uid = $_SESSION['user_id'];

// 1. LOGIKA SIMPAN GOAL BARU
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_goal'])) {
    $nama_target = htmlspecialchars($_POST['nama_target']);
    $nominal_target = (float) str_replace(['.', ','], '', $_POST['nominal_target']);
    $tgl_target = $_POST['tgl_target'];
    
    $stmt = $pdo->prepare("INSERT INTO goals (user_id, nama_target, nominal_target, tgl_target) VALUES (?, ?, ?, ?)");
    $stmt->execute([$uid, $nama_target, $nominal_target, $tgl_target]);
    
    echo "<script>window.location.href='goal.php';</script>";
    exit();
}

// HANDLE SETOR KE GOAL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setor_goal'])) {
    $goal_id = (int) $_POST['goal_id'];
    $jumlah = (float) str_replace('.', '', $_POST['jumlah']);

    // 1. update progress goal
    $stmt = $pdo->prepare("
        UPDATE goals 
        SET nominal_sekarang = nominal_sekarang + ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$jumlah, $goal_id, $uid]);

    // 2. simpan history
    $stmt = $pdo->prepare("
        INSERT INTO goal_deposits (goal_id, user_id, jumlah)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$goal_id, $uid, $jumlah]);

    echo "<script>window.location.href='goal.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_goal_id'])) {
    $goal_id = (int) $_POST['delete_goal_id'];

    // hapus history dulu (biar rapi)
    $stmt = $pdo->prepare("DELETE FROM goal_deposits WHERE goal_id = ? AND user_id = ?");
    $stmt->execute([$goal_id, $uid]);

    // hapus goal
    $stmt = $pdo->prepare("DELETE FROM goals WHERE id = ? AND user_id = ?");
    $stmt->execute([$goal_id, $uid]);

    echo "<script>window.location.href='goal.php';</script>";
    exit();
}

// 2. AMBIL SEMUA DATA TARGET
$stmtGoals = $pdo->prepare("SELECT * FROM goals WHERE user_id = ? ORDER BY id DESC");
$stmtGoals->execute([$uid]);
$goals = $stmtGoals->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Target Keuangan</h2>
        <p class="text-sm text-slate-500">Pantau progres dan estimasi capaian targetmu.</p>
    </div>
    <button onclick="toggleModal('modalBuatGoal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors shadow-sm">
       <i data-lucide="plus" class="w-4 h-4"></i> Buat Target Baru
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if(empty($goals)): ?>
        <div class="col-span-full bg-white dark:bg-slate-800 p-8 rounded-2xl border border-slate-100 dark:border-slate-700 text-center">
            <i data-lucide="target" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
            <p class="text-slate-500">Belum ada target keuangan. Yuk mulai buat impianmu!</p>
        </div>
    <?php else: 
        foreach($goals as $goal):
            $nominal_sekarang = (float)($goal['nominal_sekarang'] ?? 0);
            $nominal_target = (float)($goal['nominal_target'] ?? 0);
            
            $persen = $nominal_target > 0 ? floor(($nominal_sekarang / $nominal_target) * 100) : 0;
            $persen = min($persen, 100);
            if($persen > 100) $persen = 100;

            $sisa_nominal = $nominal_target - $nominal_sekarang;
            $hari_ini = new DateTime();
            $tgl_target = !empty($goal['tgl_target']) ? new DateTime($goal['tgl_target']) : new DateTime();
            $diff = $hari_ini->diff($tgl_target);
            
            // total hari (positif = future, negatif = lewat)
            $total_hari = (int)$diff->format('%r%a');
            $abs_hari = abs($total_hari);
            
            // konversi fleksibel
            $tahun = floor($abs_hari / 365);
            $sisa_bulan = floor(($abs_hari % 365) / 30);
            $sisa_hari = $abs_hari % 30;
            
            // build text
            if ($total_hari < 0) {
                $status_waktu = "Terlewat ";
            } else {
                $status_waktu = "Sisa ";
            }
            
            $parts = [];
            if ($tahun > 0) $parts[] = "$tahun tahun";
            if ($sisa_bulan > 0) $parts[] = "$sisa_bulan bulan";
            if ($sisa_hari > 0) $parts[] = "$sisa_hari hari";
            
            $status_waktu .= implode(" ", $parts);
            
            // fallback kalau 0
            if (empty($parts)) {
                $status_waktu = "Hari ini";
            }

            // --- FIX ESTIMASI SETORAN PER BULAN ---
            $estimasi_bulan = ($tahun * 12) + $sisa_bulan;
            if ($sisa_hari > 0) $estimasi_bulan += 1; // Pembulatan 1 bulan
            
            if ($sisa_nominal <= 0) {
                $setoran_per_bulan = 0; // Jika target sudah tercapai
            } else {
                $setoran_per_bulan = ($estimasi_bulan > 0) ? ($sisa_nominal / $estimasi_bulan) : $sisa_nominal;
            }
            // ---------------------------------------
    ?>
    
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 relative overflow-hidden group">
        <?php if($persen >= 80 && $persen < 100): ?>
            <div class="absolute top-0 right-0 bg-blue-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl z-10 flex items-center gap-1">
                <i data-lucide="zap" class="w-3 h-3"></i> Sedikit Lagi!
            </div>
        <?php elseif($persen == 100): ?>
            <div class="absolute top-0 right-0 bg-indigo-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl z-10 flex items-center gap-1">
                <i data-lucide="check-circle" class="w-3 h-3"></i> Tercapai
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-3 mb-4">
            <div class="p-3 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 rounded-xl">
                <i data-lucide="target" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($goal['nama_target']) ?></h3>
                <div class="text-xs text-slate-500 flex items-center gap-1">
                    <i data-lucide="calendar" class="w-3 h-3"></i> <?= date('d M Y', strtotime($goal['tgl_target'])) ?> 
                    <span class="<?= $hari_ini > $tgl_target ? 'text-rose-500' : 'text-blue-500' ?> font-medium">(<?= $status_waktu ?>)</span>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="flex justify-between items-end mb-1">
                <div class="text-lg font-bold text-slate-900 dark:text-white"><?= formatRupiah($nominal_sekarang) ?></div>
                <div class="text-xs font-semibold px-2 py-0.5 rounded <?= $persen == 100 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' ?>">
                    <?= $persen ?>%
                </div>
            </div>
            <div class="text-xs text-slate-400 mb-2">dari target <span class="font-medium text-slate-600 dark:text-slate-300"><?= formatRupiah($nominal_target) ?></span></div>
            <div class="w-full bg-slate-100 dark:bg-slate-700 h-2.5 rounded-full overflow-hidden">
                <div class="bg-gradient-to-r <?= $persen == 100 ? 'from-blue-400 to-blue-600' : 'from-indigo-500 to-blue-600' ?> h-full rounded-full" style="width: <?= $persen ?>%"></div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-semibold">Estimasi/bln</p>
                <p class="text-sm font-bold text-indigo-600 dark:text-indigo-400"><?= formatRupiah($setoran_per_bulan) ?></p>
            </div>
            <div class="flex gap-2">
                <button 
                    onclick="openHistory(<?= $goal['id'] ?>)"
                    class="p-2 bg-slate-50 dark:bg-slate-700 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors">
                    <i data-lucide="history" class="w-4 h-4"></i>
                </button>
                <button
                    onclick="openSetorModal(<?= $goal['id'] ?>)"
                    class="px-3 py-2 bg-indigo-50 text-indigo-600 font-semibold text-xs rounded-lg hover:bg-indigo-100 transition-colors">
                    Setor
                </button>
                <form method="POST" onsubmit="return confirm('Yakin mau hapus goal ini?')" class="inline">
                    <input type="hidden" name="delete_goal_id" value="<?= $goal['id'] ?>">
                    <button type="submit"
                        class="px-3 py-2 bg-rose-50 text-rose-600 font-semibold text-xs rounded-lg hover:bg-rose-100 transition-colors">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<!-- ================= MODALS (TAMPILAN DIPERBARUI) ================= -->

<!-- Modal Buat Goal -->
<div id="modalBuatGoal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-md scale-95 transition-transform duration-300 modal-content overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-700">
        <!-- Header -->
        <div class="flex justify-between items-center p-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <h3 class="font-bold text-lg text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="target" class="w-5 h-5 text-indigo-500"></i> Buat Target Baru
            </h3>
            <button onclick="toggleModal('modalBuatGoal')" class="text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 p-1.5 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <!-- Body -->
        <form action="" method="POST" class="p-6 space-y-5">
            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Nama Impian / Target</label>
                <input type="text" name="nama_target" placeholder="Contoh: Beli Laptop" required
                    class="w-full p-3 rounded-xl border border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>
            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Nominal Dibutuhkan (Rp)</label>
                <input type="text" name="nominal_target" onkeyup="formatRupiahInput(this)" placeholder="10.000.000" required
                    class="w-full p-3 rounded-xl border border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>
            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Tenggat Waktu</label>
                <input type="date" name="tgl_target" required
                    class="w-full p-3 rounded-xl border border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="toggleModal('modalBuatGoal')" class="px-5 py-2.5 text-sm font-medium rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 dark:text-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors">Batal</button>
                <button type="submit" name="simpan_goal" class="px-5 py-2.5 text-sm font-medium rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-colors">Simpan Target</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Setor -->
<div id="modalSetor" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-md scale-95 transition-transform duration-300 modal-content overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-700">
        <!-- Header -->
        <div class="flex justify-between items-center p-5 border-b border-slate-100 dark:border-slate-700 bg-blue-50 dark:bg-blue-900/20">
            <h3 class="font-bold text-lg text-blue-700 dark:text-blue-400 flex items-center gap-2">
                <i data-lucide="piggy-bank" class="w-5 h-5"></i> Setor ke Target
            </h3>
            <button onclick="toggleModal('modalSetor')" class="text-slate-400 hover:bg-blue-100 dark:hover:bg-slate-700 p-1.5 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <!-- Body -->
        <form method="POST" class="p-6 space-y-5">
            <input type="hidden" name="goal_id" id="setor_goal_id">
            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Jumlah Setoran (Rp)</label>
                <input type="text" name="jumlah" onkeyup="formatRupiahInput(this)" placeholder="Misal: 150.000" required 
                    class="w-full p-3 rounded-xl border border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="toggleModal('modalSetor')" class="px-5 py-2.5 text-sm font-medium rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 dark:text-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors">Batal</button>
                <button type="submit" name="setor_goal" class="px-5 py-2.5 text-sm font-medium rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-colors">Setor Sekarang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal History -->
<div id="modalHistory" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-md scale-95 transition-transform duration-300 modal-content overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-700">
        <!-- Header -->
        <div class="flex justify-between items-center p-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <h3 class="font-bold text-lg text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="history" class="w-5 h-5 text-slate-500"></i> Riwayat Setoran
            </h3>
            <button onclick="toggleModal('modalHistory')" class="text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 p-1.5 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <!-- Body -->
        <div class="p-5">
            <div id="historyContent" class="space-y-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                <!-- Content dari AJAX akan masuk kesini -->
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                <button type="button" onclick="toggleModal('modalHistory')" class="px-5 py-2.5 text-sm font-medium rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 dark:text-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
function toggleModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.modal-content');

    const isHidden = modal.classList.contains('hidden');

    if (isHidden) {
        modal.classList.remove('hidden');

        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }, 10);

    } else {
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

function openSetorModal(id) {
    document.getElementById('setor_goal_id').value = id;
    toggleModal('modalSetor');
}

function openHistory(id) {
    // Memberikan loading state
    document.getElementById('historyContent').innerHTML = '<div class="text-center py-8 text-slate-400"><i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i> Memuat data...</div>';
    lucide.createIcons();
    toggleModal('modalHistory');

    fetch(`__goalhistory.php?id=${id}`)
        .then(res => res.text())
        .then(data => {
            document.getElementById('historyContent').innerHTML = data;
        })
        .catch(err => {
            document.getElementById('historyContent').innerHTML = '<div class="text-center py-4 text-rose-500">Gagal memuat riwayat.</div>';
        });
}
</script>

<style>
/* Mempercantik Scrollbar di History */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #475569; }
</style>

<?php include '../layouts/footer.php'; ?>
