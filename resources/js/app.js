require('./bootstrap');
require('alpinejs');

import { createApp, h } from "vue";
import Alpine from 'alpinejs';
import Home from './components/Home.vue';
import Movie from './components/Movie.vue';
import Actor from './components/Actor.vue';
import Producer from './components/Producer.vue';
import Toaster from "@meforma/vue-toaster";
import Multiselect from '@vueform/multiselect';

window.Alpine = Alpine;

const app = createApp({
    components: {
        Home,
        Movie,
        Actor,
        Producer,
    }
});

app.component('multiselect', Multiselect);

app.use(Toaster).mount("#app");
Alpine.start();