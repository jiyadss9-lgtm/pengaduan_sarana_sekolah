
<?php
$currentPage = basename($_SERVER['PHP_SELF']);

$menus = [
    'dashboard.php' => ['⌂', 'Dashboard'],
    'pengaduan.php' => ['▣', 'Pengaduan'],
    'histori.php' => ['◷', 'Histori Aspirasi'],
    'laporan_aspirasi.php' => ['▥', 'Laporan Aspirasi'],
    'users.php' => ['♙', 'Data User'],
    'lokasi.php' => ['⌖', 'Data Lokasi'],
    'kategori.php' => ['▤', 'Data Kategori'],
    'feedback.php' => ['★', 'Umpan Balik']
];
?>

<aside class="admin-sidebar">
    <div class="admin-brand">
        <div class="admin-avatar">
            <img class="app-logo-img"
                 src="../../asset/img/logo.png"
                 alt="Logo">
        </div>
        <strong>ADMIN SEKOLAH</strong>
        <span>PANEL ADMINISTRATOR</span>
    </div>

    <nav class="admin-nav">
        <div class="admin-nav-title">MENU UTAMA</div>

        <?php foreach ($menus as $file => $menu): ?>
            <a href="<?= htmlspecialchars($file) ?>"
               class="<?= $currentPage === $file ? 'active' : '' ?>">
                <span><?= $menu[0] ?></span>
                <?= htmlspecialchars($menu[1]) ?>
            </a>
        <?php endforeach; ?>

        <div class="admin-nav-title">AKUN</div>

        <a href="../../controller/profilcontroller.php">
            <span>◉</span>Profil
        </a>

        <a class="admin-logout" href="../../logout.php">
            <span>↪</span>Logout
        </a>
    </nav>
</aside>
