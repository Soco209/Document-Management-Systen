<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$response = array("success" => false, "message" => "");

try {
    $database = new Database();
    $db = $database->getConnection();
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    $user_id = getCurrentUserId();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['files'])) {
        throw new Exception("Invalid request.");
    }

    if (!isset($_POST['request_id'])) {
        throw new Exception("Request ID is required.");
    }

    $request_id = $_POST['request_id'];
    error_log("Upload - Received request_id: " . $request_id . " (type: " . gettype($request_id) . ") for user_id: " . $user_id);
    
    // Verify that the request exists and belongs to the current user
    $verifyQuery = "SELECT id FROM requests WHERE id = :request_id AND student_id = :student_id";
    $verifyStmt = $db->prepare($verifyQuery);
    $verifyStmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $verifyStmt->bindParam(':student_id', $user_id, PDO::PARAM_INT);
    $verifyStmt->execute();
    
    if ($verifyStmt->rowCount() === 0) {
        throw new Exception("Request not found or access denied. Request ID: " . $request_id . ", User ID: " . $user_id);
    }
    
    error_log("Upload - Request verified successfully");
    $upload_dir = "../../uploads/" . $request_id . "/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $uploaded_files = [];
    $requirement_names = isset($_POST['requirement_names']) ? $_POST['requirement_names'] : [];

    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['files']['name'][$key];
        $file_size = $_FILES['files']['size'][$key];
        $file_type = $_FILES['files']['type'][$key];
        $file_error = $_FILES['files']['error'][$key];
        $requirement_name = isset($requirement_names[$key]) ? $requirement_names[$key] : 'General Upload';

        if ($file_error !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $file_error);
        }

        if ($file_size > 10 * 1024 * 1024) { // Increased to 10MB
            throw new Exception("File too large: " . $file_name . " (Max: 10MB)");
        }

        $allowed_types = [
            'application/pdf', 
            'image/jpeg', 
            'image/png', 
            'image/jpg',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'application/msword' // .doc
        ];
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("Invalid file type: " . $file_name);
        }

        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_filename = uniqid() . "_" . preg_replace("/[^a-zA-Z0-9\.]/", "_", $file_name);
        $file_path = $upload_dir . $unique_filename;

        if (move_uploaded_file($tmp_name, $file_path)) {
            $query = "INSERT INTO uploaded_files (request_id, requirement_name, file_name, file_path, file_size, mime_type, created_at) 
                     VALUES (:request_id, :requirement_name, :file_name, :file_path, :file_size, :mime_type, NOW())";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
            $stmt->bindParam(":requirement_name", $requirement_name);
            $stmt->bindParam(":file_name", $file_name);
            $stmt->bindParam(":file_path", $file_path);
            $stmt->bindParam(":file_size", $file_size);
            $stmt->bindParam(":mime_type", $file_type);
            
            error_log("Upload - Inserting file: " . $file_name . " for request_id: " . $request_id);
            
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                error_log("Upload - Database insert failed: " . json_encode($errorInfo));
                throw new Exception("Database error: " . $errorInfo[2]);
            }

            $uploaded_files[] = [
                'file_name' => $file_name,
                'requirement' => $requirement_name
            ];
        }
    }

    $response["success"] = true;
    $response["message"] = "Files uploaded successfully!";
    $response["files"] = $uploaded_files;

} catch (Exception $e) {
    http_response_code(400);
    error_log("Upload error: " . $e->getMessage());
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
