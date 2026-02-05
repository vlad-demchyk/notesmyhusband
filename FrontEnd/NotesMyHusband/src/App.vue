<script setup lang="ts">
import { onBeforeMount, ref } from 'vue'
import './css/main.css'
import { useRoute } from 'vue-router'
import { computed } from 'vue'
import AuthView from './layout/AuthView.vue'
import MainView from './layout/MainView.vue'
import NotFoundView from './view/NotFound.vue'
import FooterView from './view/FooterView.vue'
import ThemeToggle from './components/UI/ThemeToggle.vue'
import HeaderView from './view/HeaderView.vue'
const route = useRoute()
const layouts = {
  Auth: AuthView,
  Main: MainView,
  NotFound: NotFoundView
}
const currentLayout = computed(() => layouts[route.meta.layout])
const themeIsActive = ref(localStorage.getItem('theme') || 'light')

const toggleTheme = (theme: string) => {
  themeIsActive.value = theme
  localStorage.setItem('theme', theme)
  document.documentElement.setAttribute('data-theme', theme)
}

onBeforeMount(() => {
  document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light')
})

</script>

<template>
  <!-- <button @click="toggleTheme()">Toggle Theme</button> -->
  <HeaderView>
    <h1>Notes My Husband</h1>
    <ThemeToggle :theme="themeIsActive" @themeChanged="toggleTheme" />
  </HeaderView>
  <component :is="currentLayout" v-if="currentLayout">
    <RouterView />
  </component>

  <!-- <RouterView v-else /> -->
  <FooterView />
</template>

<style scoped></style>
