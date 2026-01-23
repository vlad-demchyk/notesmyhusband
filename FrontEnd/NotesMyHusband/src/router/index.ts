import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../view/HomeView.vue'
import AdminView from '@/view/AdminView.vue'
import LoginView from '@/view/LoginView.vue'
import NotFound from '@/view/notFound.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', component: HomeView },
    { path: '/admin', component: AdminView },
    { path: '/login', component: LoginView },
    {
      path: '/:pathMatch(.*)*',
      name: 'NotFound',
      component: NotFound,
    },
  ],
})

export default router
