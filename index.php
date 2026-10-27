<?php
session_start();
$pdo = require_once __DIR__ . '/config/database.php';

// Flash message
$flashMessage = $_SESSION['flash_message'] ?? null;
$flashType = $_SESSION['flash_type'] ?? 'info';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Filter param
$filter = $_GET['filter'] ?? 'all';

// Query base
$query = "SELECT * FROM tasks";
$params = [];

if ($filter === 'pending') {
    $query .= " WHERE is_completed = 0";
} elseif ($filter === 'completed') {
    $query .= " WHERE is_completed = 1";
}
$query .= " ORDER BY is_completed ASC, created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Statistics counts
$totalTasks = (int) $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
$completedTasks = (int) $pdo->query("SELECT COUNT(*) FROM tasks WHERE is_completed = 1")->fetchColumn();
$pendingTasks = $totalTasks - $completedTasks;

// Date format helper
function formatIndonesianDate($dateStr) {
    $time = strtotime($dateStr);
    $months = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    $day = date('d', $time);
    $month = $months[(int)date('m', $time)];
    $hour = date('H:i', $time);
    return "$day $month, $hour";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>myCatatan - Pengelola Tugas Minimalis</title>
    <meta name="description" content="Aplikasi catatan dan tugas minimalis, bersih, dan mudah digunakan.">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="app-container">
    <!-- Header -->
    <header class="app-header">
        <div>
            <h1 class="brand-title">
                <span class="brand-dot"></span>
                myCatatan
            </h1>
        </div>
        <div class="app-date">
            <?= date('l, d F Y') ?>
        </div>
    </header>

    <!-- Flash Message Notification -->
    <?php if ($flashMessage): ?>
        <div class="toast-notice <?= htmlspecialchars($flashType) ?>">
            <span><?= htmlspecialchars($flashMessage) ?></span>
        </div>
    <?php endif; ?>

    <!-- Add Task Card -->
    <section class="create-card">
        <form action="actions/add.php" method="POST">
            <input 
                type="text" 
                name="title" 
                id="input-title"
                class="input-title" 
                placeholder="Tulis tugas baru lalu tekan Enter..." 
                required 
                autocomplete="off"
            >
            <div id="expandable-details" class="expandable-details">
                <textarea 
                    name="description" 
                    id="input-desc"
                    class="input-desc" 
                    placeholder="Tambahkan catatan detail atau keterangan (opsional)..."
                ></textarea>
            </div>
            <div class="create-actions">
                <button type="button" id="toggle-details-btn" class="toggle-details-btn">
                    + Tambah catatan
                </button>
                <button type="submit" class="btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Simpan Tugas
                </button>
            </div>
        </form>
    </section>

    <!-- Filters & Counter Bar -->
    <div class="filter-bar">
        <div class="tabs-group">
            <a href="index.php" class="tab-link <?= $filter === 'all' ? 'active' : '' ?>">
                Semua (<?= $totalTasks ?>)
            </a>
            <a href="index.php?filter=pending" class="tab-link <?= $filter === 'pending' ? 'active' : '' ?>">
                Belum (<?= $pendingTasks ?>)
            </a>
            <a href="index.php?filter=completed" class="tab-link <?= $filter === 'completed' ? 'active' : '' ?>">
                Selesai (<?= $completedTasks ?>)
            </a>
        </div>
        <div class="task-stats">
            <?= $completedTasks ?> dari <?= $totalTasks ?> selesai
        </div>
    </div>

    <!-- Task List -->
    <main class="task-list">
        <?php if (empty($tasks)): ?>
            <div class="empty-state">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto; display: block;">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                <p>
                    <?= $filter === 'completed' ? 'Belum ada tugas yang selesai.' : ($filter === 'pending' ? 'Semua tugas telah diselesaikan!' : 'Belum ada tugas. Tambahkan tugas pertama Anda di atas!') ?>
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <article class="task-item <?= $task['is_completed'] ? 'completed' : '' ?>" id="task-<?= (int)$task['id'] ?>">
                    <!-- Checkbox Toggle -->
                    <a href="actions/toggle.php?id=<?= (int)$task['id'] ?>" class="checkbox-toggle" title="Ubah status">
                        <div class="custom-box <?= $task['is_completed'] ? 'checked' : '' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </a>

                    <!-- Content -->
                    <div class="task-body">
                        <div class="task-title">
                            <?= htmlspecialchars($task['title']) ?>
                        </div>
                        <?php if (!empty($task['description'])): ?>
                            <div class="task-description"><?= nl2br(htmlspecialchars($task['description'])) ?></div>
                        <?php endif; ?>
                        <div class="task-meta">
                            <?= formatIndonesianDate($task['created_at']) ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="task-actions">
                        <!-- Edit Button -->
                        <button 
                            type="button" 
                            class="btn-icon btn-edit-task" 
                            title="Edit tugas"
                            data-id="<?= (int)$task['id'] ?>"
                            data-title="<?= htmlspecialchars($task['title'], ENT_QUOTES) ?>"
                            data-description="<?= htmlspecialchars($task['description'] ?? '', ENT_QUOTES) ?>"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>

                        <!-- Delete Button -->
                        <a 
                            href="actions/delete.php?id=<?= (int)$task['id'] ?>" 
                            class="btn-icon delete" 
                            title="Hapus tugas"
                            onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<!-- Minimalist Edit Modal -->
<div id="edit-modal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Tugas</h2>
            <button type="button" id="btn-close-modal" class="btn-icon" title="Tutup">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form id="edit-form" action="actions/update.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="id" id="edit-id">
                <div class="form-group">
                    <label class="form-label" for="edit-title">Judul Tugas</label>
                    <input type="text" name="title" id="edit-title" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-desc">Catatan Tambahan</label>
                    <textarea name="description" id="edit-desc" class="form-input" rows="4"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn-cancel-modal" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/app.js"></script>
</body>
</html>
