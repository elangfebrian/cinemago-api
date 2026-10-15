<?php
// config.php - koneksi database + pengaturan header JSON/CORS

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');            // agar Flutter Web (Chrome) bisa akses
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Request preflight dari browser: cukup balas OK
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Kredensial dibaca dari config.local.php (tidak ikut di-commit ke Git).
// Jika file itu tidak ada, dipakai default XAMPP.
$localFile = __DIR__ . '/config.local.php';
$db = file_exists($localFile)
    ? require $localFile
    : ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'name' => 'cinemago'];

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($db['host'], $db['user'], $db['pass'], $db['name']);
if ($conn->connect_error) {
    respond(500, false, 'Koneksi database gagal');
}
$conn->set_charset('utf8mb4');

// Kirim response JSON lalu hentikan script
function respond($code, $success, $message, $data = null) {
    http_response_code($code);
    $out = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $out['data'] = $data;
    }
    echo json_encode($out);
    exit;
}

// Ambil body JSON dari request
function getJsonBody() {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    return is_array($json) ? $json : [];
}
