import './bootstrap';

import Alpine from 'alpinejs';
// import SideBar from './componenets/SideBar.vue';
// import OfferCard from './componenets/OfferCard.vue';
// import ResponseСard from './componenets/ResponseСard.vue';
// import PlayingField from './componenets/PlayingField.vue';

import GameField from './componenets/GameField.vue';

import {createApp} from 'vue';

window.Alpine = Alpine;

Alpine.start();


const app = createApp({});

// app.component('side-bar', SideBar);
// app.component('offer-card', OfferCard);
// app.component('response-card', ResponseСard);
// app.component('playing-field', PlayingField);

app.component('game-field', GameField);


app.mount('#app');
