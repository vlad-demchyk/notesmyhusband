# Локальна база даних

Ця структура імітує роботу з базою даних, використовуючи localStorage для зберігання даних у форматі JSON.

## Структура

```
src/
├── types/              # TypeScript типи та інтерфейси
├── data/               # Локальне сховище
│   ├── localStorage.ts # Клас для роботи з localStorage
│   ├── seed.ts         # Початкові тестові дані
│   └── README.md       # Цей файл
└── services/           # Сервіси для роботи з даними
    ├── base.service.ts # Базовий сервіс (локальний/API)
    ├── notes.service.ts
    └── categories.service.ts
```

## Як це працює

1. **LocalStorageDB** (`data/localStorage.ts`) - клас, який імітує роботу з БД:
   - `getAll()` - отримати всі записи
   - `getById()` - отримати запис за ID
   - `create()` - створити новий запис
   - `update()` - оновити запис
   - `delete()` - видалити запис
   - `find()` - знайти записи за умовою

2. **Сервіси** (`services/`) - абстракція над сховищем:
   - Можуть працювати з локальним сховищем або API
   - Легко переключитися між ними через змінну `USE_API`

3. **Stores** (`stores/`) - Pinia stores для управління станом

## Використання

### Безпосередньо через сервіс:

```typescript
import { notesService } from '@/services/notes.service'

// Отримати всі нотатки
const notes = await notesService.getAll()

// Створити нотатку
const newNote = await notesService.create({
  user_id: 1,
  title: 'Нова нотатка',
  content: 'Вміст нотатки',
  is_important: false
})

// Оновити нотатку
await notesService.update(1, { title: 'Оновлена назва' })

// Видалити нотатку
await notesService.delete(1)
```

### Через Pinia Store:

```vue
<script setup lang="ts">
import { useNotesStore } from '@/stores/useNotesStore'

const notesStore = useNotesStore()

// Завантажити нотатки
await notesStore.fetchNotes(userId)

// Створити нотатку
await notesStore.createNote({
  user_id: 1,
  title: 'Нова нотатка',
  content: 'Вміст'
})

// Використати в template
</script>

<template>
  <div v-for="note in notesStore.allNotes" :key="note.id">
    {{ note.title }}
  </div>
</template>
```

## Перехід на бекенд API

Коли бекенд буде готовий:

1. Відкрийте файл сервісу (наприклад, `services/notes.service.ts`)
2. Змініть `USE_API` на `true`:
   ```typescript
   const USE_API = true // Було false
   ```
3. Переконайтеся, що `API_BASE_URL` вказує на правильний адрес
4. Готово! Всі виклики автоматично перейдуть на API

## Перегляд даних

Дані зберігаються в localStorage з префіксом `notes_db_`:
- `notes_db_notes` - нотатки
- `notes_db_categories` - категорії

Можете переглянути їх у DevTools → Application → Local Storage

## Очищення даних

```typescript
import { db } from '@/data/localStorage'

// Очистити одну таблицю
db.clear('notes')

// Очистити всю базу
db.clearAll()
```
