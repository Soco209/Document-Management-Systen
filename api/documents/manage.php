<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

// Check authentication
if (!function_exists('isAuthenticated')) {
    function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Authentication required"]);
    exit();
}

// Check if user is admin
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Admin access required"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetDocumentTypes($db);
            break;
        case 'POST':
            handleCreateDocumentType($db);
            break;
        case 'PUT':
            handleUpdateDocumentType($db);
            break;
        case 'DELETE':
            handleDeleteDocumentType($db);
            break;
        default:
            http_response_code(405);
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}

function handleGetDocumentTypes($db) {
    try {
        $query = "SELECT dt.*, GROUP_CONCAT(CONCAT(df.field_name, ':', df.field_type, ':', df.is_required) SEPARATOR '|') as form_fields 
                  FROM document_types dt 
                  LEFT JOIN document_form_fields df ON dt.id = df.document_type_id 
                  GROUP BY dt.id 
                  ORDER BY dt.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $documentTypes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $formFields = [];
            if (!empty($row['form_fields'])) {
                $fields = explode('|', $row['form_fields']);
                foreach ($fields as $field) {
                    $parts = explode(':', $field);
                    if (count($parts) === 3) {
                        $formFields[] = [
                            'field_name' => $parts[0],
                            'field_type' => $parts[1],
                            'is_required' => (bool)$parts[2]
                        ];
                    }
                }
            }
            
            $documentTypes[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'type_code' => $row['type_code'],
                'category' => $row['category'],
                'template_path' => $row['template_path'],
                'created_at' => $row['created_at'],
                'form_fields' => $formFields
            ];
        }
        
        echo json_encode([
            "success" => true,
            "data" => $documentTypes
        ]);
    } catch (Exception $e) {
        throw new Exception("Failed to fetch document types: " . $e->getMessage());
    }
}

function handleCreateDocumentType($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = $input['name'] ?? '';
        $typeCode = $input['type_code'] ?? '';
        $category = $input['category'] ?? 'template';
        $formFields = $input['form_fields'] ?? [];
        
        // Validate inputs
        if (empty($name) || empty($typeCode)) {
            throw new Exception("Name and type code are required");
        }
        
        // Check if type code already exists
        $checkQuery = "SELECT id FROM document_types WHERE type_code = :type_code";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':type_code', $typeCode);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            throw new Exception("Type code already exists");
        }
        
        // Begin transaction
        $db->beginTransaction();
        
        // Insert document type
        $query = "INSERT INTO document_types (name, type_code, category, created_at) VALUES (:name, :type_code, :category, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type_code', $typeCode);
        $stmt->bindParam(':category', $category);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create document type");
        }
        
        $documentTypeId = $db->lastInsertId();
        
        // Insert form fields
        if (!empty($formFields)) {
            $fieldQuery = "INSERT INTO document_form_fields (document_type_id, field_name, field_type, is_required, field_order) VALUES (:doc_type_id, :field_name, :field_type, :is_required, :field_order)";
            $fieldStmt = $db->prepare($fieldQuery);
            
            foreach ($formFields as $index => $field) {
                $fieldStmt->bindParam(':doc_type_id', $documentTypeId);
                $fieldStmt->bindParam(':field_name', $field['field_name']);
                $fieldStmt->bindParam(':field_type', $field['field_type']);
                $fieldStmt->bindParam(':is_required', $field['is_required']);
                $fieldStmt->bindParam(':field_order', $index);
                
                if (!$fieldStmt->execute()) {
                    throw new Exception("Failed to create form field: " . $field['field_name']);
                }
            }
        }
        
        // Commit transaction
        $db->commit();
        
        echo json_encode([
            "success" => true,
            "message" => "Document type created successfully",
            "document_type_id" => $documentTypeId
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}

function handleUpdateDocumentType($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = $input['id'] ?? '';
        $name = $input['name'] ?? '';
        $typeCode = $input['type_code'] ?? '';
        $category = $input['category'] ?? 'template';
        $templatePath = $input['template_path'] ?? null;
        $formFields = $input['form_fields'] ?? [];
        
        // Validate inputs
        if (empty($id) || empty($name) || empty($typeCode)) {
            throw new Exception("ID, name and type code are required");
        }
        
        // Check if type code already exists for other document types
        $checkQuery = "SELECT id FROM document_types WHERE type_code = :type_code AND id != :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':type_code', $typeCode);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            throw new Exception("Type code already exists");
        }
        
        // Begin transaction
        $db->beginTransaction();
        
        // Update document type
        $query = "UPDATE document_types SET name = :name, type_code = :type_code, category = :category";
        if ($templatePath !== null) {
            $query .= ", template_path = :template_path";
        }
        $query .= " WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type_code', $typeCode);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':id', $id);
        if ($templatePath !== null) {
            $stmt->bindParam(':template_path', $templatePath);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update document type");
        }
        
        // Delete existing form fields
        $deleteFieldsQuery = "DELETE FROM document_form_fields WHERE document_type_id = :doc_type_id";
        $deleteFieldsStmt = $db->prepare($deleteFieldsQuery);
        $deleteFieldsStmt->bindParam(':doc_type_id', $id);
        $deleteFieldsStmt->execute();
        
        // Insert new form fields
        if (!empty($formFields)) {
            $fieldQuery = "INSERT INTO document_form_fields (document_type_id, field_name, field_type, is_required, field_order) VALUES (:doc_type_id, :field_name, :field_type, :is_required, :field_order)";
            $fieldStmt = $db->prepare($fieldQuery);
            
            foreach ($formFields as $index => $field) {
                $fieldStmt->bindParam(':doc_type_id', $id);
                $fieldStmt->bindParam(':field_name', $field['field_name']);
                $fieldStmt->bindParam(':field_type', $field['field_type']);
                $fieldStmt->bindParam(':is_required', $field['is_required']);
                $fieldStmt->bindParam(':field_order', $index);
                
                if (!$fieldStmt->execute()) {
                    throw new Exception("Failed to update form field: " . $field['field_name']);
                }
            }
        }
        
        // Commit transaction
        $db->commit();
        
        echo json_encode([
            "success" => true,
            "message" => "Document type updated successfully"
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}

function handleDeleteDocumentType($db) {
    try {
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            throw new Exception("Document type ID is required");
        }
        
        // Check if document type exists
        $checkQuery = "SELECT id, template_path FROM document_types WHERE id = :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            throw new Exception("Document type not found");
        }
        
        $docType = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        // Begin transaction
        $db->beginTransaction();
        
        // Delete form fields first (due to foreign key constraint)
        $deleteFieldsQuery = "DELETE FROM document_form_fields WHERE document_type_id = :doc_type_id";
        $deleteFieldsStmt = $db->prepare($deleteFieldsQuery);
        $deleteFieldsStmt->bindParam(':doc_type_id', $id);
        $deleteFieldsStmt->execute();
        
        // Delete document type
        $query = "DELETE FROM document_types WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete document type");
        }
        
        // Delete template file if exists
        if (!empty($docType['template_path'])) {
            $filePath = '../../' . $docType['template_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Commit transaction
        $db->commit();
        
        echo json_encode([
            "success" => true,
            "message" => "Document type deleted successfully"
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}
?>