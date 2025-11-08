<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$response = ["success" => false, "message" => "An error occurred."];

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = Auth::authenticate($db); // Ensures the user is authenticated

    if ($user['role'] !== 'admin') {
        throw new Exception("Forbidden: You do not have permission to perform this action.", 403);
    }

    $userId = $_GET['id'] ?? '';
    if (empty($userId)) {
        throw new Exception("User ID is required.");
    }

    // Fetch user details
    $query = "SELECT id, full_name, email, role, status, created_at FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userDetails) {
        throw new Exception("User not found.", 404);
    }

    // Fetch user requests
    $query = "SELECT r.request_id, r.submission_date, r.status, d.name as document_name
              FROM requests r
              LEFT JOIN document_types d ON r.document_type_id = d.id
              WHERE r.student_id = :student_id
              ORDER BY r.submission_date DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $userId);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $userDetails['requests'] = $requests;

    // Fetch user documents
    $query = "SELECT rd.file_name, rd.file_path, rd.upload_date, r.request_id
              FROM request_documents rd
              JOIN requests r ON rd.request_id = r.id
              WHERE r.student_id = :student_id
              ORDER BY rd.upload_date DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $userId);
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $userDetails['documents'] = $documents;

    $response["success"] = true;
    $response["data"] = $userDetails;
    $response["message"] = "User details fetched successfully.";
    http_response_code(200);

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
    $code = is_int($e->getCode()) && $e->getCode() !== 0 ? $e->getCode() : 400;
    http_response_code($code);
    error_log("User Details API Error: " . $e->getMessage());
}

echo json_encode($response);
?>