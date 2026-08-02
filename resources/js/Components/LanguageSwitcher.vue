<script setup>
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const toggleLanguage = () => {
    const nextLocale = locale.value === 'vi' ? 'en' : 'vi';
    locale.value = nextLocale;
    localStorage.setItem('locale', nextLocale);
    window.dispatchEvent(new CustomEvent('locale-changed', { detail: nextLocale }));
};
</script>

<template>
    <button
        type="button"
        @click="toggleLanguage"
        class="flex h-10 px-3 cursor-pointer items-center justify-center gap-1.5 rounded-full border border-slate-200 dark:border-slate-700/60 bg-slate-100/80 dark:bg-slate-800/80 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all duration-200 hover:bg-slate-200 dark:hover:bg-slate-700 shadow-sm outline-none"
        :title="locale === 'vi' ? 'Chuyển sang Tiếng Anh' : 'Switch to Vietnamese'"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600 dark:text-blue-400">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
            <path d="M2 12h20"/>
        </svg>
        <span class="uppercase tracking-wider font-extrabold">{{ locale }}</span>
    </button>
</template>
