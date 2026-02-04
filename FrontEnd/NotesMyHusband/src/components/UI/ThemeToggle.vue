<script setup lang="ts">

</script>

<template>
  <div class="wrapper">
    <div>
      <button @click="$emit('themeChanged', 'light')">
        <img src="../../assets/light-mode-svgrepo-com (1).svg" alt="">
      </button>
      <button @click="$emit('themeChanged', 'dark')">
        <img src="../../assets/night-svgrepo-com.svg" alt="">
      </button>
      <button @click="$emit('themeChanged', 'auto')">
        <img src="../../assets/settings-svgrepo-com.svg" alt="">
      </button>
    </div>
  </div>
</template>

<style scoped>
[data-theme="light"] img {
  filter: invert(1);
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

    background-color: var(--color-primary-light);
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


  }
}
</style>
