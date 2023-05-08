/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';
import 'bootstrap';

import Vue from 'vue';
import VueMeta from 'vue-meta'
import VueRouter from 'vue-router';
import VueI18n from 'vue-i18n';
import { createPinia, PiniaVuePlugin } from 'pinia';
import axios from 'axios';

Vue.use(VueMeta)
Vue.use(VueRouter);
Vue.use(VueI18n);
Vue.use(PiniaVuePlugin);

import App from './ts/components/app.vue';
import router from './ts/router';
import i18n from './ts/translations';

axios.defaults.withCredentials = true
axios.defaults.headers.post = { 'Content-Type': 'application/json' }
axios.defaults.headers.put = { 'Content-Type': 'application/json' }

const pinia = createPinia();

function clone(obj: any): any {
  if (typeof obj === 'object') {
    let newObj = {} as any;
    for (let i in obj) {
      newObj[i as any] = clone(obj[i as any]);
    }
    return newObj;
  } else {
    return obj;
  }
}

const app = new Vue({
  pinia, //@ts-ignore 
  el: '#app',
  template: '<App/>',
  components: { App },
  i18n: i18n(),
  router, //@ts-ignore 
});