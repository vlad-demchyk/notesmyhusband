# Як працює система - простими словами

## 🎬 Сценарій: Користувач створює нотатку

### Крок 1: Користувач натискає кнопку "Створити нотатку" у Vue.js додатку

```javascript
// Vue.js компонент
async createNote() {
  const query = `
    mutation {
      createNote(
        content: "Привіт, це моя нотатка!"
        recipient_ids: [2, 3]
      ) {
        id
        content
        author {
          login
        }
      }
    }
  `;
  
  const response = await fetch('http://localhost/api/graphql/index.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + this.token  // JWT токен збережений після логіну
    },
    body: JSON.stringify({ query })
  });
  
  const result = await response.json();
  console.log(result);
}
```

### Крок 2: HTTP запит надходить на сервер

```
POST /api/graphql/index.php
Headers:
  Content-Type: application/json
  Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...

Body:
{
  "query": "mutation { createNote(...) { ... } }"
}
```

### Крок 3: `index.php` обробляє запит

```php
// api/graphql/index.php

// 1. Отримує GraphQL запит
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$query = $input['query'];  // "mutation { createNote(...) }"

// 2. Створює GraphQL схему
$schema = new Schema([
    'query' => Types::query(),
    'mutation' => Types::mutation()  // Тут знаходиться createNote
]);

// 3. Виконує запит
$result = GraphQL::executeQuery($schema, $query);
```

### Крок 4: GraphQL знаходить мутацію `createNote`

```php
// api/graphql/types.php

'mutation' => new ObjectType([
    'name' => 'Mutation',
    'fields' => [
        'createNote' => [
            'type' => self::note(),
            'args' => [
                'content' => Type::string(),
                'recipient_ids' => Type::listOf(Type::int())
            ],
            'resolve' => function ($root, $args) {
                // Викликає цю функцію ↓
                return Resolvers::createNote(
                    $args['content'], 
                    $args['recipient_ids']
                );
            }
        ]
    ]
])
```

### Крок 5: Викликається `Resolvers::createNote()`

```php
// api/graphql/resolvers.php

public static function createNote($content, $recipientIds)
{
    // 1. Перевіряє хто робить запит
    $currentUser = Auth::requireAuth();
    // Auth::requireAuth() витягує токен з заголовка
    // Декодує JWT → отримує user_id = 1
    
    // 2. Підключається до бази даних
    $db = Database::getInstance()->getConnection();
    
    // 3. Створює нотатку
    $stmt = $db->prepare("
        INSERT INTO notes (author_id, content) 
        VALUES (?, ?)
    ");
    $stmt->execute([$currentUser['user_id'], $content]);
    // SQL: INSERT INTO notes (author_id, content) VALUES (1, 'Привіт, це моя нотатка!')
    
    $noteId = $db->lastInsertId();  // Отримуємо ID створеної нотатки (наприклад, 5)
    
    // 4. Додає отримувачів
    foreach ($recipientIds as $recipientId) {
        $stmt = $db->prepare("
            INSERT INTO note_recipients (note_id, recipient_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$noteId, $recipientId]);
        // SQL: INSERT INTO note_recipients (note_id, recipient_id) VALUES (5, 2)
        // SQL: INSERT INTO note_recipients (note_id, recipient_id) VALUES (5, 3)
    }
    
    // 5. Повертає створену нотатку
    $stmt = $db->prepare("SELECT * FROM notes WHERE id = ?");
    $stmt->execute([$noteId]);
    return $stmt->fetch();
    // Повертає: ['id' => 5, 'author_id' => 1, 'content' => 'Привіт...', ...]
}
```

### Крок 6: GraphQL формує відповідь

GraphQL бере повернуті дані та формує відповідь згідно запиту клієнта:

```json
{
  "data": {
    "createNote": {
      "id": 5,
      "content": "Привіт, це моя нотатка!",
      "author": {
        "login": "user1"
      }
    }
  }
}
```

### Крок 7: Відповідь повертається клієнту

```php
// index.php
echo json_encode($output);
// HTTP 200 OK
// Body: { "data": { "createNote": { ... } } }
```

### Крок 8: Vue.js отримує відповідь та оновлює UI

```javascript
const result = await response.json();
// result = { data: { createNote: { id: 5, content: "...", ... } } }

// Оновлює список нотаток
this.notes.push(result.data.createNote);
```

---

## 🔍 Детальніше про кожен компонент

### 1. JWT Токен - як він працює

**При реєстрації/вході:**

```php
// api/auth/auth.php

// Користувач ввів логін та пароль
$user = ['id' => 1, 'login' => 'user1'];

// Генеруємо токен
$token = Auth::generateToken(1, 'user1');

// Всередині токену:
{
  "user_id": 1,
  "login": "user1",
  "iat": 1704067200,  // Коли створено
  "exp": 1704672000   // Коли закінчується (через 7 днів)
}

// Токен підписується секретним ключем
// Результат: "eyJ0eXAiOiJKV1QiLCJhbGc..."
```

**При кожному запиті:**

