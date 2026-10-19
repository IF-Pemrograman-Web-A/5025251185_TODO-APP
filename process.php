<?php
/**
 * process.php - Handler Semua Operasi CRUD & File Handling
 * [E04] The Iron Citadel - Pemrograman Web A
 *
 * Mendukung aksi: create, update, delete, toggle
 * Semua query menggunakan prepared statements (anti SQL Injection).
 * File upload & unlink ditangani di sini (Kriteria File Handling).
 */
require_once 'config.php';

// ===================================================================
// KONFIGURASI UPLOAD FILE
// ===================================================================
define('UPLOAD_DIR',      __DIR__ . '/uploads/');
define('MAX_FILE_SIZE',   2 * 1024 * 1024);   // 2 MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);

// Pastikan folder uploads/ ada
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// ===================================================================
// FUNGSI BANTU
// ===================================================================

/**
 * Mengunggah file gambar ke folder uploads/.
 * Mengembalikan nama file baru jika berhasil, atau null jika gagal/tidak ada file.
 */
function handleUpload(): ?string
{
    if (empty($_FILES['image']['tmp_name']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash_error'] = 'Gagal mengunggah file. Kode error: ' . $file['error'];
        return null;
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        $_SESSION['flash_error'] = 'Ukuran file melebihi batas maksimum 2 MB.';
        return null;
    }

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, ALLOWED_TYPES)) {
        $_SESSION['flash_error'] = 'Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.';
        return null;
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName  = uniqid('img_', true) . '.' . strtolower($ext);
    $destPath = UPLOAD_DIR . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $_SESSION['flash_error'] = 'Gagal menyimpan file ke server.';
        return null;
    }

    return $newName;
}

/**
 * Menghapus file gambar dari folder uploads/ jika ada.
 */
function deleteImage(?string $filename): void
{
    if (!empty($filename)) {
        $path = UPLOAD_DIR . basename($filename);
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

/**
 * Redirect kembali ke index.php dengan tipe yang sesuai.
 */
function redirectBack(string $type = 'personal'): void
{
    $safeType = in_array($type, ['personal', 'shared']) ? $type : 'personal';
    header('Location: index.php?type=' . $safeType);
    exit;
}

// ===================================================================
// ROUTING AKSI
// ===================================================================
$action = $_GET['action'] ?? '';
$type   = $_GET['type']   ?? 'personal';
if (!in_array($type, ['personal', 'shared'])) $type = 'personal';

switch ($action) {

    // -----------------------------------------------------------------
    // CREATE: Tambah tugas baru
    // -----------------------------------------------------------------
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectBack($type);
        }

        $title    = trim($_POST['title']    ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $typeVal  = in_array($_POST['type'] ?? '', ['personal','shared']) ? $_POST['type'] : 'personal';
        $category = trim($_POST['category'] ?? '');
        $dueDate  = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $notifTime = !empty($_POST['notification_time']) ? $_POST['notification_time'] : null;
        $priority = in_array($_POST['priority'] ?? '', ['low','medium','high']) ? $_POST['priority'] : 'medium';

        if (empty($title)) {
            $_SESSION['flash_error'] = 'Judul tugas tidak boleh kosong.';
            redirectBack($typeVal);
        }

        $imageName = handleUpload();

        $stmt = $conn->prepare(
            "INSERT INTO `todos` (`title`,`description`,`type`,`category`,`due_date`,`notification_time`,`priority`,`image`)
             VALUES (?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param('ssssssss', $title, $desc, $typeVal, $category, $dueDate, $notifTime, $priority, $imageName);

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Tugas "' . htmlspecialchars($title) . '" berhasil ditambahkan!';
        } else {
            $_SESSION['flash_error'] = 'Gagal menyimpan tugas ke database.';
        }
        $stmt->close();
        redirectBack($typeVal);

    // -----------------------------------------------------------------
    // UPDATE: Perbarui tugas yang sudah ada
    // -----------------------------------------------------------------
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectBack($type);
        }

        $id       = intval($_POST['id'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $typeVal  = in_array($_POST['type'] ?? '', ['personal','shared']) ? $_POST['type'] : 'personal';
        $category = trim($_POST['category'] ?? '');
        $dueDate  = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $notifTime = !empty($_POST['notification_time']) ? $_POST['notification_time'] : null;
        $priority = in_array($_POST['priority'] ?? '', ['low','medium','high']) ? $_POST['priority'] : 'medium';
        $currentImage = $_POST['current_image'] ?? '';
        $deleteOld    = isset($_POST['delete_image']) && $_POST['delete_image'] == '1';

        if ($id <= 0 || empty($title)) {
            $_SESSION['flash_error'] = 'Data tidak valid untuk diperbarui.';
            redirectBack($typeVal);
        }

        // Tentukan gambar yang dipakai
        $newImage = handleUpload();
        if ($newImage !== null) {
            // Ada upload baru: hapus gambar lama
            deleteImage($currentImage);
            $finalImage = $newImage;
        } elseif ($deleteOld) {
            // Hapus gambar tanpa upload baru
            deleteImage($currentImage);
            $finalImage = null;
        } else {
            // Pakai gambar lama
            $finalImage = $currentImage ?: null;
        }

        $stmt = $conn->prepare(
            "UPDATE `todos` SET
                `title`=?, `description`=?, `type`=?, `category`=?,
                `due_date`=?, `notification_time`=?, `priority`=?, `image`=?
             WHERE `id`=?"
        );
        $stmt->bind_param('ssssssssi', $title, $desc, $typeVal, $category, $dueDate, $notifTime, $priority, $finalImage, $id);

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Tugas "' . htmlspecialchars($title) . '" berhasil diperbarui!';
        } else {
            $_SESSION['flash_error'] = 'Gagal memperbarui tugas.';
        }
        $stmt->close();
        redirectBack($typeVal);

    // -----------------------------------------------------------------
    // DELETE: Hapus tugas beserta file lampirannya (unlink)
    // -----------------------------------------------------------------
    case 'delete':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            redirectBack($type);
        }

        // Ambil nama file gambar sebelum dihapus agar bisa di-unlink
        $sel = $conn->prepare("SELECT `image` FROM `todos` WHERE `id` = ?");
        $sel->bind_param('i', $id);
        $sel->execute();
        $row = $sel->get_result()->fetch_assoc();
        $sel->close();

        if ($row) {
            deleteImage($row['image']); // Unlink file gambar jika ada (Kriteria File Handling)
        }

        $del = $conn->prepare("DELETE FROM `todos` WHERE `id` = ?");
        $del->bind_param('i', $id);
        if ($del->execute()) {
            $_SESSION['flash_success'] = 'Tugas berhasil dihapus beserta file lampirannya.';
        } else {
            $_SESSION['flash_error'] = 'Gagal menghapus tugas.';
        }
        $del->close();
        redirectBack($type);

    // -----------------------------------------------------------------
    // TOGGLE: Ubah status selesai / belum selesai
    // -----------------------------------------------------------------
    case 'toggle':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            redirectBack($type);
        }

        $tog = $conn->prepare(
            "UPDATE `todos` SET `completed` = IF(`completed` = 1, 0, 1) WHERE `id` = ?"
        );
        $tog->bind_param('i', $id);
        $tog->execute();
        $tog->close();
        redirectBack($type);

    // -----------------------------------------------------------------
    // Default: Redirect ke index
    // -----------------------------------------------------------------
    default:
        redirectBack($type);
}
