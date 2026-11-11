
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../model/pengaduan.php';
require_once __DIR__ . '/../../model/penanganan.php';
require_once __DIR__ . '/_layout.php';

$model = new Pengaduan();
$penangananModel = new Penanganan();

$result = $model->getUser((int)$_SESSION['user_id']);
$data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$stats = [
    'total' => count($data),
    'selesai' => 0,
    'ditolak' => 0,
    'diproses' => 0,
    'menunggu' => 0
];

foreach ($data as $row) {
    $key = userStatusKey($row['status'] ?? '');

    if ($key === 'selesai') {
        $stats['selesai']++;
    } elseif ($key === 'ditolak') {
        $stats['ditolak']++;
    } elseif ($key === 'diproses') {
        $stats['diproses']++;
    } else {
        $stats['menunggu']++;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Histori Aspirasi | Pengaduan Sarana Sekolah</title>
<link rel="stylesheet" href="../../asset/css/style.css?v=<?= @filemtime(__DIR__ . '/../../asset/css/style.css') ?: 1 ?>">
</head>

<body class="admin-dashboard">

<?php renderUserSidebar('riwayat'); ?>

<main class="admin-main">
<div class="admin-page">

<?php
renderUserHeader(
    'ARSIP ASPIRASI',
    'Histori Aspirasi',
    'Lihat seluruh aspirasi yang pernah kamu kirim beserta perkembangan penanganannya.'
);
?>

<section class="admin-stats">

<div class="admin-stat">
    <div class="admin-stat-icon">▣</div>
    <div>
        <div class="admin-stat-number"><?= $stats['total'] ?></div>
        <div class="admin-stat-label">Total Aspirasi</div>
    </div>
</div>

<div class="admin-stat">
    <div class="admin-stat-icon">◷</div>
    <div>
        <div class="admin-stat-number"><?= $stats['menunggu'] ?></div>
        <div class="admin-stat-label">Menunggu</div>
    </div>
</div>

<div class="admin-stat">
    <div class="admin-stat-icon">⚙</div>
    <div>
        <div class="admin-stat-number"><?= $stats['diproses'] ?></div>
        <div class="admin-stat-label">Diproses</div>
    </div>
</div>

<div class="admin-stat">
    <div class="admin-stat-icon">✓</div>
    <div>
        <div class="admin-stat-number"><?= $stats['selesai'] ?></div>
        <div class="admin-stat-label">Selesai</div>
    </div>
</div>

</section>

<section class="admin-panel">

<div class="admin-toolbar">
    <div>
        <h2 class="admin-panel-title">Daftar Histori Aspirasi Saya</h2>
        <div class="admin-page-subtitle">
            Semua aspirasi yang pernah dikirim akan tercatat di sini.
        </div>
    </div>

    <a class="admin-secondary-btn"
       href="pengaduan.php"
       style="text-decoration:none">
        ← Buat Aspirasi
    </a>
</div>

<div class="admin-toolbar">

    <div class="admin-toolbar-search">
        ⌕
        <input id="searchRiwayat"
               type="search"
               placeholder="Cari judul, kategori, lokasi...">
    </div>

    <select id="filterRiwayat" class="admin-secondary-btn">
        <option value="semua">Semua status</option>
        <option value="baru">Menunggu</option>
        <option value="diproses">Diproses</option>
        <option value="selesai">Selesai</option>
        <option value="ditolak">Ditolak</option>
    </select>

</div>

<div class="admin-table-wrap">
<table class="admin-table">

<thead>
<tr>
    <th>Aspirasi</th>
    <th>Kategori / Lokasi</th>
    <th>Status</th>
    <th>Tanggal</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>

<?php foreach ($data as $row):
    $id = (int)$row['pengaduan_id'];
    $key = userStatusKey($row['status'] ?? '');
    $ph = $penangananModel->getByPengaduan($id);

    $tanggal = !empty($row['created_at'])
        ? date('d M Y', strtotime($row['created_at']))
        : '-';

    $cari = strtolower(
        ($row['judul_pengaduan'] ?? '') . ' ' .
        ($row['nama_kategori'] ?? '') . ' ' .
        ($row['nama_ruangan'] ?? '') . ' ' .
        ($row['deskripsi'] ?? '')
    );

    $adaFoto = !empty($row['foto_bukti']) &&
        is_file(__DIR__ . '/../../asset/uploads/bukti/' . $row['foto_bukti']);
?>

<tr class="riwayat-row"
    data-id="<?= $id ?>"
    data-status="<?= htmlspecialchars($key) ?>"
    data-search="<?= htmlspecialchars($cari, ENT_QUOTES) ?>">

    <td>
        <div class="up-cell">
            <?php if ($adaFoto): ?>
                <img src="../../asset/uploads/bukti/<?= htmlspecialchars($row['foto_bukti']) ?>"
                     alt="Bukti aspirasi">
            <?php else: ?>
                <span class="up-ph">📷</span>
            <?php endif; ?>

            <div>
                <strong><?= htmlspecialchars($row['judul_pengaduan']) ?></strong>
                <div class="admin-report-meta">
                    Dikirim <?= htmlspecialchars($tanggal) ?>
                </div>
            </div>
        </div>
    </td>

    <td>
        <?= htmlspecialchars($row['nama_kategori'] ?? '-') ?>
        <div class="admin-report-meta">
            <?= htmlspecialchars($row['nama_ruangan'] ?? '-') ?>
        </div>
    </td>

    <td>
        <span class="admin-status <?= htmlspecialchars($key) ?>">
            <?= htmlspecialchars(userStatusLabel($key)) ?>
        </span>
    </td>

    <td><?= htmlspecialchars($tanggal) ?></td>

    <td>
        <button type="button"
                class="admin-secondary-btn"
                onclick="toggleDetail(<?= $id ?>)">
            Detail
        </button>
    </td>

</tr>

<tr id="detail-<?= $id ?>" class="detail-row" style="display:none">
<td colspan="5">

<div class="admin-panel"
     style="margin:8px 0;background:var(--surface-2)!important">

<div class="admin-form-grid">

    <div class="full">
        <div class="admin-report-meta">DESKRIPSI ASPIRASI</div>
        <p><?= nl2br(htmlspecialchars($row['deskripsi'] ?? '-')) ?></p>
    </div>

    <div class="full">
        <div class="admin-report-meta">STATUS PENANGANAN</div>
        <strong><?= htmlspecialchars(userStatusLabel($key)) ?></strong>
    </div>

    <div class="full">
        <div class="admin-report-meta">TOTAL BIAYA PENANGANAN DARI ADMIN</div>
        <strong>Rp <?= number_format((float)($ph['biaya'] ?? 0), 0, ',', '.') ?></strong>
    </div>

    <div class="full">
        <div class="admin-report-meta">
            UMPAN BALIK / CATATAN PERBAIKAN DARI ADMIN
        </div>

        <?php if ($ph && !empty($ph['catatan_perbaikan'])): ?>
            <p><?= nl2br(htmlspecialchars($ph['catatan_perbaikan'])) ?></p>
        <?php else: ?>
            <p class="up-muted">
                Admin belum memberikan catatan perbaikan.
            </p>
        <?php endif; ?>

        <?php if ($ph && !empty($ph['tanggal_mulai'])): ?>
            <div class="admin-report-meta">
                Tanggal mulai: <?= htmlspecialchars($ph['tanggal_mulai']) ?>
            </div>
        <?php endif; ?>

        <?php if ($ph && !empty($ph['tanggal_selesai']) &&
            strpos($ph['tanggal_selesai'], '0000') !== 0): ?>
            <div class="admin-report-meta">
                Tanggal selesai: <?= htmlspecialchars($ph['tanggal_selesai']) ?>
            </div>
        <?php endif; ?>

        <?php if ($ph && !empty($ph['foto_selesai'])): ?>
            <p>
                <a href="../../asset/uploads/penanganan/<?= htmlspecialchars($ph['foto_selesai']) ?>"
                   target="_blank"
                   rel="noopener">
                    Lihat foto hasil perbaikan
                </a>
            </p>
        <?php endif; ?>

    </div>

</div>
</div>
</td>
</tr>

<?php endforeach; ?>

<tr id="emptyRow" <?= $data ? 'style="display:none"' : '' ?>>
    <td colspan="5">
        <div class="admin-empty">Belum ada histori aspirasi.</div>
    </td>
</tr>

</tbody>
</table>
</div>

</section>
</div>
</main>

<script>
function toggleDetail(id) {
    var el = document.getElementById('detail-' + id);

    el.style.display =
        el.style.display === 'none' ? 'table-row' : 'none';
}

(function () {
    var search = document.getElementById('searchRiwayat');
    var filter = document.getElementById('filterRiwayat');
    var rows = Array.prototype.slice.call(
        document.querySelectorAll('.riwayat-row')
    );
    var empty = document.getElementById('emptyRow');

    function apply() {
        var q = search.value.trim().toLowerCase();
        var f = filter.value;
        var shown = 0;

        rows.forEach(function (row) {
            var ok =
                (f === 'semua' || row.dataset.status === f) &&
                row.dataset.search.indexOf(q) !== -1;

            row.style.display = ok ? '' : 'none';

            if (!ok) {
                document.getElementById(
                    'detail-' + row.dataset.id
                ).style.display = 'none';
            }

            if (ok) shown++;
        });

        empty.style.display = shown === 0 ? '' : 'none';
    }

    search.addEventListener('input', apply);
    filter.addEventListener('change', apply);
    apply();
})();
</script>

<script src="../../asset/js/script.js?v=<?= @filemtime(__DIR__ . '/../../asset/js/script.js') ?: 1 ?>"></script>

</body>
</html>
