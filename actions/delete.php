<?php
session_start();
$pdo = require_once __DIR__ . '/../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $_SESSION['flash_message'] = 'Tugas berhasil dihapus.';
    $_SESSION['flash_type'] = 'info';
}

$redirect = $_SERVER['HTTP_REFERER'] ?? '../index.php';
header("Location: $redirect");
exit;
