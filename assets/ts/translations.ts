import VueI18n from 'vue-i18n';
import en from '../locales/en.json';

export default () => new VueI18n({
  locale: 'en',
  fallbackLocale: 'en',
  messages: {
    en,
  },
});