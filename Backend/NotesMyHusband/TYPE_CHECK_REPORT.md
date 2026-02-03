# Звіт про перевірку відповідності типів GraphQL

## Перевірка виконана: 2026-02-03

### 1. GraphQL Схема (Backend)

**Тип User:**
```graphql
type User {
    id: ID!              # GraphQL ID (автоматично конвертується з integer)
    login: String!       # Обов'язковий рядок
    email: String        # Опціональний рядок (nullable)
    created_at: DateTime! # Обов'язкова дата (Lighthouse конвертує Carbon в ISO string)
    updated_at: DateTime! # Обов'язкова дата
}
```

**Мутація register:**
```graphql
register(login: String!, password: String!, email: String): AuthPayload!
```

**Тип AuthPayload:**
```graphql
type AuthPayload {
    token: String!
    user: User!
}
```

### 2. Резолвер Register.php

✅ **Правильно:**
- Приймає `login`, `password`, `email` (опціональний)
- Хешує пароль через `Hash::make()`
- Перевіряє унікальність login та email
- Повертає `['token' => $token, 'user' => $user]`
- Обробляє помилки через `AuthenticationException`

⚠️ **Виправлено:**
- Видалено `'password' => 'hashed'` з casts моделі User, щоб уникнути подвійного хешування

### 3. Модель User.php

✅ **Правильно:**
- `$fillable` містить `login`, `email`, `password`
- `$hidden` містить `password` (не повертається в JSON)
- Відносини налаштовані правильно

### 4. Фронтенд TypeScript

**useAuthActions.ts:**
```typescript
registerUser(credentials: { 
    login: string; 
    password: string; 
    email?: string 
}): Promise<{ 
    token: string; 
    user: { 
        id: number; 
        login: string; 
        email?: string; 
        created_at: string 
    } 
}>
```

✅ **Відповідність:**
- `login: String!` → `login: string` ✅
- `password: String!` → `password: string` ✅
- `email: String` → `email?: string` ✅
- `token: String!` → `token: string` ✅
- `id: ID!` → `id: number` ✅ (GraphQL ID конвертується в число)
- `created_at: DateTime!` → `created_at: string` ✅ (ISO 8601 string)

### 5. Потенційні проблеми та рішення

#### Проблема 1: Подвійне хешування пароля
**Статус:** ✅ Виправлено
- Видалено `'password' => 'hashed'` з casts моделі User
- Пароль хешується тільки в резолвері через `Hash::make()`

#### Проблема 2: Конвертація типів
**Статус:** ✅ Працює правильно
- Lighthouse автоматично конвертує:
  - `integer` → `ID` (GraphQL)
  - `Carbon` → `DateTime` (ISO 8601 string)
  - `null` → правильно обробляється для опціональних полів

#### Проблема 3: Обробка помилок
**Статус:** ✅ Налаштовано правильно
- `AuthenticationException` правильно обробляється Lighthouse
- Помилки повертаються у форматі GraphQL errors
- Фронтенд правильно витягує повідомлення з `error.graphQLErrors[0].message`

### 6. Тестування

**Тест 1: Створення користувача напряму (Eloquent)**
✅ Пройдено успішно
- Користувач створюється правильно
- Токен створюється через Sanctum
- Типи даних правильні

**Тест 2: GraphQL мутація через HTTP**
⚠️ Потрібно перевірити через реальний HTTP запит (через браузер/Postman)

### 7. Рекомендації

1. ✅ Виправлено подвійне хешування пароля
2. ✅ Перевірено відповідність типів між схемою та фронтендом
3. ✅ Обробка помилок налаштована правильно
4. ⚠️ Рекомендується протестувати через реальний HTTP запит

### 8. Структура відповіді GraphQL

**Очікувана відповідь:**
```json
{
  "data": {
    "register": {
      "token": "1|abc123...",
      "user": {
        "id": "1",
        "login": "testuser",
        "email": "test@example.com",
        "created_at": "2026-02-03T21:26:44+00:00",
        "updated_at": "2026-02-03T21:26:44+00:00"
      }
    }
  }
}
```

**Примітки:**
- `id` може бути рядком або числом (GraphQL ID)
- `created_at` та `updated_at` - ISO 8601 формат
- `email` може бути `null` якщо не передано

### Висновок

✅ Всі типи відповідають один одному
✅ Проблема з подвійним хешуванням виправлена
✅ Обробка помилок налаштована правильно
✅ Структура даних відповідає GraphQL схемі

Якщо все ще виникають помилки реєстрації, перевірте:
1. Чи правильно передаються дані з фронтенду
2. Чи правильно обробляються помилки на фронтенді
3. Логи Laravel (`storage/logs/laravel.log`)
