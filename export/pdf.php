<?php
session_start();
require_once '../config/database.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

if (!isset($_SESSION['user_id'])) {
    exit;
}

$uid = $_SESSION['user_id'];

/* =========================
   AMBIL DATA
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
   BUILD HTML (PDF CONTENT)
========================= */

$html = "
<h2 style='text-align:center;'>FINTRACK REPORT</h2>
<hr>
<table width='100%' border='1' cellspacing='0' cellpadding='5'>
    <tr>
        <th>Tanggal</th>
        <th>Keterangan</th>
        <th>Kategori</th>
        <th>Jenis</th>
        <th>Jumlah</th>
    </tr>
";

foreach ($transactions as $t) {
    $html .= "
    <tr>
        <td>{$t['tanggal']}</td>
        <td>{$t['keterangan']}</td>
        <td>{$t['nama_kategori']}</td>
        <td>{$t['jenis']}</td>
        <td>Rp " . number_format($t['jumlah'], 0, ',', '.') . "</td>
    </tr>
    ";
}

$html .= "</table>";

/* =========================
   GENERATE PDF
========================= */
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* download file */
$dompdf->stream("fintrack_report.pdf", ["Attachment" => true]);
exit;