import { ref, computed } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const API_PATH = 'Passengers/'

export function useAttendanceLogic() {
  const passengers = ref([])
  const trips = ref([])
  const loading = ref(false)
  const isActionLoading = ref(false)
  const selectedTripId = ref('')
  const searchTerm = ref('')
  const statusFilter = ref('Todos')

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

  const fetchPassengersByTrip = async () => {
    if (!selectedTripId.value) {
      passengers.value = []
      return
    }

    loading.value = true
    try {
      const response = await axios.post(`${API_PATH}GetPassengerByTrip.php`, {
        Id_Trip: selectedTripId.value,
      })
      const res = response.data

      if (res && res.status === 200) {
        passengers.value = res.data || []
      } else {
        passengers.value = []
        if (res.status !== 202) {
          Swal.fire('Aviso', res.message || 'Sin pasajeros registrados en este viaje', 'warning')
        }
      }
    } catch {
      Swal.fire('Error', 'No se pudo sincronizar el manifiesto de pasajeros.', 'error')
    } finally {
      loading.value = false
    }
  }

  const updateBoardingStatus = async (idPassenger, newStatus) => {
    isActionLoading.value = true
    try {
      const response = await axios.post(`${API_PATH}UpdateBoardingStatus.php`, {
        idPassenger: idPassenger,
        boardingStatus: newStatus
      })
      const res = response.data

      if (res && res.status === 200) {
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true,
        })
        Toast.fire({
          icon: 'success',
          title: `Pasajero actualizado a: ${newStatus}`
        })
        await fetchPassengersByTrip()
      } else {
        Swal.fire('Validación', res.message || 'No se pudo actualizar el estado.', 'warning')
      }
    } catch {
      Swal.fire('Error', 'Fallo de red al procesar el control de abordaje.', 'error')
    } finally {
      isActionLoading.value = false
    }
  }

  const filterByTrip = () => {
    fetchPassengersByTrip()
  }

  const filteredPassengers = computed(() => {
    if (!selectedTripId.value) return []

    return passengers.value.filter((p) => {
      const q = searchTerm.value.toLowerCase().trim()
      const fullName = `${p.First_Name} ${p.Last_Name}`.toLowerCase()
      const dni = p.Id_Card_Passport?.toString().toLowerCase() || ''
      const cabin = p.Cabin_Number?.toLowerCase() || ''
      
      const matchesSearch = q === '' || 
                            fullName.includes(q) || 
                            dni.includes(q) || 
                            cabin.includes(q)

      const matchesStatus = statusFilter.value === 'Todos' || p.Boarding_Status === statusFilter.value

      return matchesSearch && matchesStatus
    })
  })

  const totalPassengers = computed(() => filteredPassengers.value.length)
  const totalAbordados = computed(() => passengers.value.filter(p => p.Boarding_Status === 'Abordado').length)
  const totalPorAbordar = computed(() => passengers.value.filter(p => p.Boarding_Status === 'Por Abordar').length)
  const totalNoPresentes = computed(() => passengers.value.filter(p => p.Boarding_Status === 'No Se Presentó').length)

  return {
    passengers,
    trips,
    loading,
    isActionLoading,
    selectedTripId,
    searchTerm,
    statusFilter,
    filteredPassengers,
    totalPassengers,
    totalAbordados,
    totalPorAbordar,
    totalNoPresentes,
    fetchTripsList,
    updateBoardingStatus,
    filterByTrip
  }
}