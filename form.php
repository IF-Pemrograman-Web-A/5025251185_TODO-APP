<?php
/**
 * form.php - Komponen Modular Formulir Tambah / Edit Tugas
 * [E04] The Iron Citadel - Pemrograman Web A
 *
 * Secara otomatis beralih antara mode "Tambah" dan "Edit"
 * berdasarkan parameter GET ?edit=<id>. Mendukung upload file (Kriteria 5).
 */

$isEditMode = false;
$editTodo   = null;

// Cek apakah ada permintaan edit
if (!empty($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    if ($editId > 0) {
        $editStmt = $conn->prepare("SELECT * FROM `todos` WHERE `id` = ?");
        $editStmt->bind_param('i', $editId);
        $editStmt->execute();
        $editRes = $editStmt->get_result();
        if ($editRes && $editRes->num_rows > 0) {
            $editTodo   = $editRes->fetch_assoc();
            $isEditMode = true;
        }
        $editStmt->close();
    }
}

// Tipe default form: ikuti menu aktif, atau tipe todo yang sedang diedit
$defaultType = $isEditMode ? $editTodo['type'] : $currentType;
?>

<!-- ========== PANEL KANAN: FORM INPUT & EDIT (Modular form.php) ========== -->
<aside class="editor-panel" aria-labelledby="form-title">
    <div class="form-section">

        <h2 id="form-title">
            <i class="fa-solid <?= $isEditMode ? 'fa-pen-to-square' : 'fa-plus-circle' ?>" aria-hidden="true"></i>
            <?= $isEditMode ? 'Edit Tugas' : 'Tambah Tugas Baru' ?>
        </h2>

        <form
            action="process.php?action=<?= $isEditMode ? 'update' : 'create' ?>"
            method="POST"
            enctype="multipart/form-data"
            class="todo-form"
            id="todo-form"
            novalidate
        >
            <?php if ($isEditMode): ?>
                <input type="hidden" name="id" value="<?= $editTodo['id'] ?>">
                <input type="hidden" name="current_image" value="<?= htmlspecialchars($editTodo['image'] ?? '') ?>">
            <?php endif; ?>

            <!-- Judul / Nama Matkul -->
            <div class="form-group">
                <label for="title">
                    Nama Matkul / Judul Tugas
                    <span class="required" aria-hidden="true">*</span>
                    <span class="sr-only">(wajib diisi)</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?= htmlspecialchars($editTodo['title'] ?? '') ?>"
                    placeholder="Contoh: PBO, Matdis 2, Rapat Tim"
                    required
                    aria-required="true"
                    aria-describedby="title-hint"
                >
                <small id="title-hint" class="form-hint">Masukkan kode atau nama mata kuliah / kegiatan.</small>
            </div>

            <!-- Deskripsi -->
            <div class="form-group">
                <label for="description">Deskripsi / Catatan</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Masukkan rincian tugas, link referensi, atau catatan..."
                    aria-describedby="desc-hint"
                ><?= htmlspecialchars($editTodo['description'] ?? '') ?></textarea>
                <small id="desc-hint" class="form-hint">Rincian pekerjaan atau catatan tambahan.</small>
            </div>

            <!-- Kategori -->
            <div class="form-group">
                <label for="category">Kategori / Topik</label>
                <input
                    type="text"
                    id="category"
                    name="category"
                    value="<?= htmlspecialchars($editTodo['category'] ?? '') ?>"
                    placeholder="Kuliah, Organisasi, Mandiri, dll"
                >
            </div>

            <!-- Batas Waktu (Deadline) -->
            <div class="form-group">
                <label for="due_date">Batas Waktu (Deadline)</label>
                <input
                    type="date"
                    id="due_date"
                    name="due_date"
                    value="<?= htmlspecialchars($editTodo['due_date'] ?? '') ?>"
                >
            </div>

            <!-- Waktu Pengingat Notifikasi (dipertahankan dari E03) -->
            <div class="form-group">
                <label for="notification_time">Waktu Pengingat (Notifikasi)</label>
                <input
                    type="datetime-local"
                    id="notification_time"
                    name="notification_time"
                    value="<?= !empty($editTodo['notification_time']) ? date('Y-m-d\TH:i', strtotime($editTodo['notification_time'])) : '' ?>"
                    aria-describedby="notif-hint"
                >
                <small id="notif-hint" class="form-hint">Pilih tanggal &amp; jam untuk pengingat tugas ini.</small>
            </div>

            <!-- Prioritas -->
            <div class="form-group">
                <label for="priority">Prioritas Tugas</label>
                <select id="priority" name="priority">
                    <option value="low"    <?= ($editTodo['priority'] ?? '') === 'low'    ? 'selected' : '' ?>>Rendah (Low)</option>
                    <option value="medium" <?= ($editTodo['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Sedang (Medium)</option>
                    <option value="high"   <?= ($editTodo['priority'] ?? '') === 'high'   ? 'selected' : '' ?>>Mendesak (High)</option>
                </select>
            </div>

            <!-- Tipe Menu Bar: Personal vs Shared (Kriteria E04) -->
            <div class="form-group">
                <label>
                    Kategori Menu Bar
                    <span class="required" aria-hidden="true">*</span>
                </label>
                <div class="radio-group" role="radiogroup" aria-label="Pilih tipe tugas">
                    <label class="radio-label <?= $defaultType === 'personal' ? 'selected' : '' ?>">
                        <input
                            type="radio"
                            name="type"
                            value="personal"
                            <?= $defaultType === 'personal' ? 'checked' : '' ?>
                        >
                        <i class="fa-solid fa-user-lock" aria-hidden="true"></i> Personal
                    </label>
                    <label class="radio-label <?= $defaultType === 'shared' ? 'selected' : '' ?>">
                        <input
                            type="radio"
                            name="type"
                            value="shared"
                            <?= $defaultType === 'shared' ? 'checked' : '' ?>
                        >
                        <i class="fa-solid fa-users" aria-hidden="true"></i> Shared
                    </label>
                </div>
                <small class="form-hint">Personal: tugas pribadi. Shared: tugas bersama kelompok/organisasi.</small>
            </div>

            <!-- Media Capture API: Upload Foto Tugas (Kriteria E03 + Kriteria File Handling E04) -->
            <div class="form-group media-capture-group">
                <label id="media-label">
                    Lampiran Foto Tugas
                    <span class="form-hint" style="font-weight:normal;">(JPG/PNG/WEBP, maks 2MB)</span>
                </label>

                <?php
                // Jika sedang edit dan ada gambar lama, tampilkan preview
                $existingImage = $editTodo['image'] ?? null;
                $existingPath  = __DIR__ . '/uploads/' . $existingImage;
                if ($isEditMode && !empty($existingImage) && file_exists($existingPath)):
                ?>
                    <div class="image-preview-container" id="image-preview-container" style="display: flex;">
                        <img
                            src="uploads/<?= htmlspecialchars($existingImage) ?>"
                            alt="Foto tugas yang sudah ada"
                            class="image-preview"
                            id="image-preview"
                        >
                        <div>
                            <p style="font-size:12px; color:#6b7280; margin-bottom:8px;">Foto saat ini</p>
                            <label>
                                <input type="checkbox" name="delete_image" value="1">
                                <small style="color:#991b1b;"> Hapus foto ini</small>
                            </label>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="image-preview-container" id="image-preview-container" style="display: none;">
                        <img src="" alt="Pratinjau foto tugas yang dilampirkan" class="image-preview" id="image-preview">
                        <button type="button" id="btn-remove-image" class="btn-remove-image" aria-label="Hapus foto lampiran">
                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i> Hapus Foto
                        </button>
                    </div>
                <?php endif; ?>

                <div class="media-actions" role="group" aria-labelledby="media-label">
                    <label for="image-upload" class="btn-media btn-file" tabindex="0" role="button"
                           aria-label="Pilih foto dari perangkat">
                        <i class="fa-solid fa-file-arrow-up" aria-hidden="true"></i>
                        <span>Pilih Berkas</span>
                    </label>
                    <input
                        type="file"
                        id="image-upload"
                        name="image"
                        accept="image/png, image/jpeg, image/jpg, image/webp"
                        class="sr-only"
                        aria-label="Pilih berkas gambar foto tugas"
                    >

                    <button
                        type="button"
                        id="btn-open-camera"
                        class="btn-media btn-camera"
                        aria-label="Buka kamera untuk mengambil foto"
                    >
                        <i class="fa-solid fa-camera" aria-hidden="true"></i>
                        <span>Buka Kamera</span>
                    </button>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="form-actions">
                <button type="submit" id="btn-submit" class="btn-submit"
                        aria-label="<?= $isEditMode ? 'Simpan perubahan tugas' : 'Tambahkan tugas baru' ?>">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    <?= $isEditMode ? 'Simpan Perubahan' : 'Tambah Tugas' ?>
                </button>

                <?php if ($isEditMode): ?>
                    <a href="index.php?type=<?= $currentType ?>" class="btn-cancel" id="btn-cancel"
                       aria-label="Batalkan proses edit">
                        Batal
                    </a>
                <?php else: ?>
                    <button type="button" id="btn-cancel" class="btn-cancel" style="display: none;"
                            aria-label="Batalkan proses edit">
                        Batal
                    </button>
                <?php endif; ?>
            </div>

        </form>
    </div>
</aside>
