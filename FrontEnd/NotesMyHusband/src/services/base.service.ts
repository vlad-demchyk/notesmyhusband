// Базовий сервіс для роботи з даними
// Легко замінити на API виклики

import { db } from '../data/localStorage'
import type { CreateData, UpdateData } from '../types'

export interface IDataService<T> {
  getAll(): Promise<T[]>
  getById(id: number): Promise<T | null>
  create(data: CreateData<T>): Promise<T>
  update(id: number, data: UpdateData<T>): Promise<T | null>
  delete(id: number): Promise<boolean>
  find(predicate: (item: T) => boolean): Promise<T[]>
}

export class LocalDataService<T extends { id?: number }> implements IDataService<T> {
  constructor(private tableName: string) {}

  async getAll(): Promise<T[]> {
    return db.getAll<T>(this.tableName)
  }

  async getById(id: number): Promise<T | null> {
    return db.getById<T>(this.tableName, id)
  }

  async create(data: CreateData<T>): Promise<T> {
    return db.create<T>(this.tableName, data as T)
  }

  async update(id: number, data: UpdateData<T>): Promise<T | null> {
    return db.update<T>(this.tableName, id, data)
  }

  async delete(id: number): Promise<boolean> {
    return db.delete(this.tableName, id)
  }

  async find(predicate: (item: T) => boolean): Promise<T[]> {
    return db.find<T>(this.tableName, predicate)
  }
}

// Для майбутнього переходу на API
export class ApiDataService<T extends { id?: number }> implements IDataService<T> {
  constructor(
    private endpoint: string,
    private getAuthToken?: () => string | null
  ) {}

  private getHeaders(): HeadersInit {
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
    }
    
    if (this.getAuthToken) {
      const token = this.getAuthToken()
      if (token) {
        headers['Authorization'] = `Bearer ${token}`
      }
    }
    
    return headers
  }

  async getAll(): Promise<T[]> {
    const response = await fetch(this.endpoint, {
      headers: this.getHeaders()
    })
    if (!response.ok) throw new Error('Failed to fetch')
    const data = await response.json()
    return Array.isArray(data) ? data : data.data || []
  }

  async getById(id: number): Promise<T | null> {
    const response = await fetch(`${this.endpoint}/${id}`, {
      headers: this.getHeaders()
    })
    if (!response.ok) return null
    const data = await response.json()
    return data.data || data
  }

  async create(data: CreateData<T>): Promise<T> {
    const response = await fetch(this.endpoint, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(data)
    })
    if (!response.ok) throw new Error('Failed to create')
    const result = await response.json()
    return result.data || result
  }

  async update(id: number, data: UpdateData<T>): Promise<T | null> {
    const response = await fetch(`${this.endpoint}/${id}`, {
      method: 'PUT',
      headers: this.getHeaders(),
      body: JSON.stringify(data)
    })
    if (!response.ok) return null
    const result = await response.json()
    return result.data || result
  }

  async delete(id: number): Promise<boolean> {
    const response = await fetch(`${this.endpoint}/${id}`, {
      method: 'DELETE',
      headers: this.getHeaders()
    })
    return response.ok
  }

  async find(predicate: (item: T) => boolean): Promise<T[]> {
    const all = await this.getAll()
    return all.filter(predicate)
  }
}
