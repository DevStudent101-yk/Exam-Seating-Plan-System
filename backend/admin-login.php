<?php 
// backend/admin-login.php
header('Content-Type: application/json; charset=utf-8');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Method not allowed']);
    exit;
}

// READ FORM DATA, NOT JSON
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'username and password required']);
    exit;
}

include_once __DIR__ . '/db.php';

$stmt = $mysqli->prepare("SELECT id, password_hash FROM admins WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
$admin = $res->fetch_assoc();
$stmt->close();

if (!$admin || !password_verify($password, $admin['password_hash'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Invalid credentials']);
    exit;
}

// login success
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_username'] = $username;

echo json_encode(['success'=>true,'message'=>'Logged in']);
