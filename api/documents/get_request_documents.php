<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Authentication required"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$request_id = isset($_GET['request_id']) ? $_GET['request_id'] : null;

if (!$request_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Request ID is required"]);
    exit();
}

try {
    $query = "SELECT * FROM uploaded_files WHERE request_id = (SELECT id FROM requests WHERE request_id = :request_id)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':request_id', $request_id);
    $stmt->execute();

    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $documents
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}
?>