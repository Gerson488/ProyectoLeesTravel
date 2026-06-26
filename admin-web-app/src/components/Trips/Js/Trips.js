import { ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import iziToast from 'izitoast'
import { MEDIA_BASE_URL } from '../../../config.js'

export function useTripLogic() {
  const trips = ref([])
  const loading = ref(false)
  const isActionLoading = ref(false)
  const showTripModal = ref(false)
  const imagePreview = ref(null)
  const selectedFile = ref(null)

  const tripForm = ref({
    idTrip: null,
    destinationName: '',
    shipName: '',
    cruiseLine: '',
    departurePort: '',
    arrivalPort: '',
    startDate: '',
    endDate: '',
    price: 0,
    maxCapacity: 0,
    requiresVisa: 0,
    includesFlight: 0,
    durationNights: 0,
    description: '',
  })

  const fetchTrips = async () => {
    loading.value = true
    try {
      const response = await axios.get('Trips/GetAllTrips.php')
      const res = response.data

      if (res && res.status === 200) {
        trips.value = res.data || []
      } else if (res && (res.status === 201 || res.status === 202)) {
        trips.value = []
        iziToast.warning({ title: 'Aviso', message: res.message, position: 'topRight' })
      } else {
        trips.value = []
      }
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'No se pudo cargar el catálogo de viajes',
        position: 'topRight',
      })
    } finally {
      loading.value = false
    }
  }

  const onFileSelected = (event) => {
    const file = event.target.files[0]
    if (file) {
      selectedFile.value = file
      imagePreview.value = URL.createObjectURL(file)
    }
  }

  const openTripModal = (trip = null) => {
    if (trip) {
      tripForm.value = {
        idTrip: trip.Id_Trip,
        destinationName: trip.Destination_Name,
        shipName: trip.Ship_Name,
        cruiseLine: trip.Cruise_Line || '',
        departurePort: trip.Departure_Port,
        arrivalPort: trip.Arrival_Port,
        startDate: trip.Start_Date,
        endDate: trip.End_Date,
        price: trip.Price,
        maxCapacity: trip.Max_Capacity,
        requiresVisa: trip.Requires_Visa || 0,
        includesFlight: trip.Includes_Flight || 0,
        durationNights: trip.Duration_Nights || 0,
        description: trip.Description || '',
      }
      imagePreview.value = trip.Trip_Photo ? `${MEDIA_BASE_URL}${trip.Trip_Photo}` : null
    } else {
      resetForm()
    }
    showTripModal.value = true
  }

  const closeTripModal = () => {
    showTripModal.value = false
    resetForm()
  }

  const resetForm = () => {
    tripForm.value = {
      idTrip: null,
      destinationName: '',
      shipName: '',
      cruiseLine: '',
      departurePort: '',
      arrivalPort: '',
      startDate: '',
      endDate: '',
      price: 0,
      maxCapacity: 0,
      requiresVisa: 0,
      includesFlight: 0,
      durationNights: 0,
      description: '',
    }
    imagePreview.value = null
    selectedFile.value = null
  }

  const saveTrip = async () => {
    isActionLoading.value = true
    const formData = new FormData()

    Object.keys(tripForm.value).forEach((key) => {
      formData.append(key, tripForm.value[key] !== null ? tripForm.value[key] : '')
    })

    if (selectedFile.value) {
      formData.append('imageTrip', selectedFile.value)
    }

    const endpoint = tripForm.value.idTrip ? 'Trips/UpdateTrips.php' : 'Trips/RegisterTrips.php'

    try {
      const response = await axios.post(endpoint, formData)
      const res = response.data

      if (res && res.status === 200) {
        Swal.fire('¡Logrado!', res.message || 'Crucero guardado con éxito', 'success')
        closeTripModal()
        fetchTrips()
      } else if (res && (res.status === 201 || res.status === 202)) {
        Swal.fire('Validación', res.message, 'warning')
      } else {
        Swal.fire('Error', res?.message || 'No se pudo guardar', 'error')
      }
    } catch {
      Swal.fire('Error', 'Error de conexión con el servidor', 'error')
    } finally {
      isActionLoading.value = false
    }
  }

  const handleDelete = async (idTrip, confirmForce = false) => {
    if (!confirmForce) {
      const result = await Swal.fire({
        title: '¿Eliminar crucero?',
        text: 'Se borrarán los registros y la imagen del servidor.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
      })
      if (!result.isConfirmed) return
    }

    try {
      const response = await axios.post('Trips/DeleteTrips.php', { idTrip, confirmForce })
      const res = response.data

      if (res && res.status === 200) {
        Swal.fire('Eliminado', 'El crucero ha sido removido.', 'success')
        fetchTrips()
      } else if (res && res.status === 202) {
        const confirmResult = await Swal.fire({
          title: '¡Atención!',
          text: res.message,
          icon: 'error',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          confirmButtonText: 'Sí, borrar todo',
          cancelButtonText: 'No, cancelar',
        })

        if (confirmResult.isConfirmed) {
          handleDelete(idTrip, true)
        }
      } else {
        Swal.fire('Aviso', res.message || 'No se pudo realizar la acción', 'warning')
      }
    } catch {
      Swal.fire('Error', 'No se pudo conectar con el servidor para eliminar.', 'error')
    }
  }

  return {
    trips,
    loading,
    isActionLoading,
    showTripModal,
    tripForm,
    imagePreview,
    onFileSelected,
    openTripModal,
    closeTripModal,
    saveTrip,
    handleDelete,
    fetchTrips,
  }
}
