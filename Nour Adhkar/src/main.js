import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import { BASE_API_URL } from './config'
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import FontAwesomeIcon from './plugins/fontawesome'


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
});

// Make BASE_API_URL available globally
app.config.globalProperties.$BASE_API_URL = BASE_API_URL

// If you're using Composition API, you can also create a global composable
app.provide('BASE_API_URL', BASE_API_URL)

// Register Font Awesome component globally
app.component('font-awesome-icon', FontAwesomeIcon);

app.mount('#app')
