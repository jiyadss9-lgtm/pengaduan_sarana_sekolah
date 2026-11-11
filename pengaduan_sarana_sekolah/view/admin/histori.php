
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../model/pengaduan.php';
require_once __DIR__ . '/../../model/penanganan.php';

$model = new Pengaduan();
$penangananModel = new Penanganan();

$data = $model->getAll();

$filter = strtolower(trim($_GET['status'] ?? ''));
$allowed = ['baru', 'pending', 'diproses', 'selesai', 'ditolak'];

if (!in_array($filter, $allowed, true)) {
    $filter = '';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori Aspirasi Admin</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
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

        <a href="dashboard.php"><span>⌂</span>Dashboard</a>
        <a href="pengaduan.php"><span>▣</span>Pengaduan</a>
        <a class="active" href="histori.php"><span>◷</span>Histori Aspirasi</a>
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
        <p>ARSIP PENGADUAN SEKOLAH</p>
        <h1>Histori Aspirasi</h1>
        <div class="admin-page-subtitle">
            Riwayat seluruh aspirasi pengguna beserta status dan catatan perbaikannya.
        </div>
    </div>

    <div class="admin-header-actions">
        <button type="button" id="themeToggle" class="admin-theme-btn">◐</button>
        <div class="admin-header-right">
            <strong><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Administrator') ?></strong>
            <span>ADMINISTRATOR</span>
        </div>
    </div>
</header>

<div class="admin-toolbar">
    <div class="admin-toolbar-search">
        ⌕ <input id="searchHistori" type="search" placeholder="Cari judul, pengguna, atau lokasi...">
    </div>

    <form method="get">
        <select class="admin-secondary-btn" name="status" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <?php
            $statuses = [
                'baru' => 'Baru',
                'pending' => 'Pending',
                'diproses' => 'Diproses',
                'selesai' => 'Selesai',
                'ditolak' => 'Ditolak'
            ];

            foreach ($statuses as $value => $label):
            ?>
                <option value="<?= $value ?>" <?= $filter === $value ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="admin-table-wrap">
<table class="admin-table">
<thead>
<tr>
    <th>Aspirasi</th>
    <th>Pengguna</th>
    <th>Lokasi</th>
    <th>Status</th>
    <th>Catatan Perbaikan Admin</th>
    <th>Detail</th>
</tr>
</thead>

<tbody>

<?php
$found = 0;

if ($data):
    while ($row = $data->fetch_assoc()):

        $status = strtolower($row['status'] ?? 'baru');

        if ($filter && $filter !== $status) {
            continue;
        }

        $found++;

        $id = (int)$row['pengaduan_id'];
        $ph = $penangananModel->getByPengaduan($id);

        $search = strtolower(
            ($row['judul_pengaduan'] ?? '') . ' ' .
            ($row['nama_lengkap'] ?? '') . ' ' .
            ($row['nama_ruangan'] ?? '')
        );
?>

<tr class="histori-row" data-search="<?= htmlspecialchars($search, ENT_QUOTES) ?>">

    <td>
        <strong><?= htmlspecialchars($row['judul_pengaduan'] ?? '-') ?></strong>
        <div class="admin-report-meta">
            <?= htmlspecialchars($row['created_at'] ?? '-') ?>
        </div>
    </td>

    <td><?= htmlspecialchars($row['nama_lengkap'] ?? '-') ?></td>

    <td><?= htmlspecialchars($row['nama_ruangan'] ?? '-') ?></td>

    <td>
        <span class="admin-status <?= htmlspecialchars($status) ?>">
            <?= htmlspecialchars(ucfirst($status)) ?>
        </span>
    </td>

    <td>
        <?= htmlspecialchars($ph['catatan_perbaikan'] ?? 'Belum ada catatan') ?>
    </td>

    <td>
        <button class="admin-secondary-btn" type="button"
                onclick="toggleHistori(<?= $id ?>)">
            Lihat
        </button>
    </td>

</tr>

<tr id="histori-detail-<?= $id ?>" style="display:none">
<td colspan="6">

    <div class="admin-panel" style="margin:8px 0">

        <div class="admin-form-grid">

            <div>
                <div class="admin-report-meta">DESKRIPSI ASPIRASI</div>
                <p><?= nl2br(htmlspecialchars($row['deskripsi'] ?? '-')) ?></p>
            </div>

            <div>
                <div class="admin-report-meta">KATEGORI</div>
                <p><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></p>
            </div>

            <div>
                <div class="admin-report-meta">URGENSI</div>
                <p><?= htmlspecialchars($row['urgensi'] ?? '-') ?></p>
            </div>

            <div>
                <div class="admin-report-meta">TANGGAL MULAI</div>
                <p><?= htmlspecialchars($ph['tanggal_mulai'] ?? '-') ?></p>
            </div>

            <div>
                <div class="admin-report-meta">TANGGAL SELESAI</div>
                <p><?= htmlspecialchars($ph['tanggal_selesai'] ?? '-') ?></p>
            </div>

            <div>
                <div class="admin-report-meta">BIAYA</div>
                <p>
                    <?= isset($ph['biaya'])
                        ? 'Rp ' . number_format((float)$ph['biaya'], 0, ',', '.')
                        : '-' ?>
                </p>
            </div>

            <div class="full">
                <div class="admin-report-meta">CATATAN PERBAIKAN ADMIN</div>

                <p>
                    <?= nl2br(htmlspecialchars(
                        $ph['catatan_perbaikan'] ?? 'Belum ada catatan perbaikan.'
                    )) ?>
                </p>

                <?php if (!empty($row['foto_bukti'])): ?>
                    <p>
                        <a href="../../asset/uploads/bukti/<?= rawurlencode($row['foto_bukti']) ?>"
                           target="_blank">
                            Lihat Foto Bukti
                        </a>
                    </p>
                <?php endif; ?>

                <?php if (!empty($ph['foto_selesai'])): ?>
                    <p>
                        <a href="../../asset/uploads/penanganan/<?= rawurlencode($ph['foto_selesai']) ?>"
                           target="_blank">
                            Lihat Foto Hasil Perbaikan
                        </a>
                    </p>
                <?php endif; ?>

            </div>

        </div>
    </div>

</td>
</tr>

<?php
    endwhile;
endif;
?>

<?php if ($found === 0): ?>
<tr>
    <td colspan="6">
        <div class="admin-empty">
            Belum ada histori aspirasi yang sesuai.
        </div>
    </td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>

</div>
</main>

<script>
function toggleHistori(id) {
    const element = document.getElementById('histori-detail-' + id);

    element.style.display =
        element.style.display === 'none' ? 'table-row' : 'none';
}

document.getElementById('searchHistori').addEventListener('input', function () {
    const query = this.value.toLowerCase();

    document.querySelectorAll('.histori-row').forEach(row => {
        const show = row.dataset.search.includes(query);

        row.style.display = show ? 'table-row' : 'none';

        const detail = row.nextElementSibling;

        if (detail && detail.id.startsWith('histori-detail-') && !show) {
            detail.style.display = 'none';
        }
    });
});
</script>

<script src="../../asset/js/script.js"></script>

</body>
</html>
