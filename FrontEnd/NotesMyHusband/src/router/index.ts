import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'
import LoginView from "../view/LoginView.vue";
import RegisterView from "../view/RegisterView.vue";
import HomeView from "../view/HomeView.vue";
import AdminView from "../view/AdminView.vue";
import NotFoundView from "../view/NotFound.vue";
import ForgotPasswordView from "../view/ForgotPass.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'Auth',
      meta: {
        layout: 'Auth',
      },
      children: [
        { path: '', component: LoginView, name: 'Login' },
        { path: '/register', component: RegisterView, name: 'Register' },
        { path: '/forgot-password', component: ForgotPasswordView, name: 'ForgotPassword' },
      ]
    },
    {
      path: '/',
      name: 'Main',
      children: [
        { path: '', component: HomeView, name: 'Home' },
        { path: '/admin', component: AdminView, name: 'Admin', },
      ],
      meta: {
        layout: 'Main',
        requiresAuth: true
      }
    },
    { path: '/:pathMatch(.*)*', component: NotFoundView, name: 'NotFound', meta: { layout: 'NotFound' } },

  ]
})


router.beforeEach(async (to, from, next) => {
  const isAuthRequired = to.matched.some(record => record.meta.requiresAuth)
  const authStore = useAuthStore()

  // Якщо store ще завантажується, чекаємо завершення
  if (authStore.isLoading) {
    // Чекаємо завершення завантаження (максимум 2 секунди)
    const maxWait = 2000
    const startTime = Date.now()
    while (authStore.isLoading && (Date.now() - startTime) < maxWait) {
      await new Promise(resolve => setTimeout(resolve, 50))
    }
  }

  // Перевіряємо авторизацію через стор
  if (isAuthRequired && !authStore.isAuthenticated) {
    // Зберігаємо URL, на який хотів перейти користувач
    next({
      path: '/login',
      query: { redirect: to.fullPath }
    })
  } else if ((to.path === '/login' || to.path === '/register') && authStore.isAuthenticated) {
    // Якщо вже авторизований і намагається зайти на сторінку логіну
    const redirect = to.query.redirect as string
    next(redirect || '/')
  } else {
    next()
  }
})

export default router
