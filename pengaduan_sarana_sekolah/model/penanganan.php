<?php

require_once __DIR__ . '/../config/koneksi.php';

class Penanganan
{
    private $db;

    public function __construct()
    {
        $koneksi = new Koneksi();
        $this->db = $koneksi->koneksi;
    }

    public function getByPengaduan($id)
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM penanganan
             WHERE pengaduan_id = ?
             LIMIT 1"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function simpan($data)
    {
        $id = (int)$data['pengaduan_id'];

        $lama = $this->getByPengaduan($id);

        $foto = !empty($data['foto_selesai'])
            ? $data['foto_selesai']
            : ($lama['foto_selesai'] ?? null);

        $tanggalSelesai = $data['tanggal_selesai'] ?: null;

        if ($lama) {

            $stmt = $this->db->prepare(
                "UPDATE penanganan SET
                    admin_id = ?,
                    tanggal_mulai = ?,
                    tanggal_selesai = ?,
                    catatan_perbaikan = ?,
                    foto_selesai = ?,
                    biaya = ?
                 WHERE pengaduan_id = ?"
            );

            $stmt->bind_param(
                "issssdi",
                $data['admin_id'],
                $data['tanggal_mulai'],
                $tanggalSelesai,
                $data['catatan_perbaikan'],
                $foto,
                $data['biaya'],
                $id
            );

        } else {

            $stmt = $this->db->prepare(
                "INSERT INTO penanganan
                (
                    pengaduan_id,
                    admin_id,
                    tanggal_mulai,
                    tanggal_selesai,
                    catatan_perbaikan,
                    foto_selesai,
                    biaya
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iissssd",
                $id,
                $data['admin_id'],
                $data['tanggal_mulai'],
                $tanggalSelesai,
                $data['catatan_perbaikan'],
                $foto,
                $data['biaya']
            );
        }

        return $stmt->execute();
    }
}