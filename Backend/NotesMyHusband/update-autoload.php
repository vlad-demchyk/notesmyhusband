<?php
/**
 * Скрипт для оновлення autoload через браузер
 * Відкрийте: http://localhost/network-notebook/update-autoload.php
 */

echo "<h1>Оновлення Autoload</h1>";

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<p style='color: red;'>❌ vendor/autoload.php не знайдено!</p>";
    echo "<p>Виконайте в терміналі: <code>composer install</code></p>";
    exit;
}

echo "<p>✅ vendor/autoload.php існує</p>";

// Шукаємо composer.phar або composer
$composerPath = null;

// Перевіряємо чи composer доступний через PATH
$output = [];
$returnVar = 0;
exec('composer --version 2>&1', $output, $returnVar);

if ($returnVar === 0) {
    $composerPath = 'composer';
    echo "<p>✅ Composer знайдено в PATH</p>";
} else {
    // Шукаємо в Laragon
    $laragonPaths = [
        'C:\\laragon\\bin\\composer\\composer.phar',
        'C:\\laragon\\bin\\composer\\composer.bat',
    ];
    
    foreach ($laragonPaths as $path) {
        if (file_exists($path)) {
            $composerPath = $path;
            echo "<p>✅ Composer знайдено: $path</p>";
            break;
        }
    }
}

if (!$composerPath) {
    echo "<p style='color: orange;'>⚠️ Composer не знайдено автоматично</p>";
    echo "<p>Виконайте вручну в терміналі:</p>";
    echo "<pre>cd c:\\laragon\\www\\network-notebook\ncomposer dump-autoload</pre>";
    echo "<p>АБО якщо composer не в PATH:</p>";
    echo "<pre>cd c:\\laragon\\www\\network-notebook\nc:\\laragon\\bin\\php\\php-8.x.x\\php.exe c:\\laragon\\bin\\composer\\composer.phar dump-autoload</pre>";
    exit;
}

echo "<h2>Виконання composer dump-autoload...</h2>";
echo "<pre>";

$command = $composerPath . ' dump-autoload 2>&1';
exec($command, $output, $returnVar);

foreach ($output as $line) {
    echo htmlspecialchars($line) . "\n";
}

echo "</pre>";

if ($returnVar === 0) {
    echo "<p style='color: green;'>✅ Autoload успішно оновлено!</p>";
    echo "<p><a href='/network-notebook/'>Перейти до API</a></p>";
    echo "<p><a href='/network-notebook/test-simple.php'>Перевірити систему</a></p>";
} else {
    echo "<p style='color: red;'>❌ Помилка при оновленні autoload</p>";
    echo "<p>Спробуйте виконати вручну в терміналі:</p>";
    echo "<pre>cd c:\\laragon\\www\\network-notebook\ncomposer dump-autoload</pre>";
}
