<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/../model/user.php';
$userModel=new User(); $action=$_POST['action']??'';
function goUser($msg='success'){ header('Location: ../view/admin/users.php?msg='.urlencode($msg)); exit; }
if($action==='tambah'){
 $data=['nama_lengkap'=>trim($_POST['nama_lengkap']??''),'nomor_induk'=>trim($_POST['nomor_induk']??''),'role'=>$_POST['role']??'siswa','email'=>trim($_POST['email']??''),'kelas'=>(($_POST['role']??'siswa')==='siswa'?trim($_POST['kelas']??''):''),'no_whatsapp'=>trim($_POST['no_whatsapp']??''),'password'=>$_POST['password']??''];
 goUser($userModel->register($data)?'User berhasil ditambahkan.':'Gagal menambahkan user.');
}
if($action==='edit'){
 $data=['user_id'=>(int)($_POST['user_id']??0),'nama_lengkap'=>trim($_POST['nama_lengkap']??''),'nomor_induk'=>trim($_POST['nomor_induk']??''),'role'=>$_POST['role']??'siswa','email'=>trim($_POST['email']??''),'kelas'=>(($_POST['role']??'siswa')==='siswa'?trim($_POST['kelas']??''):''),'no_whatsapp'=>trim($_POST['no_whatsapp']??''),'password'=>$_POST['password']??''];
 goUser($userModel->update($data)?'User berhasil diperbarui.':'Gagal memperbarui user.');
}
if($action==='hapus'){
 goUser($userModel->deleteSafe((int)($_POST['user_id']??0))?'User berhasil dihapus.':'User tidak dapat dihapus karena masih memiliki pengaduan.');
}
goUser();
?>