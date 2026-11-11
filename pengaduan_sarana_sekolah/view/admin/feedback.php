<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../../index.php'); exit; }
require_once __DIR__ . '/../../model/feedback.php';

$feedbackModel = new Feedback();
$result = $feedbackModel->getAllDetail();
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Ringkasan
$total = count($rows);
$jumlahRating = array_sum(array_map(function ($r) { return (int)$r["rating"]; }, $rows));
$rata = $total > 0 ? $jumlahRating / $total : 0;
$dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($rows as $r) {
    $n = (int)$r['rating'];
    if (isset($dist[$n])) $dist[$n]++;
}
$puas = $dist[5] + $dist[4];
$kurang = $dist[2] + $dist[1];
$persenPuas = $total > 0 ? round($puas / $total * 100) : 0;

$filter = (int)($_GET['rating'] ?? 0);

function bintang($n) {
    $n = max(0, min(5, (int)$n));
    return str_repeat('★', $n) . '<i>' . str_repeat('★', 5 - $n) . '</i>';
}
?>
<!doctype html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Umpan Balik</title><link rel="stylesheet" href="../../asset/css/style.css?v=<?= @filemtime(__DIR__ . '/../../asset/css/style.css') ?: 1 ?>"></head>
<body class="admin-dashboard">
<aside class="admin-sidebar"><div class="admin-brand"><div class="admin-avatar"><img class="app-logo-img" src="../../asset/img/logo.png" alt="Logo"></div><strong>ADMIN SEKOLAH</strong><span>PANEL ADMINISTRATOR</span></div><nav class="admin-nav"><div class="admin-nav-title">MENU UTAMA</div><a class="" href="dashboard.php"><span>⌂</span>Dashboard</a><a class="" href="pengaduan.php"><span>▣</span>Pengaduan</a><a class="" href="users.php"><span>♙</span>Data User</a><a class="" href="lokasi.php"><span>⌖</span>Data Lokasi</a><a class="" href="kategori.php"><span>▤</span>Data Kategori</a><a class="active" href="feedback.php"><span>★</span>Umpan Balik</a><div class="admin-nav-title">AKUN</div><a class="" href="../../controller/profilcontroller.php"><span>◉</span>Profil</a><a class="admin-logout" href="../../logout.php"><span>↪</span>Logout</a></nav></aside>
<main class="admin-main"><div class="admin-page">
<header class="admin-header"><div><p>PENILAIAN LAYANAN</p><h1>Umpan Balik</h1><div class="admin-page-subtitle">Rating dan komentar dari pengguna untuk pengaduan yang sudah selesai.</div></div><div class="admin-header-actions"><button type="button" id="themeToggle" class="admin-theme-btn" aria-label="Ganti tema" title="Ganti tema">◐</button><div class="admin-header-right"><strong><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Administrator') ?></strong><span>ADMINISTRATOR</span></div></div></header>

<section class="admin-stats">
<div class="admin-stat"><div class="admin-stat-icon">★</div><div><div class="admin-stat-number"><?= $total ?></div><div class="admin-stat-label">Total Umpan Balik</div></div></div>
<div class="admin-stat"><div class="admin-stat-icon">◎</div><div><div class="admin-stat-number"><?= $total > 0 ? number_format($rata, 1) : '-' ?></div><div class="admin-stat-label">Rata-rata Rating</div></div></div>
<div class="admin-stat"><div class="admin-stat-icon">✓</div><div><div class="admin-stat-number"><?= $persenPuas ?>%</div><div class="admin-stat-label">Puas (4–5 ★)</div></div></div>
<div class="admin-stat"><div class="admin-stat-icon">!</div><div><div class="admin-stat-number"><?= $kurang ?></div><div class="admin-stat-label">Kurang Puas (1–2 ★)</div></div></div>
</section>

