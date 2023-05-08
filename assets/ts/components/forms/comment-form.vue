<template>
  <custom-form 
    v-bind="$attrs"
    :formControls="formControls"
    v-on="$listeners"
    :clearOnSuccess="true"
    :redirectAfterSuccess="false"
  ></custom-form>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import CustomForm from '../custom-form.vue';
  import Comment from '../../entity/comment';

  export default defineComponent({
    components: {
      CustomForm,
    },
    props: {
      comment: {
        type: Object as PropType<Comment>,
        required: true,
      },
      videoId: {
        type: String,
        required: false,
      },
      parentId: {
        type: Number,
        required: false,
      },
    },
    data() {
      return {
        formControls: [
          {
            label: this.$t('form.comment.content.label'),
            type: 'textarea', 
            name: 'content', 
            initialValue: this.comment.content, 
            placeholder: this.$t('form.comment.content.placeholder'),
            attributes: { rows: 4 },
          },
        ] as any[],
      }
    },
    watch: {
      comment: {
        handler(newValue: Comment, oldValue?: Comment): void {
          this.formControls[0].initialValue = newValue.content;
          console.log('Comment:', newValue)
        },
        deep: true,
      },
      videoId: {
        handler(newValue: string, oldValue?: string): void {
          if (newValue !== undefined) {
            this.formControls[1] = {
              type: 'hidden', 
              name: 'video', 
              initialValue: newValue,
            };
          } else {
            delete this.formControls[1];
          }
        },
        immediate: true,
      },
      parentId: {
        handler(newValue: number, oldValue?: number): void {
          if (newValue !== undefined) {
            this.formControls[2] = {
              type: 'hidden', 
              name: 'parent', 
              initialValue: newValue,
            };
          } else {
            delete this.formControls[2];
          }
        },
        immediate: true,
      },
    },
  });
</script>