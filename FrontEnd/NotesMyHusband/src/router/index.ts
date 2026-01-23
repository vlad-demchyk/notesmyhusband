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


router.beforeEach((to, from) => {
  const isAuthRequired = to.matched.some(record => record.meta.requiresAuth)

  // Отримуємо стор (він вже ініціалізований в main.ts)
  const authStore = useAuthStore()

  // Перевіряємо авторизацію через стор
  if (isAuthRequired && !authStore.isAuthenticated) {
    return '/login'
  } else if (to.path === '/login' && authStore.isAuthenticated) {
    return '/'
  }
})

export default router
