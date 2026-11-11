<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Fitur umpan balik hanya dapat diberikan oleh Admin.
// Siswa hanya dapat melihat umpan balik Admin.

header("Location: ../view/user/riwayat.php");
exit;

?>