<template>
  <div style="width:100%;">
    <div class="video-container">
      <video controls style="display:block;" class="video"></video>
    </div>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';

  export default defineComponent({

  });
</script>

<style scoped>
  .video-container {
    width: 100%;
    padding-top: 56.25%;
    height: 0px;
    position: relative;
  }

  .video {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
  }
</style>