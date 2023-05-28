<template>
  <router-link 
    :to="{ name: 'profileShow', params: { id: (user ? user.id : 0) } }" 
    class="d-inline-flex align-items-center text-dark pe-auto" 
    style="text-decoration:none !important;"
  >
    <div class="flex-shrink-0">
      <img :src="'/images/' + ((user && user.imagePath) ? user.imagePath : 'no-image.png')" class="img-fluid" width="40">
    </div>
    <small class="flex-grow-1 ms-2">
      {{ user ? user.nick : '' }}
    </small>
  </router-link>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import User from '../entity/user';

  export default defineComponent({
    props: {
      user: {
        type: Object as PropType<User | null>,
        reqiured: true,
      },
    },
  });
</script>