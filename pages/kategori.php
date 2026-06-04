<?php
$breadcrumb_parent = "Pages";
$breadcrumb_active = "Kategori";
include '../layouts/header.php';

$uid = $_SESSION['user_id'];
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['action'])) {
        if($_POST['action'] === 'create') {
            $nama = sanitize($_POST['nama_kategori']);
            $jenis = sanitize($_POST['jenis']);
            $color = sanitize($_POST['color']);
            $stmt = $pdo->prepare("INSERT INTO kategori (user_id, nama_kategori, jenis, color) VALUES (?, ?, ?, ?)");
            $stmt->execute([$uid, $nama, $jenis, $color]);
        } elseif($_POST['action'] === 'delete') {
            $id = sanitize($_POST['id']);
            $pdo->prepare("DELETE FROM kategori WHERE id = ? AND user_id = ?")->execute([$id, $uid]);
        }
        echo "<script>window.location.href='kategori.php';</script>";
        exit;
    }
}
$categories = $pdo->query("SELECT * FROM kategori WHERE user_id = $uid")->fetchAll();
?>
<div class="flex justify-between items-center mb-6"><h2 class="text-2xl font-bold">Kategori Management</h2><button onclick="document.getElementById('modal-kategori').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-semibold">+ Kategori</button></div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <?php foreach($categories as $c): ?>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-100 dark:border-slate-700 flex items-center justify-between border-l-4" style="border-left-color: <?= $c['color'] ?>">
            <div><h4 class="font-bold"><?= htmlspecialchars($c['nama_kategori']) ?></h4><p class="text-xs uppercase text-slate-400"><?= $c['jenis'] ?></p></div>
            <form action="" method="POST"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button type="submit" class="text-rose-500"><i data-lucide="trash" class="w-4 h-4"></i></button></form>
        </div>
    <?php endforeach; ?>
</div>
<div id="modal-kategori" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-sm p-6">
        <div class="flex justify-between items-center mb-4"><h3 class="font-bold">Kategori Baru</h3><button onclick="document.getElementById('modal-kategori').classList.add('hidden')" class="text-slate-400"><i data-lucide="x"></i></button></div>
        <form action="" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">
            <div><label class="block text-xs font-bold text-slate-400 mb-1">Nama Kategori</label><input type="text" name="nama_kategori" required class="w-full p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm"></div>
            <div><label class="block text-xs font-bold text-slate-400 mb-1">Jenis Arus Dana</label><select name="jenis" class="w-full p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm"><option value="pemasukan">Pemasukan</option><option value="pengeluaran">Pengeluaran</option></select></div>
            <div><label class="block text-xs font-bold text-slate-400 mb-1">Warna</label><input type="color" name="color" value="#3b82f6" class="w-full h-10 border-0 rounded-xl cursor-pointer"></div>
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-xl shadow-lg">Simpan</button>
        </form>
    </div>
</div>
<?php include '../layouts/footer.php'; ?>