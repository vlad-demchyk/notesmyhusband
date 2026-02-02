<?php
/**
 * Router скрипт для PHP вбудованого сервера
 * Обробляє всі запити і направляє їх до відповідних файлів
 */

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Видаляємо початковий слеш
$requestPath = ltrim($requestPath, '/');

// Розширення статичних файлів, які сервер має обробити сам
$staticExtensions = ['html', 'css', 'js', 'json', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'pdf', 'txt', 'xml'];

// Перевіряємо, чи це статичний файл
$extension = pathinfo($requestPath, PATHINFO_EXTENSION);
if ($extension && in_array(strtolower($extension), $staticExtensions)) {
    $file = __DIR__ . '/' . $requestPath;
    if (file_exists($file) && is_file($file)) {
        return false; // Позволити PHP серверу обробити статичний файл
    }
}

// Якщо запит до API GraphQL
if (strpos($requestPath, 'api/graphql') === 0 || $requestPath === 'api/graphql/index.php') {
    $file = __DIR__ . '/api/graphql/index.php';
    if (file_exists($file)) {
        return require $file;
    }
}

// Якщо запит до API (інші endpoints)
if (strpos($requestPath, 'api/') === 0) {
    $file = __DIR__ . '/' . $requestPath;
    if (file_exists($file) && is_file($file)) {
        return require $file;
    }
}

// Якщо запит до public файлів
if (strpos($requestPath, 'public/') === 0) {
    $file = __DIR__ . '/' . $requestPath;
    if (file_exists($file)) {
        return false; // Позволити серверу обробити статичні файли
    }
}

// Якщо кореневий запит або порожній
if ($requestPath === '' || $requestPath === '/') {
    $file = __DIR__ . '/index.php';
    if (file_exists($file)) {
        return require $file;
    }
}

// Якщо файл існує в корені (PHP файли)
$file = __DIR__ . '/' . $requestPath;
if (file_exists($file) && is_file($file)) {
    $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
    if ($fileExtension === 'php') {
        return require $file;
    } else {
        // Для інших файлів дозволяємо серверу обробити
        return false;
    }
}

// Якщо нічого не знайдено, перенаправляємо на GraphQL endpoint
$file = __DIR__ . '/api/graphql/index.php';
if (file_exists($file)) {
    return require $file;
}

// 404 помилка
http_response_code(404);
echo json_encode([
    'error' => 'Not Found',
    'message' => 'The requested resource was not found on this server.',
    'path' => $requestPath
], JSON_PRETTY_PRINT);
