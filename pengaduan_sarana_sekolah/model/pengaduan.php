<?php

require_once __DIR__ . '/../config/koneksi.php';

class Pengaduan
{
    private $db;

    public function __construct()
    {
        $koneksi = new Koneksi();
        $this->db = $koneksi->koneksi;
    }


    /* =========================
       USER
    ========================= */

    public function getUser($userId)
    {
        $sql = "
            SELECT
                p.*,
                k.nama_kategori,
                l.nama_ruangan,
                l.gedung_blok
            FROM pengaduan p
            LEFT JOIN kategori k
                ON p.kategori_id = k.kategori_id
            LEFT JOIN lokasi l
                ON p.lokasi_id = l.lokasi_id
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result();
    }


    public function getByIdUser($id, $userId)
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM pengaduan
             WHERE pengaduan_id = ?
             AND user_id = ?
             LIMIT 1"
        );

        $stmt->bind_param(
            "ii",
            $id,
            $userId
        );

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }


    public function tambah($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO pengaduan
            (
                user_id,
                lokasi_id,
                kategori_id,
                judul_pengaduan,
                deskripsi,
                foto_bukti,
                urgensi
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "iiissss",
            $data['user_id'],
            $data['lokasi_id'],
            $data['kategori_id'],
            $data['judul_pengaduan'],
            $data['deskripsi'],
            $data['foto_bukti'],
            $data['urgensi']
        );

        return $stmt->execute();
    }


    public function edit($data)
    {
        // Urgensi sengaja tidak diubah melalui form siswa.
        // Dengan demikian nilai urgensi tidak dapat diturunkan setelah verifikasi.
        if (!empty($data['foto_bukti'])) {
            $stmt = $this->db->prepare(
                "UPDATE pengaduan SET lokasi_id = ?, kategori_id = ?,
                 judul_pengaduan = ?, deskripsi = ?, foto_bukti = ?
                 WHERE pengaduan_id = ? AND user_id = ? AND status = 'baru'"
            );
            if (!$stmt) return false;
            $stmt->bind_param("iisssii", $data['lokasi_id'], $data['kategori_id'],
                $data['judul_pengaduan'], $data['deskripsi'], $data['foto_bukti'],
                $data['pengaduan_id'], $data['user_id']);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE pengaduan SET lokasi_id = ?, kategori_id = ?,
                 judul_pengaduan = ?, deskripsi = ?
                 WHERE pengaduan_id = ? AND user_id = ? AND status = 'baru'"
            );
            if (!$stmt) return false;
            $stmt->bind_param("iissii", $data['lokasi_id'], $data['kategori_id'],
                $data['judul_pengaduan'], $data['deskripsi'],
                $data['pengaduan_id'], $data['user_id']);
        }
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function hapusUser($id, $userId)
    {
        $data = $this->getByIdUser(
            $id,
            $userId
        );

        if (!$data) {
            return false;
        }

        if (
            !in_array(
                $data['status'],
                ['baru', 'pending'],
                true
            )
        ) {
            return false;
        }

        return $this->hapus($id);
    }


    /* =========================
       ADMIN
    ========================= */

    public function getAll()
    {
        $sql = "
            SELECT
                p.*,
                u.nama_lengkap,
                k.nama_kategori,
                l.nama_ruangan,
                l.gedung_blok
            FROM pengaduan p
            LEFT JOIN users u
                ON p.user_id = u.user_id
            LEFT JOIN kategori k
                ON p.kategori_id = k.kategori_id
            LEFT JOIN lokasi l
                ON p.lokasi_id = l.lokasi_id
            ORDER BY p.created_at DESC
        ";

        return $this->db->query($sql);
    }


   

public function updateStatus($id, $status)
{
    $allowed = ['baru', 'pending', 'diproses', 'selesai', 'ditolak'];
    $id = (int)$id;
    $status = strtolower(trim($status));
    if ($id <= 0 || !in_array($status, $allowed, true)) return false;

    // Pengaduan yang sudah diverifikasi tidak boleh dikembalikan ke status "baru".
    $stmt = $this->db->prepare(
        "UPDATE pengaduan SET status = ?
         WHERE pengaduan_id = ?
           AND FIELD(status, 'baru','pending','diproses','selesai','ditolak')
               <= FIELD(?, 'baru','pending','diproses','selesai','ditolak')"
    );
    if (!$stmt) {
        error_log('Prepare update status gagal: ' . $this->db->error);
        return false;
    }
    $stmt->bind_param('sis', $status, $id, $status);
    $ok = $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    if (!$ok) return false;
    // affected_rows = 0 juga dapat berarti nilai status memang sudah sama.
    $cek = $this->db->prepare("SELECT status FROM pengaduan WHERE pengaduan_id = ? LIMIT 1");
    if (!$cek) return false;
    $cek->bind_param('i', $id);
    $cek->execute();
    $cek->bind_result($saved);
    $found = $cek->fetch();
    $cek->close();
    return $found && $saved === $status;
}

