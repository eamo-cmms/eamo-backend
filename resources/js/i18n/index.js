import { createI18n } from 'vue-i18n';
import vi from './locales/vi.json';
import en from './locales/en.json';

const savedLocale = localStorage.getItem('locale') || 'en';

const i18n = createI18n({
    legacy: false, // Composition API mode
    locale: savedLocale,
    fallbackLocale: 'en',
    messages: {
        vi,
        en,
    },
});

export function setLocale(locale) {
    i18n.global.locale.value = locale;
    localStorage.setItem('locale', locale);
}

export default i18n;
