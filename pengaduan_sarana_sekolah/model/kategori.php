<?php
require_once __DIR__ . '/../config/koneksi.php';

class Kategori
{
    private $db;

    public function __construct()
    {
        $koneksi = new Koneksi();
        $this->db = $koneksi->koneksi;
    }

    public function getAll()
    {
        return $this->db->query(
            "SELECT k.kategori_id, k.nama_kategori,
                    COUNT(p.pengaduan_id) AS jumlah_pengaduan
             FROM kategori k
             LEFT JOIN pengaduan p ON p.kategori_id = k.kategori_id
             GROUP BY k.kategori_id, k.nama_kategori
             ORDER BY k.nama_kategori ASC"
        );
    }

    public function create($nama)
    {
        $nama = trim($nama);
        if ($nama === '') return false;

        $cek = $this->db->prepare(
            "SELECT kategori_id FROM kategori WHERE LOWER(nama_kategori) = LOWER(?) LIMIT 1"
        );
        if (!$cek) return false;
        $cek->bind_param("s", $nama);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) return false;

        $stmt = $this->db->prepare(
            "INSERT INTO kategori (nama_kategori) VALUES (?)"
        );
        if (!$stmt) return false;
        $stmt->bind_param("s", $nama);
        return $stmt->execute();
    }

    public function update($data)
    {
        $id = (int)($data['kategori_id'] ?? 0);
        $nama = trim($data['nama_kategori'] ?? '');
        if ($id <= 0 || $nama === '') return false;

        $cek = $this->db->prepare(
            "SELECT kategori_id FROM kategori
             WHERE LOWER(nama_kategori) = LOWER(?) AND kategori_id <> ?
             LIMIT 1"
        );
        if (!$cek) return false;
        $cek->bind_param("si", $nama, $id);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) return false;

        $stmt = $this->db->prepare(
            "UPDATE kategori SET nama_kategori = ? WHERE kategori_id = ?"
        );
        if (!$stmt) return false;
        $stmt->bind_param("si", $nama, $id);
        return $stmt->execute();
    }

    public function deleteSafe($id)
    {
        $id = (int)$id;
        if ($id <= 0) return false;

        $cek = $this->db->prepare(
            "SELECT COUNT(*) AS jumlah FROM pengaduan WHERE kategori_id = ?"
        );
        if (!$cek) return false;
        $cek->bind_param("i", $id);
        $cek->execute();
        $row = $cek->get_result()->fetch_assoc();

        // Kategori yang sudah dipakai tidak dihapus agar riwayat pengaduan tetap aman.
        if ((int)($row['jumlah'] ?? 0) > 0) return false;

        $stmt = $this->db->prepare(
            "DELETE FROM kategori WHERE kategori_id = ?"
        );
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
