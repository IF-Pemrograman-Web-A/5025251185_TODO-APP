<?php
/**
 * content.php - Komponen Modular Daftar Tugas
 * [E04] The Iron Citadel - Pemrograman Web A
 *
 * Mengambil dan menampilkan todo sesuai tipe (personal/shared)
 * dari database MySQL secara berurutan (belum selesai dulu, lalu selesai).
 * Mendukung tampilan thumbnail gambar yang di-upload (Kriteria File Handling).
 */

// Query semua todo sesuai tipe yang aktif
$stmt = $conn->prepare(
    "SELECT * FROM `todos` WHERE `type` = ? ORDER BY `completed` ASC, `created_at` DESC"
);
$stmt->bind_param('s', $currentType);
$stmt->execute();
$todosResult = $stmt->get_result();
$todosCount  = $todosResult->num_rows;
$stmt->close();

// Peta label prioritas
$priorityLabel = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Mendesak'];
$priorityClass = ['low' => 'low', 'medium' => 'medium', 'high' => 'high'];
?>

<!-- ========== PANEL KIRI: DAFTAR TUGAS (Modular content.php) ========== -->
<section class="todo-panel" aria-labelledby="todo-list-title">

    <div class="panel-header">
        <h2 id="todo-list-title">
            <?php if ($currentType === 'personal'): ?>
                <i class="fa-solid fa-user-lock" aria-hidden="true"></i> Tugas Personal
            <?php else: ?>
                <i class="fa-solid fa-users" aria-hidden="true"></i> Tugas Shared
            <?php endif; ?>
        </h2>
        <span class="todo-count" id="todo-count" aria-live="polite">
            <?= $todosCount ?> Tasks
        </span>
    </div>

    <div class="todo-list" id="todo-list" role="list" aria-label="Daftar kartu tugas">

        <?php if ($todosCount === 0): ?>
            <!-- State Kosong -->
            <div class="empty-state" role="status">
                <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                <p>
                    Belum ada tugas pada kategori
                    <strong><?= $currentType === 'personal' ? 'Personal' : 'Shared' ?></strong>.
                </p>
                <small>Gunakan formulir di samping untuk menambahkan tugas baru!</small>
            </div>

        <?php else: ?>
            <?php while ($todo = $todosResult->fetch_assoc()): ?>
                <article
                    class="todo-card <?= $todo['completed'] ? 'completed' : '' ?>"
                    role="listitem"
                    id="todo-<?= $todo['id'] ?>"
                >
                    <div class="todo-main">

                        <!-- Toggle Selesai (Kriteria CRUD: Update via hyperlink PHP) -->
                        <a
                            href="process.php?action=toggle&id=<?= $todo['id'] ?>&type=<?= $currentType ?>"
                            class="todo-check-link <?= $todo['completed'] ? 'checked' : '' ?>"
                            title="<?= $todo['completed'] ? 'Tandai belum selesai' : 'Tandai selesai' ?>"
                            aria-label="Ubah status tugas: <?= htmlspecialchars($todo['title']) ?>"
                        >
                            <i class="fa-solid <?= $todo['completed'] ? 'fa-square-check' : 'fa-square' ?>"
                               aria-hidden="true"></i>
                        </a>

                        <!-- Thumbnail Gambar (Kriteria File Handling) -->
                        <?php
                        $imagePath = __DIR__ . '/uploads/' . $todo['image'];
                        if (!empty($todo['image']) && file_exists($imagePath)):
                        ?>
                            <a href="uploads/<?= htmlspecialchars($todo['image']) ?>"
                               target="_blank"
                               title="Klik untuk memperbesar gambar lampiran">
                                <img
                                    src="uploads/<?= htmlspecialchars($todo['image']) ?>"
                                    alt="Foto tugas: <?= htmlspecialchars($todo['title']) ?>"
                                    class="todo-thumb"
                                >
                            </a>
                        <?php endif; ?>

                        <!-- Informasi Tugas -->
                        <div class="todo-info">
                            <h3><?= htmlspecialchars($todo['title']) ?></h3>

                            <?php if (!empty($todo['description'])): ?>
                                <p><?= htmlspecialchars($todo['description']) ?></p>
                            <?php endif; ?>

                            <!-- Badge Meta: Kategori, Prioritas, Deadline -->
                            <div class="todo-meta-badges">
                                <?php if (!empty($todo['category'])): ?>
                                    <span class="todo-badge badge-category">
                                        <i class="fa-solid fa-tag" aria-hidden="true"></i>
                                        <?= htmlspecialchars($todo['category']) ?>
                                    </span>
                                <?php endif; ?>

                                <span class="todo-badge badge-priority <?= $priorityClass[$todo['priority']] ?? 'medium' ?>">
                                    <i class="fa-solid fa-flag" aria-hidden="true"></i>
                                    <?= $priorityLabel[$todo['priority']] ?? 'Sedang' ?>
                                </span>

                                <?php if (!empty($todo['due_date'])): ?>
                                    <span class="todo-notif-badge">
                                        <i class="fa-regular fa-calendar-days" aria-hidden="true"></i>
                                        <?= date('d M Y', strtotime($todo['due_date'])) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ($todo['completed']): ?>
                                    <span class="todo-badge badge-done">
                                        <i class="fa-solid fa-check" aria-hidden="true"></i> Selesai
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div><!-- .todo-main -->

                    <!-- Tombol Aksi: Edit & Hapus (Kriteria CRUD) -->
                    <div class="todo-actions">
                        <a
                            href="index.php?type=<?= $currentType ?>&edit=<?= $todo['id'] ?>"
                            class="btn-action btn-edit"
                            title="Edit tugas ini"
                            aria-label="Edit tugas: <?= htmlspecialchars($todo['title']) ?>"
                        >
                            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
                        </a>

                        <a
                            href="process.php?action=delete&id=<?= $todo['id'] ?>&type=<?= $currentType ?>"
                            class="btn-action btn-delete"
                            title="Hapus tugas ini"
                            aria-label="Hapus tugas: <?= htmlspecialchars($todo['title']) ?>"
                            onclick="return confirm('Yakin ingin menghapus tugas ini? File lampiran juga akan ikut dihapus.')"
                        >
                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i> Hapus
                        </a>
                    </div>

                </article>
            <?php endwhile; ?>
        <?php endif; ?>

    </div><!-- .todo-list -->

</section>
