<?php

class Koneksi
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "pengaduan_sarana_sekolah";

    public $koneksi;

    public function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->koneksi = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database
            );

            $this->koneksi->set_charset("utf8mb4");

        } catch (mysqli_sql_exception $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
}