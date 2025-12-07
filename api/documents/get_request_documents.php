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
    // Get the internal request ID
    $idQuery = "SELECT id FROM requests WHERE request_id = :request_id";
    $idStmt = $db->prepare($idQuery);
    $idStmt->bindParam(':request_id', $request_id);
    $idStmt->execute();
    $requestData = $idStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$requestData) {
        throw new Exception("Request not found");
    }
    
    $internalRequestId = $requestData['id'];
    
    // Get uploaded files
    $filesQuery = "SELECT * FROM uploaded_files WHERE request_id = :request_id";
    $filesStmt = $db->prepare($filesQuery);
    $filesStmt->bindParam(':request_id', $internalRequestId, PDO::PARAM_INT);
    $filesStmt->execute();
    $uploadedFiles = $filesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get application form data (custom fields)
    $formDataQuery = "SELECT field_name, field_value, created_at FROM application_form_data WHERE request_id = :request_id ORDER BY id";
    $formDataStmt = $db->prepare($formDataQuery);
    $formDataStmt->bindParam(':request_id', $internalRequestId, PDO::PARAM_INT);
    $formDataStmt->execute();
    $formData = $formDataStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => [
            "uploaded_files" => $uploadedFiles,
            "form_data" => $formData
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}
?>