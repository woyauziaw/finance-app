<?php
$breadcrumb_parent = "Pages";
$breadcrumb_active = "Profile"; 
include '../layouts/header.php';
ob_start();

$uid = $_SESSION['user_id'];

$message = '';
$msgType = '';

$uploadDir = __DIR__ . '/../uploads/';

// pastikan folder ada
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* =========================
   AMBIL DATA USER DULU (IMPORTANT)
========================= */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

if (!$user) {
    die("User tidak ditemukan");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* =========================
       UPDATE PROFILE
    ========================= */
    if (isset($_POST['update_profile'])) {

        $name = sanitize($_POST['name']);

        // update nama
        $pdo->prepare("UPDATE users SET name = ? WHERE id = ?")
            ->execute([$name, $uid]);

        $_SESSION['user_name'] = $name;

        // =========================
        // FOTO PROFIL UPLOAD
        // =========================
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {

                // nama file FIX (rapi & aman)
                $newPhoto = 'pp_' . $uid . '.' . $ext;
                $target = $uploadDir . $newPhoto;

                // hapus semua pp lama user (biar gak numpuk)
                foreach (glob($uploadDir . "pp_" . $uid . ".*") as $oldFile) {
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                // upload file baru
                $maxSize = 2 * 1024 * 1024; // 2MB

                if ($_FILES['photo']['size'] > $maxSize) {
                    $_SESSION['flash'] = [
                        'type' => 'error',
                        'message' => 'Ukuran gambar maksimal 2MB'
                    ];
                    echo "<script>window.location.href = 'profile.php';</script>";
                    exit;
                }
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {

                    $pdo->prepare("UPDATE users SET photo = ? WHERE id = ?")
                        ->execute([$newPhoto, $uid]);

                    $_SESSION['user_photo'] = $newPhoto;
                }
            }
        }

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Profil Berhasil Diperbarui!'
        ];
        echo "<script>window.location.href = 'profile.php';</script>";
        exit;
    }
    
    /* =========================
   DELETE PROFILE PHOTO
========================= */
if (isset($_POST['delete_photo'])) {

    $stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    $oldPhoto = $stmt->fetchColumn();

    if (!empty($oldPhoto) && file_exists(__DIR__ . '/../uploads/' . $oldPhoto)) {
        unlink(__DIR__ . '/../uploads/' . $oldPhoto);
    }

    $pdo->prepare("UPDATE users SET photo = NULL WHERE id = ?")
        ->execute([$uid]);

    $_SESSION['user_photo'] = null;

    $message = 'Foto profil berhasil dihapus!';
    $msgType = 'success';
}

    /* =========================
       CHANGE PASSWORD
    ========================= */
    if (isset($_POST['change_password'])) {

        $old = $_POST['old_password'];
        $new = $_POST['new_password'];
        
        if (strlen($new) < 8) {
          $message = 'Password minimal 8 karakter.';
          $msgType = 'error';
        } elseif (!preg_match('/[A-Z]/', $new)) {
          $message = 'Password harus mengandung huruf besar.';
          $msgType = 'error';
        } elseif (!preg_match('/[a-z]/', $new)) {
          $message = 'Password harus mengandung huruf kecil.';
          $msgType = 'error';
        } elseif (!preg_match('/[0-9]/', $new)) {
          $message = 'Password harus mengandung angka.';
          $msgType = 'error';
        } elseif (strtolower($new) === strtolower($user["name"])) {
          $message = 'Password tidak boleh sama dengan username.';
          $msgType = 'error';
        } else {
        

            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            $pwdDb = $stmt->fetchColumn();
    
            if (password_verify($old, $pwdDb)) {
    
                $newHashed = password_hash($new, PASSWORD_BCRYPT);
    
                $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
                    ->execute([$newHashed, $uid]);
    
                $message = 'Password Berhasil Diganti!';
                $msgType = 'success';
    
            } else {
                $message = 'Password Lama Anda Salah!';
                $msgType = 'error';
            }
        }
    }
}

/* =========================
   STATISTIK USER
========================= */

$stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE user_id = ?");
$stmt->execute([$uid]);
$totalCount = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->prepare("SELECT SUM(jumlah) FROM transaksi WHERE user_id = ? AND jenis = 'pemasukan'");
$stmt->execute([$uid]);
$sumIn = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->prepare("SELECT SUM(jumlah) FROM transaksi WHERE user_id = ? AND jenis = 'pengeluaran'");
$stmt->execute([$uid]);
$sumOut = $stmt->fetchColumn() ?? 0;

$netSaldo = $sumIn - $sumOut;
?>

<?php if($message): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({ icon: '<?= $msgType ?>', title: '<?= $message ?>', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
    });
</script>
<?php endif; ?>

<?php if (!empty($_SESSION['flash'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({
        icon: "<?= $_SESSION['flash']['type'] ?>",
        title: "<?= $_SESSION['flash']['message'] ?>",
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000
    });
});
</script>
<?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
    <!-- LEFT SIDEBAR CARD: IDENTITY INSIGHTS -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-6 text-center shadow-sm">
        <div class="relative w-32 h-32 mx-auto mb-4 group">
            <?php
                $initial = strtoupper(substr($user['name'], 0, 2));
                
                $colors = [
                    'bg-blue-500',
                    'bg-indigo-500',
                    'bg-purple-500',
                    'bg-pink-500',
                    'bg-emerald-500',
                    'bg-amber-500',
                ];
                
                $color = $colors[$user['id'] % count($colors)];
                
                // FIX IMPORTANT
                $photo = trim($user['photo'] ?? '');
                ?>
                
                <?php if ($photo !== '' && file_exists(__DIR__ . '/../uploads/' . $photo)): ?>
                    <img src="../uploads/<?= htmlspecialchars($photo) ?>"
                         class="w-full h-full rounded-full object-cover ring-4 ring-blue-500/20">
                <?php else: ?>
                    <div class="w-full h-full rounded-full flex items-center justify-center text-white text-xl font-bold <?= $color ?> ring-4 ring-white/10">
                        <?= $initial ?>
                    </div>
                <?php endif; ?>
            <button onclick="document.getElementById('edit-profile-modal').classList.remove('hidden')" class="absolute inset-0 bg-black/40 rounded-full text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                <i data-lucide="camera" class="w-6 h-6"></i>
            </button>
        </div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($user['name']) ?></h3>
        <p class="text-xs text-slate-400 font-medium mt-0.5"><?= htmlspecialchars($user['email']) ?></p>
        <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>

    <p class="text-[10px] uppercase font-bold text-red-500 tracking-widest bg-red-500/10 px-3 py-1 rounded-full inline-block mt-3">
        Administrator
    </p>

<?php else: ?>

    <p class="text-[10px] uppercase font-bold text-blue-500 tracking-widest bg-blue-500/10 px-3 py-1 rounded-full inline-block mt-3">
        Verified Client
    </p>

<?php endif; ?>

        <div class="border-t border-slate-100 dark:border-slate-700/60 mt-6 pt-4 text-left space-y-3 text-xs text-slate-400">
            <div class="flex justify-between"><span>Terdaftar Sejak</span><span class="font-bold text-slate-700 dark:text-slate-300"><?= substr($user['created_at'], 0, 10) ?></span></div>
            <div class="flex justify-between"><span>Total Pemrosesan</span><span class="font-bold text-slate-700 dark:text-slate-300"><?= $totalCount ?> Transaksi</span></div>
        </div>
        
        <div class="mt-6 flex flex-col gap-2">
            <form method="POST" onsubmit="return confirm('Yakin mau hapus foto profil?')">
                <input type="hidden" name="delete_photo" value="1">
                <button type="submit"
                    class="w-full bg-red-500/10 hover:bg-red-500/20 text-red-500 font-semibold py-2.5 rounded-xl text-xs transition">
                    Hapus Foto Profil
                </button>
            </form>
            <button onclick="document.getElementById('edit-profile-modal').classList.remove('hidden')" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-2.5 rounded-xl text-xs shadow-md">Ubah Informasi Profil</button>
            <button onclick="document.getElementById('security-modal').classList.remove('hidden')" class="w-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold py-2.5 rounded-xl text-xs">Ganti Password</button>
        </div>
    </div>

    <!-- RIGHT CARD: PREMIUM STATISTICS CARDS & METRICS -->
    <div class="lg:col-span-2 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700">
                <p class="text-xs font-bold text-slate-400 uppercase">Ekosistem Saldo Bersih</p>
                <p class="text-2xl font-black text-blue-500 mt-1"><?= formatRupiah($netSaldo) ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700">
                <p class="text-xs font-bold text-slate-400 uppercase">Perputaran Pemasukan</p>
                <p class="text-2xl font-black text-emerald-500 mt-1"><?= formatRupiah($sumIn) ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 from-slate-900 to-slate-800 rounded-3xl p-6 relative overflow-hidden border border-slate-100 dark:border-slate-700">
            <h4 class="text-lg font-bold mb-1">Keamanan Data Terjaga</h4>
            <p class="text-xs text-slate-400 max-w-sm">
            Catatan keuangan Anda disimpan secara pribadi dan tidak dibagikan kepada pihak lain.</p>
        </div>
    </div>
</div>

<!-- MODAL BOX: UPDATE DATA PROFIL -->
<div id="edit-profile-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-2xl max-w-sm w-full p-6 animate-in zoom-in-95 duration-150">
        <div class="flex justify-between items-center mb-4"><h3 class="font-bold">Edit Informasi</h3><button onclick="document.getElementById('edit-profile-modal').classList.add('hidden')"><i data-lucide="x" class="text-slate-400"></i></button></div>
        <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="update_profile" value="1">
            <div><label class="block text-xs font-bold text-slate-400 mb-1">Nama Baru</label><input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-xs font-bold text-slate-400 mb-1">Ubah Foto Profil</label><input type="file" name="photo" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-500 file:text-white hover:file:bg-blue-600"></div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-xl">Simpan Profil Baru</button>
        </form>
    </div>
</div>

<!-- MODAL BOX: UBAH SECURITY SECURITY PASSWORD -->
<div id="security-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-2xl max-w-sm w-full p-6 animate-in zoom-in-95 duration-150">
        <div class="flex justify-between items-center mb-4"><h3 class="font-bold">Ubah Kredensial Keamanan</h3><button onclick="document.getElementById('security-modal').classList.add('hidden')"><i data-lucide="x" class="text-slate-400"></i></button></div>
        <form action="" method="POST" class="space-y-4">
            <input type="hidden" name="change_password" value="1">
            <div><label class="block text-xs font-bold text-slate-400 mb-1">Password Lama</label><input type="password" name="old_password" required class="w-full p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-xs font-bold text-slate-400 mb-1">Password Baru</label><input type="password" name="new_password" required class="w-full p-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-blue-500"></div>
            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2.5 rounded-xl">Ganti Autentikasi Password</button>
        </form>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>



<?php /**
include '../layouts/header.php';
$uid = $_SESSION['user_id'];
$message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $pdo->prepare("UPDATE users SET name = ? WHERE id = ?")->execute([$name, $uid]);
    $_SESSION['user_name'] = $name;
    $message = 'Profil berhasil diperbarui!';
}
$user = $pdo->query("SELECT * FROM users WHERE id = $uid")->fetch();
?>
<div class="max-w-xl mx-auto bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
    <h2 class="text-2xl font-bold mb-6">Pengaturan Profil</h2>
    <?php if($message): ?><div class="bg-emerald-900/40 text-emerald-400 p-3 rounded-xl text-sm mb-4 text-center"><?= $message ?></div><?php endif; ?>
    <form action="" method="POST" class="space-y-4">
        <div><label class="block text-sm mb-1">Nama Pengguna</label><input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700"></div>
        <div><label class="block text-sm mb-1">Alamat Email</label><input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled class="w-full px-4 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-900 text-slate-400 cursor-not-allowed"></div>
        <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-xl shadow-lg shadow-blue-500/20">Simpan Perubahan</button>
    </form>
</div>
<?php include '../layouts/footer.php'; */?>