<template>
  <div>
    <h1>{{ $t('page.notificationIndexPage.mainHeader') }}</h1>
    
    <go-back-link></go-back-link>

    <div class="row">
      <div class="col-12 col-sm-4">
        <h3 class="mb-3">{{ $t('common.filter') }}</h3>

        <div class="mt-3 mb-4">
          <p class="mb-3">{{ $t('page.notificationIndexPage.filterBySeen') }}</p>

          <div v-for="seenValue in ['seen', 'notSeen']" class="form-check">
            <input 
              type="checkbox" 
              name="seenValue" 
              class="form-check-input" 
              :id="'seenValue_' + seenValue" 
              @change="seenFilter = updateFilter(seenFilter, $event, seenValue)"
              checked
            >
            <label class="form-check-label" :for="'seenValue_' + seenValue">
              {{ $t('common.' + seenValue) }}
            </label>
          </div>
        </div>

        <div class="mb-4">
          <p class="mb-3">{{ $t('page.notificationIndexPage.filterByReceiver') }}</p>

          <div class="form-check">
            <input 
              type="checkbox" 
              name="receiverValue" 
              class="form-check-input" 
              id="receiverValue_personal" 
              @change="receiverFilter = updateFilter(receiverFilter, $event, 'personal')"
              checked
            >
            <label class="form-check-label" for="receiverValue_personal">
              {{ $t('page.notificationIndexPage.personal') }}
            </label>
          </div>
          <div v-for="role in notificationRoles" v-if="notificationRoles.length" class="form-check">
            <input 
              type="checkbox" 
              name="receiverValue" 
              class="form-check-input" 
              :id="'receiverValue_' + role" 
              @change="receiverFilter = updateFilter(receiverFilter, $event, role)"
            >
            <label class="form-check-label" :for="'receiverInput_' + role">
              {{ $t('common.role.' + role) }}
            </label>
          </div>
        </div>

        <div class="mb-4">
          <p class="mb-3">{{ $t('page.notificationIndexPage.filterByType') }}</p>

          <div v-for="action in actionTypes" class="form-check">
            <input 
              type="checkbox" 
              name="action" 
              class="form-check-input" 
              :id="'action_' + action" 
              @change="actionFilter = updateFilter(actionFilter, $event, action)"
              checked
            >
            <label class="form-check-label" :for="'action_' + action">
              {{ $t('common.' + action) }}
            </label>
          </div>
        </div>

        <div class="mb-4">
          <button class="btn btn-primary btn-block w-100">{{ $t('common.markAllAsRead') }}</button>
        </div>
      </div>

      <div class="col-12 col-sm-8 my-4">
        <notification-list @processedNotifications="setProcessedNotifications"></notification-list>
        <div v-for="pn in filteredNotifications" :key="pn.id" class="card">
          <div class="card-body">
            <router-link :to="pn.subjectLink" style="text-decoration:none;" @click="pn.role === null ? markAsRead(pn) : null">
              {{ pn.message }}
            </router-link>
            <button v-if="!pn.seen" class="btn btn-sm btn-primary" @click="markAsRead(pn)">
              {{ $t('common.markAsRead') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import GoBackLink from '../../go-back-link.vue';
  import { useAlertStore, useAppStore } from '../../../state';
  import NotificationList from '../../notification-list.vue';
  import { getResponseError, requestApi } from '../../../api';
  import { getBackendRoute } from '../../../router';

  export default defineComponent({
    components: {
      GoBackLink,
      NotificationList,
    },
    metaInfo() {
      return {
        title: (this as any).$t('page.notificationIndexPage.title'),
      }
    },
    data() {
      let actionTypes = ['like', 'dislike', 'report', 'subscribe', 'comment_create', 'video_create', 'playlist_create'];

      return {
        appStore: useAppStore(),
        seenFilter: ['seen', 'notSeen'],
        receiverFilter: ['personal'],
        actionFilter: actionTypes,
        actionTypes: actionTypes,
        notificationRoles: [] as string[],
        filteredNotifications: [] as any[],
        processedNotifications: [] as any[],
      }
    },
    watch: {
      processedNotifications: {
        handler(newValue: any[]): void {
          if (newValue === undefined) {
            return;
          }

          this.notificationRoles = newValue
            .map(n => n.role)
            .filter(v => v !== undefined)
            .filter((value, index, array) => array.indexOf(value) === index);
          
          this.filterNotifications();
        },
        deep: true,
        immediate: true,
      },
      seenFilter(): void {
        this.filterNotifications();
      },
      receiverFilter(): void {
        this.filterNotifications();
      },
      actionFilter(): void {
        this.filterNotifications();
      },
    },
    methods: {
      setProcessedNotifications(processedNotifications: any[]): void {
        this.processedNotifications.splice(processedNotifications.length);
        for (let i in processedNotifications) {
          this.$set(this.processedNotifications, i, processedNotifications[i] as any);
        }
      },
      updateFilter(filter: string[], e: Event, action: string): string[] {
        let checked = (e.target as HTMLInputElement).checked;
        if (checked) {
          filter.push(action);
          filter = filter.filter((value, index, array) => array.indexOf(value) === index);
        } else {
          filter = filter.filter(a => a !== action);
        }

        return filter;
      },
      filterNotifications(): void {
        this.filteredNotifications = this.processedNotifications.filter(n => n !== undefined);
        
        if (!this.seenFilter.includes('notSeen')) {
          this.filteredNotifications = this.filteredNotifications.filter(n => n.seen);
        }
        if (!this.seenFilter.includes('seen')) {
          this.filteredNotifications = this.filteredNotifications.filter(n => !n.seen);
        }

        if (this.receiverFilter.includes('personal')) {
          this.filteredNotifications = this.filteredNotifications.filter(n => n.owner !== null);
        }
        this.filteredNotifications = this.filteredNotifications.filter(n => this.receiverFilter.includes(n.role));

        this.filteredNotifications = this.filteredNotifications.filter(n => this.actionFilter.includes(n.action));
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
      markAllAsRead(): void {
        requestApi(getBackendRoute('notificationSeeAll'))
          .then(response => {
            alert(response.data.message);
          })
          .catch(error => {
            useAlertStore().addDanger(getResponseError(error));
          });
      },
    },
  });
</script>