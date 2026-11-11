<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . '/../../model/pengaduan.php';
require_once __DIR__ . '/../../model/penanganan.php';
require_once __DIR__ . '/_layout.php';

$model = new Pengaduan();
$penangananModel = new Penanganan();

$result = $model->getUser((int)$_SESSION['user_id']);
$semua = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Halaman ini hanya menampilkan pengaduan yang masih berjalan.
$data = [];
foreach ($semua as $row) {
    $key = userStatusKey($row['status'] ?? '');
    if ($key === 'baru' || $key === 'diproses') {
        $data[] = $row;
    }
}

$kategoriResult = $model->getKategori();
$lokasiResult = $model->getLokasi();
$kategori = $kategoriResult ? $kategoriResult->fetch_all(MYSQLI_ASSOC) : [];
$lokasi = $lokasiResult ? $lokasiResult->fetch_all(MYSQLI_ASSOC) : [];

$msg = $_GET['msg'] ?? '';
$pesan = [
    'created'       => 'Pengaduan berhasil dibuat.',
    'updated'       => 'Pengaduan berhasil diperbarui.',
    'deleted'       => 'Pengaduan berhasil dihapus.',
    'cannot_delete' => 'Pengaduan sudah diproses dan tidak dapat dihapus.',
    'foto'          => 'Foto tidak valid. Gunakan JPG, JPEG, PNG, atau WEBP maksimal 5 MB.',
    'failed'        => 'Pengaduan gagal diproses.',
];

$filterAwal = $_GET['status'] ?? 'semua';
if (!in_array($filterAwal, ['semua', 'baru', 'diproses'], true)) {
    $filterAwal = 'semua';
}
$bukaForm = (($_GET['buat'] ?? '') === '1');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Pengaduan | Pengaduan Sarana Sekolah</title>
<link rel="stylesheet" href="../../asset/css/style.css?v=<?= @filemtime(__DIR__ . '/../../asset/css/style.css') ?: 1 ?>">
</head>
<body class="admin-dashboard">

<?php renderUserSidebar('pengaduan'); ?>

<main class="admin-main"><div class="admin-page">

<?php renderUserHeader('LAPORAN SAYA', 'Pengaduan', 'Buat laporan baru dan pantau pengaduan yang masih berjalan.'); ?>

<?php if ($msg && isset($pesan[$msg])): ?>
<div class="admin-alert">✓ <?= htmlspecialchars($pesan[$msg]) ?></div>
<?php endif; ?>

<section class="admin-panel admin-form-card" id="formPanel" <?= $bukaForm ? '' : 'style="display:none"' ?>>
<div class="admin-toolbar">
<div><h2 class="admin-panel-title" id="formTitle">Buat Pengaduan</h2><div class="admin-page-subtitle">Jelaskan kondisi sarana sekolah yang bermasalah.</div></div>
</div>
<form id="formPengaduan" action="../../controller/pengaduancontroller.php" method="post" enctype="multipart/form-data" class="admin-form-grid">
<input type="hidden" name="action" id="fAction" value="tambah">
<input type="hidden" name="pengaduan_id" id="fId" value="">
<div class="admin-field full"><label>Judul Pengaduan</label><input name="judul_pengaduan" id="fJudul" required maxlength="150" placeholder="Contoh: Lampu kelas XI RPL mati"></div>
<div class="admin-field"><label>Kategori</label><select name="kategori_id" id="fKategori" required><option value="">Pilih kategori</option><?php foreach ($kategori as $kat): ?><option value="<?= (int)$kat['kategori_id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option><?php endforeach; ?></select></div>
<div class="admin-field"><label>Lokasi</label><select name="lokasi_id" id="fLokasi" required><option value="">Pilih lokasi</option><?php foreach ($lokasi as $lok): ?><option value="<?= (int)$lok['lokasi_id'] ?>"><?= htmlspecialchars($lok['nama_ruangan']) ?></option><?php endforeach; ?></select></div>
<div class="admin-field"><label>Urgensi</label><select name="urgensi" id="fUrgensi"><option value="rendah">Rendah</option><option value="sedang" selected>Sedang</option><option value="tinggi">Tinggi</option></select></div>
<div class="admin-field"><label>Foto Bukti <small id="fFotoNote">(opsional)</small></label><input type="file" name="foto_bukti" accept=".jpg,.jpeg,.png,.webp"></div>
<div class="admin-field full"><label>Deskripsi Masalah</label><textarea name="deskripsi" id="fDeskripsi" rows="5" required placeholder="Jelaskan kondisi sarana secara jelas..."></textarea></div>
<div class="full admin-form-actions" style="display:flex;gap:10px;flex-wrap:wrap">
<button class="admin-primary-btn" id="fSubmit" type="submit">Simpan Pengaduan</button>
<button class="admin-secondary-btn" type="button" onclick="tutupForm()">Batal</button>
</div>
</form>
</section>

