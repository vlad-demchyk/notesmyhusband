<?php

// Підключення autoload
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    // Якщо autoload не працює, підключаємо файли вручну
    require_once __DIR__ . '/types.php';
    require_once __DIR__ . '/resolvers.php';
    require_once __DIR__ . '/../auth/auth.php';
    require_once __DIR__ . '/../database/db.php';
    require_once __DIR__ . '/../database/migrations.php';
}

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use App\graphql\Types;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Обробка preflight запитів
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Створюємо схему
    $schema = new Schema([
        'query' => Types::query(),
        'mutation' => Types::mutation()
    ]);

    // Отримуємо запит (підтримка GET для тестування)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Для GET запитів показуємо інформацію про API
        echo json_encode([
            'message' => 'Network Notebook GraphQL API',
            'endpoint' => '/network-notebook/',
            'usage' => 'Використовуйте POST запити з GraphQL query в body',
            'example' => [
                'method' => 'POST',
                'headers' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer YOUR_TOKEN (для автентифікованих запитів)'
                ],
                'body' => [
                    'query' => 'query { me { id login } }'
                ]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Отримуємо запит з POST
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $query = $input['query'] ?? null;
    $variables = $input['variables'] ?? null;

    if (!$query) {
        throw new \Exception('GraphQL запит не може бути порожнім');
    }

    // Виконуємо запит
    $result = GraphQL::executeQuery($schema, $query, null, null, $variables);
    $output = $result->toArray();

    // Обробка помилок
    if (!empty($output['errors'])) {
        http_response_code(400);
    }

    echo json_encode($output);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'errors' => [
            [
                'message' => $e->getMessage()
            ]
        ]
    ]);
}
