import { ref, computed } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const API_PATH = 'History/' 

export function useHistoryLogic() {
  const historyList = ref([])
  const trips = ref([])
  const loading = ref(false)
  const isActionLoading = ref(false)
  const selectedTripId = ref('')
  const searchTerm = ref('')
  const categoryFilter = ref('Todos')
  const fetchTripsList = async () => {
    try {
      const response = await axios.get('Trips/GetAllTrips.php')
      const res = response.data
      if (res && res.status === 200) {
        trips.value = res.data || []
      }
    } catch {
      Swal.fire('Error', 'Fallo al obtener la lista de cruceros.', 'error')
    }
  }

  const fetchHistoryByTrip = async () => {
    if (!selectedTripId.value) {
      historyList.value = []
      return
    }

    loading.value = true
    try {
      const response = await axios.post(`${API_PATH}GetHistoryByTrip.php`, {
        idTrip: selectedTripId.value,
      })
      const res = response.data

      if (res && res.status === 200) {
        historyList.value = res.data || []
      } else {
        historyList.value = []
        if (res.status !== 202) {
          Swal.fire('Aviso', res.message || 'Sin registros en esta bitácora', 'info')
        }
      }
    } catch {
      Swal.fire('Error', 'No se pudo sincronizar la bitácora.', 'error')
    } finally {
      loading.value = false
    }
  }

  const registerHistoryEntry = async (entryData) => {
    isActionLoading.value = true
    try {
      const response = await axios.post(`${API_PATH}RegisterHistory.php`, entryData)
      const res = response.data

      if (res && res.status === 200) {
        Swal.fire('Éxito', 'Entrada agregada a la bitácora', 'success')
        await fetchHistoryByTrip() 
      } else {
        Swal.fire('Validación', res.message || 'No se pudo registrar.', 'warning')
      }
    } catch {
      Swal.fire('Error', 'Error al conectar con el servidor.', 'error')
    } finally {
      isActionLoading.value = false
    }
  }

  const filterByTrip = () => {
    fetchHistoryByTrip()
  }

  const filteredHistory = computed(() => {
    if (!selectedTripId.value) return []

    return historyList.value.filter((h) => {
      const q = searchTerm.value.toLowerCase().trim()
      const desc = h.description?.toLowerCase() || ''
      const reporter = h.reporter_name?.toLowerCase() || ''
      
      const matchesSearch = q === '' || desc.includes(q) || reporter.includes(q)
      const matchesCategory = categoryFilter.value === 'Todos' || h.category === categoryFilter.value

      return matchesSearch && matchesCategory
    })
  })

  const totalRecords = computed(() => filteredHistory.value.length)
  const totalMedical = computed(() => historyList.value.filter(h => h.category === 'Médico').length)
  const totalTechnical = computed(() => historyList.value.filter(h => h.category === 'Técnico').length)

  return {
    historyList,
    trips,
    loading,
    isActionLoading,
    selectedTripId,
    searchTerm,
    categoryFilter,
    filteredHistory,
    totalRecords,
    totalMedical,
    totalTechnical,
    fetchTripsList,
    registerHistoryEntry,
    filterByTrip
  }
}