<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../vendor/autoload.php';

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
            $request_id = "REQ-" . date("Ymd") . "-" . substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);

            $query = "INSERT INTO requests (request_id, student_id, document_type_id, purpose, status) 
                      VALUES (:request_id, :student_id, :document_type_id, :purpose, 'Pending')";
            $stmt = $db->prepare($query);

            $stmt->bindParam(':request_id', $request_id);
            $stmt->bindParam(':student_id', $user['id']);
            $stmt->bindParam(':document_type_id', $document_type_id);
            $stmt->bindParam(':purpose', $data->purpose);

            if ($stmt->execute()) {
                // Send email notification to student
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

                $response["success"] = true;
                $response["message"] = "Request submitted successfully.";
                $response["request_id"] = $request_id;
                $response["id"] = $db->lastInsertId(); // Auto-increment ID
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
                $userQuery = "SELECT u.full_name, u.email FROM users u JOIN requests r ON u.id = r.student_id WHERE r.request_id = :request_id";
                $userStmt = $db->prepare($userQuery);
                $userStmt->bindParam(':request_id', $requestId);
                $userStmt->execute();
                $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($userRow) {
                    error_log("User found, preparing to send email.");

                    // --- Fetch Admin Email (Sender) ---
                    $adminQuery = "SELECT email, full_name FROM users WHERE role = 'admin' LIMIT 1";
                    $adminStmt = $db->prepare($adminQuery);
                    $adminStmt->execute();
                    $adminRow = $adminStmt->fetch(PDO::FETCH_ASSOC);
                    $adminEmail = $adminRow ? $adminRow['email'] : 'default-admin@example.com';
                    $adminName = $adminRow ? $adminRow['full_name'] : 'Default Admin';

                    // --- Brevo Email Sending Logic ---
                    $config = Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', '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');
                    $config = Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('partner-key', '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');

                    $apiInstance = new Brevo\Client\Api\TransactionalEmailsApi(
                        new GuzzleHttp\Client(),
                        $config
                    );

                    $emailBody = "<h1>Request Status Update</h1>";
                    $emailBody .= "<p>Your request with ID <strong>{$requestId}</strong> has been updated.</p>";
                    $emailBody .= "<p><strong>New Status:</strong> " . htmlspecialchars($data->status) . "</p>";
                    if (!empty($data->admin_notes)) {
                        $emailBody .= "<p><strong>Admin Feedback:</strong> " . htmlspecialchars($data->admin_notes) . "</p>";
                    }

                    $sendSmtpEmail = new \Brevo\Client\Model\SendSmtpEmail([
                         'subject' => 'Request Status Update',
                         'sender' => ['name' => $adminName, 'email' => $adminEmail],
                         'replyTo' => ['name' => $adminName, 'email' => $adminEmail],
                         'to' => [[ 'name' => $userRow['full_name'], 'email' => $userRow['email']]],
                         'htmlContent' => $emailBody
                    ]);

                    try {
                        $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
                        error_log("Email sent successfully to: " . $userRow['email']);
                    } catch (Exception $e) {
                        error_log('Exception when calling TransactionalEmailsApi->sendTransacEmail: '. $e->getMessage());
                    }
                } else {
                    error_log("User not found for the given request ID.");
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

            // Verify ownership and status
            $verifyQuery = "SELECT id FROM requests WHERE request_id = :request_id AND student_id = :student_id AND status = 'Pending'";
            $verifyStmt = $db->prepare($verifyQuery);
            $verifyStmt->bindParam(':request_id', $requestId);
            $verifyStmt->bindParam(':student_id', $user['id']);
            $verifyStmt->execute();
if ($verifyStmt->rowCount() === 0) {
                throw new Exception("Request not found, does not belong to you, or is not in 'Pending' status.", 403);
            }

            // Delete the request
            $deleteQuery = "DELETE FROM requests WHERE request_id = :request_id";
            $deleteStmt = $db->prepare($deleteQuery);
            $deleteStmt->bindParam(':request_id', $requestId); // FIXED: was :request_.id

            if ($deleteStmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Request deleted successfully.";
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
    $response["message"] = "An internal server error occurred. Please try again later.";
    http_response_code(500);
}

echo json_encode($response);