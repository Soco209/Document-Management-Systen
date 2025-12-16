<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

// Check authentication using JWT
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Authentication required"]);
    exit();
}

// Check if user is admin (except for GET requests which students need)
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET' && !isAdmin()) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Admin access required"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

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
        // Get document types
        $query = "SELECT * FROM document_types ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $documentTypes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Get form fields for this document type with field IDs
            $formFieldsQuery = "SELECT id, field_name, field_type, is_required, field_order 
                               FROM document_form_fields 
                               WHERE document_type_id = :doc_type_id 
                               ORDER BY field_order";
            $formFieldsStmt = $db->prepare($formFieldsQuery);
            $formFieldsStmt->bindParam(':doc_type_id', $row['id']);
            $formFieldsStmt->execute();
            
            $formFields = [];
            while ($fieldRow = $formFieldsStmt->fetch(PDO::FETCH_ASSOC)) {
                $formFields[] = [
                    'id' => $fieldRow['id'],
                    'field_name' => $fieldRow['field_name'],
                    'field_type' => $fieldRow['field_type'],
                    'is_required' => (bool)$fieldRow['is_required'],
                    'field_order' => $fieldRow['field_order']
                ];
            }
            
            // Get requirements for this document type
            $reqQuery = "SELECT id, requirement_name, requirement_description, file_type, is_mandatory 
                        FROM required_documents 
                        WHERE document_type_id = :doc_type_id 
                        ORDER BY id";
            $reqStmt = $db->prepare($reqQuery);
            $reqStmt->bindParam(':doc_type_id', $row['id']);
            $reqStmt->execute();
            
            $requirements = [];
            while ($reqRow = $reqStmt->fetch(PDO::FETCH_ASSOC)) {
                $requirements[] = [
                    'id' => $reqRow['id'],
                    'requirement_name' => $reqRow['requirement_name'],
                    'name' => $reqRow['requirement_name'], // alias for compatibility
                    'requirement_description' => $reqRow['requirement_description'],
                    'file_type' => $reqRow['file_type'],
                    'is_mandatory' => (bool)$reqRow['is_mandatory']
                ];
            }
            
            $documentTypes[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'type_code' => $row['type_code'],
                'description' => $row['description'],
                'category' => $row['category'],
                'template_path' => $row['template_path'],
                'created_at' => $row['created_at'],
                'form_fields' => $formFields,
                'requirements' => $requirements
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
        // Check if this is a special action (add/delete/update form field)
        $rawInput = file_get_contents('php://input');
        $jsonInput = json_decode($rawInput, true);
        
        if (isset($jsonInput['action']) && $jsonInput['action'] === 'add_form_field') {
            handleAddFormField($db, $jsonInput);
            return;
        }
        
        if (isset($jsonInput['action']) && $jsonInput['action'] === 'update_form_field') {
            handleUpdateFormField($db, $jsonInput);
            return;
        }
        
        // Check if this is multipart form data (has file upload)
        if (!empty($_FILES['template'])) {
            $name = $_POST['name'] ?? '';
            $typeCode = $_POST['type_code'] ?? '';
            $category = $_POST['category'] ?? 'template';
            $description = $_POST['description'] ?? '';
            $formFieldsJson = $_POST['form_fields'] ?? '[]';
            $formFields = json_decode($formFieldsJson, true) ?? [];
        } else {
            $input = $jsonInput ?? [];
            $name = $input['name'] ?? '';
            $typeCode = $input['type_code'] ?? '';
            $category = $input['category'] ?? 'template';
            $description = $input['description'] ?? '';
            $formFields = $input['form_fields'] ?? [];
        }
        
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
        
        // Handle file upload if present
        $templatePath = null;
        if (isset($_FILES['template']) && $_FILES['template']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['template'];
            $uploadDir = __DIR__ . '/../../uploads/templates/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = uniqid() . '_' . basename($file['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                $templatePath = 'uploads/templates/' . $fileName;
            } else {
                throw new Exception("Failed to upload template file");
            }
        }
        
        // Begin transaction
        $db->beginTransaction();
        
        // Insert document type
        $query = "INSERT INTO document_types (name, type_code, category, description, template_path, created_at) VALUES (:name, :type_code, :category, :description, :template_path, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type_code', $typeCode);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':template_path', $templatePath);
        
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
                $isRequired = isset($field['is_required']) ? (int)$field['is_required'] : 0;
                $fieldStmt->bindParam(':is_required', $isRequired);
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
        if ($db->inTransaction()) {
            $db->rollBack();
        }
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

