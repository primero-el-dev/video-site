<template>
    <div>
        <h1>About</h1>
    </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { appName } from '../../common';

  export default defineComponent({
    data() {
      return {
        appName: appName,
      }
    }
  });
</script>