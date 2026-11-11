<?php

session_start();

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../model/admin.php';

$userModel = new User();
$adminModel = new Admin();

$action = $_POST['action'] ?? '';


/* =========================
   LOGIN
========================= */

if ($action === 'login') {

    $username =
        trim($_POST['username'] ?? '');

    $password =
        $_POST['password'] ?? '';


    if (
        $username === '' ||
        $password === ''
    ) {
        header(
            "Location: ../index.php?error=empty"
        );
        exit;
    }


    /* ADMIN */

    $admin =
        $adminModel->login($username);

    if ($admin) {

        $benar =
            password_verify(
                $password,
                $admin['password']
            ) ||
            $password === $admin['password'];

        if ($benar) {

            $_SESSION['admin_id'] =
                $admin['admin_id'];

            $_SESSION['admin_nama'] =
                $admin['nama_admin'];

            header(
                "Location: ../view/admin/dashboard.php"
            );

            exit;
        }
    }


    /* USER */

    $user =
        $userModel->login($username);

    if ($user) {

        $benar =
            password_verify(
                $password,
                $user['password']
            ) ||
            $password === $user['password'];

        if ($benar) {

            $_SESSION['user_id'] =
                $user['user_id'];

            $_SESSION['user_nama'] =
                $user['nama_lengkap'];

            $_SESSION['user_role'] =
                $user['role'];

            $_SESSION['user_email'] =
                $user['email'];

            $_SESSION['user_kelas'] =
                $user['kelas'] ?? '';

            header(
                "Location: ../view/user/dashboard.php"
            );

            exit;
        }
    }


    header(
        "Location: ../index.php?error=login"
    );

    exit;
}


/* =========================
   REGISTER
========================= */

if ($action === 'register') {

    $data = [
        'nama_lengkap' =>
            trim($_POST['nama_lengkap'] ?? ''),

        'nomor_induk' =>
            trim($_POST['nomor_induk'] ?? ''),

        'role' =>
            $_POST['role'] ?? '',

        'email' =>
            trim($_POST['email'] ?? ''),

        'kelas' =>
            trim($_POST['kelas'] ?? ''),

        'no_whatsapp' =>
            trim($_POST['no_whatsapp'] ?? ''),

        'password' =>
            $_POST['password'] ?? ''
    ];


    $konfirmasi =
        $_POST['konfirmasi_password']
        ?? '';


    if (
        $data['nama_lengkap'] === '' ||
        $data['nomor_induk'] === '' ||
        $data['email'] === '' ||
        $data['password'] === ''
    ) {

        header(
            "Location: ../view/auth/register.php?error=empty"
        );

        exit;
    }


    if (!in_array($data['role'], ['siswa', 'guru'], true)) {

        header(
            "Location: ../view/auth/register.php?error=role"
        );

        exit;
    }


    // Siswa wajib mengisi kelas; guru tidak memiliki kelas.
    if ($data['role'] === 'siswa' && $data['kelas'] === '') {

        header(
            "Location: ../view/auth/register.php?error=kelas"
        );

        exit;
    }

    if ($data['role'] !== 'siswa') {
        $data['kelas'] = '';
    }


    if (
        $data['password'] !==
        $konfirmasi
    ) {

        header(
            "Location: ../view/auth/register.php?error=password"
        );

        exit;
    }


    if (
        $userModel->emailExists(
            $data['email']
        )
    ) {

        header(
            "Location: ../view/auth/register.php?error=email"
        );

        exit;
    }


    if (
        $userModel->nomorExists(
            $data['nomor_induk']
        )
    ) {

        header(
            "Location: ../view/auth/register.php?error=nomor"
        );

        exit;
    }


    if (
        $userModel->register($data)
    ) {

        header(
            "Location: ../index.php?register=success"
        );

        exit;
    }


    header(
        "Location: ../view/auth/register.php?error=failed"
    );

    exit;
}


header(
    "Location: ../index.php"
);

exit;