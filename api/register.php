<?php
// api/register.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include required files
require_once 'config/database.php';
require_once 'models/User.php';

// Initialize response array
$response = array(
    "success" => false,
    "message" => ""
);

try {
    // Get POST data
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // Validate required fields
    if (
        !isset($data['studentId']) ||
        !isset($data['fullName']) ||
        !isset($data['course']) ||
        !isset($data['email']) ||
        !isset($data['password']) ||
        !isset($data['confirmPassword'])
    ) {
        $response["message"] = "All fields are required.";
        echo json_encode($response);
        exit;
    }

    // Trim and sanitize input data
    $studentId = trim($data['studentId']);
    $fullName = trim($data['fullName']);
    $course = trim($data['course']);
    $email = trim($data['email']);
    $password = $data['password'];
    $confirmPassword = $data['confirmPassword'];

    // Additional validation
    if (empty($studentId) || empty($fullName) || empty($course) || empty($email) || empty($password)) {
        $response["message"] = "All fields are required.";
        echo json_encode($response);
        exit;
    }

    // Validate student ID format
    if (!preg_match('/^S\d{8}$/', $studentId)) {
        $response["message"] = "Student ID must be in format S01234567";
        echo json_encode($response);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["message"] = "Please enter a valid email address.";
        echo json_encode($response);
        exit;
    }

    // Validate password length
    if (strlen($password) < 6) {
        $response["message"] = "Password must be at least 6 characters long.";
        echo json_encode($response);
        exit;
    }

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $response["message"] = "Passwords do not match.";
        echo json_encode($response);
        exit;
    }

    // Validate full name length
    if (strlen($fullName) < 2) {
        $response["message"] = "Full name must be at least 2 characters long.";
        echo json_encode($response);
        exit;
    }

    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // Initialize User object
    $user = new User($db);

    // Check if email already exists
    $user->email = $email;
    if ($user->emailExists()) {
        $response["message"] = "Email already registered.";
        echo json_encode($response);
        exit;
    }

    // Check if student ID already exists
    $user->student_id = $studentId;
    if ($user->studentIdExists()) {
        $response["message"] = "Student ID already registered.";
        echo json_encode($response);
        exit;
    }

    // Create new user
    $user->student_id = $studentId;
    $user->full_name = $fullName;
    $user->course = $course;
    $user->email = $email;
    $user->password_hash = $user->hashPassword($password);

    if ($user->create()) {
        $response["success"] = true;
        $response["message"] = "Registration successful!";
    } else {
        $response["message"] = "Unable to register. Please try again.";
    }

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    $response["message"] = "Server error. Please try again later.";
}

echo json_encode($response);
?>