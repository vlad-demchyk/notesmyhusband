import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useAuthActions } from '@/hooks/useAuthActions'

interface User {
  id: number
  name: string
  email: string
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
  async function login(credentials: { email: string, password: string }) {
    try {
      isLoading.value = true
      const data = await authActions.login(credentials)
      
      user.value = data.user
      userToken.value = data.token
      
      return { success: true, user: data.user }
    } catch (error: any) {
      console.error('Login error:', error)
      return { 
        success: false, 
        error: error.response?.data?.message || error.message || 'Помилка входу' 
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
        user.value = fetchedUser
        userToken.value = token
      } else {
        // Токен невалідний
        user.value = null
        userToken.value = null
        localStorage.removeItem('user_token')
      }
    } catch (error: any) {
      console.error('Fetch user error:', error)
      user.value = null
      userToken.value = null
      localStorage.removeItem('user_token')
    } finally {
      isLoading.value = false
    }
  }

  // Ініціалізація при завантаженні store
  function init() {
    const token = localStorage.getItem('user_token')
    if (token) {
      userToken.value = token
      // Завантажуємо користувача з API
      fetchUser()
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
    fetchUser,
    init
  }
})
