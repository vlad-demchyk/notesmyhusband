# NotesMyHusband Backend (Laravel + GraphQL)

Бекенд на Laravel з GraphQL (Lighthouse) та авторизацією Sanctum.

## Вимоги

- PHP 8.2+
- Composer
- Розширення: ext-xml, ext-dom, ext-json, ext-mbstring, ext-sqlite3 (або інший драйвер БД)

## Встановлення

1. Встановити залежності:
   ```bash
   composer install
   ```

2. Налаштувати середовище:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. У `.env` вказати SQLite:
   ```
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```
   (або абсолютний шлях до `database/database.sqlite`)

4. Створити файл БД та виконати міграції:
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

5. Запустити сервер:
   ```bash
   php artisan serve --port=8000
   ```

API буде доступне на `http://localhost:8000`. GraphQL endpoint: **POST** `http://localhost:8000/graphql`.

## CORS

Для фронтенду на `http://localhost:5173` у `.env` можна задати:
```
FRONTEND_URL=http://localhost:5173
```

## Авторизація

- Реєстрація та логін через мутації `register` та `login`; у відповіді повертається `token` та `user`.
- Далі у заголовку запитів передавати: `Authorization: Bearer <token>`.

## Ліцензія

MIT
