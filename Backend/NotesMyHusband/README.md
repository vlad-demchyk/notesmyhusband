# Network Notebook API

API для мережевого блокнота з використанням GraphQL та SQLite.

## Встановлення

1. Встановіть залежності через Composer:
```bash
composer install
```

2. Запустіть міграції для створення таблиць:
```bash
php api/database/migrations.php
```

## Структура проекту

```
network-notebook/
├── api/
│   ├── auth/
│   │   └── auth.php          # Аутентифікація та JWT токени
│   ├── database/
│   │   ├── db.php            # Підключення до SQLite
│   │   └── migrations.php    # Міграції бази даних
│   └── graphql/
│       ├── index.php         # GraphQL endpoint
│       ├── types.php         # GraphQL типи
│       └── resolvers.php     # GraphQL resolvers
├── database/
│   └── notebook.db          # SQLite база даних (створюється автоматично)
├── public/
│   └── index.php            # Точка входу
└── composer.json
```

## GraphQL API

### Запити (Queries)

- `me` - отримати поточного користувача
- `users` - список всіх користувачів (крім заблокованих)
- `myNotes` - нотатки, створені поточним користувачем
- `receivedNotes` - нотатки, отримані поточним користувачем
- `note(id: Int!)` - отримати нотатку за ID
- `pinnedUsers` - список запінених користувачів
- `blockedUsers` - список заблокованих користувачів

### Мутації (Mutations)

- `register(login: String!, password: String!)` - реєстрація нового користувача
- `login(login: String!, password: String!)` - вхід в систему
- `createNote(content: String!, recipient_ids: [Int!])` - створити нотатку
- `updateNote(id: Int!, content: String!)` - оновити нотатку (тільки автор)
- `deleteNote(id: Int!)` - видалити нотатку (тільки автор)
- `pinUser(user_id: Int!)` - запінити користувача
- `unpinUser(user_id: Int!)` - відпінити користувача
- `blockUser(user_id: Int!)` - заблокувати користувача
- `unblockUser(user_id: Int!)` - розблокувати користувача

## Аутентифікація

Для автентифікованих запитів додайте заголовок:
```
Authorization: Bearer YOUR_JWT_TOKEN
```

Токен отримується після успішної реєстрації або входу.

## Приклади запитів

### Реєстрація
```graphql
mutation {
  register(login: "user1", password: "password123") {
    token
    user {
      id
      login
    }
  }
}
```

### Вхід
```graphql
mutation {
  login(login: "user1", password: "password123") {
    token
    user {
      id
      login
    }
  }
}
```

### Створення нотатки
```graphql
mutation {
  createNote(content: "Привіт!", recipient_ids: [2, 3]) {
    id
    content
    author {
      login
    }
    recipients {
      login
    }
  }
}
```

### Отримання отриманих нотаток
```graphql
query {
  receivedNotes {
    id
    content
    author {
      login
    }
    created_at
  }
}
```

## База даних

Використовується SQLite база даних, яка зберігається в `database/notebook.db`.

Таблиці:
- `users` - користувачі
- `notes` - нотатки
- `note_recipients` - зв'язок нотаток з отримувачами
- `blocked_users` - чорний список користувачів
- `pinned_users` - запінені користувачі
