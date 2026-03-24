<?php

define('JWT_SECRET', 'b7X$kL9@qP2!mZr5T#8VnYc4F^sHjW6p'); 
define('JWT_EXPIRY', 60 * 60); // 1 hour in seconds

// Step 1: Encode data to base64url (embedding binary files like images and documents within text files, such as hypertext transfer protocol (HTML) or JavaScript object notation (JSON)
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Step 2: Decode base64url back to data
function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}

// Step 3: Generate a JWT token
function generate_jwt($username, $role) {
    // Header
    $header = base64url_encode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]));

    // Payload
    $payload = base64url_encode(json_encode([
        'username' => $username,
        'role'     => $role,
        'iat'      => time(),                  // issued at
        'exp'      => time() + JWT_EXPIRY      // expiry
    ]));

    // Signature
    $signature = base64url_encode(
        hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
    );

    return "$header.$payload.$signature";
}

// Verify and decode a JWT token
function verify_jwt($token) {
    $parts = explode('.', $token);

    // A valid JWT must have exactly 3 parts
    if (count($parts) !== 3) {
        return null;
    }

    [$header, $payload, $signature] = $parts;

    // Re-compute the expected signature and compare
    $expected_signature = base64url_encode(
        hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
    );

    // If signatures don't match, token was tampered with
    if (!hash_equals($expected_signature, $signature)) {
        return null;
    }

    // Decode the payload
    $data = json_decode(base64url_decode($payload), true);

    // Check if token has expired
    if (!isset($data['exp']) || time() > $data['exp']) {
        return null;
    }

    return $data; // Return the decoded payload (username, role, etc.)
}
?>