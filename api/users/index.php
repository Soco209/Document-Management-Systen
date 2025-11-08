<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    if (!isAuthenticated() || !isAdmin()) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Admin access required"]);
        exit();
    }

    $user_id = getCurrentUserId();
    $role = getRole();
    $user = ['id' => $user_id, 'role' => $role];

    switch ($method) {
        case 'GET':
            handleGetUsers($db, $user);
            break;
        case 'PUT':
            handleUpdateUser($db, $user);
            break;
        case 'DELETE':
            handleDeleteUser($db, $user);
            break;
        default:
            // If the method is not supported, exit silently to prevent interference.
            exit();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}

function handleGetUsers($db, $user) {
    try {
        $query = "SELECT id, student_id, full_name, email, course, role, status, created_at FROM users";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $row;
        }
        
        echo json_encode([
            "success" => true,
            "data" => $users
        ]);
    } catch (Exception $e) {
        throw new Exception("Failed to fetch users: " . $e->getMessage());
    }
}

function handleUpdateUser($db, $user) {
    try {


        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $_GET['id'] ?? null;
        
        if (empty($userId)) {
            throw new Exception("User ID is required");
        }

        if (empty($input)) {
            throw new Exception("No update data provided");
        }

        $fields = [];
        $params = [':id' => $userId];

        if (isset($input['full_name'])) {
            $fields[] = 'full_name = :full_name';
            $params[':full_name'] = $input['full_name'];
        }

        if (isset($input['email'])) {
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            // Check for existing email
            $checkQuery = "SELECT id FROM users WHERE email = :email AND id != :id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':email', $input['email']);
            $checkStmt->bindParam(':id', $userId);
            $checkStmt->execute();
            if ($checkStmt->rowCount() > 0) {
                throw new Exception("Email already exists");
            }
            $fields[] = 'email = :email';
            $params[':email'] = $input['email'];
        }

        if (isset($input['course'])) {
            $fields[] = 'course = :course';
            $params[':course'] = $input['course'];
        }

        if (isset($input['role'])) {
            if (!in_array($input['role'], ['admin', 'student'])) {
                throw new Exception("Invalid role");
            }
            $fields[] = 'role = :role';
            $params[':role'] = $input['role'];
        }

        if (isset($input['status'])) {
            if (!in_array($input['status'], ['active', 'inactive'])) {
                throw new Exception("Invalid status");
            }
            $fields[] = 'status = :status';
            $params[':status'] = $input['status'];
        }

        if (empty($fields)) {
            throw new Exception("No valid fields to update");
        }

        $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute($params)) {
            echo json_encode([
                "success" => true,
                "message" => "User updated successfully"
            ]);
        } else {
            throw new Exception("Failed to update user");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}

function handleDeleteUser($db, $user) {
    try {

        $userId = $_GET['id'] ?? '';
        
        if (empty($userId)) {
            throw new Exception("User ID is required for deletion.");
        }
        
        // Don't allow deleting the current admin user
        if ($userId == $user['id']) {
            throw new Exception("Cannot delete your own account");
        }
        
        // Check if user exists
        $checkQuery = "SELECT id FROM users WHERE id = :id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $userId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            throw new Exception("User not found");
        }
        
        // Delete user (this will cascade delete related records due to foreign keys)
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $userId);
        
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "User deleted successfully"
            ]);
            exit();
        } else {
            throw new Exception("Failed to delete user");
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}
?>