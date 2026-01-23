// Сервіс для роботи з нотатками
import { LocalDataService, ApiDataService, IDataService } from './base.service'
import type { Note, CreateData, UpdateData } from '../types'

// Конфігурація: використовувати локальне сховище або API
const USE_API = false // Змініть на true, коли бекенд буде готовий
const API_BASE_URL = 'http://localhost:8000/api'

class NotesService {
  private service: IDataService<Note>

  constructor() {
    if (USE_API) {
      // Використовуємо API сервіс
      this.service = new ApiDataService<Note>(
        `${API_BASE_URL}/notes`,
        () => localStorage.getItem('user_token')
      )
    } else {
      // Використовуємо локальне сховище
      this.service = new LocalDataService<Note>('notes')
    }
  }

  // Отримати всі нотатки користувача
  async getAll(userId?: number): Promise<Note[]> {
    const notes = await this.service.getAll()
    if (userId) {
      return notes.filter(note => note.user_id === userId)
    }
    return notes
  }

  // Отримати нотатку за ID
  async getById(id: number): Promise<Note | null> {
    return this.service.getById(id)
  }

  // Створити нову нотатку
  async create(data: CreateData<Note>): Promise<Note> {
    return this.service.create(data)
  }

  // Оновити нотатку
  async update(id: number, data: UpdateData<Note>): Promise<Note | null> {
    return this.service.update(id, data)
  }

  // Видалити нотатку
  async delete(id: number): Promise<boolean> {
    return this.service.delete(id)
  }

  // Знайти нотатки за умовою
  async findByUser(userId: number): Promise<Note[]> {
    return this.service.find(note => note.user_id === userId)
  }

  // Знайти важливі нотатки
  async findImportant(userId: number): Promise<Note[]> {
    return this.service.find(note => note.user_id === userId && note.is_important === true)
  }

  // Пошук за текстом
  async search(userId: number, query: string): Promise<Note[]> {
    const lowerQuery = query.toLowerCase()
    return this.service.find(note => 
      note.user_id === userId && 
      (note.title.toLowerCase().includes(lowerQuery) || 
       note.content.toLowerCase().includes(lowerQuery))
    )
  }
}

// Експортуємо singleton instance
export const notesService = new NotesService()
