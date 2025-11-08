<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../middleware/auth.php';

$response = ['success' => false, 'message' => 'An error occurred.'];

try {
    $user = requireAuth();

    if ($user['role'] !== 'admin') {
        throw new Exception("Forbidden: You do not have permission to perform this action.", 403);
    }

    if (!isset($_FILES['template'])) {
        throw new Exception("No file was uploaded.");
    }

    $file = $_FILES['template'];
    $docTypeId = $_POST['doc_type_id'] ?? null;

    if (!$docTypeId) {
        throw new Exception("Document type ID is missing.");
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error: " . $file['error']);
    }

    $uploadDir = __DIR__ . '/../../uploads/templates/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $uploadFile = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        $response['success'] = true;
        $response['message'] = 'File uploaded successfully.';
        $response['file_path'] = '/uploads/templates/' . $fileName;
        http_response_code(200);
    } else {
        throw new Exception("Failed to move uploaded file.");
    }

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
    $code = is_int($e->getCode()) && $e->getCode() !== 0 ? $e->getCode() : 400;
    http_response_code($code);
    error_log("Template Upload Error: " . $e->getMessage());
}

echo json_encode($response);
?>