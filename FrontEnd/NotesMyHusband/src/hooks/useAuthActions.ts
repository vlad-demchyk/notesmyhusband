import axios from 'axios'

const API_BASE_URL = 'http://localhost:8000/api'

export function useAuthActions() {
  const login = async (credentials: { email: string, password: string }) => {
    const response = await axios.post(`${API_BASE_URL}/login`, {
      email: credentials.email,
      password: credentials.password
    })
    if (response.status !== 200) {
      throw new Error('Login failed')
    }
    localStorage.setItem('user_token', response.data.token)
    return {
      user: response.data.user,
      token: response.data.token
    }
  }

  const logout = async () => {
    const token = localStorage.getItem('user_token')
    if (!token) {
      localStorage.removeItem('user_token')
      return
    }

    try {
      const response = await axios.post(`${API_BASE_URL}/logout`, {}, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      if (response.status !== 200) {
        throw new Error('Logout failed')
      }
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      localStorage.removeItem('user_token')
    }
  }

  const getCurrentUser = async () => {
    const token = localStorage.getItem('user_token')
    if (!token) {
      return null
    }

    try {
      const response = await axios.get(`${API_BASE_URL}/user`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      if (response.status === 200) {
        return response.data.user
      }
    } catch (error) {
      console.error('Get user error:', error)
      // Якщо токен невалідний, видаляємо його
      localStorage.removeItem('user_token')
      return null
    }
    return null
  }

  return { login, logout, getCurrentUser }
}
