import { ref } from 'vue'
import axios from 'axios'
import iziToast from 'izitoast'
import { API_BASE_URL } from '/src/config.js'

export function useHomeWeb() {
  const metrics = ref({
    pending_quotes: 0,
    active_promotions: 0,
    daily_itineraries: 0,
    attended_leads: 0,
    server_time: '',
  })

  const charts = ref({
    top_destinations: [],
    quote_status: [],
    monthly_demand: [],
  })

  const loading = ref(false)
  const error = ref(null)

  const today = new Date()
  const lastMonth = new Date()
  lastMonth.setDate(today.getDate() - 30)

  const startDate = ref(lastMonth.toISOString().split('T')[0])
  const endDate = ref(today.toISOString().split('T')[0])

  const fetchHomeData = async () => {
    loading.value = true
    error.value = null

    try {
      const url =
        `${API_BASE_URL}Admin/GetWebMetrics.php` +
        `?start_date=${startDate.value}` +
        `&end_date=${endDate.value}`

      const response = await axios.get(url)

      if (response.data && (response.data.status === true || response.data.status === 200)) {
        const result = response.data.data

        metrics.value = {
          pending_quotes: result.metrics?.pending_quotes || 0,

          active_promotions: result.metrics?.active_promotions || 0,

          daily_itineraries: result.metrics?.daily_itineraries || 0,

          attended_leads: result.metrics?.attended_leads || 0,

          server_time: result.metrics?.server_time || '--:--',
        }

        charts.value = {
          top_destinations: result.charts?.top_destinations || [],

          quote_status: result.charts?.quote_status || [],

          monthly_demand: result.charts?.monthly_demand || [],
        }
      } else {
        throw new Error(response.data?.message || 'Respuesta inválida del servidor')
      }
    } catch (err) {
      console.error(err)

      error.value = 'No se pudieron cargar las métricas del dashboard.'

      iziToast.error({
        title: 'Error Dashboard',
        message: error.value,
        position: 'topRight',
      })
    } finally {
      loading.value = false
    }
  }

  return {
    metrics,
    charts,
    loading,
    error,
    fetchHomeData,
  }
}
