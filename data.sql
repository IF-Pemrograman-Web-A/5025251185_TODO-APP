-- =====================================================================
-- data.sql - Skema dan Data Awal Database
-- [E04] The Iron Citadel - Pemrograman Web A
-- 
-- Cara import:
--   1. Buka phpMyAdmin di browser (localhost/phpmyadmin)
--   2. Buat database baru bernama: db_citadel_todos
--   3. Klik tab "Import" lalu pilih file ini
--   4. Klik "Go"
-- =====================================================================

-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS `db_citadel_todos`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `db_citadel_todos`;

-- =====================================================================
-- Tabel: todos
-- Menyimpan seluruh data tugas (Personal & Shared)
-- =====================================================================
DROP TABLE IF EXISTS `todos`;

CREATE TABLE `todos` (
    `id`                INT(11)         NOT NULL AUTO_INCREMENT,
    `title`             VARCHAR(255)    NOT NULL COMMENT 'Judul / Nama Matkul',
    `description`       TEXT            DEFAULT NULL COMMENT 'Deskripsi / Catatan tugas',
    `type`              ENUM('personal','shared') NOT NULL DEFAULT 'personal' COMMENT 'Jenis tugas: personal atau shared',
    `category`          VARCHAR(100)    DEFAULT NULL COMMENT 'Kategori tugas (mis: Kuliah, Organisasi)',
    `due_date`          DATE            DEFAULT NULL COMMENT 'Batas pengumpulan',
    `notification_time` DATETIME        DEFAULT NULL COMMENT 'Waktu pengingat notifikasi',
    `priority`          ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    `completed`         TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '0 = Belum, 1 = Selesai',
    `image`             VARCHAR(255)    DEFAULT NULL COMMENT 'Nama file gambar lampiran yang di-upload',
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- Data Awal (Seed Data) - Data contoh untuk demonstrasi
-- =====================================================================
INSERT INTO `todos` (`title`, `description`, `type`, `category`, `due_date`, `priority`, `completed`) VALUES
-- Tugas Personal
('Pemrograman Web A - Modul E04',     'Mengimplementasikan arsitektur modular PHP, koneksi MySQL, menu bar Personal & Shared, CRUD, dan file handling.',     'personal', 'Kuliah',     '2026-10-19', 'high',   0),
('Matematika Diskrit 2',              'Mengerjakan soal latihan Bab 7 tentang Graf dan Tree, serta mempelajari algoritma Dijkstra.',                         'personal', 'Kuliah',     '2026-10-20', 'high',   0),
('Pemrograman Berorientasi Objek',    'Membuat program simulasi sistem perpustakaan menggunakan konsep inheritance dan polymorphism.',                        'personal', 'Kuliah',     '2026-10-22', 'medium', 0),
('Kecerdasan Komputasional',          'Membuat laporan analisis algoritma Fuzzy Logic untuk sistem pengendalian lampu lalu lintas.',                         'personal', 'Kuliah',     '2026-10-25', 'medium', 1),
('Belajar Mandiri Docker',            'Mempelajari dasar-dasar Docker untuk persiapan deployment proyek akhir semester.',                                    'personal', 'Mandiri',    '2026-10-30', 'low',    0),

-- Tugas Shared / Organisasi
('Rapat Koordinasi Tim Proyek',       'Membahas progress sprint ke-3 dan rencana deployment fitur baru di aplikasi BEM. Harap semua anggota hadir.',         'shared',   'Organisasi', '2026-10-19', 'high',   0),
('Dokumentasi API Tim Backend',       'Menulis dokumentasi lengkap endpoint API menggunakan Swagger/OpenAPI 3.0 agar tim frontend bisa integrasi.',          'shared',   'Proyek',     '2026-10-21', 'high',   0),
('Review Pull Request Bersama',       'Melakukan code review bersama untuk PR fitur autentikasi JWT yang sudah diajukan oleh anggota tim.',                  'shared',   'Proyek',     '2026-10-23', 'medium', 1);
