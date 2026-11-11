<?php

require_once __DIR__ . '/../config/koneksi.php';

class User
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
             FROM users
             WHERE nomor_induk = ?
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function register($data)
    {
        $password = password_hash(
            $data['password'],
            PASSWORD_DEFAULT
        );
        $kelas = (string)($data['kelas'] ?? '');

        $stmt = $this->db->prepare(
            "INSERT INTO users
            (
                nama_lengkap,
                nomor_induk,
                role,
                email,
                kelas,
                no_whatsapp,
                password
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sssssss",
            $data['nama_lengkap'],
            $data['nomor_induk'],
            $data['role'],
            $data['email'],
            $kelas,
            $data['no_whatsapp'],
            $password
        );

        return $stmt->execute();
    }

    public function getAll()
    {
        return $this->db->query("SELECT user_id, nama_lengkap, nomor_induk, role, email, kelas, no_whatsapp FROM users ORDER BY nama_lengkap ASC");
    }

    public function update($data)
    {
        $kelas = (string)($data['kelas'] ?? '');
        if (!empty($data['password'])) {
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET nama_lengkap=?, nomor_induk=?, role=?, email=?, kelas=?, no_whatsapp=?, password=? WHERE user_id=?");
            if (!$stmt) return false;
            $stmt->bind_param("sssssssi", $data['nama_lengkap'], $data['nomor_induk'], $data['role'], $data['email'], $kelas, $data['no_whatsapp'], $password, $data['user_id']);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET nama_lengkap=?, nomor_induk=?, role=?, email=?, kelas=?, no_whatsapp=? WHERE user_id=?");
            if (!$stmt) return false;
            $stmt->bind_param("ssssssi", $data['nama_lengkap'], $data['nomor_induk'], $data['role'], $data['email'], $kelas, $data['no_whatsapp'], $data['user_id']);
        }
        return $stmt->execute();
    }

    public function deleteSafe($id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS jumlah FROM pengaduan WHERE user_id=?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ((int)$row['jumlah'] > 0) return false;
        $stmt = $this->db->prepare("DELETE FROM users WHERE user_id=?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT user_id, nama_lengkap, nomor_induk, role, email, kelas, no_whatsapp FROM users WHERE user_id=?");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function emailExists($email)
    {
        $stmt = $this->db->prepare(
            "SELECT user_id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function nomorExists($nomor)
    {
        $stmt = $this->db->prepare(
            "SELECT user_id
             FROM users
             WHERE nomor_induk = ?
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $nomor);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }
}