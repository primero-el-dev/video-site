<template>
  <div class="row p-sm-2">
    <div class="col-12 col-lg-8 p-0 p-sm-2">
      <responsive-content class="mb-2 background-dark">
        <video 
          slot="content" 
          id="video" 
          controls 
          :src="'/videos/' + video.videoPath" 
          :poster="'/images/' + video.snapshotPath"
        ></video>
      </responsive-content>

      <h1 class="my-3 fs-5">
        {{ video.name ? video.name.toUpperCase() : '' }}
      </h1>

      <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
        <user-media :user="video.owner" class="me-2 mb-4"></user-media>

        <div class="mb-4">
          <div class="btn-group btn-group-sm d-inline-flex me-1" role="group" aria-label="">
            <input type="checkbox" class="btn-check" id="btncheck" autocomplete="off">
            <label class="btn btn-outline-primary" for="btncheck">{{ $t('common.subscribe') }}</label>
          </div>

          <rating-action-panel 
            :entity="video"
            :rateRoute="getBackendRoute('videoRate', { id: video.id })"
            :reportRoute="getBackendRoute('videoReport', { id: video.id })"
            :reportDeleteRoute="getBackendRoute('videoReportDelete', { id: video.id })"
            @entityReported="onVideoReported"
            @entityUnreported="onVideoUnreported"
          >
            <router-link 
              slot="additionalOptions"
              v-if="video.permissions ? video.permissions.includes('VIDEO_UPDATE') : false" 
              :to="{ name: 'videoEdit', params: { id: video.id } }"
              class="dropdown-item" 
            >
              {{ t('common.edit') }}
            </router-link>
            
            <li 
              slot="additionalOptions"
              v-if="video.permissions ? video.permissions.includes('VIDEO_DELETE') : false" 
              @click="deleteVideo"
              class="dropdown-item" 
            >
              {{ t('common.delete') }}
            </li>
          </rating-action-panel>
        </div>
      </div>
      
      <div class="accordion accordion-flush mb-4" id="accordionAdditionalInfo">
        <div class="accordion-item">
          <button 
            type="button" 
            class="accordion-button accordion-header collapsed" 
            id="accordionAdditionalInfoButton" 
            data-bs-toggle="collapse" 
            data-bs-target="#accordionAdditionalInfoContent" 
            aria-expanded="false" 
            aria-controls="accordionAdditionalInfoContent"
          >
            {{ t('page.videoShowPage.videoAdditionalInfo').toUpperCase() }}
          </button>
          
          <div 
            id="accordionAdditionalInfoContent" 
            class="accordion-collapse collapse my-3" 
            aria-labelledby="accordionAdditionalInfoButton" 
            data-bs-parent="#accordionAdditionalInfo"
          >
            <h5 class="mt-4 mb-1">{{ t('common.generalInfo').toUpperCase() }}</h5>
            <div class="mb-4">
              <span class="d-block">{{ t('common.views') }}: {{ video.viewsCount }}</span>
              <span class="d-block">{{ t('common.added') }}: {{ t(relativeDateTime(video.createdAt)) }}</span>
            </div>

            <h5 class="mb-1">{{ t('common.tags').toUpperCase() }}</h5>
            <div class="mb-4">
              <small v-for="tag in video.tags" :key="tag.id" class="tag badge bg-primary me-2">
                #{{ tag.name }}
              </small>
            </div>
            
            <h5 class="mb-1">{{ t('common.description').toUpperCase() }}</h5>
            <div class="mb-4">
              {{ video.description }}
            </div>
          </div>
        </div>
      </div>
      
      <div class="accordion accordion-flush mb-4" id="accordionCcomments">
        <div class="accordion-item">
          <button 
            type="button" 
            class="accordion-button accordion-header collapsed" 
            id="accordionCommentsButton" 
            data-bs-toggle="collapse" 
            data-bs-target="#accordionCommentsContent" 
            aria-expanded="false" 
            aria-controls="accordionCommentsContent"
          >
            {{ t('page.videoShowPage.videoComments').toUpperCase() }}
          </button>
          
          <div 
            id="accordionCommentsContent" 
            class="accordion-collapse collapse my-3" 
            aria-labelledby="accordionCommentsButton" 
            data-bs-parent="#accordionComments"
          >
            <comments-section 
              :videoId="video.id" 
              :comments="comments"
              :toggleCommentFormText="toggleCommentFormText"
              :createCommentText="createCommentText"
              :loadCommentsText="loadCommentsText"
            ></comments-section>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4 p-0 p-sm-2">
      <video-list 
        :videosPerLoad="1" 
        :videoContainerAttributes="{ class: 'mb-3' }"
      ></video-list>
    </div>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import router, { fetchBeforeRender, getBackendRoute } from '../../../router';
  import VideoDeleteForm from '../../forms/video-delete-form.vue';
  import ResponsiveVideo from '../../responsive-video.vue';
  import ResponsiveContent from '../../responsive-content.vue';
  import GoBackLink from '../../go-back-link.vue';
  import UserMedia from '../../user-media.vue';
  import VideoList from '../../video-list.vue';
  import RatingForm from '../../forms/rating-form.vue';
  import CommentForm from '../../forms/comment-form.vue';
  import CommentComponent from '../../comment-component.vue';
  import CommentsSection from '../../comments-section.vue';
  import RatingActionPanel from '../../rating-action-panel.vue';
  import Video, { emptyVideo } from '../../../entity/video';
  import Comment, { emptyComment } from '../../../entity/comment';
  import { getResponseError, requestApi } from '../../../api';
  import { Stringable, relativeDateTime } from '../../../common';
  import { useAlertStore } from '../../../state';

  export default defineComponent({
    components: {
      VideoDeleteForm,
      ResponsiveVideo,
      ResponsiveContent,
      GoBackLink,
      UserMedia,
      RatingForm,
      CommentForm,
      CommentComponent,
      CommentsSection,
      RatingActionPanel,
      VideoList,
    },
    metaInfo() {
      return {
        title: (this as any).video.name,
      }
    },
    props: {
      id: {
        type: String,
        required: true,
      },
    },
    beforeRouteEnter: fetchBeforeRender<Video>('videoShow', 'video'),
    beforeRouteUpdate: fetchBeforeRender<Video>('videoShow', 'video'),
    data() {
      return {
        isLoading: false,
        video: emptyVideo(),
        comments: [] as Comment[],
        getBackendRoute,
        emptyComment,
        toggleCommentFormText: this.$t('common.toggleCommentForm') as string,
        createCommentText: this.$t('common.submitComment') as string,
        loadCommentsText: this.$t('common.loadComments') as string,
        relativeDateTime,
      }
    },
    methods: {
      t(stringable: Stringable, params?: any): string {
        if (typeof stringable === 'string') {
          return this.$t(stringable, params) as string;
        } else {
          let [key, params] = stringable;

          return this.$t(key, params) as string;
        }
      },
      onVideoReported(response: any): void {
        this.video.currentUserActions = this.video.currentUserActions || [];
        this.video.currentUserActions.push({ action: response.data.data, additionalInfo: {} });
      },
      onVideoUnreported(): void {
        this.video.currentUserActions = (this.video.currentUserActions || [])
          .filter(a => a.action !== 'report');
      },
      deleteVideo(): void {
        if (confirm(this.$t('form.videoDelete.confirm', { videoName: this.video.name! }) as string)) {
          requestApi(getBackendRoute('videoDelete', { id: this.video.id }))
            .then(response => {
              useAlertStore().addSuccess(response.data.message);
              router.push({ name: 'home' })
            })
            .catch(e => {
              useAlertStore().addDanger(getResponseError(e));
            });
        }
      },
    },
  });
</script>