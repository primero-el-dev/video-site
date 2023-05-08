<template>
  <div>
    <h1>{{ $t('page.resetPasswordResetPage.mainHeader') }}</h1>

    <custom-form 
      :submitText="$t('page.resetPasswordResetPage.submit')" 
      :submitRoute="submitRoute"
      :formControls="formControls"
      redirectRoute="login"
      class="my-3"
    ></custom-form>
    
    <router-link :to="{ name: 'login' }">
      {{ $t('page.resetPasswordResetPage.goToLogin') }}
    </router-link>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { backendRoutes, RouteParams } from '../../router';
  import CustomForm from '../custom-form.vue';
  import { repeatPasswordFirstFormControl, repeatPasswordSecondFormControl } from '../../common';
  
  export default defineComponent({
    components: {
      CustomForm,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.resetPasswordResetPage.title'),
      }
    },
    setup() {
      let submitRoute = backendRoutes['resetPasswordReset'];
      submitRoute.params = { token: new URLSearchParams(document.location.search).get('token') || null } as RouteParams;
      
      return {
        submitRoute,
      }
    },
    data() {
      return {
        formControls: [
          repeatPasswordFirstFormControl,
          repeatPasswordSecondFormControl,
        ],
      }
    },
  });
</script>