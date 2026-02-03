# Інструкції для роботи з базою даних SQLite

## Перевірка вмісту бази даних

### Варіант 1: Використання скрипта check_db.php (рекомендовано)

```bash
cd Backend/NotesMyHusband

# Показати всі таблиці та кількість записів
php check_db.php

# Показати всіх користувачів
php check_db.php users

# Показати всі токени авторизації
php check_db.php personal_access_tokens

# Показати всі нотатки
php check_db.php notes

# Показати отримувачів нотаток
php check_db.php note_recipients

# Показати заблокованих користувачів
php check_db.php blocked_users

# Показати закріплених користувачів
php check_db.php pinned_users
```

### Варіант 2: Використання SQLite CLI

```bash
cd Backend/NotesMyHusband

# Відкрити інтерактивну консоль SQLite
sqlite3 database/database.sqlite

# Або виконати команди напряму:
sqlite3 database/database.sqlite ".tables"                    # Показати всі таблиці
sqlite3 database/database.sqlite ".schema users"             # Показати структуру таблиці users
sqlite3 database/database.sqlite "SELECT * FROM users;"      # Показати всіх користувачів
sqlite3 database/database.sqlite "SELECT COUNT(*) FROM users;" # Підрахувати користувачів
```

### Варіант 3: Використання PHP напряму

```bash
cd Backend/NotesMyHusband

# Показати всі таблиці
php -r "\$db = new PDO('sqlite:database/database.sqlite'); \$tables = \$db->query(\"SELECT name FROM sqlite_master WHERE type='table'\")->fetchAll(PDO::FETCH_COLUMN); print_r(\$tables);"

# Показати всіх користувачів
php -r "\$db = new PDO('sqlite:database/database.sqlite'); \$users = \$db->query('SELECT id, login, email, created_at FROM users')->fetchAll(PDO::FETCH_ASSOC); print_r(\$users);"

# Підрахувати записи в таблиці
php -r "\$db = new PDO('sqlite:database/database.sqlite'); \$count = \$db->query('SELECT COUNT(*) FROM users')->fetchColumn(); echo \"Users: \$count\n\";"
```

## Корисні SQL запити

### Перевірка користувачів
```sql
-- Всі користувачі (без паролів)
SELECT id, login, email, created_at, updated_at FROM users;

-- Кількість користувачів
SELECT COUNT(*) FROM users;

-- Користувач за логіном
SELECT * FROM users WHERE login = 'testuser';
```

### Перевірка токенів
```sql
-- Всі токени (без повного токена)
SELECT id, tokenable_type, tokenable_id, name, 
       substr(token, 1, 20) || '...' as token_preview,
       last_used_at, expires_at, created_at 
FROM personal_access_tokens;

-- Активні токени користувача
SELECT * FROM personal_access_tokens 
WHERE tokenable_type = 'App\Models\User' 
  AND tokenable_id = 1;
```

### Перевірка нотаток
```sql
-- Всі нотатки
SELECT * FROM notes;

-- Нотатки користувача
SELECT * FROM notes WHERE author_id = 1;

-- Нотатки з отримувачами
SELECT n.id, n.content, n.author_id, 
       GROUP_CONCAT(nr.recipient_id) as recipients
FROM notes n
LEFT JOIN note_recipients nr ON n.id = nr.note_id
GROUP BY n.id;
```

### Перевірка зв'язків
```sql
-- Заблоковані користувачі
SELECT bu.user_id, u1.login as user_login,
       bu.blocked_user_id, u2.login as blocked_login
FROM blocked_users bu
JOIN users u1 ON bu.user_id = u1.id
JOIN users u2 ON bu.blocked_user_id = u2.id;

-- Закріплені користувачі
SELECT pu.user_id, u1.login as user_login,
       pu.pinned_user_id, u2.login as pinned_login
FROM pinned_users pu
JOIN users u1 ON pu.user_id = u1.id
JOIN users u2 ON pu.pinned_user_id = u2.id;
```

## Очищення бази даних

### Видалити всі дані (залишити структуру)
```bash
cd Backend/NotesMyHusband
php -r "\$db = new PDO('sqlite:database/database.sqlite'); 
\$db->exec('DELETE FROM personal_access_tokens');
\$db->exec('DELETE FROM note_recipients');
\$db->exec('DELETE FROM blocked_users');
\$db->exec('DELETE FROM pinned_users');
\$db->exec('DELETE FROM notes');
\$db->exec('DELETE FROM users');
echo 'База даних очищена.\n';"
```

### Повністю перестворити базу даних
```bash
cd Backend/NotesMyHusband
rm -f database/database.sqlite
touch database/database.sqlite
php run_migrations.php
```

## Структура бази даних

### Таблиці:
- **users** - користувачі (id, login, email, password, created_at, updated_at)
- **personal_access_tokens** - токени авторизації Sanctum
- **notes** - нотатки (id, author_id, content, created_at, updated_at)
- **note_recipients** - зв'язок нотаток з отримувачами
- **blocked_users** - заблоковані користувачі
- **pinned_users** - закріплені користувачі

## Графічні інструменти

Якщо потрібен графічний інтерфейс, можна використати:
- **DB Browser for SQLite** (https://sqlitebrowser.org/) - безкоштовний GUI
- **DBeaver** (https://dbeaver.io/) - універсальний клієнт БД
- **VS Code розширення**: SQLite Viewer або SQLite
