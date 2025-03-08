import './bootstrap';
// import Alpine from 'alpinejs';
import { createApp } from 'vue';
import MemberSelector from './components/MemberSelector.vue';
import ItineraryForm from './components/ItineraryForm.vue';
import ExpenseForm from './components/ExpenseForm.vue';

// Alpine.js
// window.Alpine = Alpine;
// Alpine.start();

// Vue.js - 単一のアプリケーションインスタンスを作成
document.addEventListener('DOMContentLoaded', () => {
    // Vue.jsのコンポーネントが必要な要素を検索
    const vueElements = document.querySelectorAll('.vue-itinerary-form, .vue-member-selector, .vue-expense-form');

    if (vueElements.length > 0) {
        // 単一のVueアプリケーションインスタンスを作成
        const app = createApp({});

        // すべてのコンポーネントを登録
        app.component('member-selector', MemberSelector);
        app.component('itinerary-form', ItineraryForm);
        app.component('expense-form', ExpenseForm);

        // 各要素にマウント
        vueElements.forEach(el => {
            // 既にマウントされている場合はスキップ
            if (!el.__vue_app__) {
                app.mount(el);
            }
        });
    }
});
