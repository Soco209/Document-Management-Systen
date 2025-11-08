<?php
// /student_affairs/api/test_db.php
header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';

$response = ['success' => false, 'message' => 'Test failed'];

try {
    $database = new Database();
    $db = $database->getConnection();
    $response['success'] = true;
    $response['message'] = 'Database connection successful!';
} catch (Exception $e) {
    $response['message'] = 'Database connection failed: ' . $e->getMessage();
}

echo json_encode($response);
?>