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
            <li v-if="isLogged()" class="nav-item dropdown me-2">
              <!-- <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell" aria-hidden="true"></i>
              </a> -->
              <a 
                href="#" 
                id="notificationDropdown" 
                data-bs-toggle="dropdown" 
                aria-expanded="false" 
                class="nav-link text-center" 
                title="notifications" 
                style="width:3rem; height:3rem; padding-top:.75rem;"
              >
                <i class="fa fa-bell fa-xl" aria-hidden="true"></i>
              </a>
              
              <notification-list @processedNotifications="setProcessedNotifications"></notification-list>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                <li>
                  <router-link class="dropdown-item notification-dropdown-item" :to="{ name: 'notificationIndex' }">
                    <small>{{ $t('common.notifications') }}</small>
                  </router-link>
                </li>
                <li v-for="pn in processedNotifications" v-if="!pn.seen" :key="pn.id">
                  <router-link 
                    class="dropdown-item notification-dropdown-item" 
                    :to="pn.subjectLink" 
                    @click="pn.role === null ? markAsRead(pn) : null"
                  >
                    <small>{{ pn.message }}</small>
                  </router-link>
                </li>
              </ul>
            </li>
            <li v-if="isLogged()" class="nav-item dropdown">
              <a 
                href="#" 
                id="profileDropdown" 
                data-bs-toggle="dropdown" 
                aria-expanded="false" 
                class="nav-link" 
                :title="t('app.navbar.accountDropdown')" 
                style="width:3rem; height:3rem; border-radius:50rem !important; background:url('/images/no-image.png') no-repeat center center; background-size:cover; background-color:white;"
              ></a>
              <!-- <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" data-bs-toggle="dropdown" aria-expanded="false"> -->
                <!-- <img :src="imagesPath + appStore.getCurrentUser().imagePath">  -->
                <!-- {{ $t('app.navbar.accountDropdown') }}
              </a> -->
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
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
  import router, { frontendRoutes, backendRoutes, getBackendRoute } from '../router';
  import { getResponseError, requestApi } from '../api';
  import { useAppStore, useAlertStore, isLogged } from '../state';
  import LoaderAnimation from './loader-animation.vue';
  import NotificationList from './notification-list.vue';
  
  export default defineComponent({
    components: {
      LoaderAnimation,
      NotificationList,
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
        processedNotifications: [] as any[],
      }
    },
    watch: {
      processedNotifications(newValue) {
        console.log(newValue)
      }
    },
    methods: {
      t(key: string): string {
        return this.$t(key) as string;
      },
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
      },
      markAsRead(notification: any): void {
        requestApi(getBackendRoute('notificationSee', { id: notification.id }))
          .then(response => {
            let returned = response.data.data;
            // Update current notification
            for (let i in this.processedNotifications) {
              if (this.processedNotifications[i].id === returned.id) {
                for (let j in returned) {
                  this.processedNotifications[i][j] = returned[j];
                }
                break;
              }
            }
            alert(response.data.message);
          })
          .catch(error => {
            useAlertStore().addDanger(getResponseError(error));
          });
      },
      setProcessedNotifications(processedNotifications: any[]): void {
        this.processedNotifications.splice(processedNotifications.length);
        for (let i in processedNotifications) {
          this.$set(this.processedNotifications, i, processedNotifications[i] as any);
        }
      },
    },
  });
</script>