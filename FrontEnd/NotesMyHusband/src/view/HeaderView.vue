<template>
  <header>
    <div class="common">
      <slot></slot>
    </div>
    <div v-if="authStore.isAuthenticated" class="protected">
      <nav>
        <RouterLink to="/">Home</RouterLink>
        <RouterLink to="/admin">Admin</RouterLink>
      </nav>
      <span>Вітаємо, {{ authStore.user?.login || authStore.user?.email }}!</span>
      <button @click="handleLogout" class="logout-btn">Вийти</button>
    </div>
  </header>
</template>
<style scoped></style>
<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'

const router = useRouter()
const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
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
