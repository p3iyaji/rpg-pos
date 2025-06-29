import './index.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './axios.js'
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import { useAuthStore } from './stores/authStore'




const app = createApp(App);
const pinia = createPinia();

pinia.use(piniaPluginPersistedstate)

app.use(pinia)
app.use(router)
const authStore = useAuthStore();

authStore.setupAxiosInterceptors()


app.mount('#app')
