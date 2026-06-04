<?php
$breadcrumb_parent = "Pages";
$breadcrumb_active = "Dashboard"; 
include '../layouts/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='../auth/login.php';</script>";
    exit();
}

$uid = $_SESSION['user_id'];

/* =======================================
   DATA CARD UTAMA & GRAPH (Bawaan)
======================================= */
$pemasukan = $pdo->query("SELECT SUM(jumlah) FROM transaksi WHERE user_id = $uid AND jenis = 'pemasukan'")->fetchColumn() ?? 0;
$pengeluaran = $pdo->query("SELECT SUM(jumlah) FROM transaksi WHERE user_id = $uid AND jenis = 'pengeluaran'")->fetchColumn() ?? 0;
$saldo = $pemasukan - $pengeluaran;

$chartData = $pdo->query("SELECT MONTH(tanggal) as bulan, SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END) as masuk, SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END) as keluar FROM transaksi WHERE user_id = $uid GROUP BY MONTH(tanggal)")->fetchAll();
$months = []; $masukArr = []; $keluarArr = [];
foreach($chartData as $row) {
    $months[] = date("F", mktime(0, 0, 0, $row['bulan'], 1));
    $masukArr[] = (float)$row['masuk'];
    $keluarArr[] = (float)$row['keluar'];
}

/* =======================================
   FUNGSI HELPER PERSENTASE
======================================= */
function calculatePercentage($current, $previous) {
    if ($previous > 0) {
        return round((($current - $previous) / $previous) * 100, 1);
    }
    return $current > 0 ? 100 : 0;
}

/* =======================================
   DATA INSIGHTS BULANAN
======================================= */
$thisMonthStart = date('Y-m-01');
$thisMonthEnd = date('Y-m-t');
$lastMonthStart = date('Y-m-01', strtotime('-1 month'));
$lastMonthEnd = date('Y-m-t', strtotime('-1 month'));

// Bulanan akumulasi
$stmtThisMonth = $pdo->prepare("SELECT jenis, SUM(jumlah) as total FROM transaksi WHERE user_id = ? AND tanggal BETWEEN ? AND ? GROUP BY jenis");
$stmtThisMonth->execute([$uid, $thisMonthStart, $thisMonthEnd]);
$dataThisMonth = $stmtThisMonth->fetchAll(PDO::FETCH_KEY_PAIR);
$thisMonthIncome = $dataThisMonth['pemasukan'] ?? 0;
$thisMonthExpense = $dataThisMonth['pengeluaran'] ?? 0;

$stmtLastMonth = $pdo->prepare("SELECT jenis, SUM(jumlah) as total FROM transaksi WHERE user_id = ? AND tanggal BETWEEN ? AND ? GROUP BY jenis");
$stmtLastMonth->execute([$uid, $lastMonthStart, $lastMonthEnd]);
$dataLastMonth = $stmtLastMonth->fetchAll(PDO::FETCH_KEY_PAIR);
$lastMonthIncome = $dataLastMonth['pemasukan'] ?? 0;
$lastMonthExpense = $dataLastMonth['pengeluaran'] ?? 0;

$incomeMonthPercent = calculatePercentage($thisMonthIncome, $lastMonthIncome);
$expenseMonthPercent = calculatePercentage($thisMonthExpense, $lastMonthExpense);

// Kategori Terbesar & Tersering (Bulanan)
$stmtTopExpenseM = $pdo->prepare("SELECT k.nama_kategori, SUM(t.jumlah) as total FROM transaksi t JOIN kategori k ON t.kategori_id = k.id WHERE t.user_id = ? AND t.jenis = 'pengeluaran' AND t.tanggal BETWEEN ? AND ? GROUP BY t.kategori_id ORDER BY total DESC LIMIT 1");
$stmtTopExpenseM->execute([$uid, $thisMonthStart, $thisMonthEnd]);
$topExpenseCatM = $stmtTopExpenseM->fetch(PDO::FETCH_ASSOC);

$stmtFreqCatM = $pdo->prepare("SELECT k.nama_kategori, COUNT(t.id) as freq FROM transaksi t JOIN kategori k ON t.kategori_id = k.id WHERE t.user_id = ? AND t.tanggal BETWEEN ? AND ? GROUP BY t.kategori_id ORDER BY freq DESC LIMIT 1");
$stmtFreqCatM->execute([$uid, $thisMonthStart, $thisMonthEnd]);
$mostFreqCatM = $stmtFreqCatM->fetch(PDO::FETCH_ASSOC);

