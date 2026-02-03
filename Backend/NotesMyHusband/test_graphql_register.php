<?php
/**
 * Тест GraphQL мутації register через HTTP запит
 * Симулює реальний запит від фронтенду
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;

echo "=== Тест GraphQL мутації register ===\n\n";

// Тестові дані
$login = 'gql_test_' . time();
$password = 'test123';
$email = 'gql@example.com';

$query = <<<GRAPHQL
mutation Register(\$login: String!, \$password: String!, \$email: String) {
  register(login: \$login, password: \$password, email: \$email) {
    token
    user {
      id
      login
      email
      created_at
      updated_at
    }
  }
}
GRAPHQL;

$variables = [
    'login' => $login,
    'password' => $password,
    'email' => $email,
];

$requestData = [
    'query' => $query,
    'variables' => $variables,
];

echo "1. GraphQL запит:\n";
echo json_encode($requestData, JSON_PRETTY_PRINT) . "\n\n";

try {
    // Створюємо HTTP запит
    $request = Request::create('/graphql', 'POST', $requestData);
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('Accept', 'application/json');
    
    // Отримуємо відповідь через Kernel
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    
    $statusCode = $response->getStatusCode();
    $content = $response->getContent();
    $data = json_decode($content, true);
    
    echo "2. HTTP статус: $statusCode\n\n";
    echo "3. Відповідь GraphQL:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    
    if (isset($data['errors'])) {
        echo "❌ Помилки GraphQL:\n";
        foreach ($data['errors'] as $error) {
            echo "   - " . ($error['message'] ?? 'Unknown error') . "\n";
            if (isset($error['extensions'])) {
                echo "     Extensions: " . json_encode($error['extensions']) . "\n";
            }
        }
        exit(1);
    }
    
    if (isset($data['data']['register'])) {
        $registerData = $data['data']['register'];
        
        echo "4. Перевірка структури відповіді:\n";
        echo "   token присутній: " . (isset($registerData['token']) ? 'YES' : 'NO') . "\n";
        echo "   user присутній: " . (isset($registerData['user']) ? 'YES' : 'NO') . "\n\n";
        
        if (isset($registerData['user'])) {
            $user = $registerData['user'];
            echo "5. Перевірка полів user:\n";
            echo "   id: " . ($user['id'] ?? 'MISSING') . " (тип: " . gettype($user['id'] ?? null) . ")\n";
            echo "   login: " . ($user['login'] ?? 'MISSING') . " (тип: " . gettype($user['login'] ?? null) . ")\n";
            echo "   email: " . ($user['email'] ?? 'NULL') . " (тип: " . gettype($user['email'] ?? null) . ")\n";
            echo "   created_at: " . ($user['created_at'] ?? 'MISSING') . " (тип: " . gettype($user['created_at'] ?? null) . ")\n";
            echo "   updated_at: " . ($user['updated_at'] ?? 'MISSING') . " (тип: " . gettype($user['updated_at'] ?? null) . ")\n\n";
            
            // Перевірка типів
            $checks = [
                'id is string or int' => is_string($user['id']) || is_int($user['id']),
                'login is string' => is_string($user['login']),
                'email is string or null' => is_null($user['email']) || is_string($user['email']),
                'created_at is string' => is_string($user['created_at']),
                'updated_at is string' => is_string($user['updated_at']),
            ];
            
            echo "6. Перевірка типів:\n";
            foreach ($checks as $check => $result) {
                echo "   $check: " . ($result ? '✅' : '❌') . "\n";
            }
            echo "\n";
        }
        
        echo "✅ GraphQL мутація працює правильно!\n";
        
        // Очищення тестового користувача
        if (isset($registerData['token'])) {
            echo "\n7. Очищення тестового користувача...\n";
            $userModel = \App\Models\User::where('login', $login)->first();
            if ($userModel) {
                $userModel->tokens()->delete();
                $userModel->delete();
                echo "   Тестовий користувач видалено.\n";
            }
        }
    } else {
        echo "❌ Відповідь не містить data.register\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "❌ Помилка: " . $e->getMessage() . "\n";
    echo "Файл: " . $e->getFile() . "\n";
    echo "Рядок: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
