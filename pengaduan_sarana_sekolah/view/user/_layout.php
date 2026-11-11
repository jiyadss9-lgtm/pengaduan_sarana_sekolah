
<?php
// Layout bersama untuk halaman user.

function userStatusKey($status)
{
    $status = strtolower((string)$status);
    return in_array($status, ['baru', 'pending', ''], true) ? 'baru' : $status;
}

function userStatusLabel($key)
{
    $label = [
        'baru' => 'Menunggu',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];

    return $label[$key] ?? 'Menunggu';
}

function userBintang($n)
{
    $n = max(0, min(5, (int)$n));
    return str_repeat('★', $n) . '<i>' . str_repeat('★', 5 - $n) . '</i>';
}

function renderUserSidebar($active)
{
    $nama = $_SESSION['user_nama'] ?? 'User';
    $inisial = strtoupper(substr(trim($nama), 0, 1));

    if ($inisial === '') {
        $inisial = 'U';
    }

    $menu = [
        'dashboard' => ['dashboard.php', '⌂', 'Dashboard'],
        'pengaduan' => ['pengaduan.php', '▣', 'Pengaduan'],
        'riwayat' => ['riwayat.php', '◷', 'Histori Aspirasi'],
    ];
    ?>
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <div class="admin-avatar"><?= htmlspecialchars($inisial) ?></div>
            <strong>PENGADUAN SEKOLAH</strong>
            <span>PANEL PENGGUNA</span>
        </div>

        <nav class="admin-nav" aria-label="Navigasi user">
            <div class="admin-nav-title">MENU UTAMA</div>

            <?php foreach ($menu as $key => $m): ?>
                <a class="<?= $key === $active ? 'active' : '' ?>"
                   href="<?= $m[0] ?>">
                    <span><?= $m[1] ?></span><?= $m[2] ?>
                </a>
            <?php endforeach; ?>

            <div class="admin-nav-title">AKUN</div>
            <a href="../../controller/profilcontroller.php">
                <span>◉</span>Profil Saya
            </a>
            <a class="admin-logout" href="../../logout.php">
                <span>↪</span>Logout
            </a>
        </nav>
    </aside>
    <?php
}

function renderUserHeader($kicker, $judul, $subjudul)
{
    $nama = $_SESSION['user_nama'] ?? 'User';
    $role = strtoupper($_SESSION['user_role'] ?? 'SISWA');
    $kelas = trim((string)($_SESSION['user_kelas'] ?? ''));
    $info = $kelas !== '' ? $role . ' · ' . $kelas : $role;
    ?>
    <header class="admin-header">
        <div>
            <p><?= htmlspecialchars($kicker) ?></p>
            <h1><?= htmlspecialchars($judul) ?></h1>
            <div class="admin-page-subtitle">
                <?= htmlspecialchars($subjudul) ?>
            </div>
        </div>

        <div class="admin-header-actions">
            <button type="button" id="themeToggle"
                    class="admin-theme-btn"
                    aria-label="Ganti tema"
                    title="Ganti tema">◐</button>

            <div class="admin-header-right">
                <strong><?= htmlspecialchars($nama) ?></strong>
                <span><?= htmlspecialchars($info) ?></span>
            </div>
        </div>
    </header>
    <?php
}
