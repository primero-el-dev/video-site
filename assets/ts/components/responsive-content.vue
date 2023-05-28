<template>
  <div class="w-100">
    <div class="responsive-container" v-bind="$attrs">
      <slot name="content" class="responsive-content"></slot>
    </div>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';

  export default defineComponent({

  });
</script>

<style scoped>
  .responsive-container {
    width: 100%;
    height: 0px;
    padding-top: 56.25%;
    position: relative;
    text-align: center;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
  }

  .responsive-content {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%,-50%);
  }
</style>