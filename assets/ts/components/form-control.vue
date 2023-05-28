<template>
  <div v-if="type === 'checkbox'" class="mb-3 w-100">
    <input 
      v-bind="{ ...$attrs, ...inputAttributes }"
      type="checkbox" 
      class="form-check-input" 
      v-model="value" 
      :id="name" 
      :name="name" 
      :checked="value === 'on'" 
      :required="required"
    >
    <label class="form-check-label" :for="name" v-html="asString(label)"></label>
    <small v-if="error" class="d-block text-danger">{{ error }}</small>
  </div>
  
  <input 
    v-else-if="type === 'hidden'"
    v-bind="{ ...$attrs, ...inputAttributes }"
    :type="type" 
    :name="name" 
    :value="value"
  >

  <label v-else class="mb-3 form-label w-100">
    {{ asString(label) }}
    <input 
      v-if="type !== 'textarea'" 
      v-bind="{ ...$attrs, ...inputAttributes }"
      :type="type" 
      v-model="value" 
      :name="name" 
      :placeholder="asString(placeholder)" 
      class="form-control"
      :required="required"
    >
    <textarea 
      v-else 
      v-bind="{ ...$attrs, ...inputAttributes }"
      v-model="value" 
      :name="name" 
      :placeholder="asString(placeholder)" 
      class="form-control" 
    >
      {{ value }}
    </textarea>
    <small v-if="error" class="text-danger">{{ error }}</small>
  </label>
  
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';

  type Stringable = string | [string, { [key: string]: string }];

  export interface FormControlInterface {
    label: string,
    name: string,
    type: string | undefined,
    placeholder: string | undefined,
    error: string | null | undefined,
    value: any
  };

  export default defineComponent({
    emits: ['input'], //@ts-ignore 
    setup(props, { emit }) {
      emit('input');
    },
    props: {
      label: {
        type: [String, Array] as PropType<Stringable>,
        required: true,
      },
      name: {
        type: String,
        required: false,
        default: '',
      },
      type: {
        type: String,
        required: false,
        default: 'text',
      },
      placeholder: {
        type: [String, Array] as PropType<Stringable>,
        required: false,
        default: '',
      },
      error: {
        type: String,
        required: false,
        default: '',
      },
      required: {
        type: Boolean,
        required: false,
        default: false,
      },
      initialValue: {
        required: false,
        default: null,
      },
      inputAttributes: {
        type: Object as PropType<{ [key: string]: string | number | null }>,
        required: false,
        default: () => ({} as { [key: string]: string | number | null }),
      }
    },
    watch: {
      initialValue: {
        handler(newValue: any, oldValue?: any): void {
          this.value = (newValue === null) ? '' : newValue;
          console.log(`new value: ${newValue}`)
        },
        deep: true,
      },
    },
    data() {
      return {
        value: (this.initialValue === null) ? '' : this.initialValue,
      }
    },
    methods: {
      asString(msg: Stringable): string {
        return typeof msg === 'string' ? msg : (this.$t(msg[0], msg[1]) as string);
      }
    },
  });
</script>