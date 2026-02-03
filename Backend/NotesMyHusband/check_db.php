<?php
/**
 * Скрипт для перевірки вмісту бази даних SQLite
 * 
 * Використання:
 *   php check_db.php                    - показати всі таблиці та кількість записів
 *   php check_db.php users              - показати всіх користувачів
 *   php check_db.php personal_access_tokens - показати всі токени
 *   php check_db.php notes              - показати всі нотатки
 */

$dbPath = __DIR__ . '/database/database.sqlite';

if (!file_exists($dbPath)) {
    echo "База даних не знайдена: $dbPath\n";
    exit(1);
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $table = $argv[1] ?? null;
    
    if ($table) {
        // Показати вміст конкретної таблиці
        echo "=== Вміст таблиці: $table ===\n\n";
        
        $result = $db->query("SELECT * FROM $table");
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($rows)) {
            echo "Таблиця порожня.\n";
        } else {
            echo "Знайдено записів: " . count($rows) . "\n\n";
            foreach ($rows as $index => $row) {
                echo "--- Запис #" . ($index + 1) . " ---\n";
                foreach ($row as $key => $value) {
                    // Приховуємо паролі та токени для безпеки
                    if ($key === 'password') {
                        echo "$key: [HIDDEN]\n";
                    } elseif ($key === 'token' && strlen($value) > 20) {
                        echo "$key: " . substr($value, 0, 20) . "...[HIDDEN]\n";
                    } else {
                        echo "$key: " . ($value ?? 'NULL') . "\n";
                    }
                }
                echo "\n";
            }
        }
    } else {
        // Показати список всіх таблиць та кількість записів
        echo "=== Структура бази даних ===\n\n";
        
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $tableName) {
            $count = $db->query("SELECT COUNT(*) FROM $tableName")->fetchColumn();
            echo "Таблиця: $tableName\n";
            echo "  Записів: $count\n";
            
            // Показати структуру таблиці
            $columns = $db->query("PRAGMA table_info($tableName)")->fetchAll(PDO::FETCH_ASSOC);
            echo "  Колонки: ";
            $colNames = array_map(fn($col) => $col['name'], $columns);
            echo implode(', ', $colNames) . "\n";
            echo "\n";
        }
        
        echo "\nДля перегляду вмісту таблиці використовуйте:\n";
        echo "  php check_db.php <назва_таблиці>\n";
        echo "\nПриклади:\n";
        echo "  php check_db.php users\n";
        echo "  php check_db.php personal_access_tokens\n";
        echo "  php check_db.php notes\n";
    }
    
} catch (PDOException $e) {
    echo "Помилка: " . $e->getMessage() . "\n";
    exit(1);
}
