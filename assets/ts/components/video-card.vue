<template>
  <router-link 
    :to="{ name: 'videoShow', params: { id: video.id } }"  
    :title="video.name"
    class="video__card card p-2"
    @mouseenter="onMouseEnter"
    @mouseout="onMouseOut"
    v-bind="$attrs"
  >
    <div class="video__container background-dark">
      <video 
        preload="metadata" 
        muted
        playsinline="true"
        disablepictureinpicture
        :src="'/videos/' + video.videoPath" 
        :poster="'/images/' + video.snapshotPath"
        @loadedmetadata="onLoadedMetadata"
      ></video>
      <small class="video__duration"></small>
    </div>
    <div class="video__text text-dark ps-3">
      <h6 class="video__header d-block mb-0">{{ video.name ? video.name.toUpperCase() : '' }}</h6>
      <small class="d-block mb-2">{{ createdAt() }}</small>
      <user-media :user="video.owner" class="w-100"></user-media>
      <small>{{ video.description }}</small>
    </div>
  </router-link>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import Video from '../entity/video';
  import UserMedia from './user-media.vue';

  export default defineComponent({
    components: {
      UserMedia,
    },
    props: {
      video: {
        type: Object as PropType<Video>,
        required: true,
      },
    },
    data() {
      return {
        hoverTimeoutId: null as number | null,
        animationTimeoutId: null as number | null,
      }
    },
    methods: {
      addTrailingZero(v: number): string {
        return (v < 10) ? `0${v}` : (v as unknown as string);
      },
      onLoadedMetadata(e: Event): void {
        let video = e.target as HTMLVideoElement;
        const addTrailingZero = (v: number) => v < 10 ? `0${v}` : v;
        let time = Math.round(video.duration / 3600)
          + ':' + addTrailingZero(Math.round((video.duration / 60) % 60))
          + ':' + addTrailingZero(Math.round(video.duration % 60));
        video.closest('.video__card')!.querySelector('.video__duration')!.innerHTML = time;
      },
      onMouseEnter(e: Event): void {
        let video = (e.target as HTMLElement).querySelector('video') as HTMLVideoElement;

        this.hoverTimeoutId = window.setTimeout(() => {
          video.play();
          video.currentTime = this.video.sampleStartTimestamp!;
          this.animationTimeoutId = window.setTimeout(() => {
            video.pause();
            video.currentTime = this.video.sampleStartTimestamp!;
          }, 4000);
        }, 1000);
      },
      onMouseOut(e: Event): void {
        let video = (e.target as HTMLElement).querySelector('video') as HTMLVideoElement;

        if (this.hoverTimeoutId) {
          window.clearTimeout(this.hoverTimeoutId);
        }
        if (this.animationTimeoutId) {
          window.clearTimeout(this.animationTimeoutId);
        }
        video.pause();
        video.currentTime = this.video.sampleStartTimestamp!;
      },
      createdAt(): string {
        let date = new Date(this.video.createdAt!);
        
        return date.getFullYear() 
          + '-' + this.addTrailingZero(date.getMonth() + 1) 
          + '-' + this.addTrailingZero(date.getDate());
      },
    },
  });
</script>

<style scoped>
  .video__card {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    width: 100%;
    text-decoration: none !important;
    padding: .5rem;
  }

  .video__container {
    display: inline-block;
    width: 50%;
    padding-top: 32%;
    height: 0px;
    position: relative;
    flex-basis: 50%;
  }

  .video__duration {
    position: absolute;
    bottom: 0;
    right: 0;
    background-color: black;
    padding: .25rem .5rem;
    color: #eee;
  }

  .video {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
  }

  .video__text {
    display: inline-block;
    flex-basis: 50%;
  }
</style>