
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . '/../../model/pengaduan.php';

$model = new Pengaduan();
$stats = $model->getStats();
$kategori = $model->getCategoryStats();
$pengaduan = $model->getAll();

$kategoriData = [];

if ($kategori) {
    while ($kat = $kategori->fetch_assoc()) {
        $kategoriData[] = [
            'nama' => $kat['nama_kategori'],
            'jumlah' => (int)$kat['jumlah']
        ];
    }
}

$totalKategori = array_sum(array_column($kategoriData, 'jumlah'));

$pieColors = ['#7c3aed','#a78bfa','#c4b5fd','#8b5cf6','#6d28d9','#ddd6fe'];
$segments = [];
$start = 0;

foreach ($kategoriData as $i => $kat) {
    $percent = $totalKategori > 0
        ? ($kat['jumlah'] / $totalKategori) * 100
        : 0;

    $end = $start + $percent;

    $segments[] = $pieColors[$i % count($pieColors)] . ' ' . $start . '% ' . $end . '%';
    $start = $end;
}

$pieGradient = $segments
    ? 'conic-gradient(' . implode(',', $segments) . ')'
    : 'conic-gradient(#ddd 0 100%)';

$msg = $_GET['msg'] ?? '';
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../../asset/css/style.css?v=<?= @filemtime(__DIR__ . '/../../asset/css/style.css') ?: 1 ?>">
</head>

<body class="admin-dashboard">

<aside class="admin-sidebar">
    <div class="admin-brand">
        <div class="admin-avatar">
            <img class="app-logo-img" src="../../asset/img/logo.png" alt="Logo">
        </div>
        <strong>ADMIN SEKOLAH</strong>
        <span>PANEL ADMINISTRATOR</span>
    </div>

    <nav class="admin-nav">
        <div class="admin-nav-title">MENU UTAMA</div>

        <a class="active" href="dashboard.php"><span>⌂</span>Dashboard</a>
        <a href="pengaduan.php"><span>▣</span>Pengaduan</a>
        <a href="histori.php"><span>◷</span>Histori Aspirasi</a>
        <a href="laporan_aspirasi.php"><span>▥</span>Laporan Aspirasi</a>
        <a href="users.php"><span>♙</span>Data User</a>
        <a href="lokasi.php"><span>⌖</span>Data Lokasi</a>
        <a href="kategori.php"><span>▤</span>Data Kategori</a>

        <div class="admin-nav-title">AKUN</div>

        <a href="../../controller/profilcontroller.php"><span>◉</span>Profil</a>
        <a class="admin-logout" href="../../logout.php"><span>↪</span>Logout</a>
    </nav>
</aside>

<main class="admin-main">
<div class="admin-page">

<header class="admin-header">
    <div>
        <p>SISTEM PENGADUAN SARANA SEKOLAH</p>
        <h1>Dashboard</h1>
        <div class="admin-page-subtitle">Ringkasan aktivitas pengaduan sekolah.</div>
    </div>

    <div class="admin-header-actions">
        <button type="button" id="themeToggle" class="admin-theme-btn"
                aria-label="Ganti tema" title="Ganti tema">◐</button>

        <div class="admin-header-right">
            <strong><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Administrator') ?></strong>
            <span>ADMINISTRATOR</span>
        </div>
    </div>
</header>

<?php if ($msg): ?>
    <div class="admin-alert">
        ✓ <?= htmlspecialchars(
            $msg === 'updated'
                ? 'Status pengaduan diperbarui.'
                : ($msg === 'deleted' ? 'Pengaduan dihapus.' : 'Proses berhasil.')
        ) ?>
    </div>
<?php endif; ?>

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

    <a class="admin-kpi-link" href="pengaduan.php?status=selesai">
        <div class="admin-stat">
            <div class="admin-stat-icon">✓</div>
            <div>
                <div class="admin-stat-number"><?= $stats['selesai'] ?></div>
                <div class="admin-stat-label">Selesai</div>
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

    <a class="admin-kpi-link" href="pengaduan.php?status=baru">
        <div class="admin-stat">
            <div class="admin-stat-icon">!</div>
            <div>
                <div class="admin-stat-number"><?= $stats['menunggu'] ?></div>
                <div class="admin-stat-label">Menunggu</div>
            </div>
        </div>
    </a>

</section>

<div class="admin-content-grid">

    <section class="admin-panel">
        <div class="admin-toolbar">
            <div>
                <h2 class="admin-panel-title">Pengaduan Terbaru</h2>
                <div class="admin-page-subtitle">
                    Pantauan singkat. Kelola detail di menu Pengaduan.
                </div>
            </div>

            <a class="admin-secondary-btn" href="pengaduan.php">Lihat Semua →</a>
        </div>

        <?php
        $n = 0;

        if ($pengaduan):
            while ($row = $pengaduan->fetch_assoc()):
                if ($n++ >= 5) break;

                $status = strtolower($row['status'] ?? 'baru');
        ?>

        <article class="admin-report">

            <?php if (!empty($row['foto_bukti'])): ?>
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
                    <?= htmlspecialchars($row['nama_lengkap'] ?? '-') ?>
                    ·
                    <?= htmlspecialchars($row['nama_ruangan'] ?? '-') ?>
                </div>

                <div class="admin-report-meta">
                    <?= htmlspecialchars($row['created_at'] ?? '-') ?>
                </div>
            </div>

            <span class="admin-status <?= htmlspecialchars($status) ?>">
                <?= htmlspecialchars($status) ?>
            </span>

        </article>

        <?php
            endwhile;
        endif;
        ?>

        <?php if ($n === 0): ?>
            <div class="admin-empty">Belum ada pengaduan.</div>
        <?php endif; ?>

    </section>

    <section class="admin-panel">
        <h2 class="admin-panel-title">Kategori Pengaduan</h2>

        <div class="admin-category-wrap">
            <div class="admin-pie"
                 style="--pie-gradient:<?= htmlspecialchars($pieGradient, ENT_QUOTES) ?>">
            </div>

            <div class="admin-legend">

                <?php foreach ($kategoriData as $i => $kat): ?>
                    <div class="admin-legend-item">
                        <span class="admin-legend-dot"
                              style="background:<?= $pieColors[$i % count($pieColors)] ?>">
                        </span>

                        <span><?= htmlspecialchars($kat['nama']) ?></span>

                        <strong>
                            <?= $totalKategori > 0
                                ? round(($kat['jumlah'] / $totalKategori) * 100)
                                : 0 ?>%
                        </strong>
                    </div>
                <?php endforeach; ?>

                <?php if (!$kategoriData): ?>
                    <div class="admin-empty">Belum ada data kategori.</div>
                <?php endif; ?>

            </div>
        </div>
    </section>

</div>
</div>
</main>

<script src="../../asset/js/script.js?v=<?= @filemtime(__DIR__ . '/../../asset/js/script.js') ?: 1 ?>"></script>

</body>
</html>
