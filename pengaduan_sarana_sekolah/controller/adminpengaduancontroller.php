<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../model/pengaduan.php';
require_once __DIR__ . '/../model/penanganan.php';

$pengaduanModel = new Pengaduan();
$penangananModel = new Penanganan();

function kembali($pesan)
{
    header(
        "Location: ../view/admin/pengaduan.php?msg=" .
        urlencode($pesan)
    );
    exit;
}

$action = $_POST['action'] ?? '';


// ==========================
// UBAH STATUS
// ==========================

if ($action === 'status') {

    $id = (int)($_POST['pengaduan_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if ($pengaduanModel->updateStatus($id, $status)) {
        kembali("Status berhasil diperbarui menjadi " . $status);
    }

    kembali("Gagal memperbarui status.");
}


// ==========================
// VERIFIKASI
// ==========================

if ($action === 'verifikasi') {

    $id = (int)($_POST['pengaduan_id'] ?? 0);

    if ($pengaduanModel->updateStatus($id, 'pending')) {
        kembali("Pengaduan berhasil diverifikasi.");
    }

    kembali("Verifikasi gagal.");
}


// ==========================
// NAIKKAN URGENSI (TIDAK BOLEH TURUN)
// ==========================

if ($action === 'urgensi') {
    $id = (int)($_POST['pengaduan_id'] ?? 0);
    $urgensi = $_POST['urgensi'] ?? '';
    if ($pengaduanModel->naikkanUrgensi($id, $urgensi)) {
        kembali('Tingkat urgensi berhasil dinaikkan.');
    }
    kembali('Urgensi tidak berubah. Nilai hanya boleh dinaikkan, bukan diturunkan.');
}


// ==========================
// HAPUS PENGADUAN
// ==========================

if ($action === 'hapus') {

    $id = (int)($_POST['pengaduan_id'] ?? 0);

    if ($pengaduanModel->hapus($id)) {
        kembali("Pengaduan berhasil dihapus.");
    }

    kembali("Pengaduan gagal dihapus.");
}


// ==========================
// SIMPAN PENANGANAN
// ==========================

if ($action === 'penanganan') {

    $id = (int)($_POST['pengaduan_id'] ?? 0);

    if ($id <= 0) {
        kembali("ID pengaduan tidak valid.");
    }

    $tanggalMulai = $_POST['tanggal_mulai'] ?? date('Y-m-d');
    $tanggalSelesai = $_POST['tanggal_selesai'] ?? '';

    if ($tanggalSelesai !== '' && $tanggalSelesai < $tanggalMulai) {
        kembali("Tanggal selesai tidak boleh sebelum tanggal mulai.");
    }

    $foto = '';

    if (!empty($_FILES['foto_selesai']['name'])) {

        if ($_FILES['foto_selesai']['error'] !== UPLOAD_ERR_OK) {
            kembali("Upload foto gagal.");
        }

        if ($_FILES['foto_selesai']['size'] > 5 * 1024 * 1024) {
            kembali("Ukuran foto maksimal 5 MB.");
        }

        $mime = mime_content_type(
            $_FILES['foto_selesai']['tmp_name']
        );

        $ekstensi = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($ekstensi[$mime])) {
            kembali("Format foto harus JPG, PNG, atau WEBP.");
        }

        $folder = __DIR__ . '/../asset/uploads/penanganan/';

        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }

        $namaFoto = time() . '_' .
            bin2hex(random_bytes(5)) . '.' .
            $ekstensi[$mime];

        if (!move_uploaded_file(
            $_FILES['foto_selesai']['tmp_name'],
            $folder . $namaFoto
        )) {
            kembali("Foto gagal disimpan.");
        }

        $foto = $namaFoto;
    }

    $statusSetelah = $_POST['status_setelah'] ?? 'diproses';

    if (!in_array($statusSetelah, ['diproses', 'selesai'], true)) {
        kembali("Status penanganan tidak valid.");
    }

    $data = [
        'pengaduan_id' => $id,
        'admin_id' => (int)$_SESSION['admin_id'],
        'tanggal_mulai' => $tanggalMulai,
        'tanggal_selesai' => $tanggalSelesai ?: null,
        'catatan_perbaikan' => trim(
            $_POST['catatan_perbaikan'] ?? ''
        ),
        'foto_selesai' => $foto,
        'biaya' => (float)($_POST['biaya'] ?? 0)
    ];

    if ($penangananModel->simpan($data)) {

        if ($pengaduanModel->updateStatus($id, $statusSetelah)) {
            kembali("Umpan balik dan progres perbaikan berhasil disimpan.");
        }

        kembali("Penanganan tersimpan, tetapi status gagal diperbarui.");
    }

    kembali("Gagal menyimpan penanganan.");
}

kembali("Aksi tidak dikenali.");

?>