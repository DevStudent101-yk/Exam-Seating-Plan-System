<?php
// backend/admin-logout.php
session_start();
session_unset();
session_destroy();
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success'=>true,'message'=>'Logged out']);
