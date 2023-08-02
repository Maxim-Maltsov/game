import './bootstrap';
import Alpine from 'alpinejs';

import {createApp} from 'vue';
import GameField from './componenets/GameField.vue';

window.Alpine = Alpine;

Alpine.start();

let app = createApp({});

app.component('game-field', GameField);
app.mount('#app');