<section class="admin-panel">
<div class="admin-toolbar">
<div><h2 class="admin-panel-title">Pengaduan Aktif</h2><div class="admin-page-subtitle">Laporan yang masih menunggu atau sedang ditangani. Yang sudah selesai ada di <a href="riwayat.php" style="color:var(--primary);font-weight:700">Riwayat</a>.</div></div>
<button type="button" class="admin-primary-btn" onclick="bukaForm()">+ Buat Pengaduan</button>
</div>

<div class="admin-toolbar" style="margin-top:6px">
<div class="admin-toolbar-search">⌕ <input id="searchPengaduan" type="search" placeholder="Cari judul, kategori, lokasi..."></div>
<select id="filterStatus" class="admin-secondary-btn" aria-label="Filter status">
<option value="semua" <?= $filterAwal === 'semua' ? 'selected' : '' ?>>Semua status</option>
<option value="baru" <?= $filterAwal === 'baru' ? 'selected' : '' ?>>Menunggu</option>
<option value="diproses" <?= $filterAwal === 'diproses' ? 'selected' : '' ?>>Diproses</option>
</select>
</div>

<div class="admin-table-wrap"><table class="admin-table">
<thead><tr><th>Pengaduan</th><th>Kategori / Lokasi</th><th>Urgensi</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach ($data as $row):
    $id = (int)$row['pengaduan_id'];
    $key = userStatusKey($row['status'] ?? '');
    $urgensi = $row['urgensi'] ?? 'sedang';
    $tanggal = !empty($row['created_at']) ? date('d M Y • H:i', strtotime($row['created_at'])) : '-';
    $penanganan = $penangananModel->getByPengaduan($id);
    $cari = strtolower(($row['judul_pengaduan'] ?? '') . ' ' . ($row['nama_kategori'] ?? '') . ' ' . ($row['nama_ruangan'] ?? '') . ' ' . ($row['deskripsi'] ?? ''));
    $adaFoto = !empty($row['foto_bukti']) && is_file(__DIR__ . '/../../asset/uploads/bukti/' . $row['foto_bukti']);
    $editData = [
        'id' => $id,
        'judul' => $row['judul_pengaduan'],
        'kategori' => $row['kategori_id'],
        'lokasi' => $row['lokasi_id'],
        'urgensi' => $urgensi,
        'deskripsi' => $row['deskripsi'],
    ];
?>
<tr class="up-row" data-id="<?= $id ?>" data-status="<?= htmlspecialchars($key) ?>" data-search="<?= htmlspecialchars($cari, ENT_QUOTES) ?>">
<td><div class="up-cell">
<?php if ($adaFoto): ?><img src="../../asset/uploads/bukti/<?= htmlspecialchars($row['foto_bukti']) ?>" alt="Bukti"><?php else: ?><span class="up-ph">📷</span><?php endif; ?>
<div><strong><?= htmlspecialchars($row['judul_pengaduan']) ?></strong><div class="admin-report-meta"><?= htmlspecialchars($tanggal) ?></div></div>
</div></td>
<td><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?><div class="admin-report-meta"><?= htmlspecialchars($row['nama_ruangan'] ?? '-') ?></div></td>
<td><span class="up-urg <?= htmlspecialchars($urgensi) ?>">● <?= htmlspecialchars(ucfirst($urgensi)) ?></span></td>
<td><span class="admin-status <?= htmlspecialchars($key) ?>"><?= htmlspecialchars(userStatusLabel($key)) ?></span></td>
<td><div class="admin-table-actions">
<button type="button" class="admin-secondary-btn" onclick="toggleDetail(<?= $id ?>)">Detail</button>
<?php if ($key === 'baru'): ?>
<button type="button" class="admin-secondary-btn" onclick='editPengaduan(<?= json_encode($editData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>Edit</button>
<form action="../../controller/pengaduancontroller.php" method="post" onsubmit="return confirm('Hapus pengaduan ini?')"><input type="hidden" name="action" value="hapus"><input type="hidden" name="pengaduan_id" value="<?= $id ?>"><button class="admin-danger-btn" type="submit">Hapus</button></form>
<?php endif; ?>
</div></td>
</tr>
<tr id="detail-<?= $id ?>" class="up-detail" style="display:none"><td colspan="5">
<div class="admin-panel" style="margin:8px 0;background:var(--surface-2)!important">
<div class="admin-form-grid">
<div class="full"><div class="admin-report-meta">DESKRIPSI</div><p><?= nl2br(htmlspecialchars($row['deskripsi'] ?? '-')) ?></p></div>
<?php if ($penanganan): ?>
<div class="full"><div class="admin-report-meta">TOTAL BIAYA PENANGANAN DARI ADMIN</div><strong>Rp <?= number_format((float)($penanganan['biaya'] ?? 0), 0, ',', '.') ?></strong></div>
<div class="full"><div class="admin-report-meta">CATATAN PENANGANAN</div><p><?= nl2br(htmlspecialchars($penanganan['catatan_perbaikan'] ?? '-')) ?></p></div>
<?php else: ?>
<div class="full"><div class="admin-report-meta">CATATAN PENANGANAN</div><p class="up-muted">Belum ada catatan penanganan dari admin.</p></div>
<?php endif; ?>
<?php if ($adaFoto): ?><div class="full"><a href="../../asset/uploads/bukti/<?= htmlspecialchars($row['foto_bukti']) ?>" target="_blank" rel="noopener" style="color:var(--primary);font-weight:700">Lihat foto bukti ↗</a></div><?php endif; ?>
</div>
</div>
</td></tr>
<?php endforeach; ?>
<tr id="emptyRow" <?= $data ? 'style="display:none"' : '' ?>><td colspan="5"><div class="admin-empty" id="emptyText"><?= $data ? 'Pengaduan tidak ditemukan.' : 'Tidak ada pengaduan aktif. Klik “Buat Pengaduan” untuk membuat laporan baru.' ?></div></td></tr>
</tbody>
</table></div>
</section>

