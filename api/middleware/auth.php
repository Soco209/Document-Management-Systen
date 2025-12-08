<?php
require_once __DIR__ . '/jwt.php';

function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('getallheaders')) {
        $requestHeaders = getallheaders();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function isAuthenticated() {
    $jwt = getBearerToken();
    if (empty($jwt)) {
        return false;
    }
    return is_jwt_valid($jwt);
}

function getCurrentUserId() {
    $jwt = getBearerToken();
    if (empty($jwt)) {
        return null;
    }
    
    $payload = get_jwt_payload($jwt);
    return $payload['user_id'] ?? null;
}

function getCurrentUser() {
    $jwt = getBearerToken();
    if (empty($jwt)) {
        return null;
    }
    
    $payload = get_jwt_payload($jwt);
    if (!$payload || !isset($payload['user_id'])) {
        return null;
    }
    
    // Return user data from JWT payload
    return [
        'id' => $payload['user_id'] ?? null,
        'email' => $payload['email'] ?? null,
        'role' => $payload['role'] ?? null,
        'full_name' => $payload['full_name'] ?? null
    ];
}

function getRole() {
    $jwt = getBearerToken();
    if (empty($jwt)) {
        return null;
    }

    $payload = get_jwt_payload($jwt);
    return $payload['role'] ?? null;
}

function isAdmin() {
    return getRole() === 'admin';
}