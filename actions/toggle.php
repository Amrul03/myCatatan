<?php
session_start();
$pdo = require_once __DIR__ . '/../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $pdo->prepare("SELECT is_completed FROM tasks WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $task = $stmt->fetch();

    if ($task) {
        $newStatus = $task['is_completed'] ? 0 : 1;
        $updateStmt = $pdo->prepare("UPDATE tasks SET is_completed = :status WHERE id = :id");
        $updateStmt->execute([
            ':status' => $newStatus,
            ':id' => $id,
        ]);
        $_SESSION['flash_message'] = $newStatus ? 'Tugas ditandai selesai.' : 'Tugas diubah menjadi belum selesai.';
        $_SESSION['flash_type'] = 'info';
    }
}

// Redirect back to referring page or index
$redirect = $_SERVER['HTTP_REFERER'] ?? '../index.php';
header("Location: $redirect");
exit;
