# Швидкий старт

## 1. Встановлення залежностей

```bash
cd c:\laragon\www\network-notebook
composer install
```

## 2. Створення бази даних

```bash
php api/database/migrations.php
```

## 3. Доступ до API

### GraphQL Endpoint:
```
http://localhost/network-notebook/
```

### Тестовий інтерфейс:
```
http://localhost/network-notebook/test.html
```

## 4. Тестування через браузер

Відкрийте `http://localhost/network-notebook/` в браузері - ви побачите інформацію про API.

## 5. Тестування через test.html

Відкрийте `http://localhost/network-notebook/test.html` - там є простий інтерфейс для тестування GraphQL запитів.

## 6. Приклад запиту (через curl)

```bash
# Реєстрація
curl -X POST http://localhost/network-notebook/ \
  -H "Content-Type: application/json" \
  -d "{\"query\":\"mutation { register(login: \\\"test\\\", password: \\\"test123\\\") { token user { id login } } }\"}"

# Отримати поточного користувача (з токеном)
curl -X POST http://localhost/network-notebook/ \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d "{\"query\":\"query { me { id login } }\"}"
```

## 7. Приклад запиту (JavaScript)

```javascript
const response = await fetch('http://localhost/network-notebook/', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_TOKEN'
  },
  body: JSON.stringify({
    query: `
      query {
        me {
          id
          login
        }
        receivedNotes {
          id
          content
          author {
            login
          }
        }
      }
    `
  })
});

const data = await response.json();
console.log(data);
```

## Важливо!

- **GET запити** на `/network-notebook/` показують інформацію про API
- **POST запити** обробляють GraphQL запити
- Для автентифікованих запитів додайте заголовок: `Authorization: Bearer TOKEN`
- Токен отримується після `register` або `login` мутації
