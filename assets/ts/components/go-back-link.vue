<template>
  <a type="button" class="btn btn-primary" @click="$router.go(-1)" href="#">
    {{ $t('common.goBack') }}
  </a>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';

  export default defineComponent({
    
  });
</script>