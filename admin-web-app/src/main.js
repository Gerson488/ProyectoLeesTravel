import { createApp } from 'vue'
import { createPinia } from 'pinia'
import axios from 'axios'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'izitoast/dist/css/iziToast.min.css'
import './assets/main.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import iziToast from 'izitoast'
import { API_BASE_URL } from './config.js'

axios.defaults.baseURL = API_BASE_URL
axios.defaults.withCredentials = true

axios.interceptors.response.use(
  (response) => response,
  (error) => {
    return Promise.reject(error)
  },
)

import App from './App.vue'
import router from './router'

const app = createApp(App)
app.config.globalProperties.$toast = iziToast
app.config.globalProperties.$http = axios
app.use(createPinia())
app.use(router)
app.mount('#app')