$savingsRatioM = $thisMonthIncome > 0 ? round((($thisMonthIncome - $thisMonthExpense) / $thisMonthIncome) * 100, 1) : 0;

/* =======================================
   DATA INSIGHTS TAHUNAN (Baru)
======================================= */
$thisYear = date('Y');
$lastYear = date('Y', strtotime('-1 year'));

// Tahunan akumulasi
$stmtThisYear = $pdo->prepare("SELECT jenis, SUM(jumlah) as total FROM transaksi WHERE user_id = ? AND YEAR(tanggal) = ? GROUP BY jenis");
$stmtThisYear->execute([$uid, $thisYear]);
$dataThisYear = $stmtThisYear->fetchAll(PDO::FETCH_KEY_PAIR);
$thisYearIncome = $dataThisYear['pemasukan'] ?? 0;
$thisYearExpense = $dataThisYear['pengeluaran'] ?? 0;

$stmtLastYear = $pdo->prepare("SELECT jenis, SUM(jumlah) as total FROM transaksi WHERE user_id = ? AND YEAR(tanggal) = ? GROUP BY jenis");
$stmtLastYear->execute([$uid, $lastYear]);
$dataLastYear = $stmtLastYear->fetchAll(PDO::FETCH_KEY_PAIR);
$lastYearIncome = $dataLastYear['pemasukan'] ?? 0;
$lastYearExpense = $dataLastYear['pengeluaran'] ?? 0;

$incomeYearPercent = calculatePercentage($thisYearIncome, $lastYearIncome);
$expenseYearPercent = calculatePercentage($thisYearExpense, $lastYearExpense);

// Kategori Terbesar & Tersering (Tahunan)
$stmtTopExpenseY = $pdo->prepare("SELECT k.nama_kategori, SUM(t.jumlah) as total FROM transaksi t JOIN kategori k ON t.kategori_id = k.id WHERE t.user_id = ? AND t.jenis = 'pengeluaran' AND YEAR(t.tanggal) = ? GROUP BY t.kategori_id ORDER BY total DESC LIMIT 1");
$stmtTopExpenseY->execute([$uid, $thisYear]);
$topExpenseCatY = $stmtTopExpenseY->fetch(PDO::FETCH_ASSOC);

$stmtFreqCatY = $pdo->prepare("SELECT k.nama_kategori, COUNT(t.id) as freq FROM transaksi t JOIN kategori k ON t.kategori_id = k.id WHERE t.user_id = ? AND YEAR(t.tanggal) = ? GROUP BY t.kategori_id ORDER BY freq DESC LIMIT 1");
$stmtFreqCatY->execute([$uid, $thisYear]);
$mostFreqCatY = $stmtFreqCatY->fetch(PDO::FETCH_ASSOC);

