<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Must be logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

// Must POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Method not allowed']);
    exit;
}

// READ FormData ONLY
$cms_id    = trim($_POST['cms_id'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$room_no   = trim($_POST['room_no'] ?? '');
$seat_row  = (int)($_POST['seat_row'] ?? 0);
$seat_col  = (int)($_POST['seat_col'] ?? 0);

// Validate
if ($cms_id === '' || $full_name === '' || $room_no === '' || $seat_row <= 0 || $seat_col <= 0) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Missing or invalid fields']);
    exit;
}

require_once __DIR__ . '/db.php';

// INSERT
$stmt = $mysqli->prepare("
    INSERT INTO students (cms_id, full_name, room_no, seat_row, seat_col)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
      full_name = VALUES(full_name),
      room_no   = VALUES(room_no),
      seat_row  = VALUES(seat_row),
      seat_col  = VALUES(seat_col)
");

$stmt->bind_param('sssii', $cms_id, $full_name, $room_no, $seat_row, $seat_col);

if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Student saved']);
} else {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'DB error: '.$stmt->error]);
}

$stmt->close();
