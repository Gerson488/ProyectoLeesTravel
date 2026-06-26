import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import iziToast from 'izitoast'

export function useLeadLogic() {
  const leads = ref([])
  const loading = ref(false)
  const pendingCount = ref(0)
  const audioEnabled = ref(false)
  let pollingInterval = null

  const alertSound = new Audio('/sounds/bell-alert.mp3')

  const enableAudio = () => {
    audioEnabled.value = true
    alertSound
      .play()
      .then(() => {
        alertSound.pause()
        alertSound.currentTime = 0
        iziToast.success({
          title: 'Éxito',
          message: 'Alertas sonoras activadas',
          position: 'topRight',
        })
      })
      .catch((err) => console.error('Error activando audio:', err))
  }

  const fetchLeads = async () => {
    loading.value = true
    try {
      const response = await axios.get('Quotes/GetAllQuotes.php')
      const res = response.data
      if (res && res.status === 200) {
        leads.value = res.data || []
      }
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'No se pudo cargar la bandeja de entrada',
        position: 'topRight',
      })
    } finally {
      loading.value = false
    }
  }

  const checkPendingCount = async () => {
    try {
      const response = await axios.get('Quotes/GetPendingCount.php')
      const res = response.data

      if (res && res.data) {
        const newCount = res.data.count

        if (newCount > pendingCount.value && pendingCount.value !== 0) {
          iziToast.info({
            title: '¡Nuevo Prospecto!',
            message: 'Alguien acaba de solicitar una cotización.',
            position: 'topRight',
            timeout: 5000,
            icon: 'bi bi-person-bounding-box',
          })

          if (audioEnabled.value) {
            alertSound.play().catch((e) => console.log('Audio bloqueado', e))
          }

          fetchLeads()
        }

        pendingCount.value = newCount
      }
    } catch{
      ;
    }
  }

  const updateStatus = async (idQuote, newStatus) => {
    try {
      const response = await axios.post('Quotes/UpdateQuoteStatus.php', {
        idQuote: idQuote,
        status: newStatus,
      })
      const res = response.data

      if (res && res.status === 200) {
        Swal.fire({
          title: '¡Excelente!',
          text: 'El prospecto ha sido marcado como Atendido.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        })
        fetchLeads()
        checkPendingCount()
      } else {
        Swal.fire('Aviso', res.message, 'warning')
      }
    } catch {
      Swal.fire('Error', 'No se pudo actualizar el estado', 'error')
    }
  }

  onMounted(() => {
    fetchLeads()
    checkPendingCount()
    pollingInterval = setInterval(checkPendingCount, 30000)
  })

  onUnmounted(() => {
    if (pollingInterval) {
      clearInterval(pollingInterval)
    }
  })

  return {
    leads,
    loading,
    pendingCount,
    audioEnabled,
    enableAudio,
    fetchLeads,
    updateStatus,
  }
}
