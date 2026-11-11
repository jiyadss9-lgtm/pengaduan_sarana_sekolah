
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../model/lokasi.php';

$lokasiModel = new Lokasi();
$lokasi = $lokasiModel->getAll();
$msg = $_GET['msg'] ?? '';

$lokasiRows = $lokasi ? $lokasi->fetch_all(MYSQLI_ASSOC) : [];
$jumlahLokasi = count($lokasiRows);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Lokasi</title>
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
        <a href="dashboard.php"><span>⌂</span>Dashboard</a>
        <a href="pengaduan.php"><span>▣</span>Pengaduan</a>
        <a href="histori.php"><span>◷</span>Histori Aspirasi</a>
        <a href="laporan_aspirasi.php"><span>▥</span>Laporan Aspirasi</a>
        <a href="users.php"><span>♙</span>Data User</a>
        <a class="active" href="lokasi.php"><span>⌖</span>Data Lokasi</a>
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
        <p>MASTER DATA</p>
        <h1>Data Lokasi</h1>
        <div class="admin-page-subtitle">
            Kelola ruangan atau lokasi yang tersedia untuk pengaduan.
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

<?php if ($msg): ?>
    <div class="admin-alert">✓ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<section class="admin-panel admin-form-card">
    <h2 class="admin-panel-title">Tambah Lokasi</h2>

    <form action="../../controller/adminlokasicontroller.php" method="post" class="admin-form-grid">
        <input type="hidden" name="action" value="tambah">

        <div class="admin-field">
            <label>Nama Ruangan</label>
            <input name="nama_ruangan" placeholder="Contoh: Lab Komputer 1" required>
        </div>

        <div class="admin-field">
            <label>Gedung / Blok</label>
            <input name="gedung_blok" placeholder="Contoh: Gedung A">
        </div>

        <div class="full admin-form-actions">
            <button class="admin-primary-btn" type="submit">+ Tambah Lokasi</button>
        </div>
    </form>
</section>

<section class="admin-panel">
    <div class="admin-toolbar">
        <div>
            <h2 class="admin-panel-title">Daftar Lokasi</h2>
            <div class="admin-page-subtitle">
                Total lokasi terdaftar: <strong><?= $jumlahLokasi ?></strong>
            </div>
        </div>

        <div class="admin-toolbar-search" style="max-width:320px">
            ⌕ <input id="searchLokasi" type="search" placeholder="Cari lokasi...">
        </div>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Ruangan</th>
                    <th>Gedung / Blok</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php $n = 0; ?>

            <?php foreach ($lokasiRows as $l): $n++; ?>
                <tr class="lokasi-row"
                    data-search="<?= htmlspecialchars(strtolower(($l['nama_ruangan'] ?? '') . ' ' . ($l['gedung_blok'] ?? '')), ENT_QUOTES) ?>">

                    <td><?= $n ?></td>
                    <td><strong><?= htmlspecialchars($l['nama_ruangan']) ?></strong></td>
                    <td><?= htmlspecialchars($l['gedung_blok'] ?? '-') ?></td>

                    <td>
                        <div class="admin-table-actions">
                            <button class="admin-secondary-btn" type="button"
                                onclick="toggleLokasi(<?= (int)$l['lokasi_id'] ?>)">
                                Edit
                            </button>

                            <form action="../../controller/adminlokasicontroller.php"
                                  method="post"
                                  onsubmit="return confirm('Hapus lokasi ini?')">

                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="lokasi_id" value="<?= $l['lokasi_id'] ?>">

                                <button class="admin-danger-btn" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>

                <tr id="lokasi-edit-<?= (int)$l['lokasi_id'] ?>" style="display:none">
                    <td colspan="4">
                        <form action="../../controller/adminlokasicontroller.php"
                              method="post"
                              class="admin-form-grid"
                              style="padding:10px 0">

                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="lokasi_id" value="<?= $l['lokasi_id'] ?>">

                            <div class="admin-field">
                                <label>Nama Ruangan</label>
                                <input name="nama_ruangan"
                                       value="<?= htmlspecialchars($l['nama_ruangan']) ?>"
                                       required>
                            </div>

                            <div class="admin-field">
                                <label>Gedung / Blok</label>
                                <input name="gedung_blok"
                                       value="<?= htmlspecialchars($l['gedung_blok'] ?? '') ?>">
                            </div>

                            <div class="full admin-form-actions">
                                <button class="admin-primary-btn" type="submit">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($n === 0): ?>
                <tr>
                    <td colspan="4">
                        <div class="admin-empty">Belum ada lokasi.</div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

</div>
</main>

<script>
function toggleLokasi(id) {
    const e = document.getElementById('lokasi-edit-' + id);
    e.style.display = e.style.display === 'none' ? 'table-row' : 'none';
}

document.getElementById('searchLokasi').addEventListener('input', function () {
    const q = this.value.toLowerCase();

    document.querySelectorAll('.lokasi-row').forEach(function (row) {
        row.style.display = row.dataset.search.includes(q) ? 'table-row' : 'none';
    });
});
</script>

<script src="../../asset/js/script.js?v=<?= @filemtime(__DIR__ . '/../../asset/js/script.js') ?: 1 ?>"></script>
</body>
</html>
