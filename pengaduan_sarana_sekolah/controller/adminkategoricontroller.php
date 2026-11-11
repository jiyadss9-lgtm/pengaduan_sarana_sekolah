<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../model/kategori.php';

$model = new Kategori();
$action = $_POST['action'] ?? '';

function goKategori($msg = 'success')
{
    header('Location: ../view/admin/kategori.php?msg=' . urlencode($msg));
    exit;
}

if ($action === 'tambah') {
    $nama = trim($_POST['nama_kategori'] ?? '');

    if ($nama === '') {
        goKategori('Nama kategori wajib diisi.');
    }

    goKategori(
        $model->create($nama)
            ? 'Kategori berhasil ditambahkan.'
            : 'Kategori gagal ditambahkan. Pastikan nama kategori belum ada.'
    );
}

if ($action === 'edit') {
    $data = [
        'kategori_id' => (int)($_POST['kategori_id'] ?? 0),
        'nama_kategori' => trim($_POST['nama_kategori'] ?? '')
    ];

    if ($data['nama_kategori'] === '') {
        goKategori('Nama kategori wajib diisi.');
    }

    goKategori(
        $model->update($data)
            ? 'Kategori berhasil diperbarui.'
            : 'Kategori gagal diperbarui. Pastikan nama kategori belum ada.'
    );
}

if ($action === 'hapus') {
    $id = (int)($_POST['kategori_id'] ?? 0);

    goKategori(
        $model->deleteSafe($id)
            ? 'Kategori berhasil dihapus.'
            : 'Kategori tidak dapat dihapus karena masih digunakan oleh pengaduan.'
    );
}

goKategori();
