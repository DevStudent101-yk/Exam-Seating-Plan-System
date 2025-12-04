<?php
// backend/admin-register.php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';


if ($username === '' || $password === '') {
    http_response_code(400); echo json_encode(['success'=>false,'error'=>'username and password required']); exit;
}

include_once __DIR__ . '/db.php';

// check existing
$stmt = $mysqli->prepare("SELECT id FROM admins WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    echo json_encode(['success'=>false,'error'=>'Username already taken']); exit;
}
$stmt->close();

// insert
$passHash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $username, $email, $passHash);
$ok = $stmt->execute();
if (!$ok) {
    http_response_code(500); echo json_encode(['success'=>false,'error'=>'DB insert error']); exit;
}
$stmt->close();
echo json_encode(['success'=>true,'message'=>'Admin registered']);
