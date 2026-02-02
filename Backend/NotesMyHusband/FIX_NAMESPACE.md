# Виправлення помилки "Class not found"

## Проблема виправлена!

Я виправив namespace в файлах:
- `api/graphql/types.php` - змінено з `App\GraphQL\Types` на `App\graphql`
- `api/graphql/resolvers.php` - змінено з `App\GraphQL` на `App\graphql`
- `api/graphql/index.php` - оновлено use statements

## Що потрібно зробити:

### Крок 1: Оновити autoload

Відкрийте термінал в Laragon або командний рядок і виконайте:

```bash
cd c:\laragon\www\network-notebook
composer dump-autoload
```

АБО якщо composer не в PATH, використайте повний шлях до PHP:

```bash
cd c:\laragon\www\network-notebook
c:\laragon\bin\php\php-8.x.x\php.exe c:\laragon\bin\composer\composer.phar dump-autoload
```

(замініть php-8.x.x на вашу версію PHP)

### Крок 2: Перевірка

Відкрийте в браузері:
```
http://localhost/network-notebook/test-simple.php
```

Або:
```
http://localhost/network-notebook/
```

Якщо все правильно, ви побачите інформацію про API або результат запиту.

## Альтернативне рішення (якщо composer не працює)

Якщо не можете запустити composer, можна вручну додати require в index.php:

```php
// Додайте перед require_once autoload.php:
require_once __DIR__ . '/../../api/graphql/types.php';
require_once __DIR__ . '/../../api/graphql/resolvers.php';
require_once __DIR__ . '/../../api/auth/auth.php';
require_once __DIR__ . '/../../api/database/db.php';
```

Але краще використати composer dump-autoload.
