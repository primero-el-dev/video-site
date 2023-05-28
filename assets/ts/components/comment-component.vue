<template>
  <div class="comment my-5">
    <div class="comment__header d-flex justify-content-between">
      <user-media :user="innerComment.owner"></user-media>

      <rating-action-panel 
        v-if="!innerComment.deleted"
        :entity="innerComment"
        :rateRoute="getBackendRoute('commentRate', { id: innerComment.id })"
        :reportRoute="getBackendRoute('commentReport', { id: innerComment.id })"
        :reportDeleteRoute="getBackendRoute('commentReportDelete', { id: innerComment.id })"
        @entityReported="onCommentReported"
        @entityUnreported="onCommentUnreported"
      >
        <li 
          slot="additionalOptions"
          v-if="innerComment.permissions.includes('COMMENT_UPDATE')" 
          @click="toggleCommentUpdateForm"
          class="dropdown-item" 
        >
          {{ t('common.edit') }}
        </li>
        
        <li 
          slot="additionalOptions"
          v-if="innerComment.permissions.includes('COMMENT_DELETE')" 
          @click="deleteComment"
          class="dropdown-item" 
        >
          {{ t('common.delete') }}
        </li>
      </rating-action-panel>
    </div>

    <div class="comment__content mt-4">
      <comment-form 
        v-if="showCommentEditForm"
        :comment="innerComment"
        :submitRoute="getBackendRoute('commentUpdate', { id: innerComment.id })"
        :submitText="t('common.edit')"
        @responseData="onCommentUpdateSuccess"
      ></comment-form>

      <span v-else-if="innerComment.deleted" class="text-danger">
        [{{ t('common.deleted') }}]
      </span>

      <span v-else>
        {{ innerComment.content }}
      </span>
    </div>

    <div>
      <button class="btn btn-block w-100 mt-4" @click="toggleSubcommentForm">
        {{ t('common.toggleSubcommentForm') }}
      </button>

      <comment-form 
        v-if="showSubcommentForm"
        class="mb-4"
        :comment="emptyComment()"
        :submitRoute="getBackendRoute('commentCreate')"
        :videoId="videoId"
        :parentId="innerComment.id"
        :submitText="t('common.submitSubcomment')"
        @responseData="onSubcommentCreateSuccess"
      ></comment-form>

      <div v-if="subcomments.length" id="comments" class="mt-4 ps-4 border-start border-3">
        <comment-component 
          v-for="comm in subcomments" 
          :key="comm.id" 
          :comment="comm" 
          :videoId="videoId"
        ></comment-component>
      </div>

      <button v-if="showLoadSubcomments" class="btn btn-block w-100" @click="loadSubcomments">
        {{ t('common.loadSubcomments') }}
      </button>
    </div>
  </div>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import RatingForm from './forms/rating-form.vue';
  import UserMedia from './user-media.vue';
  import CustomForm from './custom-form.vue';
  import RatingActionPanel from './rating-action-panel.vue';
  import CommentForm from './forms/comment-form.vue';
  import { getBackendRoute } from '../router';
  import Comment, { emptyComment } from '../entity/comment';
  import { useAlertStore } from '../state';
  import { getResponseError, requestApi, SuccessfulApiResponse } from '../api';
  import { commentsPerLoad } from '../common';

  export default defineComponent({
    name: 'CommentComponent',
    components: {
      UserMedia,
      RatingForm,
      CustomForm,
      CommentForm,
      RatingActionPanel,
    },
    props: {
      comment: {
        type: Object as PropType<Comment>,
        required: true,
      },
      videoId: {
        type: String,
        required: true,
      },
    },
    data() {
      return {
        showCommentEditForm: false,
        showSubcommentForm: false,
        showLoadSubcomments: false,
        subcomments: [] as Comment[],
        emptyComment,
        getBackendRoute,
        innerComment: emptyComment(),
      }
    },
    watch : {
      comment: {
        handler(newValue: Comment, oldValue?: Comment): void {
          this.showLoadSubcomments = newValue.childrenCount > 0;
          this.subcomments = newValue.children ? newValue.children : [];
          this.innerComment = newValue;
        },
        immediate: true,
        deep: true,
      },
    },
    methods: {
      t(key: string): string {
        return this.$t(key) as string;
      },
      onCommentUpdateSuccess(response: SuccessfulApiResponse): void {
        this.innerComment = response.data as unknown as Comment;
        this.showCommentEditForm = false;
        alert(response.message);
      },
      onSubcommentCreateSuccess(response: SuccessfulApiResponse): void {
        this.subcomments.unshift(response.data as unknown as Comment);
        this.showSubcommentForm = false;
        alert(response.message);
      },
      toggleSubcommentForm(): void {
        this.showSubcommentForm = !this.showSubcommentForm;
      },
      toggleCommentUpdateForm(): void {
        this.showCommentEditForm = !this.showCommentEditForm;
      },
      loadSubcomments(): void {
        let maxOrder = this.subcomments.length 
          ? ((this.subcomments[this.subcomments.length - 1].order as number) - 1) as unknown as string
          : undefined;
        
        requestApi(getBackendRoute('commentIndex', { 
          video: this.videoId, 
          parent: this.innerComment.id, 
          limit: commentsPerLoad, 
          'order-dir': 'desc', 
          'max-order': maxOrder,
        }))
          .then(response => {
            let subcomments = response.data.data as Comment[];
            this.subcomments = this.subcomments.concat(subcomments);
            if (subcomments.length < commentsPerLoad) {
              this.showLoadSubcomments = false;
            }
          })
          .catch(e => {
            useAlertStore().addDanger(getResponseError(e));
          });
      },
      onCommentReported(response: any): void {
        this.innerComment.currentUserActions = this.innerComment.currentUserActions || [];
        this.innerComment.currentUserActions.push({ action: response.data.data, additionalInfo: {} });
      },
      onCommentUnreported(): void {
        this.innerComment.currentUserActions = (this.innerComment.currentUserActions || [])
          .filter(a => a.action !== 'report');
      },
      deleteComment(): void {
        if (confirm(this.$t('component.commentComponent.deleteConfirm') as string)) {
          requestApi(getBackendRoute('commentDelete', { id: this.innerComment.id }))
            .then(response => {
              this.innerComment = response.data.data as Comment;
              alert(response.data.message);
            })
            .catch(e => {
              useAlertStore().addDanger(getResponseError(e));
            });
        }
      },
    },
  });
</script>