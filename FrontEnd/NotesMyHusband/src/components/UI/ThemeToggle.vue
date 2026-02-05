<script setup lang="ts">
const props = defineProps<{
  theme: string | null
}>()
const emit = defineEmits<{
  (e: 'themeChanged', theme: string): void
}>()

function toChange(theme: string) {
  emit('themeChanged', theme)
}
</script>

<template>
  <div class="wrapper">
    <div>
      <button :class="{ active: theme === 'light' }" @click="toChange('light')">
        <img src="../../assets/light-mode-svgrepo-com (1).svg" alt="" />
      </button>
      <button :class="{ active: theme === 'dark' }" @click="toChange('dark')">
        <img src="../../assets/night-svgrepo-com.svg" alt="" />
      </button>
      <button :class="{ active: theme === 'auto' }" @click="toChange('auto')">
        <img src="../../assets/settings-svgrepo-com.svg" alt="" />
      </button>
    </div>
  </div>
</template>

<style scoped>
[data-theme='light'] {
  img {
    /* filter: invert(1); */
  }

  .wrapper div {
    background-image: url('../../assets/dayDraft.avif');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;


  }
}

[data-theme='dark'] {
  img {
    filter: invert(1);
  }
}

.wrapper {
  border: solid 2px var(--color-primary-light);
  padding: 3px;
  width: fit-content;
  border-radius: 1.5rem;

  div {
    cursor: pointer;
    height: 50px;
    min-width: 150px;
    box-sizing: border-box;
    border-radius: 1.2rem;

    /* background-color: var(--color-primary-light); */
    background: transparent;
    background-image: url('../../assets/nightDraft.jpg');


    padding: 5px 10px;
    display: flex;

    justify-content: center;
    align-items: center;
    align-content: center;

    button {
      background: none;
      border: none;
      height: 100%;
      width: fit-content;

      img {
        height: 100%;
        width: auto;
      }

      transition: all 0.3s ease;

      &:hover,
      &:active {
        transform: scale(1.1);
      }
    }

    button.active {}
  }
}
</style>
