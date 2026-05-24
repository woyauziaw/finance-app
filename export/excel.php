<?php
session_start();
require_once '../config/database.php';
if(!isset($_SESSION['user_id'])) exit('Unauthorized');
$uid = $_SESSION['user_id'];
$transactions = $pdo->query("SELECT t.*, k.nama_kategori FROM transaksi t JOIN kategori k ON t.kategori_id = k.id WHERE t.user_id = $uid ORDER BY t.tanggal DESC")->fetchAll();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_FinTrack.xls");
echo "Tanggal	Keterangan	Kategori	Jenis	Jumlah
";
foreach($transactions as $t) {
    echo "{$t['tanggal']}	".htmlspecialchars($t['keterangan'])."	".htmlspecialchars($t['nama_kategori'])."	".strtoupper($t['jenis'])."	{$t['jumlah']}
";
}