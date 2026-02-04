import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useAuthActions } from '@/hooks/useAuthActions'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const authActions = useAuthActions()

  // State
  const user = ref<User | null>(null)
  const userToken = ref<string | null>(localStorage.getItem('user_token'))
  const isLoading = ref(false)

  // Actions instance

  // Getters (computed)
  const isAuthenticated = computed(() => {
    const check = !!userToken.value && !!user.value
    console.log('isAuthenticated check:', check)
    return check
  })

  async function login(credentials: { login: string; password: string; email?: string }) {
    isLoading.value = true
    try {
      const data = await authActions.login(credentials)
      user.value = data.user
      userToken.value = data.token
      localStorage.setItem('user_token', data.token)
    } catch (error) {
      user.value = null
      userToken.value = null
      localStorage.removeItem('user_token')
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function register(credentials: { login: string; password: string; email: string }) {
    isLoading.value = true
    try {
      const data = await authActions.registerUser(credentials)
      user.value = data.user
      userToken.value = data.token
      localStorage.setItem('user_token', data.token)
    } catch (error) {
      user.value = null
      userToken.value = null
      localStorage.removeItem('user_token')
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    user.value = null
    userToken.value = null
    localStorage.removeItem('user_token')
  }

  async function init() {
    isLoading.value = true
    try {
      const data = await authActions.currentUser()
      user.value = data.me || data // залежить від API
    } catch (error) {
      user.value = null
      userToken.value = null
      localStorage.removeItem('user_token')
    } finally {
      isLoading.value = false
    }
  }

  return {
    // State
    user,
    userToken,
    isLoading,

    // Getters
    isAuthenticated,
    init,
    login,
    register,
    logout,
  }
})
