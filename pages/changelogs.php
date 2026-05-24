<?php
include '../layouts/header.php';

/* =========================
   ROLE CHECK
========================= */
$isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

/* =========================
   ADD (ADMIN ONLY)
========================= */
if ($isAdmin && isset($_POST['add_log'])) {

    $version = trim($_POST['version']);
    $date = trim($_POST['release_date']);
    $desc = trim($_POST['description']);

    if ($version && $date && $desc) {
        $stmt = $pdo->prepare("
            INSERT INTO changelogs (version, release_date, description)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$version, $date, $desc]);

        echo "<script>window.location.href='changelogs.php';</script>";
        exit;
    }
}

/* =========================
   DELETE (ADMIN ONLY)
========================= */
if ($isAdmin && isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM changelogs WHERE id = ?");
    $stmt->execute([$id]);

    echo "<script>window.location.href='changelogs.php';</script>";
    exit;
}

/* =========================
   GET DATA
========================= */
$logs = $pdo->query("SELECT * FROM changelogs ORDER BY release_date DESC")->fetchAll();
?>

<div class="min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">

    <!-- HEADER -->
    <div class="max-w-5xl mx-auto px-6 py-10">

        <h1 class="text-3xl font-bold">Changelogs</h1>
        <p class="text-slate-500 text-sm mt-1">
            Riwayat update dan perubahan sistem
        </p>

        <!-- ADMIN FORM -->
        <?php if ($isAdmin): ?>
            <div class="mt-8 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow border border-slate-100 dark:border-slate-700">

                <h2 class="font-bold mb-4 flex items-center gap-2">
                    Tambah Update
                </h2>

                <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <input type="text"
                           name="version"
                           placeholder="v1.0.0"
                           class="p-3 rounded-xl bg-slate-100 dark:bg-slate-700"
                           required>

                    <input type="date"
                           name="release_date"
                           class="p-3 rounded-xl bg-slate-100 dark:bg-slate-700"
                           required>

                    <button name="add_log"
                            class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold">
                        Simpan
                    </button>

                    <textarea name="description"
                              placeholder="Deskripsi update..."
                              rows="3"
                              class="md:col-span-3 p-3 rounded-xl bg-slate-100 dark:bg-slate-700"
                              required></textarea>

                </form>

            </div>
        <?php endif; ?>

        <!-- TIMELINE -->
        <div class="mt-10 relative border-l-2 border-slate-300 dark:border-slate-700 pl-6 space-y-10">

            <?php foreach ($logs as $row): ?>

                <div class="relative">

                    <!-- DOT -->
                    <span class="absolute -left-[9px] top-1 w-4 h-4 bg-blue-500 rounded-full border-4 border-white dark:border-slate-900"></span>

                    <!-- CONTENT -->
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow border border-slate-100 dark:border-slate-700">

                        <div class="flex justify-between items-start gap-4">

                            <div>
                                <h3 class="font-bold text-blue-500">
                                    <?= htmlspecialchars($row['version']) ?>
                                </h3>

                                <p class="text-xs text-slate-400">
                                    <?= date('d M Y', strtotime($row['release_date'])) ?>
                                </p>

                                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                                    <?= nl2br(htmlspecialchars($row['description'])) ?>
                                </p>
                            </div>

                            <?php if ($isAdmin): ?>
                                <a href="?delete=<?= $row['id'] ?>"
                                   onclick="return confirm('Hapus changelog ini?')"
                                   class="text-xs bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-xl">
                                    Hapus
                                </a>
                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>

<?php include '../layouts/footer.php'; ?>