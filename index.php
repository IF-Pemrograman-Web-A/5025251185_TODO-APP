<?php
/**
 * index.php - Halaman Utama (Entry Point)
 * [E04] The Iron Citadel - Pemrograman Web A
 *
 * Menggunakan arsitektur modular PHP:
 * config.php -> header.php -> menu-bar.php -> [content.php + form.php] -> footer.php
 *
 * Dilanjutkan dari E03 (IndexedDB, Media Capture, Service Worker, Aksesibilitas WCAG 2.1 AA)
 * dengan tambahan E04: PHP Modular, MySQL, Menu Bar Personal/Shared, CRUD, File Handling.
 */
require_once 'config.php';

// Tentukan tipe menu yang aktif (personal / shared)
$currentType = $_GET['type'] ?? 'personal';
if (!in_array($currentType, ['personal', 'shared'])) {
    $currentType = 'personal';
}

// ===== KOMPONEN MODULAR: Header =====
include 'header.php';

// ===== KOMPONEN MODULAR: Menu Bar (Personal & Shared) =====
include 'menu-bar.php';
?>

<!-- ================= FLASH MESSAGE (Notifikasi Aksi CRUD) ================= -->
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="flash-alert flash-success" role="alert" aria-live="assertive">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="flash-alert flash-error" role="alert" aria-live="assertive">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- ================= MAIN CONTAINER ================= -->
<main id="main-content" class="main-container" role="main">

    <!-- ===== KOMPONEN MODULAR: Daftar Tugas (content.php) ===== -->
    <?php include 'content.php'; ?>

    <!-- ===== KOMPONEN MODULAR: Form Input / Edit (form.php) ===== -->
    <?php include 'form.php'; ?>

</main>

<?php
// ===== KOMPONEN MODULAR: Footer + JavaScript =====
include 'footer.php';
?>
