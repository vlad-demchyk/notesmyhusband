<script setup lang="ts">


// 1. Оголошуємо еміти (в TS краще через типізацію)
const props = defineProps<{
  isSending?: boolean
  fields: Record<string, { type: string, required: boolean, placeholder: string }>
}>()
const emit = defineEmits<{
  (e: 'submit', data: object): void
}>()

// 2. Оголошуємо дані форми

// 3. Функція обробки
const handleSubmit = (e: Event) => {
  const formData = new FormData(e.target as HTMLFormElement)
  emit('submit', formData)
}
</script>

<template>
  <form @submit.prevent="handleSubmit">

    <template v-if="props.fields">
      <template v-for="(field, index) in props.fields" :key="index">
        <div>
          <label :for="field.type">{{ field.type }}</label>
          <input :name="field.type" :type="field.type" :id="field.type" />
        </div>

      </template>
    </template>
    <template v-else>
      <slot >
        <div>
          <label for="email">Email</label>
          <input name="email" type="email" id="email" />
        </div>
        <div>
          <label for="password">Password</label>
          <input name="password" type="password" id="password" />
        </div>
      </slot>
    </template>
    <button type="submit" :disabled="props.isSending">Submit</button>
    <template v-if="props?.isSending">
      <div>
        <p>Sending...</p>
      </div>
    </template>

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
</style>
