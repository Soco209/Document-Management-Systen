<?php
// api/documents/requirements.php

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../middleware/auth.php';

// Initialize database connection
$db = new Database();
$conn = $db->getConnection();

// Middleware: Authenticate user
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Authentication required"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet($conn);
        break;
    case 'POST':
        handlePost($conn);
        break;
    case 'DELETE':
        handleDelete($conn);
        break;
    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['message' => 'Method not allowed.']);
        break;
}

function handleGet($conn) {
    if (empty($_GET['doc_type_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Document type ID is required.']);
        return;
    }

    $docTypeId = $_GET['doc_type_id'];

    try {
        $query = "SELECT * FROM required_documents WHERE document_type_id = :doc_type_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':doc_type_id', $docTypeId);
        $stmt->execute();
        
        $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $requirements]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch requirements: ' . $e->getMessage()]);
    }
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'));
    
    if (empty($data->document_type_id) || empty($data->name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Document type ID and requirement name are required.']);
        return;
    }
    
    try {
        $query = "INSERT INTO required_documents (document_type_id, requirement_name) VALUES (:document_type_id, :name)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':document_type_id', $data->document_type_id);
        $stmt->bindParam(':name', $data->name);
        
        if ($stmt->execute()) {
            http_response_code(201); // Created
            echo json_encode(['success' => true, 'message' => 'Requirement added successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add requirement.']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add requirement: ' . $e->getMessage()]);
    }
}

function handleDelete($conn) {
    if (empty($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Requirement ID is required.']);
        return;
    }
    
    $id = $_GET['id'];
    
    try {
        $query = "DELETE FROM required_documents WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Requirement deleted successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete requirement.']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete requirement: ' . $e->getMessage()]);
    }
}
