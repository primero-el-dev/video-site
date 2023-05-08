<template>
  <custom-form 
    :submitText="submitText" 
    :submitRoute="submitRoute"
    :formControls="formControls"
    :redirectRoute="redirectRoute"
    sendAs="multipart"
    :processBeforeSend="processBeforeSend"
    class="my-3"
  >
    <div slot="afterInputs">
      <tags-form :defaultSelectedTags="video.tags.map(t => t.name)" class="mb-3"></tags-form>

      <label class="form-label">{{ $t('form.video.video.label') }}</label>
      
      <div class="row">
        <div class="col-md-6 col-lg-4">
          <input type="file" class="form-control mb-3" name="video" accept="video/*" @change="loadVideo"/>

          <responsive-content class="mb-3 background-dark">
            <video slot="content" id="video" controls :src="(video.videoPath ? ('/videos/' + video.videoPath) : '')"></video>
          </responsive-content>
        </div>

        <div class="col-md-6 col-lg-4">
          <button type="button" class="btn btn-primary btn-block w-100 mb-3" @click="makeSnapshotOfCurrentVideo">
            {{ $t('form.video.momentAsPoster') }}
          </button>

          <div class="snapshot-container mb-3 background-dark">
            <canvas slot="content" class="snapshot" id="snapshot"></canvas>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <button type="button" class="btn btn-primary btn-block w-100 mb-3" @click="clearSetSampleOfVideo">
            {{ $t('form.video.momentAsSample') }}
          </button>

          <responsive-content class="mb-3 background-dark">
            <video slot="content" id="sampleVideo" muted autoplay loop playsinline="true"></video>
          </responsive-content>
        </div>
      </div>
    </div>
  </custom-form>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import { BackendRoute } from '../../router';
  import { dataUriToBlob } from '../../file';
  import { getElementByIdOrThrowError } from '../../common';
  import CustomForm from '../custom-form.vue';
  import ResponsiveContent from '../responsive-content.vue';
  import ResponsiveVideo from '../responsive-video.vue';
  import TagsForm from './tags-form.vue';
  import Video, { emptyVideo } from '../../entity/video';

  export default defineComponent({
    components: {
      CustomForm,
      ResponsiveContent,
      ResponsiveVideo,
      TagsForm,
    },
    props: {
      changedVideo: {
        type: Object as PropType<Video>,
        required: false,
        default: () => emptyVideo(),
      },
      submitRoute: {
        type: Object as PropType<BackendRoute>,
        required: true,
      },
      submitText: {
        type: String,
        required: true,
      },
      // redirectRoute: {
      //   type: Object as PropType<RouteConfig>,
      //   required: true,
      // },
      redirectRoute: {
        type: String,
        required: true,
      },
    },
    data() {
      return {
        video: this.changedVideo,
        snapshotExtension: 'png',
        snapshot: null as Blob | null,
        samplePlayInterval: null as number | null,
        formControls: [
          {
            name: 'name',
            label: this.$t('form.video.name.label'),
            type: 'text',
            placeholder: this.$t('form.video.name.placeholder'),
            initialValue: this.changedVideo.name,
          },
          {
            name: 'description',
            label: this.$t('form.video.description.label'),
            type: 'textarea',
            placeholder: this.$t('form.video.description.placeholder'),
            initialValue: this.changedVideo.description,
            inputAttributes: { rows: 3 },
          },
        ],
      }
    },
    computed: {
      videoElement: () => getElementByIdOrThrowError<HTMLVideoElement>('video'),
      snapshotCanvas: () => getElementByIdOrThrowError<HTMLCanvasElement>('snapshot'),
      sampleVideoElement: () => getElementByIdOrThrowError<HTMLVideoElement>('sampleVideo'),
      snapshotCanvasContext(): CanvasRenderingContext2D {
        let context = this.snapshotCanvas.getContext('2d');
        if (!context) {
          throw 'Cannot get snapshot canvas context.';
        }
        return context;
      },
    },
    watch: {
      changedVideo: {
        handler(newVideo: Video, oldVideo?: Video) {
          this.formControls[0].initialValue = newVideo.name;
          this.formControls[1].initialValue = newVideo.description;
          this.video = newVideo;
          if (newVideo.snapshotPath) {
            this.clearSnapshot(false);
            this.setSnapshotFromPath('/images/' + newVideo.snapshotPath);
          }
          if (newVideo.videoPath) {
            this.clearSetSample('/videos/' + newVideo.videoPath, newVideo.sampleStartTimestamp || 0);
          }
        },
        deep: true,
        immediate: true,
      },
    },
    methods: {
      loadVideo(event: Event): void {
        let input = event.target as HTMLInputElement;
        if (!input.files) {
          return;
        }

        let fileURL = (window.URL || window.webkitURL).createObjectURL(input.files[0]);
        this.videoElement.src = fileURL

        this.clearSnapshot();
        this.clearSample();
      },
      setSnapshot(image: CanvasImageSource, width?: number, height?: number): void {
        let toNumber = (value: any) => typeof value === 'number' ? value : value as number;
        width = toNumber(width ?? image.width);
        height = toNumber(height ?? image.height);
        this.snapshotCanvas.width = width;
        this.snapshotCanvas.height = height;
        this.snapshotCanvasContext.drawImage(image, 0, 0, width, height);

        this.snapshot = dataUriToBlob(this.snapshotCanvas.toDataURL('image/' + this.snapshotExtension));
      },
      setSnapshotFromPath(path: string): void {
        const image = new Image();
        image.src = path;
        image.onload = () => this.setSnapshot(image);
      },
      makeSnapshotOfCurrentVideo(): void {
        let video = this.videoElement;
        this.setSnapshot(video, video.videoWidth, video.videoHeight);
        this.video.snapshotTimestamp = video.currentTime || 0;
      },
      clearSnapshot(updateEntity: boolean = true, width?: number, height?: number): void {
        if (width) {
          this.snapshotCanvas.width = width;
        }
        if (height) {
          this.snapshotCanvas.height = height;
        }
        this.snapshotCanvasContext.clearRect(0, 0, this.snapshotCanvas.width, this.snapshotCanvas.height);

        this.snapshot = null;
        if (updateEntity) {
          this.video.snapshotPath = '';
          this.video.snapshotTimestamp = null;
        }
      },
      clearSetSampleOfVideo(): void {
        this.clearSample();
        this.setSample(this.videoElement.src, this.videoElement.currentTime || 0);
      },
      clearSetSample(source: string, sampleStartTimestamp: number): void {
        this.clearSample(false);
        this.setSample(source, sampleStartTimestamp);
      },
      setSample(source: string, sampleStartTimestamp: number): void {
        let sampleVideo = this.sampleVideoElement;
        sampleVideo.src = source;
        sampleVideo.currentTime = this.video.sampleStartTimestamp = sampleStartTimestamp;

        this.samplePlayInterval = window.setInterval(() => {
          sampleVideo.pause();
          sampleVideo.currentTime = this.video.sampleStartTimestamp!;
          sampleVideo.play();
        }, 3000);
      },
      clearSample(updateEntity: boolean = true): void {
        if (this.samplePlayInterval) {
          window.clearInterval(this.samplePlayInterval);
        }

        this.sampleVideoElement.src = '';
        if (updateEntity) {
          this.video.sampleStartTimestamp = null;
        }
      },
      processBeforeSend(formData: FormData): FormData {
        if (!this.snapshot) {
          alert(this.$t('form.video.mustMakeSnapshot'));
          throw this.$t('form.video.mustMakeSnapshot');
        }

        if (this.video.sampleStartTimestamp === null) {
          alert(this.$t('form.video.mustMakeSample'));
          throw this.$t('form.video.mustMakeSample');
        }

        formData.append('snapshot', this.snapshot, 'snapshot.' + this.snapshotExtension);
        formData.append('snapshotTimestamp', this.video.snapshotTimestamp!.toString());
        formData.append('sampleStartTimestamp', this.video.sampleStartTimestamp.toString());

        return formData;
      },
    },
  });
</script>