import './bootstrap';
import { createApp } from 'vue';

// Import Vue components
import ExampleComponent from './components/ExampleComponent.vue';

// Create Vue app and mount it
const app = createApp({});

// Register global components
app.component('example-component', ExampleComponent);

// Mount the app to the #app element in the layout
app.mount('#app');
