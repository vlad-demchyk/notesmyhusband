// Сервіс для роботи з категоріями
import { LocalDataService, ApiDataService, IDataService } from './base.service'
import type { Category, CreateData, UpdateData } from '../types'

// Конфігурація: використовувати локальне сховище або API
const USE_API = false
const API_BASE_URL = 'http://localhost:8000/api'

class CategoriesService {
  private service: IDataService<Category>

  constructor() {
    if (USE_API) {
      this.service = new ApiDataService<Category>(
        `${API_BASE_URL}/categories`,
        () => localStorage.getItem('user_token')
      )
    } else {
      this.service = new LocalDataService<Category>('categories')
    }
  }

  async getAll(userId?: number): Promise<Category[]> {
    const categories = await this.service.getAll()
    if (userId) {
      return categories.filter(cat => cat.user_id === userId)
    }
    return categories
  }

  async getById(id: number): Promise<Category | null> {
    return this.service.getById(id)
  }

  async create(data: CreateData<Category>): Promise<Category> {
    return this.service.create(data)
  }

  async update(id: number, data: UpdateData<Category>): Promise<Category | null> {
    return this.service.update(id, data)
  }

  async delete(id: number): Promise<boolean> {
    return this.service.delete(id)
  }

  async findByUser(userId: number): Promise<Category[]> {
    return this.service.find(cat => cat.user_id === userId)
  }
}

export const categoriesService = new CategoriesService()
