<template>
  <div>
    <h1>{{ $t('page.loginPage.mainHeader') }}</h1>

    <custom-form 
      :submitText="submitText" 
      :submitRoute="submitRoute"
      :formControls="formControls"
      redirectRoute="home"
      :afterSuccessResponse="setCurrentUser"
      class="my-3"
    ></custom-form>
    
    <div>
      {{ $t('page.loginPage.forgotPassword') }}
      <router-link :to="{ name: 'resetPasswordRequest' }">
        {{ $t('page.loginPage.recoverPassword') }}
      </router-link>
    </div>

    <div>
      {{ $t('page.loginPage.haveNotVerified') }}
      <router-link :to="{ name: 'registrationConfirmResend' }">
        {{ $t('page.loginPage.resendVerificationEmail') }}
      </router-link>
    </div>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { backendRoutes } from '../../router';
  import CustomForm from '../custom-form.vue';
  import { emailFormControl, passwordFormControl } from '../../common';
  import { SuccessfulApiResponse } from '../../api';
  import { useAppStore } from '../../state';
  import User from '../../entity/user';
  
  export default defineComponent({
    components: {
      CustomForm,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.loginPage.title'),
      }
    },
    data() {
      return {
        appStore: useAppStore(),
        submitRoute: backendRoutes['login'],
        submitText: this.$t('page.loginPage.submit') as string,
        formControls: [
          emailFormControl,
          passwordFormControl,
          {
            name: 'rememberMe', 
            label: ['form.login.rememberMe.label', {}], 
            type: 'checkbox',
          },
        ],
      }
    },
    methods: {
      setCurrentUser(response: SuccessfulApiResponse): SuccessfulApiResponse {
        this.appStore.setCurrentUser(response.data as unknown as User);

        return response;
      }
    },
  });
</script>