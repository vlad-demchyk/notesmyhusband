<script setup lang="ts"></script>

<template>
  <h1>You did it!</h1>
    <h1>Hello App!</h1>
  <p><strong>Current route path:</strong> {{ $route.fullPath }}</p>
  <nav>
    <RouterLink to="/">Go to Home</RouterLink>
    <RouterLink to="/admin">Go to admin</RouterLink>
    <RouterLink to="/login">Go to login</RouterLink>
  </nav>
  <p>
    Visit <a href="https://vuejs.org/" target="_blank" rel="noopener">vuejs.org</a> to read the
    documentation
  </p>
</template>

<style scoped></style>
