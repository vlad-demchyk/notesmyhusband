<script setup lang="ts">
import { onMounted, ref } from 'vue'
import './css/main.css'
import { useRoute } from 'vue-router'
import { computed } from 'vue'
import AuthView from './layout/AuthView.vue'
import MainView from './layout/MainView.vue'
import NotFoundView from './view/NotFound.vue'
import FooterView from './view/FooterView.vue'
import { useAuthStore } from './stores/useAuthStore'
const route = useRoute()
const authStore = useAuthStore()
const layouts = {
  Auth: AuthView,
  Main: MainView,
  NotFound: NotFoundView
}
const currentLayout = computed(() => layouts[route.meta.layout] )
const isDark = ref(localStorage.getItem('theme') === 'dark')

const toggleTheme = () => {
  isDark.value = !isDark.value
  const theme = isDark.value ? 'dark' : 'light'
  localStorage.setItem('theme', theme)
  document.documentElement.setAttribute('data-theme', theme)
}

const setDefaultTheme = () => {
  localStorage.setItem('theme', 'auto')
  document.documentElement.setAttribute('data-theme', 'auto')
}
onMounted(() => {
  document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light')
})

</script>

<template>
  <button @click="toggleTheme">Toggle Theme</button>
  <button @click="setDefaultTheme">Set Default Theme</button>
  <component :is="currentLayout || ''">
    <RouterView />
  </component>
  <FooterView />
</template>

<style scoped></style>
