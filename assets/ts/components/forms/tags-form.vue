<template>
  <div v-bind="$attrs">
    <div class="form-label">{{ $t('form.tags.label') }}</div>
    <input type="text" list="tagList" id="tagsInput" class="form-control" @keyup.enter="selectTag">
    <datalist id="tagList" class="mb-2">
      <option v-for="(tag, i) in tags" :key="i" :label="tag" :value="tag"></option>
    </datalist>
    <small v-if="error" class="text-danger">{{ error }}</small>
    <div :class="'d-block mb-2' + (selectedTags.length ? ' mt-2' : '')">
      <div v-for="(tag, i) in selectedTags" :key="i" class="tag badge badge-lg fs-6 bg-primary me-2">
        <input type="hidden" name="tags[]" :value="tag">
        #{{ tag }}
        <span class="tag-delete" @click="unselectTag(tag)" style="cursor:pointer;">&times;</span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import axios from 'axios';
  import { requestApi } from '../../api';
  import { backendRoutes } from '../../router';

  export default defineComponent({
    created() {
      requestApi(backendRoutes['tagIndex'])
        .then(response => {
          this.tags = response.data.data.map((tag: any) => tag.name);
        })
        .catch(e => {
          this.error = axios.isAxiosError(e) 
            ? e?.response?.data?.error 
            : (e as Error).message;
        });
    },
    props: {
      defaultSelectedTags: {
        type: Array as PropType<string[]>,
        required: false,
        default: [] as string[],
      },
    },
    data() {
      return {
        tags: [] as string[],
        selectedTags: this.defaultSelectedTags,
        error: null as string | null,
      }
    },
    watch: {
      defaultSelectedTags(newTags: string[], oldTags: string[]): void {
        this.selectedTags = newTags;
      },
    },
    methods: {
      selectTag(e: Event): void {
        if (this.selectedTags.length >= 15) {
          this.error = this.$t('form.tags.error.tooMany', { limit: 15 }) as string;
          return;
        }

        let target = e.target as HTMLInputElement;
        let value = target.value!.toLowerCase();
        if (!value.match(/^[a-z\d\._\-]+$/)) {
          this.error = this.$t('form.tags.error.invalidValue') as string;
          return;
        }

        if (!this.selectedTags.includes(value)) {
          this.selectedTags.push(value)
        }
        this.error = null;
        target.value = '';
      },
      unselectTag(tag: string): void {
        this.selectedTags = this.selectedTags.filter(t => t !== tag);
      },
      getSelectedTags(): string[] {
        return this.selectedTags;
      },
    },
  });
</script>