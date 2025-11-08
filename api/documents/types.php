<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

$response = ["success" => false, "message" => "An error occurred."];

try {
    $database = new Database();
    $db = $database->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
        $method = 'PUT';
    }

    // Only allow specific methods. Exit silently for others to prevent interference.
    if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
        exit();
    }

    if ($method === 'GET') {
        // Fetch all document types
        $docTypesQuery = "SELECT id, type_code, name, description, category, template_path FROM document_types";
        $docTypesStmt = $db->prepare($docTypesQuery);
        $docTypesStmt->execute();
        $documentTypes = $docTypesStmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

        // Fetch all requirements
        $reqsQuery = "SELECT document_type_id, id, requirement_name, requirement_description, is_mandatory FROM required_documents";
        $reqsStmt = $db->prepare($reqsQuery);
        $reqsStmt->execute();
        $requirements = $reqsStmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        // Map requirements to document types
        foreach ($documentTypes as $id => &$docType) {
            $docType = $docType[0]; // unwrap the grouped array
            $docType['id'] = $id;
            $docType['requirements'] = $requirements[$id] ?? [];
        }

        $response["success"] = true;
        $response["data"] = array_values($documentTypes); // re-index the array
        http_response_code(200);

    } else {
        // For POST, PUT, DELETE, require admin authentication
        if (!isAuthenticated() || !isAdmin()) {
            throw new Exception("Forbidden: Admin access required.", 403);
        }

        switch ($method) {
            case 'POST':
                $data = json_decode(file_get_contents("php://input"));
                if (empty($data->name)) {
                    throw new Exception("Document type name is required.");
                }
                $type_code = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($data->name)));

                $query = "INSERT INTO document_types (name, type_code, category) VALUES (:name, :type_code, 'template')";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $data->name);
                $stmt->bindParam(':type_code', $type_code);

                if ($stmt->execute()) {
                    $response["success"] = true;
                    $response["message"] = "Document type added successfully.";
                    $response["data"] = ['id' => $db->lastInsertId(), 'name' => $data->name, 'type_code' => $type_code, 'category' => 'template'];
                    http_response_code(201);
                } else {
                    throw new Exception("Failed to add document type.");
                }
                break;

            case 'PUT':
                $id = $_GET['id'] ?? '';
                if (empty($id)) {
                    throw new Exception("Document type ID is required.");
                }

                $name = $_POST['name'] ?? '';
                if (empty($name)) {
                    throw new Exception("Document type name is required.");
                }

                $templatePath = null;
                if (isset($_FILES['template'])) {
                    $file = $_FILES['template'];
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/../../uploads/templates/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $fileName = uniqid() . '_' . basename($file['name']);
                        $uploadFile = $uploadDir . $fileName;

                        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                            $templatePath = '/uploads/templates/' . $fileName;
                        } else {
                            throw new Exception("Failed to move uploaded file.");
                        }
                    } else {
                        throw new Exception("File upload error: " . $file['error']);
                    }
                }

                if ($templatePath) {
                    $query = "UPDATE document_types SET name = :name, template_path = :template_path WHERE id = :id";
                } else {
                    $query = "UPDATE document_types SET name = :name WHERE id = :id";
                }
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':id', $id);
                if ($templatePath) {
                    $stmt->bindParam(':template_path', $templatePath);
                }

                if ($stmt->execute()) {
                    $response["success"] = true;
                    $response["message"] = "Document type updated successfully.";
                    http_response_code(200);
                } else {
                    throw new Exception("Failed to update document type.");
                }
                break;

            case 'DELETE':
                $id = $_GET['id'] ?? '';
                if (empty($id)) {
                    throw new Exception("Document type ID is required.");
                }

                // Check if the document type is in use
                $checkQuery = "SELECT COUNT(*) FROM requests WHERE document_type_id = :id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':id', $id);
                $checkStmt->execute();
                if ($checkStmt->fetchColumn() > 0) {
                    throw new Exception("Cannot delete this document type because it is currently in use by existing requests.");
                }

                $query = "DELETE FROM document_types WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    $response["success"] = true;
                    $response["message"] = "Document type deleted successfully.";
                    http_response_code(200);
                } else {
                    throw new Exception("Failed to delete document type.");
                }
                break;

            default:
                throw new Exception("Method not supported.", 405);
        }
    }

} catch (Exception $e) {
    $code = is_int($e->getCode()) && $e->getCode() !== 0 ? $e->getCode() : 400;
    http_response_code($code);
    $response["message"] = $e->getMessage();
    error_log("Document Types API Error: " . $e->getMessage());
}

echo json_encode($response);
?>
