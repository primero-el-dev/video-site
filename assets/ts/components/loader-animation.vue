<template>
  <div class="centered">
    <span class="animation-square bg-primary"></span>
    <b class="mt-2 text-dark">Loading...</b>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';

  export default defineComponent({

  });
</script>

<style>
  .animation-square {
    margin: 1rem auto;
    display: block;
    width: 2.5rem;
    height: 2.5rem;
    animation: rotation 2s infinite linear;
  }

  @keyframes rotation {
    100% {
      transform: rotateZ(360deg) rotateX(360deg);
    }
  }
</style>