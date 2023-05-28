<template>
  <div></div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import Notification from '../entity/notification';
  import { RouteConfig } from 'vue-router';
  import Comment from '../entity/comment';

  export default defineComponent({
    emits: ['processedNotifications'],
    data() {
      return {
        notifications: [] as Notification[],
        socket: null as any,
      }
    },
    created() {
      this.socket = new WebSocket('ws://localhost:3001') as any;
 
      this.socket.addEventListener('open', (e: Event) => {
        console.log('CONNECTED');
      });

      this.socket.addEventListener("message", (e: MessageEvent) => {
        let notifications = JSON.parse(e.data) as Notification[];
        for (let n of notifications) {
          let replaced = false;
          for (let i in this.notifications) {
            if (this.notifications[i].subject.id === n.subject.id && this.notifications[i].subjectType === n.subjectType) {
              this.$set(this.notifications, i, n);
              replaced = true;
              break;
            }
          }
          if (!replaced) {
            this.notifications.splice(this.notifications.length + 1);
            this.$set(this.notifications, this.notifications.length, n);
          }
        }
        this.notifications = this.notifications.sort((a, b) => Number(a.updatedAt < b.updatedAt));

        let processedNotifications: any = this.notifications.map(n => ({
          ...n,
          message: this.getNotificationMessage(n),
          subjectLink: this.getNotificationSubjectLink(n),
        }));
        
        this.$emit('processedNotifications', processedNotifications);
      });
    },
    // watch: {
    //   notifications(newValue: any[]): void {
    //     this.notifications = newValue;
    //   },
    // },
    methods: {
      getNotificationSubjectLink(notification: Notification): RouteConfig | null {
        switch(notification.subjectType) {
          case 'comment': 
            let id = (notification.subject as unknown as Comment)!.video!.id;
            return { name: 'videoShow', params: { id }} as unknown as RouteConfig;
          case 'video': 
            return { name: 'videoShow', params: { id: notification.subject.id }} as unknown as RouteConfig;
          case 'user': 
            return { name: 'profileShow', params: { id: notification.subject.id }} as unknown as RouteConfig;
          default:
            return null;
        }
      },
      getNotificationMessage(notification: Notification): string | null {
        let { count, subjectType, subject } = notification;
        let getContent = (subject: any) => {
          switch(subjectType) {
            case 'comment': return subject.content;
            case 'video': return subject.name;
            case 'user': return subject.nick;
          }
        };
        let content = getContent(subject);
        if (!content) {
          return null;
        }
        let contentExcerpt = content.length > 20 ? (content.substring(0, 20) + '...') : content;

        switch (notification.action) {
          case 'like':
            return `${count} users have liked your ${subjectType}: "${contentExcerpt}"`;
          case 'dislike':
            return `${count} users have disliked your ${subjectType}: "${contentExcerpt}"`;
          case 'report':
            return `${count} users have reported your ${subjectType}: "${contentExcerpt}"`;
          case 'subscribe':
            return `${count} users have subscribed your channel`;
          case 'comment_create':
            return `${count} users have commented your ${subjectType}: "${contentExcerpt}"`;
          case 'video_create':
            return `${count} new videos from channel "${(subject as any).nick}"`;
          default:
            return null;
        }
      },
      setNotifications(notifications: any[]): void {
        this.notifications = notifications as Notification[];
      },
    },
  });
</script>