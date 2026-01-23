

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import FormComponent from '../components/FormComponent.vue'

const fields = {
  email: { type: 'email', required: true, placeholder: 'Email' }
}

const handleSubmit = (data: any) => {
  console.log(data.email)
}

</script>

<template>
  <div class="auth-layout">
  <h1>Forgot Password</h1>
  <FormComponent :fields="fields" @submit="handleSubmit"/>
  <RouterLink to="/login">Back to login</RouterLink>
  </div>
</template>

<style scoped></style>
