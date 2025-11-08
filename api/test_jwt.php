<?php
// test_jwt.php - A script to test JWT validation from the terminal.

// Set the working directory to the script's location
chdir(__DIR__);

require_once 'middleware/jwt.php';

echo "JWT Validation Test Script\n";
echo "==========================\n\n";

$secret = 'secret';

// --- Test Cases ---

// 1. Generate a valid token
$headers = ['alg' => 'HS256', 'typ' => 'JWT'];
$payload_valid = ['user_id' => 123, 'exp' => (time() + 60)]; // Expires in 60 seconds
$valid_jwt = generate_jwt($headers, $payload_valid, $secret);

// 2. Generate an expired token
$payload_expired = ['user_id' => 123, 'exp' => (time() - 60)]; // Expired 60 seconds ago
$expired_jwt = generate_jwt($headers, $payload_expired, $secret);

// 3. Define other invalid tokens
$tampered_jwt = $valid_jwt . 'tamper'; // Tampered signature
$malformed_jwt = "header.payload.signature"; // Not a real JWT
$empty_jwt = "";
$null_jwt = null;

// --- Run Tests ---

$test_cases = [
    "Valid JWT" => $valid_jwt,
    "Expired JWT" => $expired_jwt,
    "Tampered JWT" => $tampered_jwt,
    "Malformed JWT" => $malformed_jwt,
    "Empty String JWT" => $empty_jwt,
    "Null JWT" => $null_jwt,
];

foreach ($test_cases as $description => $jwt) {
    $result = is_jwt_valid($jwt, $secret);
    $status = $result ? 'VALID' : 'INVALID';
    echo sprintf("[%s] %-20s\n", $status, $description);
}

echo "\nTest complete.\n";

?>