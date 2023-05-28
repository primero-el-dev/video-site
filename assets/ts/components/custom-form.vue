<template>
  <form 
    :action="submitRoute.path" 
    :method="submitRoute.method" 
    @submit="onSubmit" 
    v-bind="$attrs"
    v-on="$listeners"
  >
    <form-control 
      v-for="(fc, i) of innerFormControls" 
      :key="i"
      ref="values"
      :label="(fc.type !== 'hidden' && fc.label) ? fc.label : ''" 
      :type="fc.type" 
      :name="fc.name" 
      :error="errors[fc.name]"
      :placeholder="fc.placeholder || ''"
      :initialValue="fc.initialValue"
      :inputAttributes="fc.inputAttributes"
    />
    <slot name="afterInputs"></slot>
    <small v-if="error" class="text-danger">{{ error }}</small>
    <button 
      type="submit" 
      v-bind="submitButtonAttributes"
      :class="'mt-2 w-100 btn btn-' + submitButtonColor + ' ' + submitButtonClasses" 
    >
      {{ submitText }}
    </button>
    <slot name="afterButton"></slot>
  </form>
</template>

<script lang="ts">
  import { defineComponent, PropType } from 'vue';
  import router, { BackendRoute } from '../router';
  import axios from 'axios';
  import FormControl from './form-control.vue';
  import { useAlertStore } from '../state';
  import { requestApi, SuccessfulApiResponse, FailedApiResponse } from '../api';
  import { RouteConfig } from 'vue-router';
  import { Color } from '../common';

  type RequestType = 'json' | 'multipart';

  export default defineComponent({
    emits: ['responseData'],
    components: {
      FormControl,
    },
    props: {
      submitText: {
        type: String,
        required: false,
        default: 'Send',
      },
      formControls: {
        type: Array as PropType<any[]>,
        required: false,
        default: () => [],
      },
      submitRoute: {
        type: Object as PropType<BackendRoute>,
        required: true,
      },
      redirectRoute: {
        type: [String, Object] as PropType<string | RouteConfig>,
        required: false,
        default: '',
      },
      sendAs: {
        type: String as PropType<RequestType>,
        required: false,
        default: 'json',
      },
      processBeforeSend: {
        type: Function as PropType<(formData: FormData) => FormData>,
        required: false,
      },
      afterSuccessResponse: {
        type: Function as PropType<(response: SuccessfulApiResponse) => SuccessfulApiResponse>,
        required: false,
      },
      afterFailureResponse: {
        type: Function as PropType<(response: FailedApiResponse) => FailedApiResponse>,
        required: false,
      },
      submitGuard: {
        type: Function as PropType<(formData: FormData) => boolean>,
        required: false,
      },
      submitButtonColor: {
        type: String as PropType<Color>,
        required: false,
        default: 'primary',
      },
      submitButtonAttributes: {
        type: Object as PropType<{ [key: string]: string | number }>,
        required: false,
      },
      submitButtonClasses: {
        type: String,
        required: false,
        default: '',
      },
      clearOnSuccess: {
        type: Boolean,
        required: false,
        default: false,
      },
      redirectAfterSuccess: {
        type: Boolean,
        required: false,
        default: true,
      },
    },
    data() {
      return {
        errors: {} as { [field: string]: string },
        error: null,
        innerFormControls: [] as any[],
      }
    },
    watch: {
      formControls: {
        handler(newValue: any[], oldValue?: any[]): void {
          if (JSON.stringify(newValue) === JSON.stringify(oldValue)) {
            return;
          }
          
          for (let i = 0; i < this.formControls.length; i++) {
            this.$set(this.innerFormControls, i, this.formControls[i]);
          }
          
          this.innerFormControls.splice(
            this.formControls.length, 
            Math.max(this.innerFormControls.length - this.formControls.length, 0)
          );
        },
        immediate: true,
        deep: false,
      },
    },
    methods: {
      getError(field: string): string | void {
        if (this.errors[field]) {
          return this.errors[field];
        }
      },
      getAsJson(formData: FormData): { [field: string]: any } {
        let values: { [key: string]: any } = {};
        
        for (let [name, value] of formData as any) {
          // Check if there is array input
          let match = name.match(/^(?<name>.*)\[(?<key>[\w\d\_]*)\]$/);
          if (match) {
            if (values[match.groups.name] === undefined) {
              values[match.groups.name] = (match.groups.key === '') ? [] : {};
            }
            
            if (match.groups.key === '') {
              values[match.groups.name].push(value);
            } else {
              values[match.groups.name][match.groups.key] = value;
            }
          } else {
            values[name] = value;
          }
        }
        
        return values;
      },
      getContentType(): string {
        switch(this.sendAs) {
          case 'json': return 'application/json';
          case 'multipart': return 'multipart/form-data';
          default: return '';
        };
      },
      async onSubmit(e: Event): Promise<void> {
        e.preventDefault();

        let form = e.target as HTMLFormElement;
        let formData: any = new FormData(form);
        if (this.processBeforeSend) {
          formData = this.processBeforeSend(formData);
        }

        if (this.submitGuard && !this.submitGuard(formData)) {
          return;
        }

        if (this.sendAs === 'json') {
          formData = this.getAsJson(formData);
        }
        
        try {
          let response = await requestApi(this.submitRoute, formData, {
            headers: {
              'Content-Type': this.getContentType()
            }
          });

          if (!response.data) {
            throw Error('Missing response data.');
          }

          this.$emit('responseData', response.data);

          if (this.clearOnSuccess) {
            for (let i in this.innerFormControls) {
              if (['text', 'number', 'textarea'].includes(this.innerFormControls[i].type)) {
                let item: any;
                let originalInitialValue = this.innerFormControls[i].initialValue || '';
                
                item = { ...this.innerFormControls[i] };
                item.initialValue = originalInitialValue + ' ';
                this.$set(this.innerFormControls, i, item);
                
                setTimeout(() => {
                  item = { ...this.innerFormControls[i] };
                  item.initialValue = originalInitialValue;
                  this.$set(this.innerFormControls, i, item);
                }, 10);
              }
            }
          }

          if (this.afterSuccessResponse) {
            this.afterSuccessResponse(response.data as SuccessfulApiResponse);
          }

          if (response.data.message) {
            useAlertStore().addSuccess(response.data.message);
          }

          if (this.redirectAfterSuccess) {
            if (typeof this.redirectRoute === 'string') {
              router.push({ name: this.redirectRoute });
            } else {
              router.push(this.redirectRoute);
            }
          }
        } catch (error) {
          if (axios.isAxiosError(error)) {
            if (!error.response) {
              return;
            }

            if (this.afterFailureResponse) {
              this.afterFailureResponse(error.response.data as FailedApiResponse);
            }

            if (error.response.data.error) {
              this.error = error.response.data.error;
            }

            let errors = {} as { [field: string]: string };
            if (error.response.data.errors) {
              for (let field in (error.response.data.errors as any[])) {
                for (let formControl of this.formControls) {
                  let match = formControl.name.match(/^(?<name>.*)(?:\[(?<key>[\w\d\_]*)\])+$/);
                  if ((match && match[1] === field) || (formControl.name === field)) {
                    errors[formControl.name] = error.response.data.errors[field];
                    break;
                  }
                }
              }
              this.errors = errors;
            }
          } else {
            throw error;
          }
        }
      },
    },
  })
</script>