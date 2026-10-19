<?php
/**
 * header.php - Komponen Modular Header
 * [E04] The Iron Citadel - Pemrograman Web A
 * 
 * Berisi: DOCTYPE, <head>, tag <body>, komponen header visual,
 * dan inisialisasi preferensi tema dari localStorage.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi Todo App interaktif dengan PHP, MySQL, IndexedDB, Media Capture API, Service Worker, dan aksesibilitas WCAG 2.1 AA.">

    <title>Todo App - [E04] The Iron Citadel</title>

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- External CSS (style.css dari E03 yang diperluas) -->
    <link rel="stylesheet" href="style.css">

    <!-- Terapkan preferensi tema lebih awal untuk mencegah flicker -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('todo_theme_preference');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-mode');
                document.addEventListener('DOMContentLoaded', function() {
                    document.body.classList.add('dark-mode');
                    var btn = document.getElementById('theme-toggle');
                    if (btn) {
                        btn.setAttribute('aria-pressed', 'true');
                        btn.setAttribute('aria-label', 'Ganti ke mode terang (saat ini mode gelap)');
                        btn.innerHTML = '<i class="fa-solid fa-sun" aria-hidden="true"></i> <span>Mode Terang</span>';
                    }
                });
            }
        })();
    </script>
</head>

<body>

    <!-- Skip Link untuk Aksesibilitas Keyboard (WCAG Best Practice) -->
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

    <!-- Live Region untuk Screen Reader (A11y) -->
    <div id="sr-announcements" class="sr-only" aria-live="polite" aria-atomic="true"></div>

    <!-- ================= HEADER ================= -->
    <header class="header" role="banner">
        <div class="header-content">
            <div class="header-text">
                <div class="brand-badge">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i> The Iron Citadel
                </div>
                <h1>TODO LIST</h1>
                <p class="subtitle">Kelola tugas kuliah personal dan tim dengan teratur &amp; efisien</p>
            </div>

            <!-- Tombol Toggle Light/Dark Mode -->
            <button
                type="button"
                id="theme-toggle"
                class="theme-toggle-btn"
                aria-label="Ganti ke mode gelap"
                aria-pressed="false"
                onclick="toggleTheme()"
            >
                <i class="fa-solid fa-moon" aria-hidden="true"></i>
                <span>Mode Gelap</span>
            </button>
        </div>
    </header>
