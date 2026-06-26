import { ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const API_PATH = 'Passengers/'

export function usePassengerLogic() {
  const passengers = ref([])
  const trips = ref([])
  const loading = ref(false)
  const isActionLoading = ref(false)
  const showEditModal = ref(false)
  const selectedTripId = ref('')

  const passengerForm = ref({
    idPassenger: null,
    idCard: '',
    fullName: '',
    cabinNumber: '',
    boardingStatus: 0,
    idTrip: '',
    idUser: null, 
    idBooking: 0, 
    specialAssistance: '',
  })

  const searchUserByDNI = async () => {
    if (!passengerForm.value.idCard || passengerForm.value.idCard.length < 3) return

    isActionLoading.value = true
    try {
      const response = await axios.post('Utils/CheckTraveler.php', { 
        dni: passengerForm.value.idCard 
      })
      const res = response.data

      if (res && (res.status === 200 || res.success)) {
        const data = res.data || res
        passengerForm.value.fullName = data.nombreCompleto || data.Full_Name
        passengerForm.value.idUser = data.idTraveler || data.Id_Traveler
      } else {
        passengerForm.value.fullName = ''
        passengerForm.value.idUser = null
        Swal.fire({
          title: 'Información',
          text: res.message || 'El documento no coincide con ningún viajero registrado.',
          icon: 'info',
          confirmButtonText: 'Aceptar'
        })
      }
    } catch{
      Swal.fire('Error', 'Fallo al conectar con el servicio de búsqueda.', 'error')
    } finally {
      isActionLoading.value = false
    }
  }
  const fetchAllPassengersGlobal = async () => {
    loading.value = true
    try {
      const response = await axios.post(`${API_PATH}GetPassengerByTrip.php`, {
        idTrip: 'ALL', 
      })
      const res = response.data

      if (res && res.status === 200) {
        passengers.value = res.data || []
      }
    }finally {
      loading.value = false
    }
  }

  const fetchAllPassengers = async () => {
    if (!selectedTripId.value) {
      await fetchAllPassengersGlobal()
      return
    }

    loading.value = true
    try {
      const response = await axios.post(`${API_PATH}GetPassengerByTrip.php`, {
        idTrip: selectedTripId.value,
      })
      const res = response.data

      if (res && res.status === 200) {
        passengers.value = res.data || []
      } else {
        passengers.value = []
        if (res.status !== 202) {
           Swal.fire('Aviso', res.message || 'Sin datos', 'warning')
        }
      }
    }finally {
      loading.value = false
    }
  }

  const fetchTripsList = async () => {
    try {
      const response = await axios.get('Trips/GetAllTrips.php')
      const res = response.data
      if (res && res.status === 200) {
        trips.value = res.data || []
        fetchAllPassengersGlobal()
      }
    } catch {
      Swal.fire('Error', 'Fallo al obtener la lista de cruceros.', 'error')
    }
  }

  const filterByTrip = () => {
    fetchAllPassengers()
  }

  const submitPassenger = async () => {
    if (!passengerForm.value.idCard || !passengerForm.value.idTrip) {
      Swal.fire('Atención', 'DNI y Crucero son obligatorios', 'warning')
      return
    }

    if (!passengerForm.value.idUser) {
      Swal.fire('Error', 'Debe buscar y validar un viajero por DNI primero.', 'error')
      return
    }

    isActionLoading.value = true
    const url = passengerForm.value.idPassenger
      ? `${API_PATH}UpdatePassenger.php`
      : `${API_PATH}RegisterPassenger.php`

    const payload = {
      idPassenger: passengerForm.value.idPassenger,
      idBooking: passengerForm.value.idBooking,
      idTraveler: passengerForm.value.idUser,
      idTrip: passengerForm.value.idTrip,
      cabinNumber: passengerForm.value.cabinNumber,
      boardingStatus: passengerForm.value.boardingStatus,
      specialAssistance: passengerForm.value.specialAssistance,
    }

  try {
      const response = await axios.post(url, payload)
      const res = response.data

      if (res && res.status === 200) {
        Swal.fire('¡Éxito!', 'Operación realizada correctamente', 'success')
        closeEditModal()
        if (selectedTripId.value) fetchAllPassengers() 
        else fetchAllPassengersGlobal()
      } else {
        Swal.fire('Validación', res.message || 'No se pudo procesar', 'warning')
      }
    }finally {
      isActionLoading.value = false
    }
  }

  const deletePassenger = async (id) => {
    const result = await Swal.fire({
      title: '¿Eliminar?',
      text: 'Se borrará del manifiesto de este viaje.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    })

    if (result.isConfirmed) {
      try {
        const response = await axios.post(`${API_PATH}DeletePassenger.php`, { idPassenger: id })
        const res = response.data

        if (res && res.status === 200) {
          Swal.fire('Eliminado', 'Pasajero removido', 'success')
          if (selectedTripId.value) fetchAllPassengers() 
          else fetchAllPassengersGlobal()
        } else {
          Swal.fire('Aviso', res.message, 'warning')
        }
      } catch {
        Swal.fire('Error', 'No se pudo eliminar al pasajero', 'error')
      }
    }
  }

  const openEditModal = (passenger = null) => {
    if (passenger) {
      passengerForm.value = {
        idPassenger: passenger.Id_Passenger,
        idCard: passenger.Id_Card_Passport,
        fullName: `${passenger.First_Name} ${passenger.Last_Name}`,
        cabinNumber: passenger.Cabin_Number,
        boardingStatus: parseInt(passenger.Boarding_Status),
        idTrip: passenger.Id_Trip,
        idUser: passenger.Id_Traveler,
        idBooking: passenger.Id_Booking || 0, 
        specialAssistance: passenger.Special_Assistance || '',
      }
    } else {
      passengerForm.value = {
        idPassenger: null,
        idCard: '',
        fullName: '',
        cabinNumber: '',
        boardingStatus: 0,
        idTrip: selectedTripId.value,
        idUser: null,
        idBooking: 0, 
        specialAssistance: '',
      }
    }
    showEditModal.value = true
  }

  const closeEditModal = () => {
    showEditModal.value = false
  }

  return {
    passengers,
    trips,
    loading,
    isActionLoading,
    showEditModal,
    passengerForm,
    selectedTripId,
    searchUserByDNI,
    fetchAllPassengers,
    fetchTripsList,
    filterByTrip,
    deletePassenger,
    submitPassenger,
    openEditModal,
    closeEditModal,
  }
}