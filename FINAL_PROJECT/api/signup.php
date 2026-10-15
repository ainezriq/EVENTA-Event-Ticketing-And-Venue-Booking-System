<?php
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;

function signupResponse(int $status, array $data): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    signupResponse(405, [
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    signupResponse(400, [
        'success' => false,
        'message' => 'Invalid JSON body'
    ]);
}

$fullName = $data['full_name'] ?? null;
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

if (
    !is_string($fullName) ||
    trim($fullName) === '' ||
    strlen(trim($fullName)) > 100 ||
    !is_string($email) ||
    strlen($email) > 150 ||
    !filter_var(trim($email), FILTER_VALIDATE_EMAIL) ||
    !is_string($password) ||
    strlen($password) < 8 ||
    strlen($password) > 72
) {
    signupResponse(400, [
        'success' => false,
        'message' => 'Enter a name, valid email, and password (8 to 72 characters)'
    ]);
}

$fullName = trim($fullName);
$email = strtolower(trim($email));

$config = require __DIR__ . '/../config/jwt_secret.php';

if (
    empty($config['secret']) ||
    strlen($config['secret']) < 32
) {
    signupResponse(500, [
        'success' => false,
        'message' => 'JWT configuration error'
    ]);
}

try {
    $db = new Database();
    $conn = $db->connect();

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        'INSERT INTO users (full_name, email, password_hash, role)
         VALUES (?, ?, ?, ?)'
    );

    try {
        $stmt->execute([$fullName, $email, $passwordHash, 'customer']);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) === 1062) {
            signupResponse(409, [
                'success' => false,
                'message' => 'Email already registered'
            ]);
        }

        throw $e;
    }

    $userId = (int) $conn->lastInsertId();
    $now = time();
    $token = JWT::encode([
        'iss' => $config['issuer'],
        'iat' => $now,
        'nbf' => $now,
        'exp' => $now + $config['expiry_seconds'],
        'sub' => (string) $userId,
        'role' => 'customer'
    ], $config['secret'], 'HS256');

    signupResponse(201, [
        'success' => true,
        'message' => 'Account created successfully',
        'token_type' => 'Bearer',
        'access_token' => $token,
        'expires_in' => $config['expiry_seconds'],
        'user' => [
            'user_id' => $userId,
            'full_name' => $fullName,
            'email' => $email,
            'role' => 'customer'
        ]
    ]);
} catch (Throwable $e) {
    error_log('Customer signup API error: ' . $e->getMessage());

    signupResponse(500, [
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