public function naikkanUrgensi($id, $urgensi)
{
    $id = (int)$id;
    $urgensi = strtolower(trim($urgensi));
    if ($id <= 0 || !in_array($urgensi, ['rendah', 'sedang', 'tinggi'], true)) return false;

    // FIELD() memastikan urgensi hanya dapat bergerak naik, pada status apa pun.
    // Tidak memerlukan kolom tambahan pada database.
    $stmt = $this->db->prepare(
        "UPDATE pengaduan SET urgensi = ?
         WHERE pengaduan_id = ?
           AND FIELD(urgensi, 'rendah','sedang','tinggi')
               < FIELD(?, 'rendah','sedang','tinggi')"
    );
    if (!$stmt) return false;
    $stmt->bind_param('sis', $urgensi, $id, $urgensi);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

public function hapus($id)
{
    $id = (int)$id;

    if ($id <= 0) {
        return false;
    }

    try {
        $this->db->begin_transaction();

        // Tabel feedback tidak ada pada sebagian database lama.
        // Hapus hanya jika tabel tersebut memang tersedia.
        $cekFeedback = $this->db->query("SHOW TABLES LIKE 'feedback'");
        if ($cekFeedback && $cekFeedback->num_rows > 0) {
            $stmt = $this->db->prepare("DELETE FROM feedback WHERE pengaduan_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Hapus data penanganan
        $stmt = $this->db->prepare(
            "DELETE FROM penanganan WHERE pengaduan_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Hapus pengaduan
        $stmt = $this->db->prepare(
            "DELETE FROM pengaduan WHERE pengaduan_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $terhapus = $stmt->affected_rows > 0;

        $stmt->close();

        if (!$terhapus) {
            $this->db->rollback();
            return false;
        }

        $this->db->commit();

        return true;

    } catch (Throwable $e) {

        $this->db->rollback();

        error_log("Gagal menghapus pengaduan: " . $e->getMessage());

        return false;
    }
}
    /* =========================
       DASHBOARD
    ========================= */

    public function getStats()
    {
        $data = [
            'total' => 0,
            'selesai' => 0,
            'diproses' => 0,
            'menunggu' => 0
        ];

        $result = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'selesai') AS selesai,
                SUM(status = 'diproses') AS diproses,
                SUM(
                    status IN ('baru','pending')
                ) AS menunggu
             FROM pengaduan"
        );

        if ($result) {
            $row = $result->fetch_assoc();

            $data['total'] =
                (int)$row['total'];

            $data['selesai'] =
                (int)$row['selesai'];

            $data['diproses'] =
                (int)$row['diproses'];

            $data['menunggu'] =
                (int)$row['menunggu'];
        }

        return $data;
    }


    public function getCategoryStats()
    {
        return $this->db->query(
            "SELECT
                k.nama_kategori,
                COUNT(p.pengaduan_id) AS jumlah
             FROM kategori k
             LEFT JOIN pengaduan p
                ON p.kategori_id = k.kategori_id
             GROUP BY
                k.kategori_id,
                k.nama_kategori
             ORDER BY jumlah DESC"
        );
    }


    public function getUsersForFilter()
    {
        return $this->db->query("SELECT user_id, nama_lengkap, nomor_induk FROM users ORDER BY nama_lengkap ASC");
    }

    public function getAspirasiReport($filters = [])
    {
        // Filter dibatasi ke angka dan format tanggal yang valid sebelum masuk SQL.
        $sql = "SELECT p.*, u.nama_lengkap, u.nomor_induk, k.nama_kategori,
                       l.nama_ruangan, l.gedung_blok, ph.biaya
                FROM pengaduan p
                LEFT JOIN users u ON u.user_id = p.user_id
                LEFT JOIN kategori k ON k.kategori_id = p.kategori_id
                LEFT JOIN lokasi l ON l.lokasi_id = p.lokasi_id
                LEFT JOIN penanganan ph ON ph.pengaduan_id = p.pengaduan_id
                WHERE 1=1";
        foreach (['kategori_id' => 'p.kategori_id', 'lokasi_id' => 'p.lokasi_id', 'user_id' => 'p.user_id'] as $key => $column) {
            if (isset($filters[$key]) && ctype_digit((string)$filters[$key]) && (int)$filters[$key] > 0) {
                $sql .= " AND {$column} = " . (int)$filters[$key];
            }
        }
        foreach (['tanggal_mulai' => '>=', 'tanggal_selesai' => '<='] as $key => $operator) {
            $value = (string)($filters[$key] ?? '');
            if ($value !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $safeDate = $this->db->real_escape_string($value);
                $sql .= " AND DATE(p.created_at) {$operator} '{$safeDate}'";
            }
        }
        $sql .= " ORDER BY p.created_at DESC";
        try {
            return $this->db->query($sql);
        } catch (Throwable $e) {
            error_log('Gagal mengambil laporan aspirasi: ' . $e->getMessage());
            return false;
        }
    }

    public function getKategori()
    {
        return $this->db->query(
            "SELECT *
             FROM kategori
             ORDER BY nama_kategori ASC"
        );
    }


    public function getLokasi()
    {
        return $this->db->query(
            "SELECT *
             FROM lokasi
             ORDER BY nama_ruangan ASC"
        );
    }
}