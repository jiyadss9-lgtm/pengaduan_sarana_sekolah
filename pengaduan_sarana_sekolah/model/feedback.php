<?php

require_once __DIR__ . '/../config/koneksi.php';

class Feedback
{
    private $db;

    public function __construct()
    {
        $koneksi = new Koneksi();
        $this->db = $koneksi->koneksi;
    }

    private function tersedia()
    {
        try {
            $result = $this->db->query("SHOW TABLES LIKE 'feedback'");
            return $result && $result->num_rows > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getByPengaduan($pengaduan_id)
    {
        if (!$this->tersedia()) return null;
        try {
            $stmt = $this->db->prepare("SELECT * FROM feedback WHERE pengaduan_id=? LIMIT 1");
            $stmt->bind_param("i", $pengaduan_id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $data ?: null;
        } catch (Throwable $e) { return null; }
    }

    public function getByUser($user_id)
    {
        if (!$this->tersedia()) return false;
        try {
            $stmt = $this->db->prepare("SELECT f.*, p.judul_pengaduan FROM feedback f JOIN pengaduan p ON p.pengaduan_id=f.pengaduan_id WHERE p.user_id=? ORDER BY f.feedback_id DESC");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            return $stmt->get_result();
        } catch (Throwable $e) { return false; }
    }

    public function getAllDetail()
    {
        if (!$this->tersedia()) return false;
        $sql = "
            SELECT
                f.feedback_id,
                f.rating,
                f.komentar,
                f.created_at,
                p.pengaduan_id,
                p.judul_pengaduan,
                u.nama_lengkap,
                u.role,
                u.kelas,
                k.nama_kategori,
                l.nama_ruangan
            FROM feedback f
            JOIN pengaduan p ON p.pengaduan_id = f.pengaduan_id
            LEFT JOIN users u ON u.user_id = p.user_id
            LEFT JOIN kategori k ON k.kategori_id = p.kategori_id
            LEFT JOIN lokasi l ON l.lokasi_id = p.lokasi_id
            ORDER BY f.created_at DESC, f.feedback_id DESC
        ";

        try { return $this->db->query($sql); } catch (Throwable $e) { return false; }
    }

    public function simpan($pengaduan_id, $rating, $komentar)
    {
        if (!$this->tersedia()) return false;
        $cek = $this->db->prepare(
            "SELECT feedback_id FROM feedback WHERE pengaduan_id = ?"
        );
        $cek->bind_param("i", $pengaduan_id);
        $cek->execute();

        if ($cek->get_result()->num_rows > 0) {
            $stmt = $this->db->prepare(
                "UPDATE feedback SET rating = ?, komentar = ? WHERE pengaduan_id = ?"
            );
            $stmt->bind_param("isi", $rating, $komentar, $pengaduan_id);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO feedback (pengaduan_id, rating, komentar) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("iis", $pengaduan_id, $rating, $komentar);
        }

        return $stmt->execute();
    }
}