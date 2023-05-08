









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

      <div class="my-3 d-block">
        <h1 class="d-inline-block fs-5">
          {{ video.name ? video.name.toUpperCase() : '' }}
        </h1>

        <div class="d-inline-block ms-auto">
          <router-link :to="{ name: 'videoEdit', params: { id: video.id } }" class="btn btn-sm btn-primary ms-1">
            {{ $t('common.edit') }}
          </router-link>
          <video-delete-form :video="video" :mini="true" class="ms-1"></video-delete-form>
        </div>
      </div>

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
          >
            <router-link 
              slot="additionalOptions"
              v-if="video.permissions.includes('VIDEO_UPDATE')" 
              :to="{ name: 'videoEdit', params: { id: video.id } }"
              class="dropdown-item" 
            >
              {{ t('common.edit') }}
            </router-link>
            
            <li 
              slot="additionalOptions"
              v-if="video.permissions.includes('VIDEO_DELETE')" 
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
            {{ $t('page.videoShowPage.videoAdditionalInfo').toUpperCase() }}
          </button>
          
          <div 
            id="accordionAdditionalInfoContent" 
            class="accordion-collapse collapse my-3" 
            aria-labelledby="accordionAdditionalInfoButton" 
            data-bs-parent="#accordionAdditionalInfo"
          >
            <h5 class="mt-4 mb-1">{{ $t('common.tags').toUpperCase() }}</h5>
            <div class="mb-4">
              <small v-for="tag in video.tags" :key="tag.id" class="tag badge bg-primary me-2">
                #{{ tag.name }}
              </small>
            </div>
            
            <h5 class="mb-1">{{ $t('common.description').toUpperCase() }}</h5>
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
            {{ $t('page.videoShowPage.videoComments').toUpperCase() }}
          </button>
          
          <div 
            id="accordionCommentsContent" 
            class="accordion-collapse collapse my-3" 
            aria-labelledby="accordionCommentsButton" 
            data-bs-parent="#accordionComments"
          >
            <comments-section 
              :videoId="video.id || ''" 
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
      <div v-for="video of recommendedVideos" :key="video.id || 0" class="mb-3">
        <video-card :video="video"></video-card>
      </div>
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
  import VideoCard from '../../video-card.vue';
  import RatingForm from '../../forms/rating-form.vue';
  import CommentForm from '../../forms/comment-form.vue';
  import CommentComponent from '../../comment-component.vue';
  import CommentsSection from '../../comments-section.vue';
  import RatingActionPanel from '../../rating-action-panel.vue';
  import Video, { emptyVideo } from '../../../entity/video';
  import Comment, { emptyComment } from '../../../entity/comment';
  import { SuccessfulApiResponse, getResponseError, requestApi } from '../../../api';
  import { useAlertStore } from '../../../state';
  import { Modal } from 'bootstrap';
  import { addUserAction, UserAction, deleteUserAction } from '../../../entity/has-user-actions';

  export default defineComponent({
    components: {
      VideoDeleteForm,
      ResponsiveVideo,
      ResponsiveContent,
      GoBackLink,
      VideoCard,
      UserMedia,
      RatingForm,
      CommentForm,
      CommentComponent,
      CommentsSection,
      RatingActionPanel,
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
    created() {
      this.isLoading = true;
      requestApi(getBackendRoute('videoIndex'))
        .then(response => {
          this.recommendedVideos = response.data.data as Video[];
        })
        .catch(e => {
          useAlertStore().addDanger(getResponseError(e));
        })
        .finally(() => {
          this.isLoading = false;
        });

      requestApi(getBackendRoute('commentIndex', { video: this.id || '', parent: 0, limit: 3, order: 'desc' }))
        .then(response => {
          this.comments = response.data.data as Comment[];
        })
        .catch(e => {
          useAlertStore().addDanger(getResponseError(e));
        });
    },
    data() {
      return {
        isLoading: false,
        video: emptyVideo(),
        comments: [] as Comment[],
        recommendedVideos: [] as Video[],
        getBackendRoute,
        emptyComment,
        toggleCommentFormText: this.$t('common.toggleCommentForm') as string,
        createCommentText: this.$t('common.submitComment') as string,
        loadCommentsText: this.$t('common.loadComments') as string,
        reportModal: null as Modal | null,
        reportModalId: '',
      }
    },
    methods: {
      t(key: string): string {
        return this.$t(key) as string;
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
      rateVideo(event: Event, rate: 'like' | 'dislike'): void {
        requestApi(getBackendRoute('videoRate', { id: this.video.id as string }))
          .then(response => {
            this.recommendedVideos = response.data.data as Video[];
          })
          .catch(e => {
            useAlertStore().addDanger(getResponseError(e));
          });
      },
      openReportModal(): void {
        this.reportModal = this.reportModal || new Modal(document.getElementById(this.reportModalId)!);
        this.reportModal.show();
      },
      onReportResponse(response: SuccessfulApiResponse): void {
        addUserAction(this.video, response.data! as unknown as UserAction);
        alert(response.message!);
        if (this.reportModal) {
          this.reportModal.hide();
        }
      },
      unreportComment(): void {
        if (confirm(this.$t('component.commentComponent.reportDeleteConfirm') as string)) {
          requestApi(getBackendRoute('commentReportDelete', { id: this.video.id! }))
            .then(response => {
              deleteUserAction(this.video, 'report');
              alert(response.data.message);
            })
            .catch(e => {
              useAlertStore().addDanger(getResponseError(e));
            });
        }
      },
      currentUserDidAction(action: string): boolean {
        return this.video.currentUserActions!.filter(a => a.action === action).length > 0;
      },
    },
  });
</script>