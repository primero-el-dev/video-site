<template>
  <div class="btn-group btn-group-sm d-inline-flex" role="group" aria-label="">
    <input 
      type="checkbox" 
      class="btn-check" 
      :id="like.id" 
      autocomplete="off" 
      @change="rate('like')" 
      :checked="userRating === 'like'"
    >
    <label class="btn btn-outline-success" :for="like.id">
      <i class="fa-solid fa-arrow-up fa-md me-1" aria-hidden="true"></i>
      {{ like.baseCount + Number(userRating === 'like') }}
    </label>
    
    <input 
      type="checkbox" 
      class="btn-check" 
      :id="dislike.id" 
      autocomplete="off" 
      @change="rate('dislike')" 
      :checked="userRating === 'dislike'"
    >
    <label class="btn btn-outline-danger" :for="dislike.id">
      <i class="fa-solid fa-arrow-down fa-md me-1" aria-hidden="true"></i>
      {{ dislike.baseCount + Number(userRating === 'dislike') }}
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
        userRating: null as string | null,
        like: {
          endpoint: 'like',
          id: uuidv4(),
          baseCount: 0,
        },
        dislike: {
          endpoint: 'dislike',
          id: uuidv4(),
          baseCount: 0,
        },
      }
    },
    watch: {
      rating: {
        handler(newValue: HasRating, oldValue?: HasRating) {
          console.log(newValue)
          this.userRating = newValue.currentUserRating;
          this.like.baseCount = newValue.likesCount - Number(this.userRating === 'like');
          this.dislike.baseCount = newValue.dislikesCount - Number(this.userRating === 'dislike');
        },
        deep: true,
        immediate: true,
      },
    },
    methods: {
      rate(rate: 'like' | 'dislike'): void {
        let route = { ...this.backendRoute };
        if (route.params) {
          route.params['rate'] = rate;
        } else {
          route.params = { rate };
        }

        requestApi(route)
          .then(response => {
            this.userRating = response.data.data as string | null;
            this.$emit('rate', this.userRating);
          })
          .catch(e => {
            useAlertStore().addDanger(getResponseError(e));
          });
      },
    },
  });
</script>