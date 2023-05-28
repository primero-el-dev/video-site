<template>
  <div>
    <h1>{{ $t('page.profileIndexPage.mainHeader') }}</h1>
    
    <go-back-link></go-back-link>

    <router-link :to="{ name: 'videoCreate' }">
      {{ $t('page.profileIndexPage.createVideo') }}
    </router-link>

    <router-link :to="{ name: 'profileResetPassword' }">
      {{ $t('page.profileIndexPage.resetPassword') }}
    </router-link>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import GoBackLink from '../../go-back-link.vue';
  import { useAppStore } from '../../../state';

  export default defineComponent({
    components: {
      GoBackLink,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.profileIndexPage.title'),
      }
    },
    data() {
      return {
        appStore: useAppStore(),
      }
    },
  });
</script>