import { authService } from '@/services/auth'
import { useAuthStore } from '@/stores/useAuthStore'

export function useAuthActions() {
  const auth = authService()
  const authStore = useAuthStore()

  // Тільки API-виклики, без роботи зі станом чи localStorage
  const login = async (credentials: { login: string; password: string; email?: string }) => {
    try {
      return await auth.login(credentials) // повертає { token, user }
    } catch (error) {
      console.error('Login error:', error)
      throw error
    }
  }

  const registerUser = async (credentials: { login: string; password: string; email: string }) => {
    try {
      return await auth.register(credentials) // повертає { token, user }
    } catch (error) {
      console.error('Register error:', error)
      throw error
    }
  }

  const currentUser = async () => {
    try {
      if (!authStore.userToken) {
        return null
      }
      return await auth.getCurrentUser() // повертає user або null
    } catch (error) {
      console.error('User error:', error)
      throw error
    }
  }

  return { login, registerUser, currentUser }
}
