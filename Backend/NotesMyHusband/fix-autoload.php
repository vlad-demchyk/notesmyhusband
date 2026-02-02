<?php
/**
 * Скрипт для перевірки та виправлення autoload
 * Запустіть: php fix-autoload.php
 */

echo "Перевірка autoload...\n\n";

// Перевірка vendor/autoload.php
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "❌ vendor/autoload.php не знайдено!\n";
    echo "Виконайте: composer install\n";
    exit(1);
}

echo "✅ vendor/autoload.php існує\n";
require_once __DIR__ . '/vendor/autoload.php';

// Перевірка класів
$classes = [
    'App\graphql\Types',
    'App\graphql\Resolvers',
    'App\Auth\Auth',
    'App\Database\Database',
];

foreach ($classes as $class) {
    if (class_exists($class) || trait_exists($class)) {
        echo "✅ Клас $class знайдено\n";
    } else {
        echo "❌ Клас $class не знайдено\n";
        
        // Спробуємо знайти файл
        $parts = explode('\\', $class);
        $namespace = $parts[0]; // App
        $path = str_replace('App\\', 'api/', $class);
        $path = str_replace('\\', '/', $path) . '.php';
        
        echo "   Шукаємо файл: $path\n";
        if (file_exists(__DIR__ . '/' . $path)) {
            echo "   ✅ Файл існує: $path\n";
            echo "   Перевірте namespace в файлі\n";
        } else {
            echo "   ❌ Файл не знайдено: $path\n";
        }
    }
}

echo "\nЯкщо класи не знайдено, виконайте:\n";
echo "composer dump-autoload\n";
