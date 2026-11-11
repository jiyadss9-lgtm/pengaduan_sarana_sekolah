
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../model/user.php';

$model = new User();
$data = $model->getAll();
$msg = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User</title>
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
        <a href="histori.php"><span>◷</span>Histori Aspirasi</a>
        <a href="laporan_aspirasi.php"><span>▥</span>Laporan Aspirasi</a>
        <a class="active" href="users.php"><span>♙</span>Data User</a>
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
        <h1>Data User</h1>
        <div class="admin-page-subtitle">Kelola pengguna sistem pengaduan.</div>
    </div>
</header>

<?php if ($msg): ?>
    <div class="admin-alert"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<section class="admin-panel">
    <div class="admin-toolbar">
        <h2 class="admin-panel-title">Daftar User</h2>
        <a href="tambah_user.php" class="admin-primary-btn">+ Tambah User</a>
    </div>

    <div class="admin-toolbar-search">
        <input type="search" id="searchUser" placeholder="Cari nama atau email...">
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php
            $no = 1;
            if ($data):
                while ($row = $data->fetch_assoc()):
            ?>
                <tr class="user-row">
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_lengkap'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['nomor_induk'] ?? '-') ?></td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="edit_user.php?id=<?= (int)$row['user_id'] ?>"
                               class="admin-secondary-btn">Edit</a>
                            <form action="../../controller/adminusercontroller.php" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>">
                                <button type="submit" class="admin-danger-btn">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php
                endwhile;
            endif;
            ?>
            </tbody>
        </table>
    </div>
</section>

</div>
</main>

<script>
document.getElementById('searchUser').addEventListener('input', function () {
    const keyword = this.value.toLowerCase();

    document.querySelectorAll('.user-row').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().includes(keyword)
            ? ''
            : 'none';
    });
});
</script>

<script src="../../asset/js/script.js"></script>
</body>
</html>
