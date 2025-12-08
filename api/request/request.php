<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
// Remove vendor/autoload requirement if not needed
// require_once __DIR__ . '/../../vendor/autoload.php';

$response = ["success" => false, "message" => "An error occurred."];

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }

    $user_id = getCurrentUserId();
    $role = getRole();

    // A simple user array to replace the old $user object
    $user = ['id' => $user_id, 'role' => $role];

    $method = $_SERVER['REQUEST_METHOD'];
    error_log("Request method detected: " . $method);

    switch ($method) {
        case 'POST':
            // Create a new request
            $data = json_decode(file_get_contents("php://input"), false);

            if (empty($data->document_type) || empty($data->purpose)) {
                throw new Exception("Document type and purpose are required.");
            }

            // Get document_type_id from document_types table based on type_code
            $docTypeQuery = "SELECT id FROM document_types WHERE type_code = :type_code";
            $docTypeStmt = $db->prepare($docTypeQuery);
            $docTypeStmt->bindParam(':type_code', $data->document_type);
            $docTypeStmt->execute();
            $docTypeRow = $docTypeStmt->fetch(PDO::FETCH_ASSOC);

            if (!$docTypeRow) {
                throw new Exception("Invalid document type specified.");
            }
            $document_type_id = $docTypeRow['id'];

            // Generate a unique request ID
            // Use "REQ-" prefix for all requests (both applications and simple requests)
            $prefix = "REQ";
            $request_id = "{$prefix}-" . date("Ymd") . "-" . substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);

            $query = "INSERT INTO requests (request_id, student_id, document_type_id, purpose, status) 
                      VALUES (:request_id, :student_id, :document_type_id, :purpose, 'Pending')";
            $stmt = $db->prepare($query);

            $stmt->bindParam(':request_id', $request_id);
            $stmt->bindParam(':student_id', $user['id']);
            $stmt->bindParam(':document_type_id', $document_type_id);
            $stmt->bindParam(':purpose', $data->purpose);

            if ($stmt->execute()) {
                // Get the inserted ID for file uploads
                $insertedId = $db->lastInsertId();
                error_log("Request created successfully - ID: " . $insertedId . ", Request ID: " . $request_id);
                
                // Send email notification to student (optional)
                try {
                    if (file_exists(__DIR__ . '/../../utils/email.php')) {
                        require_once __DIR__ . '/../../utils/email.php';
                        $userQuery = "SELECT full_name, email FROM users WHERE id = :user_id";
                        $userStmt = $db->prepare($userQuery);
                        $userStmt->bindParam(':user_id', $user['id']);
                        $userStmt->execute();
                        $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);

                        if ($userRow) {
                            $emailBody = "<h1>Request Submitted</h1>";
                            $emailBody .= "<p>Your request with ID <strong>{$request_id}</strong> has been submitted successfully.</p>";
                            $emailBody .= "<p>You will be notified when the status of your request is updated.</p>";
                            sendEmail($userRow['email'], $userRow['full_name'], "Request Submitted Successfully", $emailBody);
                        }
                    }
                } catch (Exception $emailError) {
                    // Log email error but don't fail the request
                    error_log("Email notification failed: " . $emailError->getMessage());
                }

                $response["success"] = true;
                $response["message"] = "Request submitted successfully.";
                $response["request_id"] = $request_id;
                $response["id"] = $insertedId; // Database ID for file uploads
                http_response_code(201);
            } else {
                throw new Exception("Failed to create request in the database.");
            }
            break;

        case 'GET':
            // Fetch requests
            if ($user['role'] === 'admin') {
                $query = "SELECT r.request_id, r.submission_date, r.status, r.purpose, r.admin_notes,
                                 u.full_name AS student_name, u.email AS student_email,
                                 d.name AS document_name
                          FROM requests r
                          JOIN users u ON r.student_id = u.id
                          LEFT JOIN document_types d ON r.document_type_id = d.id
                          ORDER BY r.submission_date DESC";
                $stmt = $db->prepare($query);
            } else {
                $query = "SELECT r.request_id, r.submission_date, r.status, r.purpose, r.admin_notes,
                                 d.name AS document_name
                          FROM requests r
                          LEFT JOIN document_types d ON r.document_type_id = d.id
                          WHERE r.student_id = :student_id
                          ORDER BY r.submission_date DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':student_id', $user['id']);
            }

            $stmt->execute();
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response["success"] = true;
            $response["data"] = $requests;
            $response["message"] = "Requests fetched successfully.";
            http_response_code(200);
            break;

        case 'PUT':
            // Update request (Admin only)
            if ($user['role'] !== 'admin') {
                throw new Exception("Forbidden: You do not have permission to perform this action.", 403);
            }

            $requestId = $_GET['id'] ?? '';
            if (empty($requestId)) {
                throw new Exception("Request ID is required.");
            }

            $data = json_decode(file_get_contents("php://input"), false);
            if (empty($data->status)) {
                throw new Exception("Status is required for update.");
            }

            $query = "UPDATE requests SET status = :status, admin_notes = :admin_notes WHERE request_id = :request_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':status', $data->status);
            $stmt->bindValue(':admin_notes', $data->admin_notes ?? null);
            $stmt->bindParam(':request_id', $requestId);

            error_log("Executing PUT for request ID: {$requestId}");
            error_log("Data: " . json_encode($data));

            $executionResult = $stmt->execute();
            $rowCount = $stmt->rowCount();

            error_log("Execution result: " . ($executionResult ? 'true' : 'false'));
            error_log("Affected rows: {$rowCount}");

            if ($executionResult && $rowCount > 0) {
                // Notify student
                error_log("Update successful, trying to notify student.");
                
                // Get user details for email notification
                $userQuery = "SELECT u.id as user_id, u.full_name, u.email, dt.name as document_name
                              FROM users u 
                              JOIN requests r ON u.id = r.student_id 
                              JOIN document_types dt ON r.document_type_id = dt.id
                              WHERE r.request_id = :request_id";
                
                $userStmt = $db->prepare($userQuery);
                $userStmt->bindParam(':request_id', $requestId);
                $userStmt->execute();
                $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($userRow) {
                    error_log("User found: " . $userRow['email'] . ", sending email notification.");
                    
                    // Include the email utility file and send notification
                    try {
                        require_once __DIR__ . '/../../utils/email.php';
                        
                        $emailSent = sendFormStatusUpdateEmail(
                            $userRow['user_id'],
                            $userRow['document_name'],
                            $data->status,
                            $data->admin_notes ?? ''
                        );
                        
                        if ($emailSent) {
                            error_log("Email notification sent successfully to: " . $userRow['email']);
                        } else {
                            error_log("Email notification failed to send to: " . $userRow['email']);
                        }
                    } catch (Exception $emailError) {
                        error_log("Email notification error: " . $emailError->getMessage());
                        // Don't throw - we still want to return success for the status update
                    }
                } else {
                    error_log("User not found for request ID: " . $requestId);
                }

                $response["success"] = true;
                $response["message"] = "Request status updated successfully.";
                http_response_code(200);
            } else {
                throw new Exception("Request not found or no changes made.", 404);
            }
            break;

        case 'DELETE':
            if ($user['role'] !== 'student') {
                throw new Exception("Forbidden: You do not have permission to perform this action.", 403);
            }

            // Delete pending request (Student only)
            $requestId = $_GET['id'] ?? '';
            if (empty($requestId)) {
                throw new Exception("Request ID is required.");
            }

            // Verify ownership and status and get the internal ID
            $verifyQuery = "SELECT id FROM requests WHERE request_id = :request_id AND student_id = :student_id AND status = 'Pending'";
            $verifyStmt = $db->prepare($verifyQuery);
            $verifyStmt->bindParam(':request_id', $requestId);
            $verifyStmt->bindParam(':student_id', $user['id']);
            $verifyStmt->execute();
            
            if ($verifyStmt->rowCount() === 0) {
                throw new Exception("Request not found, does not belong to you, or is not in 'Pending' status.", 403);
            }
            
            $requestData = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            $internalRequestId = $requestData['id'];

            // Get all uploaded files for this request before deleting
            $filesQuery = "SELECT file_path FROM uploaded_files WHERE request_id = :request_id";
            $filesStmt = $db->prepare($filesQuery);
            $filesStmt->bindParam(':request_id', $internalRequestId, PDO::PARAM_INT);
            $filesStmt->execute();
            $uploadedFiles = $filesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Delete the request (this will cascade delete uploaded_files records)
            $deleteQuery = "DELETE FROM requests WHERE request_id = :request_id";
            $deleteStmt = $db->prepare($deleteQuery);
            $deleteStmt->bindParam(':request_id', $requestId);

            if ($deleteStmt->execute()) {
                // Delete physical files after successful database deletion
                $deletedFilesCount = 0;
                foreach ($uploadedFiles as $file) {
                    if (!empty($file['file_path'])) {
                        $fullPath = __DIR__ . '/../../' . ltrim($file['file_path'], '/');
                        if (file_exists($fullPath)) {
                            if (unlink($fullPath)) {
                                $deletedFilesCount++;
                                error_log("Deleted file: " . $fullPath);
                            } else {
                                error_log("Warning: Failed to delete file: " . $fullPath);
                            }
                        } else {
                            error_log("File not found: " . $fullPath);
                        }
                    }
                }
                
                error_log("Request deleted: " . $requestId . ", Files deleted: " . $deletedFilesCount);
                
                $response["success"] = true;
                $response["message"] = "Request deleted successfully.";
                $response["deleted_files"] = $deletedFilesCount;
                http_response_code(200);
            } else {
                throw new Exception("Failed to delete the request.");
            }
            break;

        case 'OPTIONS':
            http_response_code(200);
            exit;

        default:
            throw new Exception("Method not supported.", 405);
    }

} catch (Exception $e) {
    error_log("Request API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    // Show actual error message in development (change to generic message in production)
    $response["message"] = $e->getMessage();
    $response["error_details"] = [
        "file" => $e->getFile(),
        "line" => $e->getLine(),
        "trace" => $e->getTraceAsString()
    ];
    
    http_response_code(500);
}

echo json_encode($response);