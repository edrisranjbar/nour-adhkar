import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import store from './store'
import { BASE_API_URL } from './config'
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import FontAwesomeIcon from './plugins/fontawesome'
import axios from 'axios';

// Import CSS
import './assets/main.css'
import './assets/css/dark-mode.css'

// Import additional FontAwesome icons
import { library } from '@fortawesome/fontawesome-svg-core'
import { faMinus, faPlus, faChevronLeft } from '@fortawesome/free-solid-svg-icons'

// Add icons to the library
library.add(faMinus, faPlus, faChevronLeft)

const app = createApp(App)

app.use(router)
app.use(store)
app.use(Toast, {
    position: "top-center",
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    rtl: true,
    bodyClassName: "vazirmatn-font",
});

// Make BASE_API_URL available globally
app.config.globalProperties.$BASE_API_URL = BASE_API_URL

// If you're using Composition API, you can also create a global composable
app.provide('BASE_API_URL', BASE_API_URL)

// Register Font Awesome component globally
app.component('font-awesome-icon', FontAwesomeIcon);

// Add this to your main.js or a separate axios config file
axios.interceptors.request.use(
    config => {
        const token = store.state.token;
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
            config.headers['Accept'] = 'application/json';
            config.headers['Content-Type'] = 'application/json';
        }
        return config;
    },
    error => {
        return Promise.reject(error);
    }
);

axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            store.commit('clearUser');
            router.push('/login');
        }
        return Promise.reject(error);
    }
);

app.use(createPinia())

app.mount('#app')
