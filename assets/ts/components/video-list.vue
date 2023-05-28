<template>
  <div v-bind="$attrs">
    <div v-for="video of videos" :key="video.id" v-bind="videoContainerAttributes">
      <video-card :video="video"></video-card>
    </div>
    <button 
      v-if="showLoadMoreButton" 
      @click="loadVideos" 
      class="btn btn-primary btn-block w-100"
    >
      {{ $t('common.loadMore') }}
    </button>
  </div>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import { requestApi, getResponseError } from '../api';
  import { getBackendRoute, RouteParams } from '../router';
  import Video from '../entity/video';
  import VideoCard from './video-card.vue';
  
  export default defineComponent({
    components: {
      VideoCard,
    },
    props: {
      videosPerLoad: {
        type: Number,
        required: false,
        default: 10,
      },
      endpointQueryParams: {
        type: Object as PropType<RouteParams>,
        required: false,
        default: () => ({ order: 'desc', 'order-by': 'created-at' }),
      },
      videoContainerAttributes: {
        type: Object as PropType<{ [key: string]: string | number }>,
        required: false,
        default: () => ({}),
      },
    },
    created() {
      this.loadVideos();
    },
    data() {
      return {
        videos: [] as Video[],
        isLoading: true,
        videoLoadError: null as string | null,
        videosOffset: 0,
        showLoadMoreButton: true,
      }
    },
    methods: {
      loadVideos(): void {
        this.isLoading = true;
        requestApi(getBackendRoute('videoIndex', {
          limit: this.videosPerLoad, 
          offset: this.videosOffset,
          ...this.endpointQueryParams,
        }))
          .then(response => {
            let loadedVideos = response.data.data as Video[];
            this.videos = this.videos.concat(loadedVideos);
            this.videosOffset += this.videosPerLoad;
            if (loadedVideos.length < this.videosPerLoad) {
              this.showLoadMoreButton = false;
            }
          })
          .catch(e => {
            this.videoLoadError = getResponseError(e);
          })
          .finally(() => {
            this.isLoading = false;
          });
      },
    },
  });
</script>