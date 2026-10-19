<?php
/**
 * footer.php - Komponen Modular Footer
 * [E04] The Iron Citadel - Pemrograman Web A
 *
 * Berisi footer visual, Camera Modal (dari E03 Media Capture API),
 * dan seluruh JavaScript interaktif yang sudah ada di E03 (tema, kamera, notifikasi)
 * serta radio behavior baru untuk form tipe Personal/Shared.
 */
?>

    <!-- ================= CAMERA MODAL DIALOG (dari E03 Media Capture API) ================= -->
    <div
        id="camera-modal"
        class="modal-backdrop"
        role="dialog"
        aria-modal="true"
        aria-labelledby="camera-modal-title"
        style="display: none;"
    >
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 id="camera-modal-title">Ambil Foto dengan Kamera</h3>
                <button type="button" id="btn-close-camera" class="btn-close-modal" aria-label="Tutup jendela kamera">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body">
                <video id="camera-stream" autoplay playsinline aria-label="Tampilan langsung video kamera"></video>
                <canvas id="camera-canvas" class="sr-only"></canvas>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn-capture-photo" class="btn-capture" aria-label="Jepret foto dari kamera">
                    <i class="fa-solid fa-camera" aria-hidden="true"></i> Ambil Foto
                </button>
            </div>
        </div>
    </div>

    <!-- ================= FOOTER ================= -->
    <footer class="footer" role="contentinfo">
        <p>&copy; 2026 Todo App </p>
        <p>Teknik Informatika | Institut Teknologi Sepuluh Nopember (ITS)</p>
    </footer>

    <!-- ================= JAVASCRIPT ================= -->
    <script>
    // ============================================================
    // TEMA TOGGLE (dari E03, dipertahankan)
    // ============================================================
    const THEME_KEY = 'todo_theme_preference';

    function toggleTheme() {
        const isDark = document.body.classList.toggle('dark-mode');
        const btn = document.getElementById('theme-toggle');
        localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
        if (btn) {
            btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            btn.setAttribute('aria-label', isDark
                ? 'Ganti ke mode terang (saat ini mode gelap)'
                : 'Ganti ke mode gelap (saat ini mode terang)');
            btn.innerHTML = isDark
                ? '<i class="fa-solid fa-sun" aria-hidden="true"></i> <span>Mode Terang</span>'
                : '<i class="fa-solid fa-moon" aria-hidden="true"></i> <span>Mode Gelap</span>';
        }
        announceA11y(isDark ? 'Mode gelap diaktifkan.' : 'Mode terang diaktifkan.');
    }

    function announceA11y(msg) {
        const el = document.getElementById('sr-announcements');
        if (el) {
            el.textContent = '';
            setTimeout(() => { el.textContent = msg; }, 50);
        }
    }

    // ============================================================
    // RADIO LABEL BEHAVIOUR (baru untuk form Personal/Shared E04)
    // ============================================================
    document.querySelectorAll('.radio-group input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.radio-label').forEach(l => l.classList.remove('selected'));
            if (this.checked) {
                this.closest('.radio-label').classList.add('selected');
            }
        });
    });

    // ============================================================
    // IMAGE UPLOAD PREVIEW (dari E03 Media Capture)
    // ============================================================
    const imageUploadInput   = document.getElementById('image-upload');
    const imagePreview       = document.getElementById('image-preview');
    const imagePreviewCont   = document.getElementById('image-preview-container');
    const btnRemoveImage     = document.getElementById('btn-remove-image');

    if (imageUploadInput) {
        imageUploadInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (imagePreview) {
                        imagePreview.src = e.target.result;
                        imagePreview.alt = 'Pratinjau: ' + file.name;
                    }
                    if (imagePreviewCont) imagePreviewCont.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (btnRemoveImage) {
        btnRemoveImage.addEventListener('click', function () {
            if (imageUploadInput) imageUploadInput.value = '';
            if (imagePreview) imagePreview.src = '';
            if (imagePreviewCont) imagePreviewCont.style.display = 'none';
        });
    }

    // ============================================================
    // CAMERA MODAL (dari E03 Media Capture API - dipertahankan)
    // ============================================================
    let cameraStream      = null;
    let lastFocusedEl     = null;
    const cameraModal     = document.getElementById('camera-modal');
    const cameraVideo     = document.getElementById('camera-stream');
    const cameraCanvas    = document.getElementById('camera-canvas');
    const btnOpenCamera   = document.getElementById('btn-open-camera');
    const btnCloseCamera  = document.getElementById('btn-close-camera');
    const btnCapture      = document.getElementById('btn-capture-photo');

    if (btnOpenCamera) {
        btnOpenCamera.addEventListener('click', async () => {
            lastFocusedEl = document.activeElement;
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                if (cameraVideo) cameraVideo.srcObject = cameraStream;
                if (cameraModal) {
                    cameraModal.style.display = 'flex';
                    cameraModal.removeAttribute('aria-hidden');
                }
                if (btnCloseCamera) btnCloseCamera.focus();
            } catch (err) {
                alert('Kamera tidak dapat diakses: ' + err.message);
            }
        });
    }

    function closeCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(t => t.stop());
            cameraStream = null;
        }
        if (cameraModal) {
            cameraModal.style.display = 'none';
            cameraModal.setAttribute('aria-hidden', 'true');
        }
        if (lastFocusedEl) lastFocusedEl.focus();
    }

    if (btnCloseCamera) btnCloseCamera.addEventListener('click', closeCamera);

    if (cameraModal) {
        cameraModal.addEventListener('click', (e) => {
            if (e.target === cameraModal) closeCamera();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && cameraModal.style.display !== 'none') closeCamera();
        });
    }

    if (btnCapture) {
        btnCapture.addEventListener('click', () => {
            if (!cameraVideo || !cameraCanvas || !imagePreview || !imagePreviewCont) return;
            cameraCanvas.width  = cameraVideo.videoWidth;
            cameraCanvas.height = cameraVideo.videoHeight;
            cameraCanvas.getContext('2d').drawImage(cameraVideo, 0, 0);
            const dataUrl = cameraCanvas.toDataURL('image/jpeg', 0.9);
            imagePreview.src = dataUrl;
            imagePreviewCont.style.display = 'flex';

            // Ubah data URL kamera menjadi File dan inject ke input file
            cameraCanvas.toBlob((blob) => {
                if (imageUploadInput) {
                    const dt = new DataTransfer();
                    dt.items.add(new File([blob], 'kamera_' + Date.now() + '.jpg', { type: 'image/jpeg' }));
                    imageUploadInput.files = dt.files;
                }
            }, 'image/jpeg', 0.9);

            closeCamera();
            announceA11y('Foto berhasil diambil dari kamera.');
        });
    }

    // ============================================================
    // SERVICE WORKER (dari E03, dipertahankan)
    // ============================================================
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js').catch(err => {
            console.warn('Service Worker gagal terdaftar:', err);
        });
    }

    // ============================================================
    // AUTO-DISMISS FLASH MESSAGE
    // ============================================================
    const flashAlerts = document.querySelectorAll('.flash-alert');
    flashAlerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });
    </script>

</body>
</html>
