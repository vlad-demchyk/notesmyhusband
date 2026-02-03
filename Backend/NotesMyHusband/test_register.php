<?php
/**
 * Тестовий скрипт для перевірки реєстрації користувача
 * Симулює те, що робить GraphQL резолвер
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

echo "=== Тест реєстрації користувача ===\n\n";

try {
    // Тестові дані
    $login = 'testuser_' . time();
    $password = 'test123';
    $email = 'test@example.com';
    
    echo "1. Перевірка структури таблиці users...\n";
    $hasEmailColumn = Schema::hasColumn('users', 'email');
    echo "   Колонка email існує: " . ($hasEmailColumn ? 'YES' : 'NO') . "\n\n";
    
    echo "2. Перевірка наявності користувача з логіном '$login'...\n";
    $exists = User::where('login', $login)->exists();
    echo "   Користувач існує: " . ($exists ? 'YES' : 'NO') . "\n\n";
    
    if ($exists) {
        echo "   Користувач вже існує, пропускаємо створення.\n";
    } else {
        echo "3. Створення користувача...\n";
        $userData = [
            'login' => $login,
            'password' => Hash::make($password),
        ];
        
        if ($hasEmailColumn && $email) {
            $userData['email'] = $email;
            echo "   Додано email: $email\n";
        }
        
        echo "   Дані для створення: " . json_encode(array_merge($userData, ['password' => '[HIDDEN]'])) . "\n";
        
        $user = User::create($userData);
        echo "   Користувач створений з ID: {$user->id}\n\n";
        
        echo "4. Перевірка створеного користувача...\n";
        echo "   ID: {$user->id}\n";
        echo "   Login: {$user->login}\n";
        echo "   Email: " . ($user->email ?? 'NULL') . "\n";
        echo "   Created at: {$user->created_at}\n";
        echo "   Updated at: {$user->updated_at}\n";
        echo "   Password hash length: " . strlen($user->password) . "\n\n";
        
        echo "5. Тест створення токена Sanctum...\n";
        $token = $user->createToken('auth')->plainTextToken;
        echo "   Токен створений: " . substr($token, 0, 20) . "...\n";
        echo "   Довжина токена: " . strlen($token) . "\n\n";
        
        echo "6. Перевірка структури відповіді (як у GraphQL)...\n";
        $response = [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'email' => $user->email,
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
            ]
        ];
        echo "   Структура: " . json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
        
        echo "7. Перевірка типів даних...\n";
        echo "   user.id тип: " . gettype($user->id) . " (очікується: integer)\n";
        echo "   user.login тип: " . gettype($user->login) . " (очікується: string)\n";
        echo "   user.email тип: " . gettype($user->email) . " (очікується: string або NULL)\n";
        echo "   user.created_at тип: " . get_class($user->created_at) . " (очікується: Carbon)\n";
        echo "   created_at ISO string: " . $user->created_at->toIso8601String() . "\n\n";
        
        echo "✅ Тест пройдено успішно!\n";
        
        // Очищення тестового користувача
        echo "\n8. Видалення тестового користувача...\n";
        $user->tokens()->delete();
        $user->delete();
        echo "   Тестовий користувач видалено.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Помилка: " . $e->getMessage() . "\n";
    echo "Файл: " . $e->getFile() . "\n";
    echo "Рядок: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
