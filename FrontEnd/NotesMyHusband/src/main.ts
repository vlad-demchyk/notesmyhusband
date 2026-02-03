import { createApp } from 'vue'
import { DefaultApolloClient } from '@vue/apollo-composable'
import { apolloClient } from './hooks/apolloClient'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/useAuthStore'

async function initApp() {
  const app = createApp(App)

  app.provide(DefaultApolloClient, apolloClient) // Надаємо клієнт через метод app
  app.use(createPinia())
  app.use(router)

  // Ініціалізуємо store та чекаємо завершення перед монтуванням
  const authStore = useAuthStore()
  await authStore.init()

  app.mount('#app')
}

initApp()