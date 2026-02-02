# Інструкція з встановлення

## Крок 1: Встановлення Composer

Якщо у вас ще не встановлений Composer, завантажте його з [getcomposer.org](https://getcomposer.org/)

## Крок 2: Встановлення залежностей

Відкрийте термінал в директорії проекту та виконайте:

```bash
cd c:\laragon\www\network-notebook
composer install
```

Це встановить необхідні бібліотеки:
- `webonyx/graphql-php` - для роботи з GraphQL
- `firebase/php-jwt` - для JWT токенів

## Крок 3: Створення бази даних

Запустіть міграції для створення таблиць:

```bash
php api/database/migrations.php
```

Це створить SQLite базу даних в `database/notebook.db` з усіма необхідними таблицями.

## Крок 4: Налаштування веб-сервера

### Для Laragon (Apache):

1. Переконайтеся, що проект знаходиться в `c:\laragon\www\network-notebook`
2. Відкрийте браузер та перейдіть на: `http://localhost/network-notebook/api/graphql/index.php`

### Для вбудованого PHP сервера (для тестування):

```bash
cd c:\laragon\www\network-notebook
php -S localhost:8000 -t public
```

Потім відкрийте: `http://localhost:8000/api/graphql/index.php`

## Крок 5: Тестування API

Використовуйте будь-який GraphQL клієнт, наприклад:
- [GraphQL Playground](https://github.com/graphql/graphql-playground)
- [Postman](https://www.postman.com/)
- [Insomnia](https://insomnia.rest/)

Або використовуйте curl:

```bash
# Реєстрація
curl -X POST http://localhost/network-notebook/api/graphql/index.php \
  -H "Content-Type: application/json" \
  -d '{"query":"mutation { register(login: \"test\", password: \"test123\") { token user { id login } } }"}'

# Вхід
curl -X POST http://localhost/network-notebook/api/graphql/index.php \
  -H "Content-Type: application/json" \
  -d '{"query":"mutation { login(login: \"test\", password: \"test123\") { token user { id login } } }"}'

# Отримати поточного користувача (з токеном)
curl -X POST http://localhost/network-notebook/api/graphql/index.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{"query":"query { me { id login } }"}'
```

## Структура бази даних

Після виконання міграцій будуть створені наступні таблиці:

- **users** - користувачі системи
- **notes** - нотатки
- **note_recipients** - зв'язок нотаток з отримувачами
- **blocked_users** - чорний список користувачів
- **pinned_users** - запінені користувачі

## Важливо

1. Змініть секретний ключ в `api/auth/auth.php` для продакшн використання
2. Налаштуйте права доступу до файлу бази даних `database/notebook.db`
3. Для продакшн використання налаштуйте HTTPS
