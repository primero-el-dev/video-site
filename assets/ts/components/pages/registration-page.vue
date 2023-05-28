<template>
  <div>
    <h1>{{ $t('page.registrationPage.mainHeader') }}</h1>

    <custom-form 
      :submitText="submitText" 
      :submitRoute="submitRoute"
      :formControls="formControls"
      redirectRoute="home"
      sendAs="multipart"
      class="my-3"
    >
      <div slot="afterInputs">
        <div class="modal fade" id="regulationsModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" v-html="$t('page.registrationPage.regulationsModal.title')"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body" v-html="$t('page.registrationPage.regulationsModal.content')"></div>
            </div>
          </div>
        </div>
      </div>
    </custom-form>
    
    {{ $t('page.registrationPage.alreadyHaveAccount') }}
    <router-link :to="{ name: 'login' }">
      {{ $t('page.registrationPage.login') }}
    </router-link>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { backendRoutes } from '../../router';
  import CustomForm from '../custom-form.vue';
  import { 
    nickFormControl, 
    emailFormControl, 
    repeatPasswordFirstFormControl, 
    repeatPasswordSecondFormControl, 
    birthDateFormControl,
    userImageFormControl,
    userBackgroundFormControl
  } from '../../common';
  
  export default defineComponent({
    components: {
      CustomForm,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.registrationPage.title'),
      }
    },
    data() {
      return {
        submitText: this.$t('page.registrationPage.submit') as string,
        submitRoute: backendRoutes['registration'],
        formControls: [
          nickFormControl,
          emailFormControl,
          repeatPasswordFirstFormControl,
          repeatPasswordSecondFormControl,
          birthDateFormControl,
          userImageFormControl,
          userBackgroundFormControl,
          {
            name: 'acceptRegulations', 
            label: this.$t('page.registrationPage.regulationsModal.open'), 
            type: 'checkbox',
            required: true,
          },
        ],
      }
    },
  });
</script>