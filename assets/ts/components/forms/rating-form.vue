<template>
  <div class="btn-group btn-group-sm d-inline-flex" role="group" aria-label="">
    <input 
      type="checkbox" 
      class="btn-check" 
      :id="ids[0]" 
      autocomplete="off" 
      @change="rate('like')" 
      :checked="userRating === values.like"
    >
    <label class="btn btn-outline-success" :for="ids[0]">
      <i class="fa-sharp fa-light fa-up"></i> 
      {{ baseCount.like + ((userRating === values.like) ? 1 : 0) }}
    </label>

    <input 
      type="checkbox" 
      class="btn-check" 
      :id="ids[1]" 
      autocomplete="off" 
      @change="rate('dislike')" 
      :checked="userRating === values.dislike"
    >
    <label class="btn btn-outline-danger" :for="ids[1]">
      <i class="fa-sharp fa-light fa-down"></i> 
      {{ baseCount.dislike + ((userRating === values.dislike) ? 1 : 0) }}
    </label>

    <slot name="additionalButtons"></slot>
  </div>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import { BackendRoute } from '../../router';
  import { getResponseError, requestApi } from '../../api';
  import { useAlertStore } from '../../state';
  import { v4 as uuidv4 } from 'uuid';
  import HasRating from '../../entity/has-rating';

  export default defineComponent({
    emits: ['rate'],
    props: {
      backendRoute: {
        type: Object as PropType<BackendRoute>,
        required: true,
      },
      rating: {
        type: Object as PropType<HasRating>,
        required: true,
      },
    },
    data() {
      return {
        userRating: null as number | null,
        values: {
          like: 1,
          dislike: -1,
        },
        baseCount: {
          like: 0,
          dislike: 0,
        },
        ids: [
          uuidv4(),
          uuidv4(),
        ],
      }
    },
    watch: {
      rating: {
        handler(newValue: HasRating, oldValue?: HasRating) {
          this.userRating = this.rating.currentUserRating;
          
          if (this.userRating === this.values.like) {
            this.baseCount.like = this.rating.likesCount - 1;
          } else if (this.userRating === this.values.dislike) {
            this.baseCount.like = this.rating.dislikesCount - 1;
          }
        },
        deep: true,
        immediate: true,
      },
    },
    methods: {
      rate(rate: 'like' | 'dislike'): void {
        requestApi(this.backendRoute, { rating: this.values[rate] })
          .then(response => {
            this.userRating = response.data.data.rating as number;
            this.$emit('rate', this.userRating);
          })
          .catch(e => {
            useAlertStore().addDanger(getResponseError(e));
          });
      },
    },
  });
</script>