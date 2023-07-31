import './bootstrap';
import Alpine from 'alpinejs';

import {createApp} from 'vue';
import GameField from './componenets/GameField.vue';

window.Alpine = Alpine;

Alpine.start();

const app = createApp({});

app.mount('#app');
app.component('game-field', GameField);