</div></main>

<script>
var panel = document.getElementById('formPanel');
var form = document.getElementById('formPengaduan');

function tampilkanForm() {
    panel.style.display = '';
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    setTimeout(function () { document.getElementById('fJudul').focus(); }, 250);
}

function bukaForm() {
    form.reset();
    document.getElementById('fAction').value = 'tambah';
    document.getElementById('fId').value = '';
    document.getElementById('formTitle').textContent = 'Buat Pengaduan';
    document.getElementById('fSubmit').textContent = 'Simpan Pengaduan';
    document.getElementById('fFotoNote').textContent = '(opsional)';
    tampilkanForm();
}

function editPengaduan(d) {
    document.getElementById('fAction').value = 'edit';
    document.getElementById('fId').value = d.id;
    document.getElementById('fJudul').value = d.judul || '';
    document.getElementById('fKategori').value = d.kategori || '';
    document.getElementById('fLokasi').value = d.lokasi || '';
    document.getElementById('fUrgensi').value = d.urgensi || 'sedang';
    document.getElementById('fDeskripsi').value = d.deskripsi || '';
    document.getElementById('formTitle').textContent = 'Edit Pengaduan';
    document.getElementById('fSubmit').textContent = 'Simpan Perubahan';
    document.getElementById('fFotoNote').textContent = '(kosongkan jika tidak diganti)';
    tampilkanForm();
}

function tutupForm() {
    form.reset();
    panel.style.display = 'none';
}

function toggleDetail(id) {
    var el = document.getElementById('detail-' + id);
    el.style.display = el.style.display === 'none' ? 'table-row' : 'none';
}

(function () {
    var search = document.getElementById('searchPengaduan');
    var filter = document.getElementById('filterStatus');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.up-row'));
    var emptyRow = document.getElementById('emptyRow');
    var emptyText = document.getElementById('emptyText');

    function apply() {
        var q = search.value.trim().toLowerCase();
        var f = filter.value;
        var shown = 0;
        rows.forEach(function (row) {
            var ok = (f === 'semua' || row.dataset.status === f) && row.dataset.search.indexOf(q) !== -1;
            row.style.display = ok ? '' : 'none';
            if (!ok) {
                document.getElementById('detail-' + row.dataset.id).style.display = 'none';
            }
            if (ok) shown++;
        });
        if (rows.length > 0) {
            emptyRow.style.display = shown === 0 ? '' : 'none';
            emptyText.textContent = 'Pengaduan tidak ditemukan.';
        }
    }

    search.addEventListener('input', apply);
    filter.addEventListener('change', apply);
    apply();
})();

<?php if ($bukaForm): ?>
document.getElementById('fJudul').focus();
<?php endif; ?>
</script>
<script src="../../asset/js/script.js?v=<?= @filemtime(__DIR__ . '/../../asset/js/script.js') ?: 1 ?>"></script>
</body>
</html>
