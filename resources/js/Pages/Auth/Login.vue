<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { theme } from 'ant-design-vue';
import SloganIcon from '@/Components/SloganIcon.vue';
import 'ant-design-vue/dist/reset.css';

import {
    ConfigProvider as AConfigProvider,
    Form as AForm,
    FormItem as AFormItem,
    Input as AInput,
    InputPassword as AInputPassword,
    Button as AButton
} from 'ant-design-vue';

defineProps({
    status: {
        type: String,
    },
});


const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

// Dark Mode State
const isDark = ref(false);

const tokenTheme = computed(() => ({
    algorithm: isDark.value ? theme.darkAlgorithm : theme.defaultAlgorithm,
}));

// SVG icon colors - bound directly to avoid Tailwind SVG class issues
const iconColor = computed(() => isDark.value ? '#cbd5e1' : '#475569');

function applyTheme(dark) {
    isDark.value = dark;
    if (dark) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
}

// Smooth view-transition ripple toggle — identical to vben's theme-button.vue
function toggleTheme(event) {
    const isAppearanceTransition =
        typeof document.startViewTransition === 'function' &&
        !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!isAppearanceTransition || !event) {
        applyTheme(!isDark.value);
        return;
    }

    const x = event.clientX;
    const y = event.clientY;
    const endRadius = Math.hypot(
        Math.max(x, innerWidth - x),
        Math.max(y, innerHeight - y),
    );
    const nextDark = !isDark.value;

    const transition = document.startViewTransition(async () => {
        applyTheme(nextDark);
        await nextTick();
    });

    transition.ready.then(() => {
        const clipPath = [
            `circle(0px at ${x}px ${y}px)`,
            `circle(${endRadius}px at ${x}px ${y}px)`,
        ];
        document.documentElement.animate(
            { clipPath: nextDark ? [...clipPath].reverse() : clipPath },
            {
                duration: 450,
                easing: 'ease-in',
                pseudoElement: nextDark
                    ? '::view-transition-old(root)'
                    : '::view-transition-new(root)',
            },
        );
    });
}

onMounted(() => {
    // Default is light mode; only go dark if the user explicitly saved 'dark'
    const saved = localStorage.getItem('theme');
    applyTheme(saved === 'dark');
});
</script>

