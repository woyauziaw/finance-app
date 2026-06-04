<?php
include '../config/database.php';

session_start();
$uid = $_SESSION['user_id'];

$goal_id = (int) ($_GET['id'] ?? 0);

// ambil history khusus user + goal
$stmt = $pdo->prepare("
    SELECT * 
    FROM goal_deposits 
    WHERE goal_id = ? AND user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$goal_id, $uid]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$data) {
    echo "<p class='text-sm text-slate-500'>Belum ada riwayat setoran</p>";
    exit;
}

foreach ($data as $row) {
    echo "
    <div class='flex justify-between items-center border-b border-slate-100 dark:border-slate-700 py-2'>
        <div class='text-sm font-semibold text-emerald-600'>
            +Rp " . number_format($row['jumlah'], 0, ',', '.') . "
        </div>
        <div class='text-xs text-slate-400'>
            " . date('d M Y H:i', strtotime($row['created_at'])) . "
        </div>
    </div>";
}
?>