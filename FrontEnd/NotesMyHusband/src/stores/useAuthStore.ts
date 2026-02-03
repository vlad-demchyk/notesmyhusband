import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useAuthActions } from '@/hooks/useAuthActions'

interface User {
  id: number
  login: string
  email?: string
  created_at: string
  [key: string]: any
}

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null)
  const userToken = ref<string | null>(localStorage.getItem('user_token'))
  const isLoading = ref(false)

  // Actions instance
  const authActions = useAuthActions()

  // Getters (computed)
  const isAuthenticated = computed(() => {
    return !!userToken.value && !!user.value
  })

  const getUser = computed(() => user.value)
  const getToken = computed(() => userToken.value)

  // Actions
  async function login(credentials: { login: string, password: string, email?: string }) {
    try {
      isLoading.value = true
      const data = await authActions.login(credentials)
      
      // Оновлюємо стан
      user.value = data.user
      userToken.value = data.token
      localStorage.setItem('user_token', data.token)
      
      return { success: true, user: data.user }
    } catch (error: any) {
      // Обробка помилок для UI
      const errorMessage = error?.message || 'Помилка входу'
      console.error('[useAuthStore] Login error:', errorMessage)
      
      return { 
        success: false, 
        error: errorMessage
      }
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    try {
      isLoading.value = true
      await authActions.logout()
      
      user.value = null
      userToken.value = null
      
      return { success: true }
    } catch (error: any) {
      console.error('Logout error:', error)
      // Навіть якщо помилка, очищаємо локальний стан
      user.value = null
      userToken.value = null
      return { 
        success: false, 
        error: error.message || 'Помилка виходу' 
      }
    } finally {
      isLoading.value = false
    }
  }

  async function registerUser(credentials: { login: string, password: string, email?: string }) {
    try {
      isLoading.value = true
      const data = await authActions.registerUser(credentials)
      
      // Оновлюємо стан
      user.value = data.user
      userToken.value = data.token
      localStorage.setItem('user_token', data.token)
      
      return { success: true, user: data.user }
    } catch (error: any) {
      // Обробка помилок для UI
      const errorMessage = error?.message || 'Помилка реєстрації'
      console.error('[useAuthStore] Register error:', errorMessage)
      
      return { 
        success: false, 
        error: errorMessage
      }
    } finally {
      isLoading.value = false
    }
  }

  async function fetchUser() {
    const token = localStorage.getItem('user_token')
    if (!token) {
      user.value = null
      userToken.value = null
      return
    }

    try {
      isLoading.value = true
      const fetchedUser = await authActions.getCurrentUser()
      
      if (fetchedUser) {
        // Користувач знайдений - оновлюємо стан
        user.value = fetchedUser
        userToken.value = token // Токен беремо з localStorage, не з user
      } else {
        // Токен невалідний - очищаємо стан
        user.value = null
        userToken.value = null
        localStorage.removeItem('user_token')
      }
    } catch (error: any) {
      // Помилка при отриманні користувача - очищаємо стан тільки якщо це помилка авторизації
      console.error('[useAuthStore] Fetch user error:', error)
      user.value = null
      userToken.value = null
      localStorage.removeItem('user_token')
    } finally {
      isLoading.value = false
    }
  }

  // Ініціалізація при завантаженні store
  // Повертає Promise, щоб можна було дочекатися завершення
  async function init() {
    const token = localStorage.getItem('user_token')
    if (token) {
      userToken.value = token
      // Завантажуємо користувача з API і чекаємо завершення
      await fetchUser()
    } else {
      user.value = null
      userToken.value = null
    }
  }

  return {
    // State
    user,
    userToken,
    isLoading,
    
    // Getters
    isAuthenticated,
    getUser,
    getToken,
    
    // Actions
    login,
    logout,
    registerUser,
    fetchUser,
    init
  }
})
