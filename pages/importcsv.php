<?php
$breadcrumb_parent = "Pages";
$breadcrumb_active = "Importcsv";
include '../layouts/header.php';

$uid = $_SESSION['user_id'];
$previewData = [];
$importLog = '';

if (!isset($_SESSION['import_preview'])) {
    $_SESSION['import_preview'] = [];
}

/* =========================
   HANDLE UPLOAD
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* =========================
       UPLOAD FILE
    ========================= */
    if (!empty($_FILES['import_file']['name'])) {

        if ($_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $importLog = "Upload gagal";
        } else {

            $fileTmp = $_FILES['import_file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));

            $rawData = [];

            /* =========================
               CSV PARSER (SESUAI FORMAT KAMU)
               Tanggal,Keterangan,Kategori,Jenis,Jumlah
            ========================= */
            if (in_array($ext, ['csv', 'txt'])) {

                $content = file_get_contents($fileTmp);
                $lines = explode("\n", trim($content));

                foreach ($lines as $i => $line) {

                    if ($i === 0) continue; // skip header

                    $data = str_getcsv($line);

                    if (count($data) < 5) continue;

                    $jenis = strtolower(trim($data[3]));

                    // FIX ENUM ERROR
                    if ($jenis !== 'pemasukan' && $jenis !== 'pengeluaran') {
                        continue;
                    }

                    $rawData[] = [
                        'tanggal' => trim($data[0]),
                        'deskripsi' => trim($data[1]),
                        'kategori' => trim($data[2]),
                        'jenis' => $jenis,
                        'jumlah' => (float) str_replace(',', '', $data[4]),
                    ];
                }
            }

            if (!empty($rawData)) {
                $_SESSION['import_preview'] = $rawData;
                $previewData = $rawData;
                $importLog = "Preview: " . count($rawData) . " data";
            } else {
                $importLog = "Tidak ada data valid (cek format CSV)";
            }
        }
    }

    /* =========================
       COMMIT IMPORT
    ========================= */
    if (isset($_POST['commit_import'])) {

        $previewData = $_SESSION['import_preview'] ?? [];

        $inserted = 0;
        $duplicate = 0;

        foreach ($previewData as $row) {

            // ambil kategori
            $stmt = $pdo->prepare("SELECT id FROM kategori WHERE nama_kategori = ? AND user_id = ?");
            $stmt->execute([$row['kategori'], $uid]);
            $katId = $stmt->fetchColumn();

            if (!$katId) {
                $ins = $pdo->prepare("INSERT INTO kategori (user_id, nama_kategori, jenis, color)
                                      VALUES (?, ?, ?, '#3b82f6')");
                $ins->execute([$uid, $row['kategori'], $row['jenis']]);
                $katId = $pdo->lastInsertId();
            }

            // INSERT SESUAI TABLE KAMU (INI FIX UTAMA)
            try {
                $ins = $pdo->prepare("
                    INSERT INTO transaksi
                    (user_id, kategori_id, jenis, jumlah, keterangan, tanggal)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $ins->execute([
                    $uid,
                    $katId,
                    $row['jenis'],
                    $row['jumlah'],
                    $row['deskripsi'],
                    $row['tanggal']
                ]);

                $inserted++;

            } catch (PDOException $e) {
                $duplicate++;
                echo $e;
            }
        }

        unset($_SESSION['import_preview']);
        $previewData = [];

        $importLog = "Import selesai: $inserted masuk, $duplicate gagal/duplikat";
    }
}
?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm mb-6">
        <h2 class="text-2xl font-bold mb-2">Import Data Transaksi</h2>
        <p class="text-slate-400 text-sm mb-6">Unggah file laporan Anda (Format didukung: .CSV, .XLSX, dan .PDF Tabel Arus Kas).</p>

        <?php if($importLog): ?>
            <div class="p-4 bg-blue-500/10 text-blue-500 rounded-xl mb-4 text-sm font-semibold border border-blue-500/20 animate-pulse"><?= $importLog ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">

    <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-8 text-center hover:border-blue-500 transition-colors">

        <input
            type="file"
            name="import_file"
            id="import_file"
            class="hidden"
            accept=".csv,text/csv,.xls,.xlsx,.pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            onchange="document.getElementById('uploadForm').submit();"
        >

        <label for="import_file" class="cursor-pointer">
            <i data-lucide="cloud-lightning" class="w-12 h-12 mx-auto mb-2 text-blue-500 animate-bounce"></i>
            <span class="text-sm font-semibold block text-slate-300">Klik untuk upload CSV</span>
            <span class="text-xs text-slate-500 block mt-1">Proses cepat anti lelet!</span>
        </label>

    </div>
</form>
    </div>

    <!-- Live Preview Table Section Sebelum Commit Permanen -->
    <?php if(!empty($previewData)): ?>
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm animate-in fade-in slide-in-from-bottom-4 duration-300">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Pratinjau Data Sinkronisasi</h3>
                <div class="w-32 bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                    <div class="bg-blue-500 h-full w-2/3 animate-pulse"></div>
                </div>
            </div>
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-700/50 text-slate-400 font-bold uppercase text-xs">
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Jenis</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3">Nominal</th>
                            <th class="p-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 font-medium">
                        <?php foreach($previewData as $p): ?>
                            <tr>
                                <td class="p-3"><?= $p['tanggal'] ?></td>
                                <td class="p-3 uppercase text-xs font-bold <?= $p['jenis'] === 'pemasukan' ? 'text-emerald-500' : 'text-rose-500' ?>"><?= $p['jenis'] ?></td>
                                <td class="p-3"><span class="px-2 py-0.5 bg-blue-500/10 text-blue-500 rounded text-xs"><?= htmlspecialchars($p['kategori']) ?></span></td>
                                <td class="p-3 font-mono"><?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                                <td class="p-3 text-slate-400"><?= htmlspecialchars($p['deskripsi']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <form action="" method="POST">
                <button type="submit" name="commit_import" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transition-all">Konfirmasi & Impor Masal Sekarang</button>
            </form>
        </div>
    <?php endif; ?>
</div>
<?php include '../layouts/footer.php'; ?>