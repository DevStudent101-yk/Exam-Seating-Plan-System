<?php
// backend/db.php
// Adjust credentials if needed
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'exam_seating';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_errno) {
    // For local dev, show message. In prod, hide details.
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $mysqli->connect_error]);
    exit;
}

$mysqli->set_charset("utf8mb4");
