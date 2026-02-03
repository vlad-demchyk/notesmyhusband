<script setup lang="ts">
import { ref } from 'vue'

// 1. Оголошуємо еміти (в TS краще через типізацію)
const props = defineProps<{
  isSending?: boolean
  fields?: Record<string, { type: string, name: string, required: boolean, placeholder: string }>
  errorMessage?: string
  successMessage?: string
}>()
const emit = defineEmits<{
  (e: 'submit', data: object): void
}>()

const id = ref(Math.random().toString(36).substring(2, 15))

// 2. Оголошуємо дані форми

// 3. Функція обробки
const handleSubmit = (e: Event) => {
  const formData = new FormData(e.target as HTMLFormElement)
  const data = Object.fromEntries(formData.entries())
  console.log(data)
  emit('submit', data)
}

</script>

<template>
  <form @submit.prevent="handleSubmit">

    <template v-if="props.fields">
      <template v-for="(field, index) in props.fields" :key="index">
        <div>
          <label :for="`${field.name}-${id}`">{{ field.name }}</label>
          <input :name="field.name" :type="field.type" :id="`${field.name}-${id}`" />
        </div>

      </template>
    </template>
    <template v-else>
      <slot >
        <div>
          <label :for="`login-${id}`">Login</label>
          <input name="login" type="text" :id="`login-${id}`" />
        </div>
        <div>
          <label :for="`password-${id}`">Password</label>
          <input name="password" type="password" :id="`password-${id}`" />
        </div>
      </slot>
    </template>
    <button type="submit" :disabled="props.isSending">Submit</button>
    <template v-if="props?.isSending">
      <div>
        <p>Sending...</p>
      </div>
    </template>
    <div v-if="props.successMessage" class="success-message">
      {{ props.successMessage }}
    </div>
    <div v-if="props.errorMessage" class="error-message">
      {{ props.errorMessage }}
    </div>
</form>

</template>

<style scoped>
form {
  display: flex;
  gap: 1rem;
  flex-direction: column;
  align-items: center;
  justify-content: center;

  border: 2px solid var(--color-primary-light);
  border-radius: 10px;
  padding: 2rem;
  margin-bottom: 1rem;

  div {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
  }

  input {
    width: 100%;
    padding: 10px 20px;
    box-sizing: border-box;
    border-radius: 5px;
    border: 1px solid #f0f0f0;
    background-color: #f0f0f0;
  }

  button {
    text-align: center;
    display: block;
    text-decoration: none;
    color: #000;
    font-size: 16px;
    width: 8rem;
    font-weight: 500;
    padding: 10px 20px;
    box-sizing: border-box;
    border-radius: 5px;
    background-color: #f0f0f0;
  }

}

.success-message {
  color: #008000;
  background-color: #e6ffe6;
  padding: 10px;
  border-radius: 5px;
  margin-top: 10px;
  text-align: center;
  font-size: 14px;
}

.error-message {
  color: #d32f2f;
  background-color: #ffebee;
  padding: 10px;
  border-radius: 5px;
  margin-top: 10px;
  text-align: center;
  font-size: 14px;
}
</style>
