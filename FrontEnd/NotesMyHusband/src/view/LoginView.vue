<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import FormComponent from '../components/FormComponent.vue'
import { useAuthStore } from '../stores/useAuthStore'

const router = useRouter()
const authStore = useAuthStore()
const logActive = ref(false)
const isSending = ref(false)


const handleLogin = async (data: any) => {
  if (isSending.value) return
  isSending.value = true
  const result = await authStore.login({
    email: data.email,
    password: data.password
  })

  if (result.success) {
    // Перенаправляємо на головну сторінку після успішного входу
    router.push('/')
  } else {
    // Показуємо помилку (можна додати toast або alert)
    console.log(result.error || 'Помилка входу')
  }
  isSending.value = false
}
</script>

<template>
  <div class="auth-layout">
    <h1>Benvenuto!</h1>
    <p>Ora sei nella sezione di autenticazione.</p>
    <button v-if="!logActive" @click="logActive = !logActive">Login</button>
    <FormComponent v-else @submit="handleLogin" :isSending="isSending"></FormComponent>
    <RouterLink to="/register">Register</RouterLink>
    <RouterLink to="/forgot-password">Forgot password</RouterLink>
  </div>
</template>

<style scoped>
.auth-layout {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  /* height: 100vh; */
}

a,
button {
  text-align: center;
  display: block;
  margin-bottom: 10px;
  text-decoration: none;
  color: #000;
  font-size: 16px;
  width: 8rem;
  font-weight: 500;
  padding: 10px 20px;
  box-sizing: border-box;
  border-radius: 5px;
  outline: 2px solid var(--color-primary-light);
  outline-offset: 2px;
  background-color: var(--color-primary-light);
  color: var(--color-secondary);
}
</style>
