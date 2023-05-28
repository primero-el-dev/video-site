import VueRouter, { RouteConfig, Route, NavigationGuardNext, RawLocation } from 'vue-router';
import HomePage from './components/pages/home-page.vue';
import AboutPage from './components/pages/about-page.vue';
import LoginPage from './components/pages/login-page.vue';
import RegistrationPage from './components/pages/registration-page.vue';
import RegistrationConfirmResendPage from './components/pages/registration-confirm-resend-page.vue';
import ResetPasswordRequestPage from './components/pages/reset-password-request-page.vue';
import ResetPasswordResetPage from './components/pages/reset-password-reset-page.vue';
import ProfileIndexPage from './components/pages/profile/index-page.vue';
import ProfileResetPasswordPage from './components/pages/profile/reset-password-page.vue';
import VideoCreatePage from './components/pages/video/create-page.vue';
import VideoShowPage from './components/pages/video/show-page.vue';
import VideoEditPage from './components/pages/video/edit-page.vue';
import NotificationIndexPage from './components/pages/notification/index-page.vue';
import { isLogged, useAlertStore } from './state';
import { requestApi } from './api';

export type RouteParams = { [key: string]: string | number | undefined };

export interface BackendRoute {
  path: string,
  method: string,
  params?: RouteParams,
};

export interface BackendRouteMap {
  [key: string]: BackendRoute
}

export const autheticatedGuard = (to: Route, from: Route, next: NavigationGuardNext<Vue>): void => {
  isLogged() ? next() : next({ name: 'login' });
}

export const anonymousGuard = (to: Route, from: Route, next: NavigationGuardNext<Vue>): void => {
  isLogged() ? next({ name: 'home' }) : next();
}

export const backendRoutes: BackendRouteMap = {
  keepAlive: { path: '/api/keep-alive', method: 'GET' },
  login: { path: '/api/login', method: 'POST' },
  logout: { path: '/api/logout', method: 'POST' },
  registration: { path: '/api/registration', method: 'POST' },
  registrationConfirmResend: { path: '/api/registration/confirm/resend', method: 'POST' },
  resetPasswordRequest: { path: '/api/reset-password/request', method: 'POST' },
  resetPasswordReset: { path: '/api/reset-password/reset', method: 'POST' },
  videoIndex: { path: '/api/video/', method: 'GET' },
  videoShow: { path: '/api/video/{id}', method: 'GET' },
  videoCreate: { path: '/api/video/', method: 'POST' },
  videoUpdate: { path: '/api/video/{id}/update', method: 'POST' },
  videoDelete: { path: '/api/video/{id}', method: 'DELETE' },
  videoRate: { path: '/api/video/{id}/{rate}', method: 'POST' },
  videoReport: { path: '/api/video/{id}/report', method: 'POST' },
  videoReportDelete: { path: '/api/video/{id}/report', method: 'DELETE' },
  tagIndex: { path: '/api/tag/', method: 'GET' },
  commentIndex: { path: '/api/comment/', method: 'GET' },
  commentCreate: { path: '/api/comment/', method: 'POST' },
  commentUpdate: { path: '/api/comment/{id}', method: 'PUT' },
  commentDelete: { path: '/api/comment/{id}', method: 'DELETE' },
  commentRate: { path: '/api/comment/{id}/{rate}', method: 'POST' },
  commentReport: { path: '/api/comment/{id}/report', method: 'POST' },
  commentReportDelete: { path: '/api/comment/{id}/report', method: 'DELETE' },
  userGet: { path: '/api/user/{id}', method: 'GET' },
  notificationSeeAll: { path: '/api/notification/see', method: 'POST' },
  notificationSee: { path: '/api/notification/{id}/see', method: 'POST' },
};

