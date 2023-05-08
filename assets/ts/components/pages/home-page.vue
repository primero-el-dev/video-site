<template>
  <div>
    <h1>{{ $t('page.homePage.mainHeader', { appName }) }}</h1>
    <p>{{ $t('page.homePage.secondaryHeader') }}</p>
    
    <div class="row">
      <div v-for="video of videos" :key="video.id || 0" class="col-12 col-md-6 col-lg-4 p-2">
        <video-card :video="video"></video-card>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import axios from 'axios';
  import { appName } from '../../common';
  import { requestApi } from '../../api';
  import { backendRoutes } from '../../router';
  import Video from '../../entity/video';
  import VideoCard from '../video-card.vue';
  
  export default defineComponent({
    components: {
      VideoCard,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.homePage.title'),
      }
    },
    created() {
      this.isLoading = true;
      requestApi(backendRoutes['videoIndex'])
        .then(response => {
          this.videos = response.data.data as Video[];
        })
        .catch(e => {
          this.videoLoadError = axios.isAxiosError(e) 
            ? e!.response!.data!.error 
            : (e as Error).message;
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    data() {
      return {
        appName,
        videos: [] as Video[],
        isLoading: true,
        videoLoadError: null as string | null,
      }
    }
  });
</script>