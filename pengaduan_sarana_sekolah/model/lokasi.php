<?php

require_once __DIR__ . '/../config/koneksi.php';

class Lokasi
{
    private $db;

    public function __construct()
    {
        $koneksi = new Koneksi();
        $this->db = $koneksi->koneksi;
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO lokasi (nama_ruangan, gedung_blok) VALUES (?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("ss", $data['nama_ruangan'], $data['gedung_blok']);
        return $stmt->execute();
    }

    public function update($data)
    {
        $stmt = $this->db->prepare("UPDATE lokasi SET nama_ruangan=?, gedung_blok=? WHERE lokasi_id=?");
        if (!$stmt) return false;
        $stmt->bind_param("ssi", $data['nama_ruangan'], $data['gedung_blok'], $data['lokasi_id']);
        return $stmt->execute();
    }

    public function deleteSafe($id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS jumlah FROM pengaduan WHERE lokasi_id=?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ((int)$row['jumlah'] > 0) return false;
        $stmt = $this->db->prepare("DELETE FROM lokasi WHERE lokasi_id=?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getAll()
    {
        return $this->db->query(
            "SELECT *
             FROM lokasi
             ORDER BY nama_ruangan ASC"
        );
    }
}