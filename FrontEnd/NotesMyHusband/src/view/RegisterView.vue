<template>
 <FormComponent 
   :fields="fields" 
   :errorMessage="errorMessage" 
   :successMessage="successMessage" 
   :isSending="isSending"
   @submit="handleRegister"
 />
 <RouterLink to="/login">Back to login</RouterLink>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import FormComponent from '../components/FormComponent.vue'
import { useAuthStore } from '../stores/useAuthStore'
import { useRouter } from 'vue-router'
import type { RegisterData } from '../types'
import { ref } from 'vue'
const authStore = useAuthStore()
const router = useRouter()
const errorMessage = ref<string | null>(null)
const successMessage = ref<string | null>(null)
const isSending = ref(false)
const fields = {
  login: { type: 'text', name: 'login', required: true, placeholder: 'Login' },
  email: { type: 'email', name: 'email', required: false, placeholder: 'Email (optional)' },
  password: { type: 'password', name: 'password', required: true, placeholder: 'Password' },
  confirm_password: { type: 'password', name: 'confirm_password', required: true, placeholder: 'Confirm Password' },
}

const handleRegister = async (data: RegisterData) => {
  if (isSending.value) return
  isSending.value = true
  errorMessage.value = null
  successMessage.value = null

  if (data.password !== data.confirm_password) {
    errorMessage.value = 'Паролі не співпадають'
    isSending.value = false
    return
  }

  try {
    const result = await authStore.registerUser({
      login: data.login,
      email: data.email,
      password: data.password
    })

    if (result.success) {
      successMessage.value = 'Реєстрація успішна'
      router.push('/')
    } else {
      errorMessage.value = result.error || 'Помилка реєстрації'
      console.error('Register error:', result.error)
    }
  } catch (error: any) {
    errorMessage.value = error?.message || 'Несподівана помилка'
    console.error('Unexpected register error:', error)
  } finally {
    isSending.value = false
  }
}
</script>
