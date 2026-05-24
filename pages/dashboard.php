<?php
include '../layouts/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='../auth/login.php';</script>";
    exit();
}

$uid = $_SESSION['user_id'];
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
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 lg:col-span-2"><h3 class="text-lg font-bold mb-4">Arus Kas Bulanan</h3><div class="h-64"><canvas id="financialChart"></canvas></div></div>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700">
        <h3 class="text-lg font-bold mb-4">Aktivitas Terbaru</h3>
        <div class="space-y-4">
            <?php
            $recent = $pdo->query("SELECT t.*, k.nama_kategori FROM transaksi t JOIN kategori k ON t.kategori_id = k.id WHERE t.user_id = $uid ORDER BY t.tanggal DESC LIMIT 4")->fetchAll();
            if(empty($recent)): echo "<p class='text-slate-400 text-sm text-center py-8'>Belum ada transaksi.</p>";
            else: foreach($recent as $t): ?>
                <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-700/30">
                    <div><div class="font-semibold text-sm"><?= htmlspecialchars($t['keterangan']) ?></div><div class="text-xs text-slate-400"><?= $t['tanggal'] ?></div></div>
                    <div class="text-sm font-bold <?= $t['jenis'] == 'pemasukan' ? 'text-emerald-500' : 'text-rose-500' ?>"><?= ($t['jenis'] == 'pemasukan' ? '+' : '-') . number_format($t['jumlah'], 0, ',', '.') ?></div>
                </div>
            <?php endforeach; endif; ?>
        </div>
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
                { label: 'Keluar', data: <?= json_encode($keluarArr) ?>, backgroundColor: '#ef4444', borderRadius: 4 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>
<?php include '../layouts/footer.php'; ?>