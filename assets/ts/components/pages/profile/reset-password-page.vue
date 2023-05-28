<template>
  <div>
    <h1>{{ $t('page.profilePasswordResetPage.mainHeader') }}</h1>

    <custom-form 
      :submitText="$t('page.profilePasswordResetPage.submit')" 
      :submitRoute="submitRoute"
      :formControls="formControls"
      redirectRoute="profileIndex"
      class="my-3"
    ></custom-form>
    
    <go-back-link></go-back-link>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { backendRoutes } from '../../../router';
  import CustomForm from '../../custom-form.vue';
  import GoBackLink from '../../go-back-link.vue';
  import { repeatPasswordFirstFormControl, repeatPasswordSecondFormControl } from '../../../common';

  export default defineComponent({
    components: {
      CustomForm,
      GoBackLink,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.profilePasswordResetPage.title'),
      }
    },
    data() {
      return {
        submitRoute: backendRoutes['resetPasswordReset'],
        formControls: [
          {
            name: 'oldPassword', 
            label: ['form.common.currentPassword.label', {}], 
            type: 'password', 
            placeholder: ['form.common.currentPassword.placeholder', {}],
          },
          repeatPasswordFirstFormControl,
          repeatPasswordSecondFormControl,
        ],
      }
    },
  });
</script>