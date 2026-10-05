# [E03] The Lost Cavern - Advanced Todo App

Aplikasi **Todo App** interaktif tingkat lanjut yang dikembangkan dengan HTML5, CSS3, dan Vanilla JavaScript sesuai spesifikasi penugasan **[E03] The Lost Cavern** mata kuliah Pemrograman Web A (ITS).

---

## 🎯 Pemenuhan Kriteria Tugas [E03]

| No | Kriteria Tugas | Implementasi |
|---|---|---|
| 1 | **Web Storage: IndexedDB & localStorage** | • **IndexedDB (`TodoAppDB`)**: Seluruh data Todo (judul, deskripsi, status selesai, foto lampiran, waktu notifikasi) disimpan permanen di database lokal browser. Data tidak hilang saat refresh.<br>• **localStorage (`todo_theme_preference`)**: Menyimpan preferensi pengguna untuk Mode Terang / Gelap secara permanen. |
| 2 | **Media Capture API (Foto Tugas)** | • Menambahkan field lampiran gambar pada form todo.<br>• Mendukung unggah berkas (`<input type="file" capture="environment">`) dan akses **kamera langsung (live stream webcam)** menggunakan `navigator.mediaDevices.getUserMedia()`.<br>• Jepret foto via `<canvas>` dan simpan ke IndexedDB, ditampilkan sebagai thumbnail di kartu tugas. |
| 3 | **Service Worker & Notifikasi Berjadwal** | • File Service Worker (`sw.js`) didaftarkan untuk caching offline dan penanganan notifikasi latar belakang.<br>• Input waktu pengingat (`<input type="datetime-local">`) pada form todo.<br>• Scheduler memeriksa jadwal dan menampilkan notifikasi desktop browser (`showNotification`) saat waktu tiba. |
| 4 | **Aksesibilitas Komponen (A11y)** | • Struktur semantik HTML5 (`<header>`, `<main>`, `<section>`, `<aside>`, `<footer>`).<br>• Labeling eksplisit `<label for="...">` pada setiap input.<br>• Atribut ARIA (`role="status"`, `aria-live="polite"`, `aria-label`, `aria-describedby`, `aria-pressed`, `aria-required`).<br>• Live region untuk pengumuman aksi ke pengguna screen reader. |
| 5 | **Best Practices Aksesibilitas (WCAG 2.1 AA)** | • *Skip link* (`Lewati ke konten utama`) untuk navigasi cepat via keyboard.<br>• Indikator fokus visual yang jelas (`:focus-visible`) dengan kontras tinggi.<br>• Rasio kontras warna teks dan background memenuhi standar minimal 4.5:1.<br>• Manajemen fokus modal kamera (fokus otomatis ke tombol jepret dan kembali ke tombol pembuka saat ditutup via keyboard Escape). |

---

## 📁 Struktur File

```
[E01] The Style Warrior/
├── index.html        # Struktur HTML5 semantik, modal kamera, ARIA & A11y
├── style.css         # Styling antarmuka (Light & Dark Mode WCAG AA)
├── script.js         # Logika IndexedDB, Media Capture, Theme, dan A11y
├── sw.js             # Service Worker (caching offline & background notification)
└── README.md         # Dokumentasi penugasan E03
```

---

## 🚀 Panduan Git Branch E03 (Submission)

Untuk melakukan submit tugas **[E03]** ke GitHub:

1. Buka terminal pada folder proyek Anda (`[E01] The Style Warrior`):
   ```bash
   cd "[E01] The Style Warrior"
   ```
2. Buat dan berpindah ke branch baru `E03`:
   ```bash
   git checkout -b E03
   ```
3. Tambahkan semua file perubahan:
   ```bash
   git add .
   ```
4. Lakukan commit:
   ```bash
   git commit -m "feat(E03): implement IndexedDB, Media Capture API, Service Worker, and WCAG A11y"
   ```
5. Push ke GitHub repository Anda:
   ```bash
   git push -u origin E03
   ```
