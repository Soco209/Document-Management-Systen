<?php
function generate_jwt($headers, $payload, $secret = 'secret') {
    $headers_encoded = base64url_encode(json_encode($headers));
    $payload_encoded = base64url_encode(json_encode($payload));
    $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
    $signature_encoded = base64url_encode($signature);
    return "$headers_encoded.$payload_encoded.$signature_encoded";
}

function is_jwt_valid($jwt, $secret = 'secret') {
    error_log("JWT: " . $jwt);
    // split the jwt
    $tokenParts = explode('.', $jwt);
    error_log("Token Parts: " . print_r($tokenParts, true));
    if (count($tokenParts) !== 3) {
        // The token is not structured correctly
        return FALSE;
    }

    $header_encoded = $tokenParts[0];
    $payload_encoded = $tokenParts[1];
    $signature_provided = $tokenParts[2];

    // Decode payload for expiration check
    $payload = base64url_decode($payload_encoded);
    error_log("Payload: " . $payload);
    $decoded_payload = json_decode($payload);
    error_log("Decoded Payload: " . print_r($decoded_payload, true));

    if ($decoded_payload === null || !isset($decoded_payload->exp)) {
        return FALSE; // Invalid payload
    }

    $is_token_expired = ($decoded_payload->exp - time()) < 0;
    error_log("Is Token Expired: " . ($is_token_expired ? 'true' : 'false'));

    // Build signature from original encoded parts
    $signature = hash_hmac('SHA256', $header_encoded . "." . $payload_encoded, $secret, true);
    $base64_url_signature = base64url_encode($signature);
    error_log("Base64 URL Signature: " . $base64_url_signature);
    error_log("Signature Provided: " . $signature_provided);

    // verify it matches the signature provided in the jwt
    $is_signature_valid = ($base64_url_signature === $signature_provided);
    error_log("Is Signature Valid: " . ($is_signature_valid ? 'true' : 'false'));
    
    if ($is_token_expired || !$is_signature_valid) {
        return FALSE;
    } else {
        return TRUE;
    }
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function get_jwt_payload($jwt) {
    $tokenParts = explode('.', $jwt);
    $payload = base64url_decode($tokenParts[1]);
    return json_decode($payload, true);
}