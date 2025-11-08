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
    $upload_dir = "../../uploads/" . $request_id . "/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $uploaded_files = [];

    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['files']['name'][$key];
        $file_size = $_FILES['files']['size'][$key];
        $file_type = $_FILES['files']['type'][$key];
        $file_error = $_FILES['files']['error'][$key];

        if ($file_error !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $file_error);
        }

        if ($file_size > 5 * 1024 * 1024) {
            throw new Exception("File too large: " . $file_name);
        }

        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("Invalid file type: " . $file_name);
        }

        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_filename = uniqid() . "_" . preg_replace("/[^a-zA-Z0-9\.]/", "_", $file_name);
        $file_path = $upload_dir . $unique_filename;

        if (move_uploaded_file($tmp_name, $file_path)) {
            $query = "INSERT INTO uploaded_files (request_id, file_name, file_path, file_size, mime_type) VALUES (:request_id, :file_name, :file_path, :file_size, :mime_type)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":request_id", $request_id);
            $stmt->bindParam(":file_name", $file_name);
            $stmt->bindParam(":file_path", $file_path);
            $stmt->bindParam(":file_size", $file_size);
            $stmt->bindParam(":mime_type", $file_type);
            $stmt->execute();

            $uploaded_files[] = $file_name;
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
?>