<template>
  <div>
    <button class="btn btn-block w-100 mt-4" @click="toggleCommentForm">
      {{ toggleCommentFormText }}
    </button>

    <comment-form 
      v-if="showSubcommentForm"
      class="mb-4"
      :comment="emptyComment()"
      :submitRoute="getBackendRoute('commentCreate')"
      :videoId="videoId"
      :parentId="parentId"
      :submitText="createCommentText"
      @responseData="onCommentCreateSuccess"
    ></comment-form>

    <div id="comments">
      <comment-component 
        v-for="comment in allComments" 
        :key="comment.id" 
        :comment="comment" 
        :videoId="videoId"
      ></comment-component>
    </div>

    <button v-if="showLoadComments" class="btn btn-block w-100" @click="loadComments">
      {{ loadCommentsText }}
    </button>
  </div>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import { getBackendRoute } from '../router';
  import CommentForm from './forms/comment-form.vue';
  import CommentComponent from './comment-component.vue';
  import Comment, { emptyComment } from '../entity/comment';
  import { getResponseError, requestApi, SuccessfulApiResponse } from '../api';
  import { useAlertStore } from '../state';
  import { commentsPerLoad } from '../common';

  export default defineComponent({
    components: {
      CommentForm,
      CommentComponent,
    },
    props: {
      parentId: {
        type: String,
        required: false,
      },
      videoId: {
        type: String,
        required: true,
      },
      comments: {
        type: Array as PropType<Comment[]>,
        required: false,
        default: () => [] as Comment[],
      },
      toggleCommentFormText: {
        type: String,
        required: true,
      },
      createCommentText: {
        type: String,
        required: true,
      },
      loadCommentsText: {
        type: String,
        required: true,
      },
    },
    data() {
      return {
        emptyComment,
        getBackendRoute,
        allComments: this.comments as Comment[],
        showSubcommentForm: false,
        showLoadComments: true,
      }
    },
    methods: {
      onCommentCreateSuccess(response: SuccessfulApiResponse): void {
        this.showSubcommentForm = false;
        this.allComments.unshift(response.data as unknown as Comment);
        alert(response.message);
      },
      toggleCommentForm(): void {
        this.showSubcommentForm = !this.showSubcommentForm;
      },
      loadComments(): void {
        let maxOrder = this.allComments.length 
          ? ((this.allComments[this.allComments.length - 1].order as number) - 1) as unknown as string
          : undefined;
        
        requestApi(getBackendRoute('commentIndex', { 
          video: this.videoId, 
          parent: this.parentId, 
          limit: commentsPerLoad, 
          'order-dir': 'desc', 
          'max-order': maxOrder,
        }))
          .then(response => {
            let loadedComments = response.data.data as Comment[];
            this.allComments = this.allComments.concat(loadedComments);
            if (loadedComments.length < commentsPerLoad) {
              this.showLoadComments = false;
            }
          })
          .catch(e => {
            useAlertStore().addDanger(getResponseError(e));
          });
      },
    },
  });
</script>