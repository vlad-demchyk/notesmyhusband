// Базові типи для даних

export interface User {
  id: number
  login: string
  email?: string
  created_at: string
  updated_at: string
}

export interface Note {
  id: number
  user_id: number
  title: string
  content: string
  is_important?: boolean
  created_at: string
  updated_at: string
}

export interface Category {
  id: number
  user_id: number
  name: string
  color?: string
  created_at: string
  updated_at: string
}

// Типи для операцій
export type CreateData<T> = Omit<T, 'id' | 'created_at' | 'updated_at'>
export type UpdateData<T> = Partial<Omit<T, 'id' | 'created_at' | 'updated_at'>>

export type RegisterData = {
  login: string
  email?: string
  password: string
  confirm_password?: string
}