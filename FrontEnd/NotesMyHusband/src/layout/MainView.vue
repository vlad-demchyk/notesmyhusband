<template>
  <header>
    <h1>NotesMyHusband</h1>
    <nav>
      <RouterLink to="/">Home</RouterLink>
      <RouterLink to="/admin">Admin</RouterLink>
    </nav>
      <span>Вітаємо, {{ authStore.getUser?.name || authStore.getUser?.email }}!</span>
      <button @click="handleLogout" class="logout-btn">Вийти</button>
  </header>
  <RouterView />
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'

const router = useRouter()
const authStore = useAuthStore()

const handleLogout = async () => {
  const result = await authStore.logout()
  if (result.success) {
    router.push('/login')
  }
}
</script>

<style scoped>
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  border-bottom: 1px solid #e5e5e5;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
}

.logout-btn:hover {
  background-color: #c82333;
}
</style>
