import './bootstrap';

import Alpine from 'alpinejs';
import Vue from 'vue';

window.Alpine = Alpine;

Alpine.start();


const app = Vue.createApp({});

app.mount('#app')
