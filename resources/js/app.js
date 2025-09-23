import './bootstrap';
import '../css/app.css';
import '../js/assets/scss/main.scss';
import { createPinia } from "pinia";

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import Tooltip from 'primevue/tooltip';

import BaseBlock from "@/Components/BaseBlock.vue";
import BaseBackground from "@/Components/BaseBackground.vue";
import BasePageHeading from "@/Components/BasePageHeading.vue";

import SuccessMessage from "@/Components/SuccessMessage.vue";
import ErrorMessage from "@/Components/ErrorMessage.vue";

import ToastService from 'primevue/toastservice';

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
                theme: {
                    preset: Aura,
                    options: {
                        prefix: 'p',
                        darkModeSelector: '.my-app-dark',
                        cssLayer: false
                    }
                },
                locale : {
                    firstDayOfWeek: 1,
                    dayNamesMin : [
                        'Do',
                        'Lu',
                        'Ma',
                        'Me',
                        'Gi',
                        'Ve',
                        'Sa',
                    ],
                    dayNames: [
                        'Domenica',
                        'Lunedì',
                        'Martedì',
                        'Mercoledì',
                        'Giovedì',
                        'Venerdì',
                        'Sabato',
                    ],
                    dayNamesShort : [
                        'Dom',
                        'Lun',
                        'Mar',
                        'Mer',
                        'Gio',
                        'Ven',
                        'Sab',
                    ],
                    monthNames : [
                        'Gennaio',
                        'Febbraio',
                        'Marzo',
                        'Aprile',
                        'Maggio',
                        'Giugno',
                        'Luglio',
                        'Agosto',
                        'Settembre',
                        'Ottobre',
                        'Novembre',
                        'Dicembre'
                    ],
                    monthNamesShort : [
                        'Gen',
                        'Feb',
                        'Mar',
                        'Apr',
                        'Mag',
                        'Giu',
                        'Lug',
                        'Ago',
                        'Set',
                        'Ott',
                        'Nov',
                        'Dic'
                    ]
                }
            })
            .directive('tooltip', Tooltip)
            .use(ToastService)
            .component("BaseBlock", BaseBlock)
            .component("BaseBackground", BaseBackground)
            .component("BasePageHeading", BasePageHeading)
            .component("SuccessMessage", SuccessMessage)
            .component("ErrorMessage", ErrorMessage)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;