
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../model/kategori.php';

$model = new Kategori();
$result = $model->getAll();
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kategori</title>
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
        <a href="lokasi.php"><span>⌖</span>Data Lokasi</a>
        <a class="active" href="kategori.php"><span>▤</span>Data Kategori</a>

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
        <h1>Data Kategori</h1>
        <div class="admin-page-subtitle">
            Kelola kategori sarana yang digunakan pada pengaduan sekolah.
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
    <h2 class="admin-panel-title">Tambah Kategori</h2>

    <form action="../../controller/adminkategoricontroller.php"
          method="post"
          class="admin-form-grid">

        <input type="hidden" name="action" value="tambah">

        <div class="admin-field">
            <label>Nama Kategori</label>
            <input type="text"
                   name="nama_kategori"
                   maxlength="100"
                   placeholder="Contoh: Elektronik"
                   required>
        </div>

        <div class="full admin-form-actions">
            <button class="admin-primary-btn" type="submit">
                + Tambah Kategori
            </button>
        </div>
    </form>
</section>

<section class="admin-panel">
    <div class="admin-toolbar">
        <div>
            <h2 class="admin-panel-title">Daftar Kategori</h2>
            <div class="admin-page-subtitle">
                Total kategori: <strong><?= count($rows) ?></strong>
            </div>
        </div>

        <div class="admin-toolbar-search" style="max-width:320px">
            ⌕ <input id="searchKategori" type="search" placeholder="Cari kategori...">
        </div>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Jumlah Pengaduan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php $no = 0; ?>

            <?php foreach ($rows as $row): $no++; ?>
                <tr class="kategori-row"
                    data-search="<?= htmlspecialchars(strtolower($row['nama_kategori'] ?? ''), ENT_QUOTES) ?>">

                    <td><?= $no ?></td>
                    <td><strong><?= htmlspecialchars($row['nama_kategori']) ?></strong></td>
                    <td><?= (int)($row['jumlah_pengaduan'] ?? 0) ?> pengaduan</td>

                    <td>
                        <div class="admin-table-actions">
                            <button class="admin-secondary-btn"
                                    type="button"
                                    onclick="toggleKategori(<?= (int)$row['kategori_id'] ?>)">
                                Edit
                            </button>

                            <form action="../../controller/adminkategoricontroller.php"
                                  method="post"
                                  onsubmit="return confirm('Hapus kategori ini?')">

                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="kategori_id"
                                       value="<?= (int)$row['kategori_id'] ?>">

                                <button class="admin-danger-btn" type="submit">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <tr id="kategori-edit-<?= (int)$row['kategori_id'] ?>" style="display:none">
                    <td colspan="4">
                        <form action="../../controller/adminkategoricontroller.php"
                              method="post"
                              class="admin-form-grid"
                              style="padding:10px 0">

                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="kategori_id"
                                   value="<?= (int)$row['kategori_id'] ?>">

                            <div class="admin-field">
                                <label>Nama Kategori</label>
                                <input name="nama_kategori"
                                       maxlength="100"
                                       value="<?= htmlspecialchars($row['nama_kategori']) ?>"
                                       required>
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

            <?php if ($no === 0): ?>
                <tr>
                    <td colspan="4">
                        <div class="admin-empty">Belum ada kategori.</div>
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
function toggleKategori(id) {
    const e = document.getElementById('kategori-edit-' + id);
    if (!e) return;

    e.style.display = e.style.display === 'none' ? 'table-row' : 'none';
}

const searchKategori = document.getElementById('searchKategori');

if (searchKategori) {
    searchKategori.addEventListener('input', function () {
        const q = this.value.toLowerCase();

        document.querySelectorAll('.kategori-row').forEach(function (row) {
            row.style.display = row.dataset.search.includes(q) ? 'table-row' : 'none';
        });
    });
}
</script>

<script src="../../asset/js/script.js?v=<?= @filemtime(__DIR__ . '/../../asset/js/script.js') ?: 1 ?>"></script>
</body>
</html>
