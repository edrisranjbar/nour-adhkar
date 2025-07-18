import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import store from './store'
import { BASE_API_URL } from './config'
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import FontAwesomeIcon from './plugins/fontawesome'
import metaPlugin from './plugins/meta'
import validationPlugin from './plugins/validation'
import axios from 'axios';

// Set axios base URL
axios.defaults.baseURL = BASE_API_URL;

// Import CSS
import './assets/main.css'
import './assets/css/fonts.css'
import './assets/css/style.css'
import './assets/css/dark-mode.css'

const app = createApp(App)

app.use(createPinia())

app.use(router)
app.use(store)
app.use(validationPlugin)
app.use(Toast, {
    position: "top-center",
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    rtl: true,
    bodyClassName: "vazirmatn-font",
});
app.use(metaPlugin)

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
    async error => {
        const originalRequest = error.config;

        if (
            error.response?.status === 401 &&
            !originalRequest._retry &&
            !originalRequest.url.includes('/auth/refresh')
        ) {
            originalRequest._retry = true;

            try {
                const refreshed = await store.dispatch('refreshToken');
                if (refreshed) {
                    const token = store.state.token;
                    originalRequest.headers['Authorization'] = `Bearer ${token}`;
                    return axios(originalRequest);
                } else {
                    store.commit('clearAuth');
                    await router.push('/login');
                }
            } catch (refreshError) {
                store.commit('clearAuth');
                await router.push('/login');
                return Promise.reject(refreshError);
            }
        }

        return Promise.reject(error);
    }
);


app.mount('#app')
