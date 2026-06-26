import { ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import iziToast from 'izitoast'

export function useItineraryLogic() {
  const trips = ref([])
  const selectedTripId = ref('')
  const itineraryDays = ref([])

  const loading = ref(false)
  const isActionLoading = ref(false)
  const showModal = ref(false)

  const form = ref({
    idItinerary: null,
    idTrip: '',
    dayNumber: 1,
    portOfCall: '',
    arrivalTime: '',
    departureTime: '',
    activityDescription: '',
  })

  const fetchTrips = async () => {
    try {
      const response = await axios.get('Trips/GetAllTrips.php')
      const res = response.data
      if (res && res.status === 200) {
        trips.value = res.data || []
      }
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'No se pudieron cargar los cruceros',
        position: 'topRight',
      })
    }
  }

  const fetchItinerary = async () => {
    if (!selectedTripId.value) return

    loading.value = true
    try {
      const response = await axios.post('Itineraries/GetItineraryByTrip.php', {
        idTrip: selectedTripId.value,
      })
      const res = response.data

      if (res && res.status === 200) {
        itineraryDays.value = res.data || []
      } else {
        itineraryDays.value = []
      }
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'Fallo al cargar el itinerario',
        position: 'topRight',
      })
    } finally {
      loading.value = false
    }
  }

  const handleTripChange = () => {
    fetchItinerary()
  }

  const openModal = (day = null) => {
    if (!selectedTripId.value) {
      Swal.fire('Aviso', 'Primero selecciona un crucero', 'warning')
      return
    }

    if (day) {
      form.value = {
        idItinerary: day.Id_Itinerary,
        idTrip: day.Id_Trip,
        dayNumber: day.Day_Number,
        portOfCall: day.Port_of_Call,
        arrivalTime: day.Arrival_Time || '',
        departureTime: day.Departure_Time || '',
        activityDescription: day.Activity_Description || '',
      }
    } else {
      resetForm()
      form.value.idTrip = selectedTripId.value
      form.value.dayNumber = itineraryDays.value.length + 1
    }
    showModal.value = true
  }

  const closeModal = () => {
    showModal.value = false
    resetForm()
  }

  const resetForm = () => {
    form.value = {
      idItinerary: null,
      idTrip: '',
      dayNumber: 1,
      portOfCall: '',
      arrivalTime: '',
      departureTime: '',
      activityDescription: '',
    }
  }

  const saveActivity = async () => {
    isActionLoading.value = true
    const endpoint = form.value.idItinerary
      ? 'Itineraries/UpdateActivity.php'
      : 'Itineraries/RegisterItinerary.php'

    const payload = {
      ...form.value,
      arrivalTime: form.value.arrivalTime || null,
      departureTime: form.value.departureTime || null,
    }

    try {
      const response = await axios.post(endpoint, payload)
      const res = response.data

      if (res && res.status === 200) {
        Swal.fire('¡Éxito!', res.message, 'success')
        closeModal()
        fetchItinerary()
      } else {
        Swal.fire('Atención', res.message, 'warning')
      }
    } catch {
      Swal.fire('Error', 'Fallo de conexión al guardar', 'error')
    } finally {
      isActionLoading.value = false
    }
  }

  const deleteActivity = async (idItinerary) => {
    const result = await Swal.fire({
      title: '¿Eliminar este día?',
      text: 'Se borrará la actividad del itinerario.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminar',
    })

    if (result.isConfirmed) {
      try {
        const response = await axios.post('Itineraries/DeleteActivity.php', { idItinerary })
        const res = response.data
        if (res && res.status === 200) {
          Swal.fire('Eliminado', res.message, 'success')
          fetchItinerary()
        } else {
          Swal.fire('Aviso', res.message, 'warning')
        }
      } catch {
        Swal.fire('Error', 'No se pudo eliminar', 'error')
      }
    }
  }

  return {
    trips,
    selectedTripId,
    itineraryDays,
    loading,
    isActionLoading,
    showModal,
    form,
    fetchTrips,
    handleTripChange,
    openModal,
    closeModal,
    saveActivity,
    deleteActivity,
  }
}
