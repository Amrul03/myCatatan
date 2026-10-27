<?php
session_start();
$pdo = require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!empty($title)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, is_completed) VALUES (:title, :description, 0)");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description !== '' ? $description : null,
        ]);
        $_SESSION['flash_message'] = 'Tugas baru berhasil ditambahkan.';
        $_SESSION['flash_type'] = 'success';
    } else {
        $_SESSION['flash_message'] = 'Judul tugas tidak boleh kosong.';
        $_SESSION['flash_type'] = 'error';
    }
}

header('Location: ../index.php');
exit;
