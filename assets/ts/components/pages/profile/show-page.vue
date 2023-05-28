<template>
  <div>
    <h1>{{ $t('page.profileShowPage.mainHeader') }}</h1>
    
    <go-back-link></go-back-link>

    
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import GoBackLink from '../../go-back-link.vue';
  import { useAppStore } from '../../../state';
  import { requestApi } from '../../../api';
  import { backendRoutes } from '../../../router';
  import User from '../../../entity/user';

  export default defineComponent({
    components: {
      GoBackLink,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.profileShowPage.title'),
      }
    },
    props: {
      id: {
        type: Number,
        required: true,
      }
    },
    created() {
      let route = backendRoutes['userGet'];
      route.params = { id: this.id };

      requestApi(route)
        .then(response => {
          this.user = response.data.data as User;
        })
        .catch(error => {

        });
    },
    data() {
      return {
        appStore: useAppStore(),
        user: null as User | null,
      }
    },
  });
</script>