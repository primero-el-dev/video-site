<template>
  <div>
    <div class="d-flex flex-column-reverse flex-sm-row justify-content-between">
      <h1 class="mt-3 mt-sm-0">
        {{ $t('page.videoEditPage.mainHeader') }}
      </h1>
      <video-delete-form 
        style="margin:0 !important" 
        :submitButtonAttributes="{ style: 'margin:0 !important;' }" 
        :video="video"
      ></video-delete-form>
    </div>
    
    <video-form 
      :submitText="submitText" 
      :submitRoute="updateRoute" 
      redirectRoute="home"
      :changedVideo="video"
    ></video-form>

    <go-back-link></go-back-link>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { fetchBeforeRender, getBackendRoute } from '../../../router';
  import VideoDeleteForm from '../../forms/video-delete-form.vue';
  import VideoForm from '../../forms/video-form.vue';
  import GoBackLink from '../../go-back-link.vue';
  import Video, { emptyVideo } from '../../../entity/video';

  export default defineComponent({
    components: {
      VideoDeleteForm,
      VideoForm,
      GoBackLink,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.videoEditPage.title'),
      }
    },
    props: {
      id: {
        type: String,
        required: true,
      },
    },
    beforeRouteEnter: fetchBeforeRender<Video>('videoShow', 'video', 'VIDEO_UPDATE'),
    beforeRouteUpdate: fetchBeforeRender<Video>('videoShow', 'video', 'VIDEO_UPDATE'),
    data() {
      return {
        loaded: false,
        video: emptyVideo(),
        submitText: this.$t('page.videoEditPage.submit') as string,
        updateRoute: getBackendRoute('videoUpdate', { id: this.id }),
        deleteRoute: getBackendRoute('videoDelete', { id: this.id }),
      }
    },
  });
</script>