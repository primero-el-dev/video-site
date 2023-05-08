<template>
  <div>
    <nav class="navbar navbar-expand-sm navbar-dark bg-primary" aria-label="Navbar">
      <div class="container-fluid">
        <router-link :to="{ name: 'home' }" class="navbar-brand">
          {{ appName }}
        </router-link>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <router-link :to="{ name: 'home' }" :class="'nav-link' + (($route.name === 'home') ? ' active' : '')">
                {{ $t('app.navbar.home') }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link :to="{ name: 'about' }" :class="'nav-link' + (($route.name === 'about') ? ' active' : '')">
                About
              </router-link>
            </li>
            <li v-if="!isLogged()" class="nav-item">
              <router-link :to="{ name: 'login' }" :class="'nav-link' + (($route.name === 'login') ? ' active' : '')">
                {{ $t('app.navbar.login') }}
              </router-link>
            </li>
            <li v-if="!isLogged()" class="nav-item">
              <router-link :to="{ name: 'registration' }" :class="'nav-link' + (($route.name === 'registration') ? ' active' : '')">
                {{ $t('app.navbar.registration') }}
              </router-link>
            </li>
            <li v-if="isLogged()" class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <!-- <img :src="imagesPath + appStore.getCurrentUser().imagePath">  -->
                {{ $t('app.navbar.accountDropdown') }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><router-link :to="{ name: 'profileIndex' }" class="dropdown-item">{{ $t('app.navbar.profile') }}</router-link></li>
                <li><a class="dropdown-item" @click="logout($event)">{{ $t('app.navbar.logout') }}</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="container-fluid py-4">
      <div v-for="alert of alertStore.getAllAndTimeoutDelete(10)" :key="alert.id" :class="'my-3 alert alert-dismissible fade show alert-'+alert.type" role="alert">
        {{ alert.message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      
      <router-view></router-view>
    </main>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import axios from 'axios';
  import { appName, imagesPath } from '../common';
  import router, { frontendRoutes, backendRoutes } from '../router';
  import { requestApi } from '../api';
  import { useAppStore, useAlertStore, isLogged } from '../state';
  import LoaderAnimation from './loader-animation.vue';
  
  export default defineComponent({
    components: {
      LoaderAnimation,
    },
    metaInfo: {
      titleTemplate: (title) => title ? `${title} - ${appName}` : appName,
    },
    data() {
      useAppStore().checkUpdateIsLogged();

      return {
        message: 'Hello',
        appName: appName,
        imagesPath: imagesPath,
        frontendRoutes: frontendRoutes,
        alertStore: useAlertStore(),
        appStore: useAppStore(),
        isLogged: useAppStore().isLogged,
      }
    },
    methods: {
      async logout(e: Event): Promise<void> {
        e.preventDefault();
        let route = backendRoutes['logout'];

        try {
          let response = await requestApi(route);
          if (!response.data) {
            throw Error('Missing response data.');
          }

          if (response.data.message) {
            useAlertStore().addSuccess(response.data.message);
          }
          router.push({ name: 'home' });
        } catch (error) {
          if (axios.isAxiosError(error)) {
            if (!error.response) {
              return;
            }
            if (error.response.data.error) {
              useAlertStore().addDanger(error.response.data.error);
            }
          } else {
            throw error;
          }
        }
      }
    },
  });
</script>