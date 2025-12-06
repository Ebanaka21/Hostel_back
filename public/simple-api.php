<?php

// Простой API для тестирования JWT токена
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Получаем endpoint из query параметра или из URI
$endpoint = $_GET['endpoint'] ?? '';
if (!$endpoint && strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    $endpoint = $_SERVER['REQUEST_URI'];
}

// Mock response для тестирования /api/me
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $endpoint === '/api/me') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    // Извлекаем токен из заголовка (может быть с или без "Bearer ")
    $token = $authHeader;
    if (strpos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
    }

    // Проверяем, что токен начинается правильно (mock проверка)
    if (strpos($token, 'eyJ0eXAiOiJKV1Qi') === 0) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => 2,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'user'
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid token format'
        ]);
    }
    exit();
}

// Mock для регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $endpoint === '/api/register') {
    $input = json_decode(file_get_contents('php://input'), true);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.mock.jwt.token',
        'user' => [
            'id' => 2,
            'name' => $input['name'] ?? 'New User',
            'email' => $input['email'] ?? 'new@example.com',
            'role' => 'user'
        ]
    ]);
    exit();
}

// Mock для логина
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $endpoint === '/api/login') {
    $input = json_decode(file_get_contents('php://input'), true);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.mock.jwt.token',
        'user' => [
            'id' => 2,
            'name' => 'Existing User',
            'email' => $input['email'] ?? 'existing@example.com',
            'role' => 'user'
        ]
    ]);
    exit();
}

http_response_code(404);
echo json_encode(['error' => 'Not Found']);
