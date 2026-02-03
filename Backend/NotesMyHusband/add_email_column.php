<?php
$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Перевіряємо, чи існує колонка email
    $result = $db->query("PRAGMA table_info(users)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    $hasEmail = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'email') {
            $hasEmail = true;
            break;
        }
    }
    
    if (!$hasEmail) {
        // Додаємо колонку email після login
        $db->exec("ALTER TABLE users ADD COLUMN email VARCHAR(255) NULL");
        echo "Column 'email' added successfully.\n";
    } else {
        echo "Column 'email' already exists.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
