<?php
/**
 * Database Configuration
 * Siap untuk Docker Compose & Pengembangan Lokal
 * 
 * Membaca environment variables:
 * - DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD
 * - APP_ENV (opsional: 'production' / 'docker' / 'development')
 */

// 1. Baca konfigurasi dari Environment Variables (dengan default yang aman)
$envHost     = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? null));
$envPort     = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? ($_SERVER['DB_PORT'] ?? null));
$envName     = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? ($_SERVER['DB_NAME'] ?? null));
$envUser     = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? ($_SERVER['DB_USER'] ?? null));
$envPassword = getenv('DB_PASSWORD');
if ($envPassword === false) {
    $envPassword = $_ENV['DB_PASSWORD'] ?? ($_SERVER['DB_PASSWORD'] ?? '');
}

$host     = $envHost ?: '127.0.0.1';
$port     = $envPort ?: '3306';
$dbname   = $envName ?: 'myCtatatan_db';
$username = $envUser ?: 'root';
$password = $envPassword; // Tidak ada password yang di-hardcode ke source code

// Deteksi apakah sedang berjalan di lingkungan Docker / Production
$isDockerOrProd = (!empty($envHost) && $envHost !== '127.0.0.1' && $envHost !== 'localhost')
    || file_exists('/.dockerenv') 
    || (getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? '')) === 'production';

$pdo = null;

try {
    // 2. Hubungkan ke MySQL
    // Coba terlebih dahulu koneksi langsung ke database yang ditentukan
    $pdoOptions = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 3,
    ];

    try {
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $username, $password, $pdoOptions);
    } catch (PDOException $e) {
        // Jika database belum ada dan akun memiliki hak akses, buat database baru
        $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, $pdoOptions);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbname}`");
    }

    // 3. Pastikan tabel tasks sudah dibuat otomatis
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `tasks` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

} catch (Throwable $e) {
    // Catat detail error teknis ke server log tanpa menampilkan password ke browser
    error_log("[Database Error] Gagal terhubung ke MySQL ({$host}:{$port}/{$dbname}): " . $e->getMessage());

    // Jika dalam mode Docker atau Production, JANGAN beralih ke SQLite
    if ($isDockerOrProd) {
        http_response_code(500);
        die("Koneksi ke server database gagal. Silakan periksa status service database dan log server.");
    }

    // 4. Fallback SQLite hanya untuk pengembangan lokal non-Docker
    try {
        $sqlitePath = __DIR__ . '/../database.sqlite';
        $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT NULL,
                is_completed INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

    } catch (Throwable $sqliteErr) {
        error_log("[Database Error] Fallback SQLite juga gagal: " . $sqliteErr->getMessage());
        http_response_code(500);
        die("Koneksi database gagal. Silakan periksa izin penulisan atau konfigurasi server.");
    }
}

return $pdo;
