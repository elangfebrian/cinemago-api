<?php
// POST /login.php
// Body: { "email": "", "password": "" }
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, false, 'Method harus POST');
}

$body     = getJsonBody();
$email    = strtolower(trim($body['email'] ?? ''));
$password = $body['password'] ?? '';

if ($email === '' || $password === '') {
    respond(400, false, 'Email dan password wajib diisi');
}

$stmt = $conn->prepare(
    'SELECT id, first_name, last_name, email, password FROM users WHERE email = ?'
);
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Pesan error sengaja disamakan agar tidak membocorkan email mana yang terdaftar
if (!$user || !password_verify($password, $user['password'])) {
    respond(401, false, 'Email atau password salah');
}

unset($user['password']); // jangan kirim hash ke client
respond(200, true, 'Login berhasil', $user);