function handleAddFormField($db, $input) {
    try {
        $docTypeId = $input['document_type_id'] ?? null;
        $fieldName = $input['field_name'] ?? null;
        $fieldType = $input['field_type'] ?? 'text';
        $isRequired = $input['is_required'] ?? 0;
        
        if (!$docTypeId || !$fieldName) {
            throw new Exception('Document type ID and field name are required');
        }
        
        // Get the next field order
        $orderQuery = "SELECT MAX(field_order) as max_order FROM document_form_fields WHERE document_type_id = :doc_type_id";
        $orderStmt = $db->prepare($orderQuery);
        $orderStmt->bindParam(':doc_type_id', $docTypeId);
        $orderStmt->execute();
        $orderResult = $orderStmt->fetch(PDO::FETCH_ASSOC);
        $nextOrder = ($orderResult['max_order'] ?? -1) + 1;
        
        // Insert the new form field
        $insertQuery = "INSERT INTO document_form_fields (document_type_id, field_name, field_type, is_required, field_order, created_at) 
                       VALUES (:doc_type_id, :field_name, :field_type, :is_required, :field_order, NOW())";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':doc_type_id', $docTypeId);
        $insertStmt->bindParam(':field_name', $fieldName);
        $insertStmt->bindParam(':field_type', $fieldType);
        $insertStmt->bindParam(':is_required', $isRequired);
        $insertStmt->bindParam(':field_order', $nextOrder);
        
        if ($insertStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Form field added successfully',
                'field_id' => $db->lastInsertId()
            ]);
        } else {
            throw new Exception('Failed to add form field');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}

function handleUpdateFormField($db, $input) {
    try {
        $fieldId = $input['field_id'] ?? null;
        $fieldName = $input['field_name'] ?? null;
        $fieldType = $input['field_type'] ?? null;
        $isRequired = $input['is_required'] ?? null;
        
        if (!$fieldId) {
            throw new Exception('Field ID is required');
        }
        
        // Check if field exists
        $checkQuery = "SELECT id FROM document_form_fields WHERE id = :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $fieldId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            throw new Exception('Form field not found');
        }
        
        // Build update query dynamically based on provided fields
        $updates = [];
        $params = [':id' => $fieldId];
        
        if ($fieldName !== null) {
            $updates[] = "field_name = :field_name";
            $params[':field_name'] = $fieldName;
        }
        
        if ($fieldType !== null) {
            $updates[] = "field_type = :field_type";
            $params[':field_type'] = $fieldType;
        }
        
        if ($isRequired !== null) {
            $updates[] = "is_required = :is_required";
            $params[':is_required'] = (int)$isRequired;
        }
        
        if (empty($updates)) {
            throw new Exception('No fields to update');
        }
        
        $updateQuery = "UPDATE document_form_fields SET " . implode(', ', $updates) . " WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        
        foreach ($params as $key => $value) {
            $updateStmt->bindValue($key, $value);
        }
        
        if ($updateStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Form field updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update form field');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}

function handleDeleteFormField($db, $input) {
    try {
        $fieldId = $input['field_id'] ?? null;
        
        if (!$fieldId) {
            throw new Exception('Field ID is required');
        }
        
        $query = "DELETE FROM document_form_fields WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $fieldId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Form field deleted successfully']);
        } else {
            throw new Exception('Failed to delete form field');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}

function handleDeleteDocumentType($db) {
    try {
        // Check if this is a form field deletion
        $rawInput = file_get_contents('php://input');
        $jsonInput = json_decode($rawInput, true);
        
        if (isset($jsonInput['action']) && $jsonInput['action'] === 'delete_form_field') {
            handleDeleteFormField($db, $jsonInput);
            return;
        }
        
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