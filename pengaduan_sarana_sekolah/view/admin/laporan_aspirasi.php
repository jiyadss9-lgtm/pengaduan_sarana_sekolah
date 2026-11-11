<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../../index.php'); exit; }
require_once __DIR__ . '/../../model/pengaduan.php';
$model = new Pengaduan();
$result = $model->getAspirasiReport([]);
$rows = [];
if ($result) { while ($r = $result->fetch_assoc()) { $rows[] = $r; } }
function h($v) { return htmlspecialchars((string)($v ?? '-'), ENT_QUOTES, 'UTF-8'); }
function groupRows($rows, $keyFn) { $out=[]; foreach($rows as $r) { $k=$keyFn($r); if(!isset($out[$k])) $out[$k]=['label'=>$k,'jumlah'=>0,'items'=>[]]; $out[$k]['jumlah']++; $out[$k]['items'][]=$r; } uasort($out, function($a,$b){ return $b['jumlah'] <=> $a['jumlah']; }); return $out; }
$byCategory = groupRows($rows, fn($r)=>$r['nama_kategori'] ?: 'Tanpa kategori');
$byLocation = groupRows($rows, fn($r)=>trim(($r['nama_ruangan'] ?: 'Tanpa ruangan').' '.($r['gedung_blok'] ?: '')));
$byStudent = groupRows($rows, fn($r)=>($r['nama_lengkap'] ?: 'Pengguna tidak diketahui').' ('.($r['nomor_induk'] ?: '-').')');
$byDate = groupRows($rows, fn($r)=>date('d-m-Y', strtotime($r['created_at'])));
$total=count($rows); $today=0; foreach($rows as $r){ if(date('Y-m-d',strtotime($r['created_at']))===date('Y-m-d')) $today++; }
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Laporan Aspirasi</title><link rel="stylesheet" href="../../asset/css/style.css"><style>
.report-wrap{max-width:1500px;margin:auto}.report-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px;margin:22px 0}.report-stat,.report-card{background:var(--card-bg,#fff);border:1px solid var(--border-color,#e5e7eb);border-radius:16px;padding:18px;box-shadow:0 4px 18px rgba(0,0,0,.04)}.report-stat small{display:block;color:var(--text-muted,#64748b);margin-bottom:8px}.report-stat strong{font-size:28px}.report-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.report-card h2{font-size:18px;margin:0 0 5px}.report-card p{margin:0 0 14px;color:var(--text-muted,#64748b);font-size:13px}.report-table-wrap{overflow-x:auto}.report-table{width:100%;border-collapse:collapse;font-size:13px}.report-table th,.report-table td{text-align:left;padding:10px 9px;border-bottom:1px solid var(--border-color,#e5e7eb)}.report-table th{color:var(--text-muted,#64748b);font-weight:700}.report-count{font-weight:700}.report-all{margin-top:18px}.report-badge{display:inline-block;padding:4px 9px;border-radius:20px;background:#eef2ff;color:#4338ca;font-size:12px}.report-empty{text-align:center;padding:24px;color:#64748b}.report-head{display:flex;justify-content:space-between;gap:15px;align-items:center;flex-wrap:wrap}.report-head a{color:var(--primary,#4f46e5);font-weight:700;text-decoration:none}@media(max-width:850px){.report-grid{grid-template-columns:1fr}}
</style></head><body class="admin-dashboard">
<?php include __DIR__.'/_sidebar.php'; ?>
<main class="admin-main"><div class="admin-page report-wrap">
<header class="admin-header"><div><p>REKAPITULASI DATA</p><h1>Laporan Aspirasi</h1><div class="admin-page-subtitle">Daftar aspirasi dikelompokkan berdasarkan kategori, tanggal, lokasi, dan siswa.</div></div><div class="admin-header-actions"><button type="button" id="themeToggle" class="admin-theme-btn" aria-label="Ganti tema">◐</button><div class="admin-header-right"><strong><?=h($_SESSION['admin_nama']??'Administrator')?></strong><span>ADMINISTRATOR</span></div></div></header>
<div class="report-stats"><div class="report-stat"><small>Total seluruh aspirasi</small><strong><?=number_format($total)?></strong></div><div class="report-stat"><small>Aspirasi masuk hari ini</small><strong><?=number_format($today)?></strong></div><div class="report-stat"><small>Jumlah kategori terpakai</small><strong><?=count($byCategory)?></strong></div><div class="report-stat"><small>Jumlah lokasi terpakai</small><strong><?=count($byLocation)?></strong></div></div>
<div class="report-grid">
<?php $sections=[['Aspirasi per Kategori',$byCategory,'Nama kategori'],['Aspirasi per Tanggal',$byDate,'Tanggal pengaduan'],['Aspirasi per Lokasi',$byLocation,'Lokasi / gedung'],['Aspirasi per Siswa',$byStudent,'Nama siswa / nomor induk']]; foreach($sections as [$title,$groups,$label]): ?>
<section class="report-card"><h2><?=h($title)?></h2><p>Jumlah aspirasi yang tercatat pada setiap <?=strtolower(h($label))?>.</p><div class="report-table-wrap"><table class="report-table"><thead><tr><th><?=h($label)?></th><th>Jumlah aspirasi</th></tr></thead><tbody>
<?php if(!$groups): ?><tr><td colspan="2" class="report-empty">Belum ada data aspirasi.</td></tr><?php else: foreach($groups as $g): ?><tr><td><?=h($g['label'])?></td><td class="report-count"><span class="report-badge"><?=number_format($g['jumlah'])?> aspirasi</span></td></tr><?php endforeach; endif; ?>
</tbody></table></div></section><?php endforeach; ?>
</div>
<section class="report-card report-all"><div class="report-head"><div><h2>Rincian seluruh aspirasi</h2><p>Data lengkap untuk menelusuri setiap laporan.</p></div><a href="pengaduan.php">Kelola pengaduan →</a></div><div class="report-table-wrap"><table class="report-table"><thead><tr><th>Tanggal</th><th>Judul aspirasi</th><th>Kategori</th><th>Lokasi</th><th>Siswa</th><th>Status</th><th>Biaya</th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="7" class="report-empty">Belum ada aspirasi yang tercatat.</td></tr><?php else: foreach($rows as $r): ?><tr><td><?=h(date('d-m-Y H:i',strtotime($r['created_at'])))?></td><td><?=h($r['judul_pengaduan'])?></td><td><?=h($r['nama_kategori']??'Tanpa kategori')?></td><td><?=h(($r['nama_ruangan']??'-').' '.($r['gedung_blok']??''))?></td><td><?=h($r['nama_lengkap']??'-')?></td><td><?=h(ucfirst($r['status']))?></td><td>Rp <?=number_format((float)($r['biaya']??0),0,',','.')?></td></tr><?php endforeach; endif; ?>
</tbody></table></div></section>
</div></main><script src="../../asset/js/script.js"></script></body></html>