$savingsRatioY = $thisYearIncome > 0 ? round((($thisYearIncome - $thisYearExpense) / $thisYearIncome) * 100, 1) : 0;
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-2xl text-white shadow-xl">
        <div class="text-sm opacity-80 uppercase tracking-wider font-semibold">Total Saldo Bersih</div>
        <div class="text-3xl font-bold mt-2"><?= formatRupiah($saldo) ?></div>
    </div>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 flex items-center gap-4">
        <div class="p-4 rounded-xl bg-emerald-500/10 text-emerald-500"><i data-lucide="arrow-down-left" class="w-8 h-8"></i></div>
        <div><div class="text-sm text-slate-400 font-medium">Total Pemasukan</div><div class="text-2xl font-bold text-emerald-500"><?= formatRupiah($pemasukan) ?></div></div>
    </div>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 flex items-center gap-4">
        <div class="p-4 rounded-xl bg-rose-500/10 text-rose-500"><i data-lucide="arrow-up-right" class="w-8 h-8"></i></div>
        <div><div class="text-sm text-slate-400 font-medium">Total Pengeluaran</div><div class="text-2xl font-bold text-rose-500"><?= formatRupiah($pengeluaran) ?></div></div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 mb-8">
    <h3 class="text-lg font-bold mb-4">Arus Kas Bulanan</h3>
    <div class="h-64"><canvas id="financialChart"></canvas></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
    
    <div class="space-y-4">
        <h4 class="text-md font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="calendar" class="w-4 h-4 text-indigo-500"></i> Analisis Bulan Ini
        </h4>

        <?php if ($expenseMonthPercent > 0): ?>
            <div class="bg-rose-50 dark:bg-rose-500/10 border-l-4 border-rose-500 p-3 rounded-r-xl flex items-start gap-2.5">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-500 shrink-0 mt-0.5"></i>
                <p class="text-xs text-rose-700 dark:text-rose-300">Pengeluaran meningkat <span class="font-bold"><?= abs($expenseMonthPercent) ?>%</span> dr bulan lalu.</p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-4 rounded-xl shadow-sm">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Pemasukan</div>
                <div class="text-md font-bold text-slate-900 dark:text-white"><?= formatRupiah($thisMonthIncome) ?></div>
                <div class="flex items-center text-xs font-medium mt-1 <?= $incomeMonthPercent >= 0 ? 'text-emerald-500' : 'text-rose-500' ?>">
                    <i data-lucide="<?= $incomeMonthPercent >= 0 ? 'trending-up' : 'trending-down' ?>" class="w-3 h-3 mr-0.5"></i> <?= abs($incomeMonthPercent) ?>%
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-4 rounded-xl shadow-sm">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Pengeluaran</div>
                <div class="text-md font-bold text-slate-900 dark:text-white"><?= formatRupiah($thisMonthExpense) ?></div>
                <div class="flex items-center text-xs font-medium mt-1 <?= $expenseMonthPercent <= 0 ? 'text-emerald-500' : 'text-rose-500' ?>">
                    <i data-lucide="<?= $expenseMonthPercent >= 0 ? 'trending-up' : 'trending-down' ?>" class="w-3 h-3 mr-0.5"></i> <?= abs($expenseMonthPercent) ?>%
                </div>
            </div>
        </div>

        <div class="bg-indigo-600 p-4 rounded-xl text-white shadow-sm relative overflow-hidden">
            <div class="text-xs opacity-75 font-semibold uppercase tracking-wider mb-1">Rasio Tabungan Bulan Ini</div>
            <div class="text-lg font-bold"><?= $savingsRatioM ?>% Tersimpan</div>
            <p class="text-[11px] opacity-90 mt-0.5">Berhasil menyisihkan dana dari penghasilan bulan ini.</p>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-4 rounded-xl shadow-sm space-y-3">
            <div class="flex items-start gap-3">
                <div class="bg-orange-100 dark:bg-orange-500/20 p-2 rounded-lg text-orange-500 shrink-0"><i data-lucide="pie-chart" class="w-4 h-4"></i></div>
                <p class="text-xs text-slate-600 dark:text-slate-300">Bocor terbesar di kategori <span class="font-bold text-slate-950 dark:text-white"><?= htmlspecialchars($topExpenseCatM['nama_kategori'] ?? '-') ?></span> (<?= formatRupiah($topExpenseCatM['total'] ?? 0) ?>)</p>
            </div>
            <div class="flex items-start gap-3">
                <div class="bg-blue-100 dark:bg-blue-500/20 p-2 rounded-lg text-blue-500 shrink-0"><i data-lucide="activity" class="w-4 h-4"></i></div>
                <p class="text-xs text-slate-600 dark:text-slate-300">Kategori <span class="font-bold text-slate-950 dark:text-white"><?= htmlspecialchars($mostFreqCatM['nama_kategori'] ?? '-') ?></span> paling aktif digunakan (<?= number_format($mostFreqCatM['freq'] ?? 0) ?>x transaksi).</p>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <h4 class="text-md font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="globe" class="w-4 h-4 text-cyan-500"></i> Analisis Tahun Ini (<?= $thisYear ?>)
        </h4>

        <?php if ($expenseYearPercent > 0): ?>
            <div class="bg-amber-50 dark:bg-amber-500/10 border-l-4 border-amber-500 p-3 rounded-r-xl flex items-start gap-2.5">
                <i data-lucide="alert-circle" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"></i>
                <p class="text-xs text-amber-700 dark:text-amber-300">Grafik pengeluaran tahunanmu naik <span class="font-bold"><?= abs($expenseYearPercent) ?>%</span> dibanding tahun lalu.</p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-4 rounded-xl shadow-sm">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Pemasukan Tahunan</div>
                <div class="text-md font-bold text-slate-900 dark:text-white"><?= formatRupiah($thisYearIncome) ?></div>
                <div class="flex items-center text-xs font-medium mt-1 <?= $incomeYearPercent >= 0 ? 'text-emerald-500' : 'text-rose-500' ?>">
                    <i data-lucide="<?= $incomeYearPercent >= 0 ? 'trending-up' : 'trending-down' ?>" class="w-3 h-3 mr-0.5"></i> <?= abs($incomeYearPercent) ?>%
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-4 rounded-xl shadow-sm">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Pengeluaran Tahunan</div>
                <div class="text-md font-bold text-slate-900 dark:text-white"><?= formatRupiah($thisYearExpense) ?></div>
                <div class="flex items-center text-xs font-medium mt-1 <?= $expenseYearPercent <= 0 ? 'text-emerald-500' : 'text-rose-500' ?>">
                    <i data-lucide="<?= $expenseYearPercent >= 0 ? 'trending-up' : 'trending-down' ?>" class="w-3 h-3 mr-0.5"></i> <?= abs($expenseYearPercent) ?>%
                </div>
            </div>
        </div>

        <div class="bg-cyan-600 p-4 rounded-xl text-white shadow-sm relative overflow-hidden">
            <div class="text-xs opacity-75 font-semibold uppercase tracking-wider mb-1">Rasio Tabungan Makro (1 Tahun)</div>
            <div class="text-lg font-bold"><?= $savingsRatioY ?>% Tersimpan</div>
            <p class="text-[11px] opacity-90 mt-0.5">Rata-rata kestabilan dana simpanan aman jangka panjang.</p>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-4 rounded-xl shadow-sm space-y-3">
            <div class="flex items-start gap-3">
                <div class="bg-amber-100 dark:bg-amber-500/20 p-2 rounded-lg text-amber-500 shrink-0"><i data-lucide="award" class="w-4 h-4"></i></div>
                <p class="text-xs text-slate-600 dark:text-slate-300">Pengeluaran setahun terbesar: <span class="font-bold text-slate-950 dark:text-white"><?= htmlspecialchars($topExpenseCatY['nama_kategori'] ?? '-') ?></span> (<?= formatRupiah($topExpenseCatY['total'] ?? 0) ?>)</p>
            </div>
            <div class="flex items-start gap-3">
                <div class="bg-purple-100 dark:bg-purple-500/20 p-2 rounded-lg text-purple-500 shrink-0"><i data-lucide="bar-chart-3" class="w-4 h-4"></i></div>
                <p class="text-xs text-slate-600 dark:text-slate-300">Kategori paling konstan setahun ini: <span class="font-bold text-slate-950 dark:text-white"><?= htmlspecialchars($mostFreqCatY['nama_kategori'] ?? '-') ?></span> (<?= number_format($mostFreqCatY['freq'] ?? 0) ?>x).</p>
            </div>
        </div>
    </div>

