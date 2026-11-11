
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . '/../../model/pengaduan.php';
require_once __DIR__ . '/_layout.php';

$model = new Pengaduan();
$result = $model->getUser((int)$_SESSION['user_id']);
$data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$stats = [
    'total' => count($data),
    'baru' => 0,
    'diproses' => 0,
    'selesai' => 0,
    'ditolak' => 0
];

foreach ($data as $row) {
    $key = userStatusKey($row['status'] ?? '');
    if (isset($stats[$key])) {
        $stats[$key]++;
    }
}

$warna = [
    'baru' => '#f59e0b',
    'diproses' => '#3b82f6',
    'selesai' => '#16a34a',
    'ditolak' => '#6b7280'
];

$segmen = [];
$mulai = 0;

foreach ($warna as $key => $hex) {
    if ($stats['total'] > 0 && $stats[$key] > 0) {
        $persen = $stats[$key] / $stats['total'] * 100;
        $akhir = $mulai + $persen;
        $segmen[] = $hex . ' ' . $mulai . '% ' . $akhir . '%';
        $mulai = $akhir;
    }
}

$pieGradient = $segmen
    ? 'conic-gradient(' . implode(',', $segmen) . ')'
    : 'conic-gradient(#ddd 0 100%)';
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard | Pengaduan Sarana Sekolah</title>
<link rel="stylesheet" href="../../asset/css/style.css?v=<?= @filemtime(__DIR__ . '/../../asset/css/style.css') ?: 1 ?>">
</head>

<body class="admin-dashboard">

<?php renderUserSidebar('dashboard'); ?>

<main class="admin-main">
<div class="admin-page">

<?php
renderUserHeader(
    'SISTEM PENGADUAN SARANA SEKOLAH',
    'Dashboard',
    'Ringkasan aktivitas pengaduan dan laporan saya.'
);
?>

<section class="admin-stats">

<a class="admin-kpi-link" href="pengaduan.php">
<div class="admin-stat">
<div class="admin-stat-icon">▣</div>
<div>
<div class="admin-stat-number"><?= $stats['total'] ?></div>
<div class="admin-stat-label">Total Pengaduan</div>
</div>
</div>
</a>

<a class="admin-kpi-link" href="pengaduan.php?status=baru">
<div class="admin-stat">
<div class="admin-stat-icon">!</div>
<div>
<div class="admin-stat-number"><?= $stats['baru'] ?></div>
<div class="admin-stat-label">Menunggu</div>
</div>
</div>
</a>

<a class="admin-kpi-link" href="pengaduan.php?status=diproses">
<div class="admin-stat">
<div class="admin-stat-icon">◷</div>
<div>
<div class="admin-stat-number"><?= $stats['diproses'] ?></div>
<div class="admin-stat-label">Diproses</div>
</div>
</div>
</a>

<a class="admin-kpi-link" href="riwayat.php?filter=selesai">
<div class="admin-stat">
<div class="admin-stat-icon">✓</div>
<div>
<div class="admin-stat-number"><?= $stats['selesai'] ?></div>
<div class="admin-stat-label">Selesai</div>
</div>
</div>
</a>

</section>

<div class="admin-content-grid">

<section class="admin-panel">

<div class="admin-toolbar">
<div>
<h2 class="admin-panel-title">Pengaduan Terbaru</h2>
<div class="admin-page-subtitle">Lima laporan terakhir yang kamu kirim.</div>
</div>

<a class="admin-primary-btn" href="pengaduan.php?buat=1" style="text-decoration:none">
+ Buat Pengaduan
</a>
</div>

<?php
$n = 0;
foreach ($data as $row):
    if ($n++ >= 5) break;
    $key = userStatusKey($row['status'] ?? '');
?>

<article class="admin-report">

<?php if (!empty($row['foto_bukti']) && is_file(__DIR__ . '/../../asset/uploads/bukti/' . $row['foto_bukti'])): ?>

<img class="admin-report-image"
src="../../asset/uploads/bukti/<?= htmlspecialchars($row['foto_bukti']) ?>"
alt="Bukti">

<?php else: ?>

<div class="admin-report-image"
style="display:flex;align-items:center;justify-content:center;font-size:22px;">
📷
</div>

<?php endif; ?>

<div>
<div class="admin-report-title">
<?= htmlspecialchars($row['judul_pengaduan']) ?>
</div>

<div class="admin-report-meta">
<?= htmlspecialchars($row['nama_kategori'] ?? '-') ?>
·
<?= htmlspecialchars($row['nama_ruangan'] ?? '-') ?>
</div>

<div class="admin-report-meta">
<?= htmlspecialchars(!empty($row['created_at']) ? date('d M Y • H:i', strtotime($row['created_at'])) : '-') ?>
</div>
</div>

<span class="admin-status <?= htmlspecialchars($key) ?>">
<?= htmlspecialchars(userStatusLabel($key)) ?>
</span>

</article>

<?php endforeach; ?>

<?php if ($n === 0): ?>
<div class="admin-empty">
Belum ada pengaduan. Buat laporan pertamamu.
</div>
<?php endif; ?>

<?php if ($stats['total'] > 5): ?>
<div style="margin-top:14px;text-align:right">
<a class="admin-secondary-btn" href="pengaduan.php" style="text-decoration:none">
Lihat Semua →
</a>
</div>
<?php endif; ?>

</section>

<section class="admin-panel">

<h2 class="admin-panel-title">Status Laporan</h2>

<div class="admin-category-wrap">

<div class="admin-pie"
style="--pie-gradient:<?= htmlspecialchars($pieGradient, ENT_QUOTES) ?>">
</div>

<div class="admin-legend">

<?php foreach ($warna as $key => $hex): ?>

<div class="admin-legend-item">
<span class="admin-legend-dot" style="background:<?= $hex ?>"></span>
<span><?= htmlspecialchars(userStatusLabel($key)) ?></span>
<strong><?= $stats[$key] ?></strong>
</div>

<?php endforeach; ?>

</div>
</div>

<div style="margin-top:18px;display:flex;gap:10px;flex-wrap:wrap">

<a class="admin-secondary-btn" href="pengaduan.php" style="text-decoration:none">
Kelola Pengaduan
</a>

<a class="admin-secondary-btn" href="riwayat.php" style="text-decoration:none">
Histori Aspirasi
</a>

</div>

</section>

</div>
</div>
</main>

<script src="../../asset/js/script.js?v=<?= @filemtime(__DIR__ . '/../../asset/js/script.js') ?: 1 ?>"></script>

</body>
</html>
