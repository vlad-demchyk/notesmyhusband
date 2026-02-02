# Усунення проблем

## Помилка: "Not Found" або "404"

### Перевірка 1: Правильний URL

Переконайтеся, що ви використовуєте правильний URL:

✅ **Правильно:**
```
http://localhost/network-notebook/
http://localhost/network-notebook/test-simple.php
http://localhost/network-notebook/test.html
```

❌ **Неправильно:**
```
http://localhost/network-notebook/api/graphql/index.php
http://network-notebook.local/
```

### Перевірка 2: Laragon налаштування

1. Переконайтеся, що Laragon запущений
2. Перевірте, що Apache працює (зелена іконка)
3. Перевірте, що проект знаходиться в `c:\laragon\www\network-notebook`

### Перевірка 3: Тестовий файл

Відкрийте в браузері:
```
http://localhost/network-notebook/test-simple.php
```

Цей файл покаже:
- Чи працює PHP
- Чи існують всі файли
- Чи встановлені залежності
- Чи створена база даних

### Перевірка 4: .htaccess

Якщо `.htaccess` не працює:

1. Перевірте, що в Apache увімкнено `mod_rewrite`
2. В Laragon: Menu → Apache → mod_rewrite (має бути увімкнено)
3. Перевірте права доступу до файлу `.htaccess`

### Перевірка 5: Composer залежності

Якщо бачите помилку про відсутність класів:

```bash
cd c:\laragon\www\network-notebook
composer install
```

### Перевірка 6: База даних

Якщо бачите помилки про базу даних:

```bash
cd c:\laragon\www\network-notebook
php api/database/migrations.php
```

### Перевірка 7: Прямий доступ до файлу

Спробуйте відкрити напряму:
```
http://localhost/network-notebook/api/graphql/index.php
```

Якщо це працює, але `/network-notebook/` не працює - проблема в `.htaccess`

### Альтернативне рішення (якщо .htaccess не працює)

Створіть файл `api.php` в корені проекту:

```php
<?php
// api.php
require_once __DIR__ . '/api/graphql/index.php';
```

І використовуйте:
```
http://localhost/network-notebook/api.php
```

## Помилка: "Class not found"

**Причина:** Не встановлені Composer залежності

**Рішення:**
```bash
cd c:\laragon\www\network-notebook
composer install
```

## Помилка: "Database connection failed"

**Причина:** База даних не створена

**Рішення:**
```bash
cd c:\laragon\www\network-notebook
php api/database/migrations.php
```

Перевірте права доступу до папки `database/`

## Помилка: "Unauthorized" (401)

**Причина:** Не передано токен або токен невалідний

**Рішення:**
1. Спочатку зареєструйтеся або увійдіть:
```graphql
mutation {
  register(login: "test", password: "test123") {
    token
  }
}
```

2. Використовуйте отриманий токен в заголовку:
```
Authorization: Bearer YOUR_TOKEN
```

## Помилка: CORS

Якщо робите запити з фронтенду на іншому порту:

Додайте в `api/graphql/index.php`:
```php
header('Access-Control-Allow-Origin: http://localhost:3000'); // ваш порт
```

Або для розробки:
```php
header('Access-Control-Allow-Origin: *');
```

## Діагностика

Запустіть тестовий файл:
```
http://localhost/network-notebook/test-simple.php
```

Він покаже всі можливі проблеми та їх рішення.

## Контакти та підтримка

Якщо проблема залишається:
1. Перевірте логи Apache в Laragon
2. Перевірте помилки PHP (включіть `display_errors` в `php.ini`)
3. Перевірте права доступу до файлів та папок