```php
// Клієнт надсилає: Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...

// Сервер перевіряє:
$decoded = JWT::decode($token, $secretKey);
// Якщо токен валідний → отримуємо user_id
// Якщо токен невалідний/прострочений → null
```

### 2. SQLite - як зберігаються дані

**Файл:** `database/notebook.db` (бінарний файл)

**Структура:**

```
notebook.db
├── users
│   ├── id: 1, login: "user1", password: "$2y$10$..."
│   ├── id: 2, login: "user2", password: "$2y$10$..."
│   └── id: 3, login: "user3", password: "$2y$10$..."
│
├── notes
│   ├── id: 1, author_id: 1, content: "Привіт!", created_at: "2026-02-02..."
│   ├── id: 2, author_id: 2, content: "Як справи?", created_at: "2026-02-02..."
│   └── id: 3, author_id: 1, content: "До побачення", created_at: "2026-02-02..."
│
└── note_recipients
    ├── note_id: 1, recipient_id: 2
    ├── note_id: 1, recipient_id: 3
    └── note_id: 2, recipient_id: 1
```

**Як працює запит:**

```php
// Отримати всі нотатки користувача з ID = 1
$stmt = $db->prepare("
    SELECT * FROM notes 
    WHERE author_id = ?
");
$stmt->execute([1]);

// SQLite шукає в таблиці notes всі рядки де author_id = 1
// Повертає: [['id' => 1, 'content' => 'Привіт!', ...], ['id' => 3, ...]]
```

### 3. GraphQL Resolvers - бізнес-логіка

**Приклад: Отримання отриманих нотаток**

```php
public static function getReceivedNotes()
{
    // 1. Хто робить запит?
    $currentUser = Auth::requireAuth();  // user_id = 1
    
    // 2. Знайти нотатки, де я є отримувачем
    $db = self::getDb();
    $stmt = $db->prepare("
        SELECT DISTINCT n.* 
        FROM notes n
        INNER JOIN note_recipients nr ON n.id = nr.note_id
        WHERE nr.recipient_id = ?
        ORDER BY n.created_at DESC
    ");
    $stmt->execute([$currentUser['user_id']]);
    
    // SQL виконується:
    // 1. Бере таблицю notes (n)
    // 2. З'єднує з note_recipients (nr) де note_id співпадає
    // 3. Фільтрує де recipient_id = 1 (поточний користувач)
    // 4. Сортує за датою створення
    
    return $stmt->fetchAll();
}
```

### 4. Безпека - як захищаються дані

**1. SQL Injection захист:**
```php
// ❌ НЕБЕЗПЕЧНО:
$query = "SELECT * FROM users WHERE login = '$login'";
// Якщо login = "admin' OR '1'='1" → отримаємо всіх користувачів!

// ✅ БЕЗПЕЧНО:
$stmt = $db->prepare("SELECT * FROM users WHERE login = ?");
$stmt->execute([$login]);
// PDO автоматично екранує спеціальні символи
```

**2. Паролі:**
```php
// Зберігання:
$hashed = password_hash('myPassword123', PASSWORD_DEFAULT);
// Результат: "$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy"
// Неможливо відновити оригінальний пароль!

// Перевірка:
if (password_verify('myPassword123', $hashed)) {
    // Пароль правильний
}
```

**3. JWT токени:**
- Підписуються секретним ключем
- Неможливо підробити без знання ключа
- Мають термін дії (exp)
- Містять мінімум інформації (тільки user_id та login)

---

## 🎯 Порівняння з REST API

### REST підхід:

```
GET  /api/users/1              → Отримати користувача
GET  /api/users/1/notes        → Отримати нотатки користувача
GET  /api/notes/1              → Отримати нотатку
POST /api/notes                → Створити нотатку
PUT  /api/notes/1              → Оновити нотатку
DELETE /api/notes/1            → Видалити нотатку
```

**Проблеми:**
- Багато endpoints
- Over-fetching (отримуємо більше даних ніж потрібно)
- Under-fetching (потрібно кілька запитів для отримання повної інформації)

### GraphQL підхід:

```
POST /api/graphql/index.php
Body: {
  "query": "query { user(id: 1) { login notes { id content } } }"
}
```

**Переваги:**
- Один endpoint
- Клієнт визначає що отримати
- Один запит для складних даних

---

## 📚 Ключові терміни

- **GraphQL Schema** - опис структури даних та операцій
- **Query** - операція читання даних
- **Mutation** - операція зміни даних
- **Resolver** - функція яка виконує запит
- **Type** - опис структури об'єкта (User, Note)
- **JWT** - токен для аутентифікації
- **PDO** - інтерфейс для роботи з БД
- **Singleton** - паттерн для одного екземпляра об'єкта
- **PSR-4** - стандарт автозавантаження класів

---

## 🚀 Що далі?

Після розуміння архітектури можна:

1. **Додати нові типи** - наприклад, коментарі до нотаток
2. **Додати нові мутації** - наприклад, редагування профілю
3. **Оптимізувати запити** - додати кешування
4. **Додати валідацію** - перевірка даних перед збереженням
5. **Додати тести** - автоматичне тестування API
