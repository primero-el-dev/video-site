<template>
  <custom-form
    v-bind="$attrs"
    :submitText="finalSubmitText"
    :submitRoute="deleteRoute" 
    redirectRoute="home"
    :submitGuard="submitGuard"
    class="m-0 d-inline-block"
    submitButtonColor="danger"
    :submitButtonClasses="(mini ? ' btn-sm' : '')"
    :submitButtonAttributes="{ style: 'margin: 0 !important;' }"
  ></custom-form>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import { getBackendRoute } from '../../router';
  import CustomForm from '../custom-form.vue';
  import Video from '../../entity/video';

  export default defineComponent({
    components: {
      CustomForm,
    },
    props: {
      video: {
        type: Object as PropType<Video>,
        required: true,
      },
      submitText: {
        type: String,
        required: false,
      },
      mini: {
        type: Boolean,
        required: false,
        default: false,
      }
    },
    data() {
      return {
        selectedVideo: this.video,
        finalSubmitText: this.submitText || this.$t('form.videoDelete.submit') as string,
        deleteRoute: getBackendRoute('videoDelete', { id: this.video.id! }),
      }
    },
    watch: {
      video: {
        handler(newVideo: Video, oldVideo?: Video) {
          this.selectedVideo = newVideo;
          this.deleteRoute = getBackendRoute('videoDelete', { id: this.selectedVideo.id! })
        },
        deep: true,
      }
    },
    methods: {
      submitGuard(formData: FormData): boolean {
        return confirm(this.$t('form.videoDelete.confirm', { videoName: this.selectedVideo.name! }) as string)
      },
    },
  });
</script>