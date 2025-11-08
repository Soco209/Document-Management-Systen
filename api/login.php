<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

session_start();

require_once 'config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/middleware/jwt.php';

$response = array("success" => false, "message" => "");

try {
    // Get JSON input
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (!$data || !isset($data['email']) || !isset($data['password'])) {
        $response["message"] = "Email and password are required.";
        echo json_encode($response);
        exit;
    }

    $email = trim($data['email']);
    $password = $data['password'];

    if (empty($email) || empty($password)) {
        $response["message"] = "Email and password are required.";
        echo json_encode($response);
        exit;
    }

    // Initialize database and user
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Check if email exists and verify password
    $user->email = $email; // Assuming email is a property of the User object
    if ($user->login($password)) { // Pass the plain password from JSON input
        $headers = array('alg'=>'HS256','typ'=>'JWT');
        $payload = array('user_id' => $user->id, 'role' => $user->role, 'exp' => (time() + 3600));
        $jwt = generate_jwt($headers, $payload);

        http_response_code(200);
        if ($user->status !== 'active') {
            $response["message"] = "Account is inactive. Please contact administrator.";
            echo json_encode($response);
            exit;
        }

        // Set session variables
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->full_name;
        $_SESSION['student_id'] = $user->student_id;
        $_SESSION['user_role'] = $user->role; // Store the user's role from the database
        $_SESSION['is_logged_in'] = true;

        $response["success"] = true;
        $response["message"] = "Login successful!";
        $response["user"] = [
            "id" => $user->id,
            "student_id" => $user->student_id,
            "full_name" => $user->full_name,
            "email" => $user->email,
            "course" => $user->course,
            "role" => $user->role, // Add the user's role from the database
            "token" => $jwt // Add the generated JWT to the response
        ];
    } else {
        $response["message"] = "Invalid email or password.";
    }

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    $response["message"] = "Server error. Please try again.";
}

echo json_encode($response);