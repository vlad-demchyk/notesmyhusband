import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/useAuthStore'
import { seedDatabase } from './data/seed'

const app = createApp(App)

const pinia = createPinia()
app.use(pinia)
app.use(router)

// Ініціалізуємо auth store після створення pinia
const authStore = useAuthStore()
authStore.init()

// Заповнюємо локальну базу тестовими даними (тільки якщо вона порожня)
seedDatabase()

app.mount('#app')
