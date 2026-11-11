<?php

require_once __DIR__ . '/../config/koneksi.php';

class Admin
{
    private $db;

    public function __construct()
    {
        $koneksi = new Koneksi();
        $this->db = $koneksi->koneksi;
    }

    public function login($username)
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM admin
             WHERE nama_admin = ?
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}