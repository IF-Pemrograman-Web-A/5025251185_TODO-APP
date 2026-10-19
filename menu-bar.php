<?php
/**
 * menu-bar.php - Komponen Modular Menu Bar / Navigasi
 * [E04] The Iron Citadel - Pemrograman Web A
 *
 * Menampilkan navigasi Personal & Shared sesuai kriteria E04.
 * Mengambil jumlah total tugas per-tipe langsung dari database MySQL.
 */

// Ambil jumlah tugas per tipe dari database
$personalCount = 0;
$sharedCount   = 0;

$countQuery = $conn->query("SELECT `type`, COUNT(*) AS total FROM `todos` GROUP BY `type`");
if ($countQuery) {
    while ($row = $countQuery->fetch_assoc()) {
        if ($row['type'] === 'personal') {
            $personalCount = (int) $row['total'];
        } elseif ($row['type'] === 'shared') {
            $sharedCount = (int) $row['total'];
        }
    }
}
?>

<!-- ================= MENU BAR / NAVIGASI (Kriteria E04: Menu Personal & Shared) ================= -->
<nav class="navigation-bar" role="navigation" aria-label="Menu Utama Tugas">
    <div class="nav-container">

        <ul class="nav-menu-list" role="tablist">

            <!-- Menu 1: Personal Tasks -->
            <li role="presentation">
                <a
                    href="index.php?type=personal"
                    class="nav-menu-item <?= $currentType === 'personal' ? 'active' : '' ?>"
                    role="tab"
                    aria-selected="<?= $currentType === 'personal' ? 'true' : 'false' ?>"
                    title="Tampilkan tugas personal pribadi"
                >
                    <i class="fa-solid fa-user-lock" aria-hidden="true"></i>
                    <span>Personal Tasks</span>
                    <span class="menu-badge" aria-label="<?= $personalCount ?> tugas personal"><?= $personalCount ?></span>
                </a>
            </li>

            <!-- Menu 2: Shared Tasks (Organisasi) -->
            <li role="presentation">
                <a
                    href="index.php?type=shared"
                    class="nav-menu-item <?= $currentType === 'shared' ? 'active' : '' ?>"
                    role="tab"
                    aria-selected="<?= $currentType === 'shared' ? 'true' : 'false' ?>"
                    title="Tampilkan tugas bersama organisasi"
                >
                    <i class="fa-solid fa-users" aria-hidden="true"></i>
                    <span>Shared Tasks</span>
                    <span class="menu-badge" aria-label="<?= $sharedCount ?> tugas shared"><?= $sharedCount ?></span>
                </a>
            </li>

        </ul>

        <!-- Status Koneksi Database -->
        <div class="nav-actions">
            <span class="db-status-badge" title="Status koneksi database MySQL">
                <i class="fa-solid fa-database" aria-hidden="true"></i>
                <code><?= DB_NAME ?></code>
            </span>
        </div>

    </div>
</nav>
