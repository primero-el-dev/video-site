import Vue from 'vue';
import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';
import User from './entity/user';
import { getCookieValue } from './common';

const sessionExpiryCookieName = 'SESSION-EXPIRY';

const rememberMeExpiryCookieName = 'REMEMBER-ME-EXPIRY';

export const useAppStore = defineStore('appStore', {
  state: () => ({
    sessionExpiry: useStorage('SESSION-EXPIRY', 0),
  }),
  actions: {
    isLogged(): boolean {
      // return (window.localStorage.getItem('session-expiry') || 0) as number >= (new Date().getTime() / 1000);
      return getSessionExpiry() >= (new Date().getTime() / 1000);
      // return this.sessionExpiry >= (new Date().getTime() / 1000);
    },
    checkUpdateIsLogged(): void {
      this.sessionExpiry = getSessionExpiry();
    },
    setCurrentUser(user: User | null): void {
      if (user) {
        window.localStorage.setItem('user', JSON.stringify(user));
      } else {
        window.localStorage.removeItem('user');
      }
    },
    getCurrentUser(): User | null {
      if (!this.isLogged()) {
        return null;
      }
      let userJson = window.localStorage.getItem('user');
      if (userJson) {
        return JSON.parse(userJson) as User;
      }
      return null;
    },
  },
});

export function isLogged(): boolean {
  return getSessionExpiry() >= (new Date().getTime() / 1000);
}

export function getSessionExpiry(): number {
  return Math.max(
    getCookieValue(sessionExpiryCookieName) as unknown as number, 
    getCookieValue(rememberMeExpiryCookieName) as unknown as number
  );
}

type AlertType = 'success' | 'danger';

type Alert = {
  id: number, 
  type: AlertType, 
  message: string,
}

export const useAlertStore = defineStore('alertStore', {
  state: () => ({
    alerts: [] as Alert[],
    alertId: 0,
  }),
  actions: {
    getAllAndTimeoutDelete(seconds: number): Alert[] {
      let ids = this.alerts.map(a => a.id);
      window.setTimeout(() => {
        this.alerts = this.alerts.filter(a => !ids.includes(a.id));
      }, seconds * 1000);

      return this.alerts;
    },
    add(type: AlertType, message: string): void {
      this.alerts.push({ id: ++this.alertId, type: type, message: message });
    },
    addSuccess(message: string): void {
      this.add('success', message);
    },
    addDanger(message: string): void {
      this.add('danger', message);
    },
  },
});