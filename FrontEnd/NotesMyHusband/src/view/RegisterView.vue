<template>
 <FormComponent :fields="fields" @submit="handleSubmit"/>
 <RouterLink to="/login">Back to login</RouterLink>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import FormComponent from '../components/FormComponent.vue'
import { useAuthStore } from '../stores/useAuthStore'

const authStore = useAuthStore()

const fields = {
  name: { type: 'text', required: true, placeholder: 'Name' },
  email: { type: 'email', required: true, placeholder: 'Email' },
  password: { type: 'password', required: true, placeholder: 'Password' },
  confirm_password: { type: 'password', required: true, placeholder: 'Confirm Password' },
}

const handleSubmit = (data: any) => {
  console.log(data.name, data.email, data.password)
}
</script>
