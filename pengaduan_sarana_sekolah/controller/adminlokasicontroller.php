<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../model/lokasi.php';
$lokasiModel=new Lokasi(); $action=$_POST['action']??'';
function goLokasi($msg='success'){ header('Location: ../view/admin/lokasi.php?msg='.urlencode($msg)); exit; }
if($action==='tambah'){
 $data=['nama_ruangan'=>trim($_POST['nama_ruangan']??''),'gedung_blok'=>trim($_POST['gedung_blok']??'')];
 goLokasi($lokasiModel->create($data)?'Lokasi berhasil ditambahkan.':'Gagal menambahkan lokasi.');
}
if($action==='edit'){
 $data=['lokasi_id'=>(int)($_POST['lokasi_id']??0),'nama_ruangan'=>trim($_POST['nama_ruangan']??''),'gedung_blok'=>trim($_POST['gedung_blok']??'')];
 goLokasi($lokasiModel->update($data)?'Lokasi berhasil diperbarui.':'Gagal memperbarui lokasi.');
}
if($action==='hapus'){
 goLokasi($lokasiModel->deleteSafe((int)($_POST['lokasi_id']??0))?'Lokasi berhasil dihapus.':'Lokasi tidak dapat dihapus karena masih digunakan pengaduan.');
}
goLokasi();
?>