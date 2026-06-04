<?php
$breadcrumb_parent = "Pages";
$breadcrumb_active = "Transaksi"; 
include '../layouts/header.php'; 
$uid = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $kat = sanitize($_POST['kategori_id']);
            $jml = (float)str_replace('.', '', sanitize($_POST['jumlah']));
            $ket = sanitize($_POST['keterangan']);
            $tgl = sanitize($_POST['tanggal']);
            $jenis = $pdo->query("SELECT jenis FROM kategori WHERE id = $kat")->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO transaksi (user_id, kategori_id, jenis, jumlah, keterangan, tanggal) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$uid, $kat, $jenis, $jml, $ket, $tgl]);
        } elseif ($_POST['action'] === 'delete') {
            $id = sanitize($_POST['id']);
            $pdo->prepare("DELETE FROM transaksi WHERE id = ? AND user_id = ?")->execute([$id, $uid]);
        }
        echo "<script>window.location.href='transaksi.php';</script>";
        exit;
    }
}
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$queryStr = "SELECT t.*, k.nama_kategori, k.color FROM transaksi t JOIN kategori k ON t.kategori_id = k.id WHERE t.user_id = $uid";
$params = [];
if(!empty($search)) { $queryStr .= " AND t.keterangan LIKE ?"; $params[] = "%$search%"; }
$queryStr .= " ORDER BY t.tanggal DESC";
$stmt = $pdo->prepare($queryStr); $stmt->execute($params); $transactions = $stmt->fetchAll();

?>
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Data Log Transaksi</h2>
    <div class="flex gap-3">

    <!-- EXPORT -->
    <a href="../export/export.php"
       class="flex flex-col items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 sm:px-4 sm:py-3 rounded-xl text-xs sm:text-sm font-semibold w-16 sm:w-20 transition">

        <i data-lucide="upload" class="w-4 h-4 sm:w-5 sm:h-5 mb-1"></i>
        <span>Export</span>
    </a>

    <!-- IMPORT -->
    <a href="importcsv.php"
       class="flex flex-col items-center justify-center bg-red-500 hover:bg-red-600 text-white px-3 py-2 sm:px-4 sm:py-3 rounded-xl text-xs sm:text-sm font-semibold w-16 sm:w-20 transition">

        <i data-lucide="download" class="w-4 h-4 sm:w-5 sm:h-5 mb-1"></i>
        <span>Import</span>
    </a>


        <button onclick="document.getElementById('modal-transaksi').classList.remove('hidden')"
    class="flex flex-col items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 sm:px-4 sm:py-3 rounded-xl text-xs sm:text-sm font-semibold w-16 sm:w-20 transition">

    <i data-lucide="plus" class="w-4 h-4 sm:w-5 sm:h-5 mb-1"></i>
    <span>Baru</span>

</button>
    </div>
</div>
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden shadow-sm p-4">
    <form method="GET" class="mb-4"><input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari transaksi..." class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 border-0 text-sm w-full max-w-xs"></form>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead><tr class="bg-slate-50 dark:bg-slate-700/50 text-xs text-slate-400 font-bold uppercase"><th class="p-4">Tanggal</th><th class="p-4">Keterangan</th><th class="p-4">Kategori</th><th class="p-4">Jumlah</th><th class="p-4 text-center">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                <?php if(empty($transactions)): ?><tr><td colspan="5" class="text-center p-8 text-slate-400">Data kosong.</td></tr>
                <?php else: foreach($transactions as $t): ?>
                    <tr>
                        <td class="p-4"><?= $t['tanggal'] ?></td>
                        <td class="p-4 font-semibold"><?= htmlspecialchars($t['keterangan']) ?></td>
                        <td class="p-4"><span class="px-2 py-0.5 rounded text-xs text-white" style="background-color: <?= $t['color'] ?>"><?= htmlspecialchars($t['nama_kategori']) ?></span></td>
                        <td class="p-4 font-bold <?= $t['jenis'] == 'pemasukan' ? 'text-emerald-500' : 'text-rose-500' ?>"><?= formatRupiah($t['jumlah']) ?></td>
                        <td class="p-4 text-center">
                            <form action="" method="POST"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>"><button type="submit" class="text-rose-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="modal-transaksi" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4"><h3 class="font-bold">Tambah Transaksi</h3><button onclick="document.getElementById('modal-transaksi').classList.add('hidden')" class="text-slate-400"><i data-lucide="x"></i></button></div>
        <form action="" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">
            <div><label class="block text-xs font-bold text-slate-400 mb-1">Kategori</label><select name="kategori_id" required class="w-full p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm"><?php $kats = $pdo->query("SELECT * FROM kategori WHERE user_id = $uid")->fetchAll(); foreach($kats as $k) { echo "<option value='{$k['id']}'>[" . strtoupper($k['jenis']) . "] {$k['nama_kategori']}</option>"; } ?></select></div>
            <div><label class="block text-xs font-bold text-slate-400 mb-1">Nominal</label><input type="text" name="jumlah" onkeyup="formatRupiahInput(this)" placeholder="100.000" required class="w-full p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm"></div>
            <div><label class="block text-xs font-bold text-slate-400 mb-1">Keterangan</label><input type="text" name="keterangan" required class="w-full p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm"></div>
            <div><label class="block text-xs font-bold text-slate-400 mb-1">Tanggal</label><input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required class="w-full p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm"></div>
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-xl shadow-lg">Simpan</button>
        </form>
    </div>
</div>
<?php include '../layouts/footer.php'; ?>