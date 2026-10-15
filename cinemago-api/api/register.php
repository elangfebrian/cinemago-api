<?php
// POST /register.php
// Body: { "first_name": "", "last_name": "", "email": "", "password": "" }
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, false, 'Method harus POST');
}

$body      = getJsonBody();
$firstName = trim($body['first_name'] ?? '');
$lastName  = trim($body['last_name'] ?? '');
$email     = strtolower(trim($body['email'] ?? ''));
$password  = $body['password'] ?? '';

// Validasi
if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
    respond(400, false, 'Semua field wajib diisi');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(400, false, 'Format email tidak valid');
}
if (strlen($password) < 6) {
    respond(400, false, 'Password minimal 6 karakter');
}

// Cek email sudah dipakai atau belum
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    respond(409, false, 'Email sudah terdaftar');
}
$stmt->close();

// Simpan user (password di-hash, jangan simpan teks asli)
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    'INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)'
);
$stmt->bind_param('ssss', $firstName, $lastName, $email, $hash);

if ($stmt->execute()) {
    respond(201, true, 'Registrasi berhasil', [
        'id'         => $stmt->insert_id,
        'first_name' => $firstName,
        'last_name'  => $lastName,
        'email'      => $email,
    ]);
}
respond(500, false, 'Registrasi gagal, coba lagi');
