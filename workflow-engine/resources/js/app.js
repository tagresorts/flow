import './bootstrap';

import { createApp } from 'vue';

import AuthenticatedLayout from './Layouts/AuthenticatedLayout.vue';
import Dashboard from './components/Dashboard.vue';
import RequestForm from './components/RequestForm.vue';
import Sidebar from './components/Sidebar.vue';

const app = createApp({});

app.component('authenticated-layout', AuthenticatedLayout);
app.component('dashboard', Dashboard);
app.component('request-form', RequestForm);
app.component('sidebar', Sidebar);

app.mount('#app');
