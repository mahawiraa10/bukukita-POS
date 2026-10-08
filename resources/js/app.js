import './bootstrap';
import { createApp } from 'vue';
import MasterHierarki from './components/MasterHierarki.vue';

const appElement = document.getElementById('master-hierarki-app');
if (appElement) {
    const app = createApp(MasterHierarki);
    app.mount('#master-hierarki-app');
}
