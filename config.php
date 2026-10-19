<?php
/**
 * config.php - Konfigurasi Koneksi Database MySQL
 * [E04] The Iron Citadel - Pemrograman Web A
 * 
 * Memulai session PHP dan menginisialisasi koneksi ke database MySQL
 * menggunakan MySQLi. Koneksi tersedia secara global melalui variabel $conn.
 */

session_start();

// ======================== KONFIGURASI DATABASE ========================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_citadel_todos');

// ======================== INISIALISASI KONEKSI ========================
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek error koneksi
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        'status'  => 'error',
        'message' => 'Koneksi database gagal: ' . $conn->connect_error
    ]));
}

// Set charset UTF-8 agar karakter Indonesia (huruf khusus) tersimpan dengan benar
$conn->set_charset('utf8mb4');
