<?php
// Disable error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set JSON headers first
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ["success" => false, "message" => ""];

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../middleware/auth.php';
    
    // Check authentication
    if (!isAuthenticated()) {
        http_response_code(401);
        $response["message"] = "Authentication required.";
        echo json_encode($response);
        exit();
    }

    // Get current user
    $user = getCurrentUser();
    
    if (!$user || !isset($user['id'])) {
        throw new Exception("Failed to get user information.");
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed.");
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON input: " . json_last_error_msg());
        }
        
        $requestId = $input['request_id'] ?? null;
        $formData = $input['form_data'] ?? [];
        
        error_log("Save form data - Request ID: " . $requestId . ", User ID: " . $user['id']);
        
        if (!$requestId || empty($formData)) {
            throw new Exception("Request ID and form data are required.");
        }
        
        // Verify the request belongs to this user
        $checkQuery = "SELECT id FROM requests WHERE id = :request_id AND student_id = :student_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':request_id', $requestId, PDO::PARAM_INT);
        $checkStmt->bindParam(':student_id', $user['id'], PDO::PARAM_INT);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            throw new Exception("Request not found or access denied. Request ID: " . $requestId);
        }
        
        // Insert form data into application_form_data table
        $insertQuery = "INSERT INTO application_form_data (request_id, field_name, field_value, created_at) 
                       VALUES (:request_id, :field_name, :field_value, NOW())";
        $insertStmt = $db->prepare($insertQuery);
        
        $savedCount = 0;
        foreach ($formData as $fieldName => $fieldValue) {
            $insertStmt->bindParam(':request_id', $requestId, PDO::PARAM_INT);
            $insertStmt->bindParam(':field_name', $fieldName, PDO::PARAM_STR);
            $insertStmt->bindParam(':field_value', $fieldValue, PDO::PARAM_STR);
            
            if (!$insertStmt->execute()) {
                $errorInfo = $insertStmt->errorInfo();
                throw new Exception("Failed to save form field '" . $fieldName . "': " . $errorInfo[2]);
            }
            $savedCount++;
        }
        
        $response["success"] = true;
        $response["message"] = "Form data saved successfully. Saved " . $savedCount . " fields.";
        $response["saved_count"] = $savedCount;
        echo json_encode($response);
        
    } else {
        throw new Exception("Invalid request method.");
    }
    
} catch (Exception $e) {
    error_log("Save Form Data Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    $response["message"] = $e->getMessage();
    $response["error_details"] = [
        "file" => basename($e->getFile()),
        "line" => $e->getLine()
    ];
    http_response_code(500);
    echo json_encode($response);
} catch (Throwable $e) {
    // Catch any fatal errors
    error_log("Save Form Data Fatal Error: " . $e->getMessage());
    $response["message"] = "A server error occurred: " . $e->getMessage();
    http_response_code(500);
    echo json_encode($response);
}
?>
