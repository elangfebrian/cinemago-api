<?php
// migrate.php - menjalankan semua file di database/migrations/*.sql secara berurutan.
// Migrasi yang sudah pernah dijalankan dicatat di tabel `migrations` dan dilewati.
//
// Pemakaian (dari folder utama repo):
//   php database/migrate.php

if (php_sapi_name() !== 'cli') {
    exit("Jalankan lewat terminal: php database/migrate.php\n");
}

$localFile = __DIR__ . '/../api/config.local.php';
$cfg = file_exists($localFile)
    ? require $localFile
    : ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'name' => 'cinemago'];

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass']);
if ($conn->connect_error) {
    exit("Gagal konek ke MySQL: {$conn->connect_error}\nPastikan MySQL di XAMPP sudah menyala.\n");
}

// Buat database jika belum ada, lalu pakai
$dbName = $conn->real_escape_string($cfg['name']);
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($cfg['name']);

// Tabel pencatat migrasi
$conn->query(
    "CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB"
);

$applied = [];
$res = $conn->query('SELECT filename FROM migrations');
while ($row = $res->fetch_assoc()) {
    $applied[] = $row['filename'];
}

$files = glob(__DIR__ . '/migrations/*.sql');
sort($files);

$ran = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $applied, true)) {
        echo "[lewati] $name (sudah dijalankan)\n";
        continue;
    }

    $sql = file_get_contents($file);
    if (!$conn->multi_query($sql)) {
        exit("[GAGAL]  $name -> {$conn->error}\n");
    }
    // Habiskan semua hasil multi_query supaya error di statement belakang ikut terdeteksi
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    if ($conn->errno) {
        exit("[GAGAL]  $name -> {$conn->error}\n");
    }

    $stmt = $conn->prepare('INSERT INTO migrations (filename) VALUES (?)');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->close();

    echo "[sukses] $name\n";
    $ran++;
}

echo $ran === 0 ? "Tidak ada migrasi baru.\n" : "Selesai: $ran migrasi dijalankan.\n";