</div>

<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700">
    <h3 class="text-lg font-bold mb-4">Aktivitas Terbaru</h3>
    <div class="space-y-4">
        <?php
        $recent = $pdo->query("SELECT t.*, k.nama_kategori FROM transaksi t JOIN kategori k ON t.kategori_id = k.id WHERE t.user_id = $uid ORDER BY t.tanggal DESC LIMIT 4")->fetchAll();
        if(empty($recent)): echo "<p class='text-slate-400 text-sm text-center py-8'>Belum ada transaksi.</p>";
        else: foreach($recent as $t): ?>
            <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-700/30">
                <div>
                    <div class="font-semibold text-sm"><?= htmlspecialchars($t['keterangan']) ?></div>
                    <div class="text-xs text-slate-400"><?= $t['tanggal'] ?></div>
                </div>
                <div class="text-sm font-bold <?= $t['jenis'] == 'pemasukan' ? 'text-emerald-500' : 'text-rose-500' ?>">
                    <?= ($t['jenis'] == 'pemasukan' ? '+' : '-') . number_format($t['jumlah'], 0, ',', '.') ?>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('financialChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [
                { label: 'Masuk', data: <?= json_encode($masukArr) ?>, backgroundColor: '#10b981', borderRadius: 4 },
                { label: 'Keluar', data: <?= json_encode($keluarArr) ?>, backgroundColor: '#f43f5e', borderRadius: 4 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>

<?php include '../layouts/footer.php'; ?>
