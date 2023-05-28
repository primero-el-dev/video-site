<template>
  <div>
    <h1>{{ $t('page.registrationConfirmResendPage.mainHeader') }}</h1>

    <custom-form 
      :submitText="submitText" 
      :submitRoute="submitRoute"
      :formControls="formControls"
      redirectRoute="home"
      class="my-3"
    ></custom-form>
    
    <go-back-link></go-back-link>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { backendRoutes } from '../../router';
  import CustomForm from '../custom-form.vue';
  import GoBackLink from '../go-back-link.vue';
  import { emailFormControl } from '../../common';
  
  export default defineComponent({
    components: {
      CustomForm,
      GoBackLink,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.registrationConfirmResendPage.title'),
      }
    },
    data() {
      return {
        submitText: this.$t('page.registrationConfirmResendPage.submit') as string,
        submitRoute: backendRoutes['registrationConfirmResend'],
        formControls: [emailFormControl],
      }
    },
  });
</script>