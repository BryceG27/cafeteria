import './bootstrap';
import '../css/app.css';
import '../js/assets/scss/main.scss';
import { createPinia } from "pinia";

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import PrimeVue from 'primevue/config';

import BaseBlock from "@/Components/BaseBlock.vue";
import BaseBackground from "@/Components/BaseBackground.vue";
import BasePageHeading from "@/Components/BasePageHeading.vue";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(createPinia())
            .use(PrimeVue, {
                theme : {
                    preset : 'lara',
                }
            })
            .component("BaseBlock", BaseBlock)
            .component("BaseBackground", BaseBackground)
            .component("BasePageHeading", BasePageHeading)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;