<template>
    <AConfigProvider :theme="tokenTheme">
        <Head title="Login">
            <link rel="icon" type="image/png" href="/favicon.png" />
        </Head>

        <!-- Root wrapper: dark class drives Tailwind dark: utilities -->
        <div :class="{ dark: isDark }">
            <div class="relative flex min-h-screen w-full overflow-hidden select-none bg-slate-50 text-slate-900 dark:bg-[#070709] dark:text-slate-100 transition-colors duration-300">

                <!-- ═══════════ LEFT — Slogan (flex-1 ≈ 60%) ═══════════ -->
                <div class="relative hidden lg:flex flex-1 flex-col items-center justify-center
                            bg-gradient-to-br from-blue-50 via-indigo-50 to-sky-100
                            dark:bg-none dark:bg-[#070709]
                            transition-colors duration-300">
                    <!-- Gradient blob background -->
                    <div class="login-background absolute inset-0"></div>
                    <!-- Center: SVG illustration + text -->
                    <div class="relative z-10 flex flex-col items-center px-10 text-center">
                        <SloganIcon class="h-72 w-auto max-w-xs animate-float" />
                        <p class="mt-10 text-2xl font-bold tracking-wide text-slate-800 dark:text-white">WELCOME TO EAMO</p>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Equipment Asset Management Solution</p>
                    </div>
                </div>

                <!-- ═══════════ RIGHT — Form (w-2/5 ≈ 40%) ═══════════ -->
                <div class="relative flex w-full lg:w-2/5 min-h-screen flex-col justify-between
                            bg-white dark:bg-[#0f0f11]
                            px-6 py-12 sm:px-12
                            border-l border-slate-100 dark:border-slate-800
                            transition-colors duration-300">

                    <!-- ── Theme toggle button (top-right) ── -->
                    <button
                        type="button"
                        :class="isDark ? 'is-light' : 'is-dark'"
                        class="theme-toggle absolute top-5 right-5 z-10 flex h-10 w-10 cursor-pointer items-center justify-center rounded-full border-none bg-transparent transition-colors duration-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                        @click="toggleTheme"
                    >
                        <svg aria-hidden="true" height="24" width="24" viewBox="0 0 24 24">
                            <mask id="theme-moon-mask" class="theme-toggle__moon">
                                <rect fill="white" height="100%" width="100%" x="0" y="0" />
                                <circle cx="40" cy="8" fill="black" r="11" />
                            </mask>
                            <circle
                                class="theme-toggle__sun"
                                cx="12" cy="12" r="11"
                                mask="url(#theme-moon-mask)"
                                :fill="iconColor"
                            />
                            <g class="theme-toggle__sun-beams" :stroke="iconColor" stroke-width="2">
                                <line x1="12" x2="12" y1="1"    y2="3"    />
                                <line x1="12" x2="12" y1="21"   y2="23"   />
                                <line x1="4.22"  x2="5.64"  y1="4.22"  y2="5.64"  />
                                <line x1="18.36" x2="19.78" y1="18.36" y2="19.78" />
                                <line x1="1"  x2="3"  y1="12" y2="12" />
                                <line x1="21" x2="23" y1="12" y2="12" />
                                <line x1="4.22"  x2="5.64"  y1="19.78" y2="18.36" />
                                <line x1="18.36" x2="19.78" y1="5.64"  y2="4.22"  />
                            </g>
                        </svg>
                    </button>

                    <!-- ── Form section (vertically centred) ── -->
                    <div class="my-auto w-full max-w-[360px] mx-auto">
                        <!-- Heading -->
                        <h2 class="text-2xl font-semibold text-slate-900 dark:text-white sm:text-3xl">Welcome Back</h2>
                        <!-- Flash status -->
                        <div v-if="status" class="mt-4 text-sm font-medium text-green-600">
                            {{ status }}
                        </div>

                        <!-- Error Alert -->
                        <div v-if="form.errors.email" class="mt-4 rounded-md bg-red-50 p-4 dark:bg-red-950/20 border border-red-200 dark:border-red-900/30">
                            <div class="flex">
                                <div class="shrink-0">
                                    <svg class="h-5 w-5 text-red-400 dark:text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                        {{ form.errors.email }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Ant Design Form -->
                        <AForm layout="vertical" :model="form" class="mt-8" @finish="submit">
                            <AFormItem
                                label="Email"
                                name="email"
                                :rules="[
                                    { required: true, message: 'Please enter email' },
                                    { type: 'email', message: 'Please enter a valid email address' }
                                ]"
                                :validate-status="form.errors.email ? 'error' : undefined"
                                :help="form.errors.email"
                            >
                                <AInput
                                    v-model:value="form.email"
                                    size="large"
                                    placeholder="Please enter email"
                                />
                            </AFormItem>

                            <AFormItem
                                label="Password"
                                name="password"
                                :rules="[{ required: true, message: 'Please enter password' }]"
                                :validate-status="form.errors.password ? 'error' : undefined"
                                :help="form.errors.password"
                            >
                                <AInputPassword
                                    v-model:value="form.password"
                                    size="large"
                                    placeholder="Please enter password"
                                />
                            </AFormItem>

                            <!-- Remember Me — left-aligned -->
                            <div class="mb-5 flex items-center">
                                <label class="flex cursor-pointer items-center gap-2 select-none text-sm text-slate-600 dark:text-slate-400">
                                    <input
                                        v-model="form.remember"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 text-blue-600 accent-blue-600 dark:border-slate-600 cursor-pointer"
                                    />
                                    Remember me
                                </label>
                            </div>

                            <AFormItem class="mb-0">
                                <AButton
                                    type="primary"
                                    html-type="submit"
                                    size="large"
                                    block
                                    :loading="form.processing"
                                >
                                    Login
                                </AButton>
                            </AFormItem>
                        </AForm>
                    </div>

                    <!-- Footer -->
                    <p class="text-center text-xs text-slate-400 dark:text-slate-600">
                        Copyright &copy; 2026 EAMO
                    </p>
                </div>

            </div>
        </div>
    </AConfigProvider>
</template>

<style scoped>
/* ── Background blob — different gradient for light vs dark ── */
.login-background {
    background: linear-gradient(
        154deg,
        rgba(99, 102, 241, 0.08) 30%,
        rgba(37, 99, 235, 0.18) 48%,
        rgba(99, 102, 241, 0.08) 64%
    );
    filter: blur(80px);
}

.dark .login-background {
    background: linear-gradient(
        154deg,
        #07070915 30%,
        rgba(37, 99, 235, 0.22) 48%,
        #07070915 64%
    );
    filter: blur(100px);
}

/* ── Float animation for slogan SVG ── */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-12px); }
}
.animate-float { animation: float 4s ease-in-out infinite; }

/* ══ Theme Toggle Button ══════════════════════════════════════════════ */
.theme-toggle__moon > circle {
    transition: transform 0.5s cubic-bezier(0, 0, 0.3, 1);
}

.theme-toggle__sun {
    stroke: none;
    transform-origin: center center;
    transition: transform 1.6s cubic-bezier(0.25, 0, 0.2, 1);
}

.theme-toggle__sun-beams {
    transform-origin: center center;
    transition:
        transform 0.6s cubic-bezier(0.5, 1.5, 0.75, 1.25),
        opacity   0.6s cubic-bezier(0.25, 0, 0.3, 1);
}

/* is-dark  → light mode active  → show full sun */
.theme-toggle.is-dark .theme-toggle__moon > circle {
    transform: translateX(-20px);
}
.theme-toggle.is-dark .theme-toggle__sun-beams {
    opacity: 1;
}

/* is-light → dark mode active   → collapse to crescent moon */
.theme-toggle.is-light .theme-toggle__sun {
    transform: scale(0.5);
}
.theme-toggle.is-light .theme-toggle__sun-beams {
    transform: rotateZ(0.25turn);
    opacity: 0;
}
</style>

<!-- Global style — needed for View Transition API ripple -->
<style>
::view-transition-old(root),
::view-transition-new(root) {
    animation: none;
    mix-blend-mode: normal;
}
</style>
