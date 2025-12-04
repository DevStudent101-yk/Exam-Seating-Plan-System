<?php
require_once __DIR__ . '/db.php';

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$cms_id = trim($_GET['cms'] ?? '');

if ($cms_id === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'CMS ID required']);
    exit;
}

$stmt = $mysqli->prepare("SELECT cms_id, full_name, room_no, seat_row, seat_col FROM students WHERE cms_id = ?");
$stmt->bind_param('s', $cms_id);
$stmt->execute();
$res = $stmt->get_result();
$student = $res->fetch_assoc();
$stmt->close();

if (!$student) {
    echo json_encode(['success' => false, 'error' => 'Student not found']);
    exit;
}

$stmt = $mysqli->prepare("SELECT MAX(seat_row) AS max_row, MAX(seat_col) AS max_col, COUNT(*) AS seat_count FROM students WHERE room_no = ?");
$stmt->bind_param('s', $student['room_no']);
$stmt->execute();
$res = $stmt->get_result();
$roomInfo = $res->fetch_assoc();
$stmt->close();

echo json_encode([
    'success' => true,
    'student' => $student,
    'room' => [
        'room_no' => $student['room_no'],
        'max_row' => (int)$roomInfo['max_row'],
        'max_col' => (int)$roomInfo['max_col'],
        'seat_count' => (int)$roomInfo['seat_count']
    ]
]);
?>
