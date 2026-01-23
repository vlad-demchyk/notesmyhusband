// Локальне сховище на основі localStorage
// Імітує роботу з базою даних

const DB_PREFIX = 'notes_db_'

export class LocalStorageDB {
  private getKey(table: string): string {
    return `${DB_PREFIX}${table}`
  }

  // Отримати всі записи з таблиці
  getAll<T>(table: string): T[] {
    try {
      const data = localStorage.getItem(this.getKey(table))
      return data ? JSON.parse(data) : []
    } catch (error) {
      console.error(`Error reading ${table}:`, error)
      return []
    }
  }

  // Отримати один запис за ID
  getById<T>(table: string, id: number): T | null {
    const items = this.getAll<T>(table)
    return items.find((item: any) => item.id === id) || null
  }

  // Створити новий запис
  create<T extends { id?: number }>(table: string, data: T): T {
    const items = this.getAll<T>(table)
    const newId = this.getNextId(table)
    const now = new Date().toISOString()
    
    const newItem: T = {
      ...data,
      id: newId,
      created_at: now,
      updated_at: now
    } as T

    items.push(newItem)
    this.save(table, items)
    return newItem
  }

  // Оновити запис
  update<T extends { id: number }>(table: string, id: number, data: Partial<T>): T | null {
    const items = this.getAll<T>(table)
    const index = items.findIndex((item: any) => item.id === id)
    
    if (index === -1) {
      return null
    }

    const updatedItem: T = {
      ...items[index],
      ...data,
      id,
      updated_at: new Date().toISOString()
    }

    items[index] = updatedItem
    this.save(table, items)
    return updatedItem
  }

  // Видалити запис
  delete(table: string, id: number): boolean {
    const items = this.getAll<any>(table)
    const filtered = items.filter((item: any) => item.id !== id)
    
    if (filtered.length === items.length) {
      return false // Запис не знайдено
    }

    this.save(table, filtered)
    return true
  }

  // Знайти записи за умовою
  find<T>(table: string, predicate: (item: T) => boolean): T[] {
    const items = this.getAll<T>(table)
    return items.filter(predicate)
  }

  // Очистити таблицю
  clear(table: string): void {
    localStorage.removeItem(this.getKey(table))
  }

  // Очистити всю базу даних
  clearAll(): void {
    const keys = Object.keys(localStorage)
    keys.forEach(key => {
      if (key.startsWith(DB_PREFIX)) {
        localStorage.removeItem(key)
      }
    })
  }

  // Приватні методи
  private save<T>(table: string, data: T[]): void {
    try {
      localStorage.setItem(this.getKey(table), JSON.stringify(data))
    } catch (error) {
      console.error(`Error saving ${table}:`, error)
      throw new Error(`Failed to save ${table}`)
    }
  }

  private getNextId(table: string): number {
    const items = this.getAll<any>(table)
    if (items.length === 0) {
      return 1
    }
    const maxId = Math.max(...items.map((item: any) => item.id || 0))
    return maxId + 1
  }
}

// Експортуємо singleton instance
export const db = new LocalStorageDB()
