import './bootstrap';
import Alpine from 'alpinejs';
import { createApp } from 'vue';
import MemberSelector from './components/MemberSelector.vue';
import ItineraryForm from './components/ItineraryForm.vue';

// Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Vue.js
document.addEventListener('DOMContentLoaded', () => {
    // Vue.jsのコンポーネントが必要な要素だけをマウント
    const memberSelectorElements = document.querySelectorAll('.vue-member-selector');
    if (memberSelectorElements.length > 0) {
        const memberSelectorApp = createApp({});
        memberSelectorApp.component('member-selector', MemberSelector);
        memberSelectorElements.forEach(el => {
            memberSelectorApp.mount(el);
        });
    }

    const itineraryFormElements = document.querySelectorAll('.vue-itinerary-form');
    if (itineraryFormElements.length > 0) {
        const itineraryFormApp = createApp({});
        itineraryFormApp.component('itinerary-form', ItineraryForm);
        itineraryFormElements.forEach(el => {
            itineraryFormApp.mount(el);
        });
    }
});
