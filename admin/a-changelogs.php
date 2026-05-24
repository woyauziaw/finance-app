<?php
include '../layouts/header.php';

session_start();

/* CHECK ADMIN */
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    die("<div class='p-5 text-red-500 font-bold'>Akses ditolak</div>");
}

/* ADD LOG */
if (isset($_POST['add_log'])) {

    $version = sanitize($_POST['version']);
    $date = sanitize($_POST['release_date']);
    $desc = sanitize($_POST['description']);

    $stmt = $pdo->prepare("
        INSERT INTO changelogs (version, release_date, description)
        VALUES (:version, :date, :desc)
    ");

    $stmt->execute([
        ':version' => $version,
        ':date' => $date,
        ':desc' => $desc
    ]);

    header("Location: changelogs.php");
    exit;
}

/* DELETE LOG */
if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM changelogs WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: changelogs.php");
    exit;
}

/* GET DATA */
$stmt = $pdo->query("SELECT * FROM changelogs ORDER BY release_date DESC");
$logs = $stmt->fetchAll();
?>

<div class="flex min-h-screen bg-slate-50 dark:bg-slate-900">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 text-white p-5 hidden lg:block">
        <h2 class="text-xl font-bold mb-6">Admin Panel</h2>

        <nav class="flex flex-col gap-2 text-sm">

            <a href="index.php" class="px-3 py-2 rounded-lg hover:bg-slate-800">🏠 Dashboard</a>
            <a href="users.php" class="px-3 py-2 rounded-lg hover:bg-slate-800">👥 Users</a>
            <a href="changelogs.php" class="px-3 py-2 rounded-lg bg-yellow-500 text-black font-semibold">⚙️ Changelogs</a>

        </nav>

        <div class="mt-6">
            <a href="../logout.php" class="block text-center bg-red-600 hover:bg-red-700 py-2 rounded-lg font-semibold">
                Logout
            </a>
        </div>
    </aside>

    <!-- CONTENT -->
    <main class="flex-1 p-6">

        <!-- HEADER -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Manajemen Changelogs</h1>
            <p class="text-slate-400 text-sm">Kelola riwayat update sistem</p>
        </div>

        <!-- FORM CARD -->
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl p-6 mb-6">

            <h2 class="font-bold mb-4">Tambah Log Update</h2>

            <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <input type="text" name="version" placeholder="v1.0.0"
                    class="p-3 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm outline-none focus:ring-2 focus:ring-blue-500"
                    required>

                <input type="date" name="release_date"
                    class="p-3 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm outline-none focus:ring-2 focus:ring-blue-500"
                    required>

                <button name="add_log"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl px-4 py-3">
                    + Simpan Log
                </button>

                <textarea name="description" rows="3"
                    class="md:col-span-3 p-3 rounded-xl bg-slate-100 dark:bg-slate-700 text-sm outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Deskripsi update..." required></textarea>

            </form>
        </div>

        <!-- TABLE -->
        <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl p-6">

            <h2 class="font-bold mb-4">Riwayat Changelog</h2>

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead class="text-left text-slate-400 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="py-2">Versi</th>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">

                        <?php if (count($logs) > 0): ?>
                            <?php foreach ($logs as $row): ?>

                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">

                                    <td class="font-bold text-blue-500 py-3">
                                        <?= htmlspecialchars($row['version']) ?>
                                    </td>

                                    <td class="text-slate-400">
                                        <?= date('d M Y', strtotime($row['release_date'])) ?>
                                    </td>

                                    <td class="text-slate-500">
                                        <?= nl2br(htmlspecialchars($row['description'])) ?>
                                    </td>

                                    <td>
                                        <a href="?delete=<?= $row['id'] ?>"
                                           onclick="return confirm('Hapus log ini?')"
                                           class="text-red-500 hover:text-red-700 font-semibold">
                                            Hapus
                                        </a>
                                    </td>

                                </tr>

                            <?php endforeach; ?>
                        <?php else: ?>

                            <tr>
                                <td colspan="4" class="text-center text-slate-400 py-6">
                                    Belum ada data changelog
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>
        </div>

    </main>
</div>

<?php include '../layouts/footer.php'; ?>