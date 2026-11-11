
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../model/pengaduan.php';
require_once __DIR__ . '/../../model/feedback.php';
require_once __DIR__ . '/../../model/penanganan.php';

$model = new Pengaduan();
$feedbackModel = new Feedback();
$penangananModel = new Penanganan();

$filters = [
    'kategori_id' => $_GET['kategori_id'] ?? '',
    'lokasi_id' => $_GET['lokasi_id'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
    'tanggal_mulai' => $_GET['tanggal_mulai'] ?? '',
    'tanggal_selesai' => $_GET['tanggal_selesai'] ?? ''
];
$data = $model->getAspirasiReport($filters);
$kategoriOptions = $model->getKategori();
$lokasiOptions = $model->getLokasi();
$userOptions = $model->getUsersForFilter();
$msg = $_GET['msg'] ?? '';
$filter = $_GET['status'] ?? '';
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Kelola Pengaduan</title>
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
        <a class="active" href="pengaduan.php"><span>▣</span>Pengaduan</a>
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
        <p>KELOLA DATA</p>
        <h1>Pengaduan</h1>
        <div class="admin-page-subtitle">
            Verifikasi, ubah status, tangani, dan hapus pengaduan.
        </div>
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
    <div class="admin-alert">✓ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="admin-toolbar">
    <div class="admin-toolbar-search">
        ⌕
        <input id="searchPengaduan" type="search"
               placeholder="Cari judul, user, atau lokasi...">
    </div>

    <form method="get" class="admin-filter-form" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <select class="admin-secondary-btn" name="status">
            <option value="">Semua status</option>
            <?php foreach (['baru'=>'Baru','pending'=>'Pending','diproses'=>'Diproses','selesai'=>'Selesai','ditolak'=>'Ditolak'] as $v=>$label): ?>
                <option value="<?= $v ?>" <?= $filter === $v ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <select class="admin-secondary-btn" name="kategori_id"><option value="">Semua kategori</option>
            <?php if ($kategoriOptions): while($opt=$kategoriOptions->fetch_assoc()): ?>
                <option value="<?= (int)$opt['kategori_id'] ?>" <?= (string)$filters['kategori_id']===(string)$opt['kategori_id']?'selected':'' ?>><?= htmlspecialchars($opt['nama_kategori']) ?></option>
            <?php endwhile; endif; ?>
        </select>
        <select class="admin-secondary-btn" name="lokasi_id"><option value="">Semua lokasi</option>
            <?php if ($lokasiOptions): while($opt=$lokasiOptions->fetch_assoc()): ?>
                <option value="<?= (int)$opt['lokasi_id'] ?>" <?= (string)$filters['lokasi_id']===(string)$opt['lokasi_id']?'selected':'' ?>><?= htmlspecialchars($opt['nama_ruangan']) ?></option>
            <?php endwhile; endif; ?>
        </select>
        <select class="admin-secondary-btn" name="user_id"><option value="">Semua siswa/user</option>
            <?php if ($userOptions): while($opt=$userOptions->fetch_assoc()): ?>
                <option value="<?= (int)$opt['user_id'] ?>" <?= (string)$filters['user_id']===(string)$opt['user_id']?'selected':'' ?>><?= htmlspecialchars($opt['nama_lengkap'].' ('.$opt['nomor_induk'].')') ?></option>
            <?php endwhile; endif; ?>
        </select>
        <input class="admin-secondary-btn" type="date" name="tanggal_mulai" aria-label="Dari tanggal" value="<?= htmlspecialchars($filters['tanggal_mulai']) ?>">
        <input class="admin-secondary-btn" type="date" name="tanggal_selesai" aria-label="Sampai tanggal" value="<?= htmlspecialchars($filters['tanggal_selesai']) ?>">
        <button class="admin-primary-btn" type="submit">Filter</button>
        <a class="admin-secondary-btn" href="pengaduan.php">Reset</a>
    </form>
</div>

<div class="admin-table-wrap">
<table class="admin-table">
<thead>
<tr>
    <th>Pengaduan</th>
    <th>User</th>
    <th>Lokasi</th>
    <th>Status</th>
    <th>Aksi</th>
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

        $search = strtolower(
            ($row['judul_pengaduan'] ?? '') . ' ' .
            ($row['nama_lengkap'] ?? '') . ' ' .
            ($row['nama_ruangan'] ?? '')
        );

        $ph = $penangananModel->getByPengaduan((int)$row['pengaduan_id']);
        $fb = $feedbackModel->getByPengaduan((int)$row['pengaduan_id']);
?>

<tr class="pengaduan-row"
    data-search="<?= htmlspecialchars($search, ENT_QUOTES) ?>">

    <td>
        <strong><?= htmlspecialchars($row['judul_pengaduan']) ?></strong>
        <div class="admin-report-meta">
            <?= htmlspecialchars($row['created_at'] ?? '-') ?>
        </div>
    </td>

    <td><?= htmlspecialchars($row['nama_lengkap'] ?? '-') ?></td>
    <td><?= htmlspecialchars($row['nama_ruangan'] ?? '-') ?></td>

    <td>
        <span class="admin-status <?= htmlspecialchars($status) ?>">
            <?= htmlspecialchars($status) ?>
        </span>
    </td>

    <td>
        <div class="admin-table-actions">

            <?php if ($status === 'baru'): ?>
                <form action="../../controller/adminpengaduancontroller.php" method="post">
                    <input type="hidden" name="action" value="verifikasi">
                    <input type="hidden" name="pengaduan_id"
                           value="<?= $row['pengaduan_id'] ?>">
                    <button class="admin-primary-btn" type="submit">Verifikasi</button>
                </form>
            <?php endif; ?>

            <form action="../../controller/adminpengaduancontroller.php" method="post">
                <input type="hidden" name="action" value="status">
                <input type="hidden" name="pengaduan_id"
                       value="<?= $row['pengaduan_id'] ?>">

                <select class="admin-secondary-btn" name="status">
                    <option value="baru" <?= $status === 'baru' ? 'selected' : '' ?>>Baru</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="diproses" <?= $status === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="ditolak" <?= $status === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>

                <button class="admin-secondary-btn" type="submit">Simpan</button>
            </form>

            <button class="admin-secondary-btn" type="button"
                    onclick="toggleDetail(<?= (int)$row['pengaduan_id'] ?>)">
                Detail
            </button>

            <form action="../../controller/adminpengaduancontroller.php"
                  method="post"
                  onsubmit="return confirm('Hapus pengaduan ini?')">
                <input type="hidden" name="action" value="hapus">
                <input type="hidden" name="pengaduan_id"
                       value="<?= $row['pengaduan_id'] ?>">
                <button class="admin-danger-btn" type="submit">Hapus</button>
            </form>

        </div>
    </td>
</tr>

<tr id="detail-<?= (int)$row['pengaduan_id'] ?>" style="display:none">
    <td colspan="5">
        <div class="admin-panel"
             style="margin:8px 0;background:var(--surface-2)!important">

            <div class="admin-form-grid">
                <div>
                    <div class="admin-report-meta">DESKRIPSI</div>
                    <p><?= nl2br(htmlspecialchars($row['deskripsi'] ?? '-')) ?></p>
                </div>

                <div>
                    <div class="admin-report-meta">TOTAL BIAYA YANG DITETAPKAN ADMIN</div>
                    <p><strong>Rp <?= number_format((float)($ph['biaya'] ?? 0), 0, ',', '.') ?></strong></p>
                </div>

                <div>
                    <div class="admin-report-meta">KATEGORI / URGENSI</div>
                    <p>
                        <?= htmlspecialchars($row['nama_kategori'] ?? '-') ?>
                        ·
                        <?= htmlspecialchars($row['urgensi'] ?? '-') ?>
                    </p>
                    <form action="../../controller/adminpengaduancontroller.php" method="post" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:8px">
                        <input type="hidden" name="action" value="urgensi">
                        <input type="hidden" name="pengaduan_id" value="<?= (int)$row['pengaduan_id'] ?>">
                        <select class="admin-secondary-btn" name="urgensi">
                            <?php foreach (['rendah'=>'Rendah','sedang'=>'Sedang','tinggi'=>'Tinggi'] as $uv=>$ul): ?>
                                <option value="<?= $uv ?>" <?= ($row['urgensi'] ?? 'sedang')===$uv?'selected':'' ?>><?= $ul ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="admin-secondary-btn" type="submit">Naikkan urgensi</button>
                    </form>
                </div>

                <div class="full">
                    <div class="admin-report-meta">PENANGANAN</div>

                    <form action="../../controller/adminpengaduancontroller.php"
                          method="post"
                          enctype="multipart/form-data"
                          class="admin-form-grid"
                          style="margin-top:8px">

                        <input type="hidden" name="action" value="penanganan">
                        <input type="hidden" name="pengaduan_id"
                               value="<?= $row['pengaduan_id'] ?>">

                        <div class="admin-field">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai"
                                   value="<?= !empty($ph['tanggal_mulai']) ? substr($ph['tanggal_mulai'], 0, 10) : date('Y-m-d') ?>"
                                   required>
                        </div>

                        <div class="admin-field">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai"
                                   value="<?= !empty($ph['tanggal_selesai']) && $ph['tanggal_selesai'] !== '0000-00-00 00:00:00' ? substr($ph['tanggal_selesai'], 0, 10) : '' ?>">
                        </div>

                        <div class="admin-field">
                            <label>Biaya</label>
                            <input type="number" name="biaya" min="0" step="0.01"
                                   value="<?= htmlspecialchars($ph['biaya'] ?? 0) ?>">
                        </div>

                        <div class="admin-field">
                            <label>Status Setelah</label>
                            <select name="status_setelah">
                                <option value="diproses">Diproses</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>

                        <div class="admin-field full">
                            <label>Catatan Perbaikan</label>
                            <textarea name="catatan_perbaikan"
                                      placeholder="Tuliskan tindakan/perbaikan..."><?= htmlspecialchars($ph['catatan_perbaikan'] ?? '') ?></textarea>
                        </div>

                        <div class="admin-field full">
                            <label>Foto Selesai</label>
                            <input type="file" name="foto_selesai"
                                   accept=".jpg,.jpeg,.png,.webp">
                        </div>

                        <div class="full admin-form-actions">
                            <button class="admin-primary-btn" type="submit">
                                Simpan Penanganan
                            </button>
                        </div>
                    </form>

                    <?php if ($ph): ?>
                        <div class="admin-report-meta" style="margin-top:10px">
                            Penanganan terakhir:
                            <?= htmlspecialchars($ph['catatan_perbaikan'] ?? '-') ?>

                            <?php if (!empty($ph['foto_selesai'])): ?>
                                ·
                                <a href="../../asset/uploads/penanganan/<?= htmlspecialchars($ph['foto_selesai']) ?>"
                                   target="_blank">Lihat foto</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($fb): ?>
                        <div class="admin-report-meta" style="margin-top:7px">
                            Feedback: <?= (int)$fb['rating'] ?>/5 —
                            <?= htmlspecialchars($fb['komentar']) ?>
                        </div>
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
    <td colspan="5">
        <div class="admin-empty">Tidak ada pengaduan yang sesuai.</div>
    </td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>

</div>
</main>

<script>
function toggleDetail(id) {
    const e = document.getElementById('detail-' + id);
    e.style.display = e.style.display === 'none' ? 'table-row' : 'none';
}

document.getElementById('searchPengaduan').addEventListener('input', function () {
    const q = this.value.toLowerCase();

    document.querySelectorAll('.pengaduan-row').forEach(function (row) {
        row.style.display = row.dataset.search.includes(q) ? 'table-row' : 'none';
    });
});
</script>

<script src="../../asset/js/script.js"></script>

</body>
</html>
