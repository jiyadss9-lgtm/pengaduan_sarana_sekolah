
<?php
session_start();

require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$db = (new Koneksi())->koneksi;
$isAdmin = isset($_SESSION['admin_id']);

$id = $isAdmin
    ? (int)$_SESSION['admin_id']
    : (int)$_SESSION['user_id'];

$success = '';
$error = '';

function ambilAkun($db, $isAdmin, $id) {
    if ($isAdmin) {
        $stmt = $db->prepare("SELECT admin_id, nama_admin, email, password FROM admin WHERE admin_id=? LIMIT 1");
    } else {
        $stmt = $db->prepare("SELECT user_id, nama_lengkap, nomor_induk, role, email, kelas, no_whatsapp, password FROM users WHERE user_id=? LIMIT 1");
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $account;
}

$account = ambilAkun($db, $isAdmin, $id);

if (!$account) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$nama = $isAdmin
    ? ($account['nama_admin'] ?? '')
    : ($account['nama_lengkap'] ?? '');

$email = $account['email'] ?? '';
$role = $isAdmin ? 'Administrator' : ($account['role'] ?? 'Siswa');
$nomorInduk = $isAdmin ? '-' : ($account['nomor_induk'] ?? '-');
$noWhatsapp = $isAdmin ? '-' : ($account['no_whatsapp'] ?? '-');
$kelas = ($isAdmin || empty($account['kelas'])) ? '-' : $account['kelas'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $namaBaru = trim($_POST['nama'] ?? '');
        $emailBaru = trim($_POST['email'] ?? '');
        $whatsappBaru = trim($_POST['no_whatsapp'] ?? '');
        $kelasBaru = (!$isAdmin && ($account['role'] ?? '') === 'siswa')
            ? trim($_POST['kelas'] ?? '')
            : ($account['kelas'] ?? '');

        if ($namaBaru === '' || $emailBaru === '') {
            $error = "Nama dan email wajib diisi.";
        } elseif (!$isAdmin && ($account['role'] ?? '') === 'siswa' && $kelasBaru === '') {
            $error = "Kelas wajib diisi.";
        } elseif (!filter_var($emailBaru, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid.";
        } else {
            if ($isAdmin) {
                $check = $db->prepare("SELECT admin_id FROM admin WHERE email=? AND admin_id<>? LIMIT 1");
            } else {
                $check = $db->prepare("SELECT user_id FROM users WHERE email=? AND user_id<>? LIMIT 1");
            }

            $check->bind_param("si", $emailBaru, $id);
            $check->execute();
            $emailDipakai = $check->get_result()->num_rows > 0;
            $check->close();

            if ($emailDipakai) {
                $error = "Email tersebut sudah digunakan.";
            } else {
                if ($isAdmin) {
                    $stmt = $db->prepare("UPDATE admin SET nama_admin=?, email=? WHERE admin_id=?");
                    $stmt->bind_param("ssi", $namaBaru, $emailBaru, $id);
                } else {
                    $stmt = $db->prepare("UPDATE users SET nama_lengkap=?, email=?, no_whatsapp=?, kelas=? WHERE user_id=?");
                    $stmt->bind_param("ssssi", $namaBaru, $emailBaru, $whatsappBaru, $kelasBaru, $id);
                }

                if ($stmt->execute()) {
                    if ($isAdmin) {
                        $_SESSION['admin_nama'] = $namaBaru;
                        $_SESSION['admin_email'] = $emailBaru;
                    } else {
                        $_SESSION['user_nama'] = $namaBaru;
                        $_SESSION['user_email'] = $emailBaru;
                    }
                    $success = "Informasi akun berhasil diperbarui.";
                } else {
                    $error = "Gagal menyimpan informasi akun.";
                }
                $stmt->close();
            }
        }
    }

    if ($action === 'password') {
        $passwordLama = $_POST['password_lama'] ?? '';
        $passwordBaru = $_POST['password_baru'] ?? '';
        $konfirmasi = $_POST['konfirmasi_password'] ?? '';

        $passwordBenar = password_verify($passwordLama, $account['password'])
            || $passwordLama === $account['password'];

        if (!$passwordBenar) {
            $error = "Sandi lama tidak sesuai.";
        } elseif (strlen($passwordBaru) < 6) {
            $error = "Sandi baru minimal 6 karakter.";
        } elseif ($passwordBaru !== $konfirmasi) {
            $error = "Konfirmasi sandi tidak sama.";
        } else {
            $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);

            if ($isAdmin) {
                $stmt = $db->prepare("UPDATE admin SET password=? WHERE admin_id=?");
            } else {
                $stmt = $db->prepare("UPDATE users SET password=? WHERE user_id=?");
            }

            $stmt->bind_param("si", $hash, $id);

            if ($stmt->execute()) {
                $success = "Sandi berhasil diubah.";
            } else {
                $error = "Gagal mengubah sandi.";
            }
            $stmt->close();
        }
    }

    $account = ambilAkun($db, $isAdmin, $id);

    $nama = $isAdmin
        ? ($account['nama_admin'] ?? '')
        : ($account['nama_lengkap'] ?? '');

    $email = $account['email'] ?? '';
    $role = $isAdmin ? 'Administrator' : ($account['role'] ?? 'Siswa');
    $nomorInduk = $isAdmin ? '-' : ($account['nomor_induk'] ?? '-');
    $noWhatsapp = $isAdmin ? '-' : ($account['no_whatsapp'] ?? '-');
    $kelas = ($isAdmin || empty($account['kelas'])) ? '-' : $account['kelas'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya</title>
<link rel="stylesheet" href="../asset/css/style.css?v=<?= @filemtime(__DIR__ . '/../asset/css/style.css') ?: 1 ?>">
</head>

<body class="admin-dashboard profile-page">

<?php if ($isAdmin): ?>

<aside class="admin-sidebar">
    <div class="admin-brand">
        <div class="admin-avatar">
            <img class="app-logo-img" src="../asset/img/logo.png" alt="Logo">
        </div>
        <strong>ADMIN SEKOLAH</strong>
        <span>PANEL ADMINISTRATOR</span>
    </div>

    <nav class="admin-nav">
        <div class="admin-nav-title">MENU UTAMA</div>

        <a href="../view/admin/dashboard.php"><span>⌂</span>Dashboard</a>
        <a href="../view/admin/pengaduan.php"><span>▣</span>Pengaduan</a>
        <a href="../view/admin/histori.php"><span>◷</span>Histori Aspirasi</a>
        <a href="../view/admin/users.php"><span>♙</span>Data User</a>
        <a href="../view/admin/lokasi.php"><span>⌖</span>Data Lokasi</a>
        <a href="../view/admin/kategori.php"><span>▤</span>Data Kategori</a>

        <div class="admin-nav-title">AKUN</div>
        <a class="active" href="profilcontroller.php"><span>◉</span>Profil</a>
        <a class="admin-logout" href="../logout.php"><span>↪</span>Logout</a>
    </nav>
</aside>

<?php else: ?>

<aside class="admin-sidebar user-admin-sidebar">
    <div class="admin-brand">
        <div class="admin-avatar">
            <?= htmlspecialchars(strtoupper(substr($nama, 0, 1))) ?>
        </div>
        <strong>PENGADUAN SEKOLAH</strong>
        <span>PANEL PENGGUNA</span>
    </div>

    <nav class="admin-nav">
        <div class="admin-nav-title">MENU UTAMA</div>

        <a href="../view/user/dashboard.php"><span>⌂</span>Dashboard</a>
        <a href="../view/user/pengaduan.php"><span>▣</span>Pengaduan</a>
        <a href="../view/user/riwayat.php"><span>◷</span>Histori Aspirasi</a>

        <div class="admin-nav-title">AKUN</div>
        <a class="active" href="profilcontroller.php"><span>◉</span>Profil Saya</a>
        <a class="admin-logout" href="../logout.php"><span>↪</span>Logout</a>
    </nav>
</aside>

<?php endif; ?>

<main class="admin-main">
<div class="admin-page">

<header class="admin-header">
    <div>
        <p>SISTEM PENGADUAN SARANA SEKOLAH</p>
        <h1>Profil Saya</h1>
        <div class="admin-page-subtitle">
            Kelola informasi pribadi dan pengaturan akun Anda.
        </div>
    </div>

    <div class="admin-header-actions">
        <button type="button" id="themeToggle" class="admin-theme-btn">◐</button>
        <div class="admin-header-right">
            <strong><?= htmlspecialchars($nama) ?></strong>
            <span><?= htmlspecialchars($isAdmin ? 'ADMINISTRATOR' : strtoupper($role) . ($kelas !== '-' ? ' · ' . $kelas : '')) ?></span>
        </div>
    </div>
</header>

<?php if ($success): ?>
<div class="profile-alert success">✓ <?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="profile-alert error">! <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<section class="profile-grid" style="grid-template-columns:1fr">
<div class="profile-info-card">
    <h2>INFORMASI PRIBADI</h2>

    <div class="profile-detail-grid">
        <div><span>NAMA LENGKAP</span><b>:</b><strong><?= htmlspecialchars($nama) ?></strong></div>
        <div><span>EMAIL</span><b>:</b><strong><?= htmlspecialchars($email) ?></strong></div>
        <div><span>NO WHATSAPP</span><b>:</b><strong><?= htmlspecialchars($noWhatsapp) ?></strong></div>
        <div><span>NOMOR INDUK</span><b>:</b><strong><?= htmlspecialchars($nomorInduk) ?></strong></div>
        <div><span>KELAS</span><b>:</b><strong><?= htmlspecialchars($kelas) ?></strong></div>
        <div><span>HAK AKSES</span><b>:</b><strong><?= htmlspecialchars($role) ?></strong></div>
    </div>
</div>
</section>

<section class="account-settings-card">
    <h2>PENGATURAN AKUN</h2>
    <p>Pengaturan akun sekarang berada di dalam halaman profil.</p>

    <div class="account-settings-grid">

        <form method="POST" class="account-form">
            <input type="hidden" name="action" value="profile">

            <h3>✏️ EDIT INFORMASI AKUN</h3>

            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($nama) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

            <?php if (!$isAdmin): ?>
            <label>No WhatsApp</label>
            <input type="text" name="no_whatsapp" value="<?= htmlspecialchars($noWhatsapp) ?>">
            <?php endif; ?>

            <?php if (!$isAdmin && ($account['role'] ?? '') === 'siswa'): ?>
            <label>Kelas</label>
            <input type="text" name="kelas" maxlength="30" value="<?= htmlspecialchars($account['kelas'] ?? '') ?>" required>
            <?php endif; ?>

            <button type="submit">SIMPAN PERUBAHAN</button>
        </form>

        <form method="POST" class="account-form">
            <input type="hidden" name="action" value="password">

            <h3>🔒 UBAH SANDI</h3>

            <label>Sandi Lama</label>
            <input type="password" name="password_lama" required>

            <label>Sandi Baru</label>
            <input type="password" name="password_baru" minlength="6" required>

            <label>Konfirmasi Sandi Baru</label>
            <input type="password" name="konfirmasi_password" minlength="6" required>

            <button type="submit">UBAH SANDI</button>
        </form>

    </div>
</section>

</div>
</main>

<script src="../asset/js/script.js?v=<?= @filemtime(__DIR__ . '/../asset/js/script.js') ?: 1 ?>"></script>
</body>
</html>