<section class="admin-panel">
<h2 class="admin-panel-title">Sebaran Rating</h2>
<div class="fb-dist">
<?php foreach ($dist as $bintangKe => $jml): $lebar = $total > 0 ? round($jml / $total * 100) : 0; ?>
<div class="fb-dist-row"><span><?= $bintangKe ?> ★</span><div class="fb-dist-bar"><span style="width:<?= $lebar ?>%"></span></div><b><?= $jml ?></b></div>
<?php endforeach; ?>
</div>
</section>

<section class="admin-panel" style="margin-top:18px">
<div class="admin-toolbar"><div><h2 class="admin-panel-title">Daftar Umpan Balik</h2></div>
<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
<div class="admin-toolbar-search" style="max-width:320px">⌕ <input id="searchFeedback" type="search" placeholder="Cari pengaduan, pelapor, komentar..."></div>
<select id="filterRating" class="admin-secondary-btn" aria-label="Filter rating">
<option value="0">Semua rating</option>
<?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= $filter === $i ? 'selected' : '' ?>><?= $i ?> ★</option><?php endfor; ?>
</select>
</div></div>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Pengaduan</th><th>Pelapor</th><th>Rating</th><th>Komentar</th><th>Tanggal</th></tr></thead><tbody>
<?php foreach ($rows as $r):
    $kelas = trim((string)($r['kelas'] ?? ''));
    $role = strtoupper((string)($r['role'] ?? ''));
    $info = $kelas !== '' ? $role . ' · ' . $kelas : $role;
    $komentar = trim((string)($r['komentar'] ?? ''));
    $search = strtolower(($r['judul_pengaduan'] ?? '') . ' ' . ($r['nama_lengkap'] ?? '') . ' ' . $kelas . ' ' . $komentar . ' ' . ($r['nama_ruangan'] ?? ''));
?>
<tr class="fb-row" data-rating="<?= (int)$r['rating'] ?>" data-search="<?= htmlspecialchars($search, ENT_QUOTES) ?>">
<td><strong><?= htmlspecialchars($r['judul_pengaduan']) ?></strong><div class="admin-report-meta"><?= htmlspecialchars(($r['nama_kategori'] ?? '-') . ' · ' . ($r['nama_ruangan'] ?? '-')) ?></div></td>
<td class="fb-user"><?= htmlspecialchars($r['nama_lengkap'] ?? '-') ?><small><?= htmlspecialchars($info) ?></small></td>
<td><span class="fb-stars"><?= bintang($r['rating']) ?></span><span class="fb-score"><?= (int)$r['rating'] ?>/5</span></td>
<td class="fb-comment<?= $komentar === '' ? ' empty' : '' ?>"><?= $komentar === '' ? 'Tidak ada komentar.' : nl2br(htmlspecialchars($komentar)) ?></td>
<td><?= htmlspecialchars(!empty($r['created_at']) ? date('d M Y', strtotime($r['created_at'])) : '-') ?></td>
</tr>
<?php endforeach; ?>
<tr id="fbEmpty" <?= $total > 0 ? 'style="display:none"' : '' ?>><td colspan="5"><div class="admin-empty"><?= $total > 0 ? 'Umpan balik tidak ditemukan.' : 'Belum ada umpan balik dari pengguna.' ?></div></td></tr>
</tbody></table></div></section>

</div></main>
<script>
(function () {
    var search = document.getElementById('searchFeedback');
    var filter = document.getElementById('filterRating');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.fb-row'));
    var empty = document.getElementById('fbEmpty');

    function apply() {
        var q = search.value.trim().toLowerCase();
        var r = filter.value;
        var shown = 0;
        rows.forEach(function (row) {
            var ok = (r === '0' || row.dataset.rating === r) && row.dataset.search.indexOf(q) !== -1;
            row.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });
        empty.style.display = shown === 0 ? '' : 'none';
        if (rows.length > 0) empty.querySelector('.admin-empty').textContent = 'Umpan balik tidak ditemukan.';
    }

    search.addEventListener('input', apply);
    filter.addEventListener('change', apply);
    apply();
})();
</script>
<script src="../../asset/js/script.js?v=<?= @filemtime(__DIR__ . '/../../asset/js/script.js') ?: 1 ?>"></script></body></html>
