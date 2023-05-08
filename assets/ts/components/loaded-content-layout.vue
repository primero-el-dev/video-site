<template>
  <div>
    <slot name="content" @loaded="loaded = true"></slot>
    <loader-animation v-if="!loaded"></loader-animation>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import LoaderAnimation from './loader-animation.vue';
  
  export default defineComponent({
    components: {
      LoaderAnimation,
    },
    data() {
      return {
        loaded: false,
      }
    },
  });
</script>