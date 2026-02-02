<?php
/**
 * Простий тестовий файл для перевірки роботи сервера
 * Відкрийте: http://localhost/network-notebook/test-simple.php
 */

echo "<h1>Network Notebook API - Тест</h1>";

echo "<h2>1. Перевірка PHP:</h2>";
echo "PHP версія: " . phpversion() . "<br>";
echo "✅ PHP працює<br><br>";

echo "<h2>2. Перевірка файлів:</h2>";
$files = [
    'index.php' => 'Головний файл',
    'api/graphql/index.php' => 'GraphQL endpoint',
    'api/database/db.php' => 'Database клас',
    'api/auth/auth.php' => 'Auth клас',
    'composer.json' => 'Composer конфігурація'
];

foreach ($files as $file => $desc) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $desc ($file) існує<br>";
    } else {
        echo "❌ $desc ($file) не знайдено<br>";
    }
}

echo "<br><h2>3. Перевірка vendor:</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Composer залежності встановлені<br>";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoloader завантажено<br>";
} else {
    echo "❌ Composer залежності не встановлені<br>";
    echo "Виконайте: <code>composer install</code><br>";
}

echo "<br><h2>4. Перевірка бази даних:</h2>";
$dbPath = __DIR__ . '/database/notebook.db';
if (file_exists($dbPath)) {
    echo "✅ База даних існує: " . filesize($dbPath) . " байт<br>";
} else {
    echo "⚠️ База даних не знайдена<br>";
    echo "Запустіть міграції: <code>php api/database/migrations.php</code><br>";
}

echo "<br><h2>5. Тест GraphQL endpoint:</h2>";
echo '<a href="/network-notebook/">Відкрити GraphQL endpoint</a><br>';
echo '<a href="/network-notebook/test.html">Відкрити тестовий інтерфейс</a><br>';

echo "<br><h2>6. Інформація про сервер:</h2>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'не встановлено') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'не встановлено') . "<br>";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'не встановлено') . "<br>";
