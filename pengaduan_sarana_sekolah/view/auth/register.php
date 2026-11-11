<?php
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | Pengaduan Sarana Sekolah</title>
    <link rel="stylesheet" href="../../asset/css/style.css?v=<?= @filemtime(__DIR__ . '/../../asset/css/style.css') ?: 1 ?>">
</head>
<body>

<div class="auth-page">
    <div class="auth-card" style="max-width:760px">

        <div class="auth-logo"><img class="app-logo-img" src="../../asset/img/logo.png" alt="Logo"></div>

        <div style="text-align:center;margin-bottom:26px">
            <h1 style="margin:0 0 8px;font-size:28px">Buat Akun</h1>
            <p style="margin:0">Daftar sebagai pengguna sistem pengaduan sekolah.</p>
        </div>

        <?php if ($error): ?>
            <div class="profile-alert error" style="padding:12px 14px;margin-bottom:18px">
                <?php
                $pesan = [
                    'empty' => 'Semua data wajib diisi.',
                    'role' => 'Role tidak valid.',
                    'kelas' => 'Kelas wajib diisi untuk siswa.',
                    'password' => 'Konfirmasi password tidak sama.',
                    'email' => 'Email sudah digunakan.',
                    'nomor' => 'Nomor induk sudah digunakan.',
                    'failed' => 'Registrasi gagal.'
                ];
                echo htmlspecialchars($pesan[$error] ?? 'Terjadi kesalahan.');
                ?>
            </div>
        <?php endif; ?>

        <form action="../../controller/authcontroller.php" method="POST">
            <input type="hidden" name="action" value="register">

            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px">
                <div>
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input id="nama_lengkap" type="text" name="nama_lengkap" required>
                </div>

                <div>
                    <label for="nomor_induk">Username (NIS/NIP)</label>
                    <input id="nomor_induk" type="text" name="nomor_induk" required>
                </div>

                <div>
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">Pilih Role</option>
                        <option value="siswa">Siswa</option>
                        <option value="guru">Guru</option>
                    </select>
                </div>

                <div id="kelasField">
                    <label for="kelas">Kelas</label>
                    <input id="kelas" type="text" name="kelas" maxlength="30"
                           placeholder="Contoh: XI RPL 1">
                </div>

                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" required>
                </div>

                <div>
                    <label for="no_whatsapp">No. WhatsApp</label>
                    <input id="no_whatsapp" type="text" name="no_whatsapp">
                </div>

                <div>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                </div>

                <div style="grid-column:1/-1">
                    <label for="konfirmasi_password">Konfirmasi Password</label>
                    <input id="konfirmasi_password" type="password"
                           name="konfirmasi_password" required>
                </div>
            </div>

            <button type="submit" class="auth-submit">Buat Akun</button>
        </form>

        <p style="text-align:center;margin:22px 0 0">
            Sudah punya akun?
            <a href="../../index.php"
               style="color:var(--primary);font-weight:700;text-decoration:none">
                Kembali ke Login
            </a>
        </p>

    </div>
</div>

<script src="../../asset/js/script.js?v=<?= @filemtime(__DIR__ . '/../../asset/js/script.js') ?: 1 ?>"></script>
<script>
// Kolom kelas hanya muncul & wajib diisi jika role = siswa.
(function () {
    var role = document.getElementById('role');
    var field = document.getElementById('kelasField');
    var input = document.getElementById('kelas');

    function sync() {
        var siswa = role.value === 'siswa';
        field.style.display = siswa ? '' : 'none';
        input.required = siswa;
        if (!siswa) input.value = '';
    }

    role.addEventListener('change', sync);
    sync();
})();
</script>
</body>
</html>
