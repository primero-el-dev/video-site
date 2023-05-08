<template>
  <div>
    <h1>{{ $t('page.videoCreatePage.mainHeader') }}</h1>
    
    <video-form 
      :submitText="submitText" 
      :submitRoute="submitRoute" 
      redirectRoute="home"
    ></video-form>

    <go-back-link></go-back-link>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { backendRoutes } from '../../../router';
  import VideoForm from '../../forms/video-form.vue';
  import GoBackLink from '../../go-back-link.vue';

  export default defineComponent({
    components: {
      VideoForm,
      GoBackLink,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.videoCreatePage.title'),
      }
    },
    data() {
      return {
        submitText: this.$t('page.videoCreatePage.submit') as string,
        submitRoute: backendRoutes['videoCreate'],
      }
    },
  });
</script>