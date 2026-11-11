<?php

session_start();

require_once __DIR__ . '/../model/pengaduan.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$model = new Pengaduan();

$action =
    $_POST['action'] ?? '';

$userId =
    $_SESSION['user_id'];


/* =========================
   UPLOAD FOTO
========================= */

function uploadFoto()
{
    if (
        !isset($_FILES['foto_bukti']) ||
        $_FILES['foto_bukti']['error']
        === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    if (
        $_FILES['foto_bukti']['error']
        !== UPLOAD_ERR_OK
    ) {
        return false;
    }

    if (
        $_FILES['foto_bukti']['size']
        > 5 * 1024 * 1024
    ) {
        return false;
    }

    $allowed = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    $ext = strtolower(
        pathinfo(
            $_FILES['foto_bukti']['name'],
            PATHINFO_EXTENSION
        )
    );

    if (!in_array($ext, $allowed, true)) {
        return false;
    }

    $folder =
        __DIR__ . '/../asset/uploads/bukti/';

    if (!is_dir($folder)) {
        mkdir(
            $folder,
            0777,
            true
        );
    }

    $nama =
        time() .
        '_' .
        bin2hex(
            random_bytes(4)
        ) .
        '.' .
        $ext;

    if (
        !move_uploaded_file(
            $_FILES['foto_bukti']['tmp_name'],
            $folder . $nama
        )
    ) {
        return false;
    }

    return $nama;
}


/* =========================
   TAMBAH
========================= */

if ($action === 'tambah') {

    $foto = uploadFoto();

    if ($foto === false) {

        header(
            "Location: ../view/user/pengaduan.php?msg=foto"
        );

        exit;
    }


    $data = [
        'user_id' =>
            $userId,

        'lokasi_id' =>
            (int)($_POST['lokasi_id'] ?? 0),

        'kategori_id' =>
            (int)($_POST['kategori_id'] ?? 0),

        'judul_pengaduan' =>
            trim(
                $_POST['judul_pengaduan']
                ?? ''
            ),

        'deskripsi' =>
            trim(
                $_POST['deskripsi']
                ?? ''
            ),

        'foto_bukti' =>
            $foto ?? '',

        'urgensi' =>
            $_POST['urgensi']
            ?? 'sedang'
    ];


    $ok =
        $model->tambah($data);


    header(
        "Location: ../view/user/pengaduan.php?msg=" .
        ($ok ? 'created' : 'failed')
    );

    exit;
}


/* =========================
   EDIT
========================= */

if ($action === 'edit') {

    $foto = uploadFoto();

    if ($foto === false) {

        header(
            "Location: ../view/user/pengaduan.php?msg=foto"
        );

        exit;
    }


    $data = [
        'pengaduan_id' =>
            (int)(
                $_POST['pengaduan_id']
                ?? 0
            ),

        'user_id' =>
            $userId,

        'lokasi_id' =>
            (int)(
                $_POST['lokasi_id']
                ?? 0
            ),

        'kategori_id' =>
            (int)(
                $_POST['kategori_id']
                ?? 0
            ),

        'judul_pengaduan' =>
            trim(
                $_POST['judul_pengaduan']
                ?? ''
            ),

        'deskripsi' =>
            trim(
                $_POST['deskripsi']
                ?? ''
            ),

        'foto_bukti' =>
            $foto,

        'urgensi' =>
            $_POST['urgensi']
            ?? 'sedang'
    ];


    $ok =
        $model->edit($data);


    header(
        "Location: ../view/user/pengaduan.php?msg=" .
        ($ok ? 'updated' : 'failed')
    );

    exit;
}


/* =========================
   HAPUS
========================= */

if ($action === 'hapus') {

    $id =
        (int)(
            $_POST['pengaduan_id']
            ?? 0
        );


    $ok =
        $model->hapusUser(
            $id,
            $userId
        );


    header(
        "Location: ../view/user/pengaduan.php?msg=" .
        ($ok ? 'deleted' : 'cannot_delete')
    );

    exit;
}


header(
    "Location: ../view/user/pengaduan.php"
);

exit;