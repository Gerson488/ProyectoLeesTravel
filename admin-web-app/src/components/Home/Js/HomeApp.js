import { ref } from 'vue'
import axios from 'axios'
import iziToast from 'izitoast'
import { API_BASE_URL } from '/src/config.js'

export function useHomeApp() {
  const metrics = ref({
    pending_posts: 0,
    total_passengers: 0,
    critical_health_alerts: 0,
    total_bookings: 0,
    server_time: '',
  })

  const loading = ref(false)
  const error = ref(null)

  const fetchHomeData = async () => {
    loading.value = true
    error.value = null

    try {
      const url = `${API_BASE_URL}Admin/GetAppMetrics.php`

      const response = await axios.get(url)

      if (response.data.status === 200) {
        metrics.value = response.data.data
      }
    } catch (err) {
      console.error(err)

      error.value = 'No se pudieron cargar las métricas.'

      iziToast.error({
        title: 'Dashboard APP',
        message: error.value,
        position: 'topRight',
      })
    } finally {
      loading.value = false
    }
  }

  return {
    metrics,
    loading,
    error,
    fetchHomeData,
  }
}
