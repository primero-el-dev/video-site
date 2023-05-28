<template>
  <div>
    <h1>{{ $t('page.homePage.mainHeader', { appName }) }}</h1>
    <p>{{ $t('page.homePage.secondaryHeader') }}</p>
    
    <video-list 
      class="row px-md-1 g-1"
      :videosPerLoad="1" 
      :videoContainerAttributes="{ class: 'col-12 col-md-6 col-lg-4 px-0 px-md-2 mb-3' }"
    ></video-list>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { appName } from '../../common';
  import VideoCard from '../video-card.vue';
  import VideoList from '../video-list.vue';
  
  export default defineComponent({
    components: {
      VideoCard,
      VideoList,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.homePage.title'),
      }
    },
    data() {
      return {
        appName,
      }
    },
  });
</script>