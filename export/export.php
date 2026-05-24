<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    exit;
}

$uid = $_SESSION['user_id'];

/* =========================
   AMBIL DATA TRANSAKSI
========================= */
$stmt = $pdo->prepare("
    SELECT 
        t.tanggal,
        t.keterangan,
        k.nama_kategori,
        t.jumlah,
        t.jenis
    FROM transaksi t
    JOIN kategori k ON t.kategori_id = k.id
    WHERE t.user_id = ?
    ORDER BY t.tanggal DESC
");

$stmt->execute([$uid]);
$transactions = $stmt->fetchAll();

/* =========================
   HEADER FILE DOWNLOAD CSV
========================= */
$filename = "fintrack_export_" . date("Ymd_His") . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

/* =========================
   OUTPUT CSV
========================= */
$output = fopen("php://output", "w");

/* header kolom */
fputcsv($output, [
    'Tanggal',
    'Keterangan',
    'Kategori',
    'Jenis',
    'Jumlah'
]);

/* data */
foreach ($transactions as $t) {
    fputcsv($output, [
        $t['tanggal'],
        $t['keterangan'],
        $t['nama_kategori'],
        $t['jenis'],
        $t['jumlah']
    ]);
}

fclose($output);
exit;