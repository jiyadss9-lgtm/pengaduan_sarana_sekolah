<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../index.php');
    exit;
}
require_once __DIR__ . '/../../model/user.php';
$model = new User();
$id = (int)($_GET['id'] ?? 0);
$user = $model->getById($id);
if (!$user) {
    header('Location: users.php?msg=' . urlencode('Data user tidak ditemukan.'));
    exit;
}
$title = 'Edit User';
$actionLabel = 'Simpan Perubahan';
$action = 'edit';

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $title ?> | Admin</title>
<link rel="stylesheet" href="../../asset/css/style.css">
</head>
<body class="admin-dashboard">
<?php include __DIR__ . '/_sidebar.php'; ?>
<main class="admin-main">
<div class="admin-page">
<header class="admin-header">
 <div><p>KELOLA DATA</p><h1><?= $title ?></h1>
 <div class="admin-page-subtitle">Lengkapi informasi pengguna di bawah ini.</div></div>
</header>
<section class="admin-panel admin-form-card">
<form action="../../controller/adminusercontroller.php" method="POST">
<input type="hidden" name="action" value="<?= $action ?>">
<input type="hidden" name="user_id" value="<?= (int)$user['user_id'] ?>">
<div class="admin-form-grid">
 <div class="admin-field">
  <label>Nama Lengkap</label>
  <input type="text" name="nama_lengkap" required maxlength="100" value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>">
 </div>
 <div class="admin-field">
  <label>Nomor Induk / Username</label>
  <input type="text" name="nomor_induk" required maxlength="30" value="<?= htmlspecialchars($user['nomor_induk'] ?? '') ?>">
 </div>
 <div class="admin-field">
  <label>Role</label>
  <select name="role" id="roleUser" required>
   <option value="siswa" <?= ($user['role'] ?? '' === 'siswa') ? 'selected' : '' ?>>Siswa</option>
   <option value="guru" <?= ($user['role'] ?? '' === 'guru') ? 'selected' : '' ?>>Guru</option>
  </select>
 </div>
 <div class="admin-field">
  <label>Email</label>
  <input type="email" name="email" maxlength="100" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
 </div>
 <div class="admin-field" id="kelasField">
  <label>Kelas</label>
  <input type="text" name="kelas" maxlength="30" value="<?= htmlspecialchars($user['kelas'] ?? '') ?>">
 </div>
 <div class="admin-field">
  <label>Nomor WhatsApp</label>
  <input type="text" name="no_whatsapp" maxlength="20" value="<?= htmlspecialchars($user['no_whatsapp'] ?? '') ?>">
 </div>
 <div class="admin-field full">
  <label>Password (opsional)</label>
  <input type="password" name="password" minlength="6" placeholder="Kosongkan jika tidak ingin mengubah password" autocomplete="new-password">
  <small>Kosongkan jika password tidak ingin diubah.</small>
 </div>
</div>
<div class="admin-form-actions">
 <a href="users.php" class="admin-secondary-btn">Batal</a>
 <button type="submit" class="admin-primary-btn"><?= $actionLabel ?></button>
</div>
</form>
</section>
</div>
</main>
<script>
const roleSelect = document.getElementById('roleUser');
const kelasField = document.getElementById('kelasField');
function toggleKelas() {
 kelasField.style.display = roleSelect.value === 'siswa' ? '' : 'none';
}
roleSelect.addEventListener('change', toggleKelas);
toggleKelas();
</script>
<script src="../../asset/js/script.js"></script>
</body>
</html>