export const frontendRoutes: RouteConfig[] = [
  { name: 'home', path: '/', component: HomePage },
  { name: 'about', path: '/about', component: AboutPage },
  { name: 'login', path: '/login', component: LoginPage, beforeEnter: anonymousGuard },
  { name: 'registration', path: '/registration', component: RegistrationPage, beforeEnter: anonymousGuard },
  { name: 'registrationConfirmResend', path: '/registration/confirm/resend', component: RegistrationConfirmResendPage, beforeEnter: anonymousGuard },
  { name: 'resetPasswordRequest', path: '/reset-password/request', component: ResetPasswordRequestPage, beforeEnter: anonymousGuard },
  { name: 'resetPasswordReset', path: '/reset-password/reset', component: ResetPasswordResetPage, beforeEnter: anonymousGuard },
  { name: 'profileIndex', path: '/profile/', component: ProfileIndexPage, beforeEnter: autheticatedGuard },
  { name: 'profileShow', path: '/profile/:id', component: VideoCreatePage, beforeEnter: autheticatedGuard, props: true },
  { name: 'profileResetPassword', path: '/profile/reset-password', component: ProfileResetPasswordPage, beforeEnter: autheticatedGuard },
  { name: 'videoCreate', path: '/video/create', component: VideoCreatePage, beforeEnter: autheticatedGuard },
  { name: 'videoShow', path: '/video/:id', component: VideoShowPage, props: true },
  { name: 'videoEdit', path: '/video/:id/edit', component: VideoEditPage, beforeEnter: autheticatedGuard, props: true },
  { name: 'notificationIndex', path: '/notification/', component: NotificationIndexPage, beforeEnter: autheticatedGuard },
];

export function fetchBeforeRender<T>(
  routeName: string, 
  propName: string,
  neededPermission?: string|null, 
  params?: string[], 
  failureRedirect: RawLocation = { name: 'home' }
) {
  return (to: any, from: any, next: any): void => {
    let queryParams = {} as { [key: string]: any };
    if (params === undefined) {
      queryParams = to.params;
    } else {
      for (let p of params) {
        queryParams[p] = to.params[p];
      }
    }

    let route = getBackendRoute(routeName, queryParams);

    requestApi(route)
      .then(response => {
        let data = response!.data!.data;
        if (neededPermission && data?.permissions && !data?.permissions.includes(neededPermission)) {
          useAlertStore().addDanger('Forbidden.');
          router.push(failureRedirect);
        }
        next((vm: any) => {
          vm[propName] = data as T;
        });
      })
      .catch(error => {
        useAlertStore().addDanger(error.message);
        router.push(failureRedirect);
      });
  }
}

export function getBackendRoute(name: string, params?: RouteParams): BackendRoute {
  let route = { ...backendRoutes[name] };
  if (!route) {
    throw `Missing route with name '${name}'.`;
  }

  if (route.params === undefined) {
    route.params = {};
  }
  for (let key in params) {
    route.params[key] = params[key];
  }

  return route;
}

export function constructBackendUri(route: BackendRoute, params?: RouteParams): string {
  let uri = route.path;
  params = { ...route.params, ...params };

  if (JSON.stringify(params) === '{}') {
    return uri;
  }

  let queryParams = [] as string[];
  for (let key in params) {
    if (params[key] !== undefined) {
      if (uri.includes('{'+key+'}')) {
        uri = uri.replace('{'+key+'}', params[key] as string);
      } else {
        queryParams.push(key + '=' + params[key] as string);
      }
    }
  }

  if (queryParams.length) {
    uri += '?' + queryParams.join('&');
  }

  return uri;
}

const router = new VueRouter({
  mode: 'history',
  routes: frontendRoutes,
});

// For use case see router.beforeEach
let lastKeepAliveRequest = 0;

router.beforeEach(async (to: Route, from: Route, next: NavigationGuardNext<Vue>): Promise<void> => {
  // Check last request date because CSRF tokens are one use only and we try to avoid sending terminated token
  if (isLogged() && (lastKeepAliveRequest + 2000) < new Date().getTime()) {
    requestApi(backendRoutes['keepAlive']).then(_ => {}).catch(_ => {});
    lastKeepAliveRequest = new Date().getTime();
  }
  
  next();
});

export default router;