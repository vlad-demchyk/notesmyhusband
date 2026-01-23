// Початкові дані для тестування
import { db } from './localStorage'
import type { Note, Category } from '../types'

export function seedDatabase() {
  // Перевіряємо, чи вже є дані
  const existingNotes = db.getAll('notes')
  if (existingNotes.length > 0) {
    return // Дані вже існують
  }

  // Створюємо тестові нотатки
  const notes: Omit<Note, 'id' | 'created_at' | 'updated_at'>[] = [
    {
      user_id: 1,
      title: 'Перша нотатка',
      content: 'Це моя перша нотатка в додатку NotesMyHusband',
      is_important: false
    },
    {
      user_id: 1,
      title: 'Важлива нотатка',
      content: 'Не забути зробити щось важливе',
      is_important: true
    },
    {
      user_id: 1,
      title: 'Список покупок',
      content: 'Молоко, хліб, яйця, масло',
      is_important: false
    }
  ]

  notes.forEach(note => {
    db.create('notes', note as Note)
  })

  // Створюємо тестові категорії
  const categories: Omit<Category, 'id' | 'created_at' | 'updated_at'>[] = [
    {
      user_id: 1,
      name: 'Особисте',
      color: '#3b82f6'
    },
    {
      user_id: 1,
      name: 'Робота',
      color: '#10b981'
    },
    {
      user_id: 1,
      name: 'Покупки',
      color: '#f59e0b'
    }
  ]

  categories.forEach(category => {
    db.create('categories', category as Category)
  })

  console.log('Database seeded successfully!')
}
