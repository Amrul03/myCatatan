<?php
session_start();
$pdo = require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($id && !empty($title)) {
        $stmt = $pdo->prepare("UPDATE tasks SET title = :title, description = :description WHERE id = :id");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description !== '' ? $description : null,
            ':id' => $id,
        ]);
        $_SESSION['flash_message'] = 'Tugas berhasil diperbarui.';
        $_SESSION['flash_type'] = 'success';
    } else {
        $_SESSION['flash_message'] = 'Gagal memperbarui: Judul tidak boleh kosong.';
        $_SESSION['flash_type'] = 'error';
    }
}

$redirect = $_SERVER['HTTP_REFERER'] ?? '../index.php';
header("Location: $redirect");
exit;
