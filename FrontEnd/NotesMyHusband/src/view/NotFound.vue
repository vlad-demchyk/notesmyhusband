<script setup lang="ts"></script>

<template>
  <div class="not-found">
    <h1>404</h1>
    <p>Ой! Такої сторінки не існує.</p>
    <router-link to="/">Повернутися на головну</router-link>
  </div>
</template>

<style scoped>
.not-found { text-align: center; padding: 50px; }
h1 { font-size: 5rem; color: #42b883; }
</style>
