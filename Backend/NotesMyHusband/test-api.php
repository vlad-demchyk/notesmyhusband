<?php
/**
 * Простий скрипт для тестування GraphQL API
 * Використання: php test-api.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Database\Migrations;

echo "=== Network Notebook API Test ===\n\n";

// Перевірка чи встановлені залежності
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "❌ Помилка: Composer залежності не встановлені!\n";
    echo "Виконайте: composer install\n";
    exit(1);
}

echo "✓ Composer залежності встановлені\n";

// Запуск міграцій
echo "\nЗапуск міграцій бази даних...\n";
try {
    $migrations = new Migrations();
    $migrations->run();
    echo "✓ Міграції виконано успішно\n";
} catch (Exception $e) {
    echo "❌ Помилка міграцій: " . $e->getMessage() . "\n";
    exit(1);
}

// Перевірка бази даних
$dbPath = __DIR__ . '/database/notebook.db';
if (file_exists($dbPath)) {
    echo "✓ База даних створена: " . $dbPath . "\n";
} else {
    echo "❌ База даних не знайдена\n";
    exit(1);
}

echo "\n=== Тестування API ===\n\n";

// URL GraphQL endpoint
$apiUrl = 'http://localhost/network-notebook/api/graphql/index.php';

echo "GraphQL Endpoint: $apiUrl\n\n";

// Тест 1: Реєстрація користувача
echo "1. Тест реєстрації користувача...\n";
$registerQuery = [
    'query' => 'mutation { register(login: "testuser", password: "test123") { token user { id login } } }'
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerQuery));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['data']['register']['token'])) {
        echo "✓ Реєстрація успішна\n";
        $token = $data['data']['register']['token'];
        echo "  Token: " . substr($token, 0, 20) . "...\n";
        
        // Тест 2: Отримання поточного користувача
        echo "\n2. Тест отримання поточного користувача...\n";
        $meQuery = [
            'query' => 'query { me { id login created_at } }'
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($meQuery));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['data']['me']['id'])) {
                echo "✓ Отримання користувача успішне\n";
                echo "  User ID: " . $data['data']['me']['id'] . "\n";
                echo "  Login: " . $data['data']['me']['login'] . "\n";
            } else {
                echo "❌ Помилка: " . json_encode($data) . "\n";
            }
        } else {
            echo "❌ HTTP помилка: $httpCode\n";
        }
        
        // Тест 3: Створення нотатки
        echo "\n3. Тест створення нотатки...\n";
        $createNoteQuery = [
            'query' => 'mutation { createNote(content: "Тестова нотатка", recipient_ids: []) { id content author { login } created_at } }'
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($createNoteQuery));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['data']['createNote']['id'])) {
                echo "✓ Створення нотатки успішне\n";
                echo "  Note ID: " . $data['data']['createNote']['id'] . "\n";
                echo "  Content: " . $data['data']['createNote']['content'] . "\n";
            } else {
                echo "❌ Помилка: " . json_encode($data) . "\n";
            }
        } else {
            echo "❌ HTTP помилка: $httpCode\n";
        }
        
    } else {
        echo "❌ Помилка реєстрації: " . json_encode($data) . "\n";
    }
} else {
    echo "❌ HTTP помилка: $httpCode\n";
    echo "Відповідь: $response\n";
}

echo "\n=== Тестування завершено ===\n";
echo "\nДля повного тестування використовуйте GraphQL клієнт:\n";
echo "URL: $apiUrl\n";
echo "Приклади запитів знаходяться в файлі examples.graphql\n";
