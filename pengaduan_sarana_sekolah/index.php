<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: view/admin/dashboard.php");
    exit;
}

if (isset($_SESSION['user_id'])) {
    header("Location: view/user/dashboard.php");
    exit;
}

$error = $_GET['error'] ?? '';
$success = $_GET['register'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Pengaduan Sarana Sekolah</title>
    <link rel="stylesheet" href="asset/css/style.css?v=<?= @filemtime(__DIR__ . '/asset/css/style.css') ?: 1 ?>">
</head>
<body>

<div class="auth-page">
    <div class="auth-card">

        <div class="auth-logo"><img class="app-logo-img" src="asset/img/logo.png" alt="Logo"></div>

        <div style="text-align:center;margin-bottom:28px">
            <h1 style="margin:0 0 8px;font-size:28px">Pengaduan Sarana Sekolah</h1>
            <p style="margin:0">Masuk untuk mengelola dan memantau pengaduan.</p>
        </div>

        <?php if ($error): ?>
            <div class="profile-alert error" style="padding:12px 14px;margin-bottom:18px">
                <?php
                $pesan = [
                    'empty' => 'Username dan password wajib diisi.',
                    'failed' => 'Username atau password salah.',
                    'login' => 'Username atau password salah.'
                ];
                echo htmlspecialchars($pesan[$error] ?? 'Terjadi kesalahan.');
                ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="profile-alert success" style="padding:12px 14px;margin-bottom:18px">
                Registrasi berhasil. Silakan masuk.
            </div>
        <?php endif; ?>

        <form action="controller/authcontroller.php" method="POST">
            <input type="hidden" name="action" value="login">

            <div style="margin-bottom:18px">
                <label for="username">Username</label>
                <input id="username" type="text" name="username"
                       placeholder="Masukkan username" autocomplete="username" required>
            </div>

            <div style="margin-bottom:8px">
                <label for="password">Password</label>
                <input id="password" type="password" name="password"
                       placeholder="Masukkan password" autocomplete="current-password" required>
            </div>

            <button type="submit" class="auth-submit">Masuk ke Sistem</button>
        </form>

        <p style="text-align:center;margin:22px 0 0">
            Belum punya akun?
            <a href="view/auth/register.php"
               style="color:var(--primary);font-weight:700;text-decoration:none">
                Daftar sekarang
            </a>
        </p>

    </div>
</div>

<script src="asset/js/script.js?v=<?= @filemtime(__DIR__ . '/asset/js/script.js') ?: 1 ?>"></script>
</body>
</html>
