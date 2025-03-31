import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import { BASE_API_URL } from './config'


const app = createApp(App)

app.use(router)
app.use(store)

// Make BASE_API_URL available globally
app.config.globalProperties.$BASE_API_URL = BASE_API_URL

// If you're using Composition API, you can also create a global composable
app.provide('BASE_API_URL', BASE_API_URL)

app.mount('#app')
