<?php
// backend/upload-csv.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// check admin session
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit;
}

if (!isset($_FILES['csv_file'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'CSV file not received']);
    exit;
}

$file = $_FILES['csv_file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); echo json_encode(['success'=>false,'error'=>'Upload error']); exit;
}

// validate extension
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
if (strtolower($ext) !== 'csv') {
    http_response_code(400); echo json_encode(['success'=>false,'error'=>'Only CSV allowed']); exit;
}

include_once __DIR__ . '/db.php';

$fp = fopen($file['tmp_name'], 'r');
if (!$fp) {
    http_response_code(500); echo json_encode(['success'=>false,'error'=>'Cannot open file']); exit;
}

$inserted = 0;
$skipped = 0;
$errors = [];
// Optionally: if first row is header, you can skip it. Let's detect header by checking if first cell is not numeric.
$rowIndex = 0;
while (($row = fgetcsv($fp)) !== false) {
    $rowIndex++;
    // allow small trimming and ignore empty lines
    if (count($row) === 0) continue;
    // expect columns: cms_id, name, room_no, seat_row, seat_col
    // If header row detected (first row) skip it when it contains letters like "cms" or "name"
    if ($rowIndex === 1) {
        $first = strtolower(trim($row[0]));
        if (strpos($first, 'cms') !== false || strpos($first, 'id') !== false || strpos($first, 'name') !== false) {
            continue; // skip header
        }
    }

    // normalize columns safely
    $cms_id = trim($row[0] ?? '');
    $full_name = trim($row[1] ?? '');
    $room_no = trim($row[2] ?? '');
    $seat_row = isset($row[3]) ? (int)trim($row[3]) : 0;
    $seat_col = isset($row[4]) ? (int)trim($row[4]) : 0;

    if ($cms_id === '' || $full_name === '' || $room_no === '' || $seat_row <= 0 || $seat_col <= 0) {
        $skipped++;
        $errors[] = "Row $rowIndex invalid or missing fields";
        continue;
    }

    // Insert or update existing record based on cms_id
    $stmt = $mysqli->prepare("
        INSERT INTO students (cms_id, full_name, room_no, seat_row, seat_col)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), room_no = VALUES(room_no), seat_row = VALUES(seat_row), seat_col = VALUES(seat_col)
    ");
    $stmt->bind_param('sssii', $cms_id, $full_name, $room_no, $seat_row, $seat_col);
    if ($stmt->execute()) {
        $inserted++;
    } else {
        $errors[] = "DB error on row $rowIndex: " . $stmt->error;
    }
    $stmt->close();
}

fclose($fp);

echo json_encode([
    'success' => true,
    'inserted' => $inserted,
    'skipped' => $skipped,
    'errors' => $errors
]);
