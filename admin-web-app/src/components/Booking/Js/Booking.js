import { ref } from 'vue'
import axios from 'axios'
import iziToast from 'izitoast'

export function useBookings() {
  const bookings = ref([])
  const loading = ref(false)

  const fetchAllBookings = async () => {
    loading.value = true
    try {
      const response = await axios.post('Bookings/GetMyBookings.php', {})
      const res = response.data
      if (res && (res.status == 200 || res.status == "200")) {
        bookings.value = Array.isArray(res.data) ? res.data : [];
      }
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'No se pudo obtener el listado general de reservas.',
        position: 'topRight',
      })
    } finally {
      loading.value = false
    }
  }

  const fetchBookings = async (idUser) => {
    if (!idUser) return
    loading.value = true
    try {
      const response = await axios.post('Bookings/GetMyBookings.php', { idUser })
      const res = response.data

      if (res && res.status === 200) {
        bookings.value = res.data || []
      } else if (res && (res.status === 201 || res.status === 202)) {
        iziToast.warning({ title: 'Aviso', message: res.message, position: 'topRight' })
      } else {
        iziToast.error({
          title: 'Atención',
          message: res?.message || 'Error al obtener reservas.',
          position: 'topRight',
        })
      }
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'Ocurrió un fallo de conexión al buscar las reservas.',
        position: 'topRight',
      })
    } finally {
      loading.value = false
    }
  }

  const createNewBooking = async (bookingData) => {
    loading.value = true
    try {
      const response = await axios.post('Bookings/RegisterBooking.php', bookingData)
      const res = response.data

      if (res && res.status === 200) {
        iziToast.success({
          title: 'Éxito',
          message: res.message || 'Reserva grupal creada correctamente',
          position: 'topRight',
        })
        return { success: true }
      } else {
        iziToast.error({
          title: 'Error',
          message: res?.message || 'No se pudo registrar la reserva grupal',
          position: 'topRight',
        })
        return { success: false }
      }
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'Error de red al intentar registrar la reserva.',
        position: 'topRight',
      })
      return { success: false }
    } finally {
      loading.value = false
    }
  }

  const updateStatus = async (idBooking, editData) => {
    try {
      let payload = {}
      
      if (editData && typeof editData === 'object') {
        payload = {
          idBooking,
          bookingStatus: editData.bookingStatus,
          passengers: editData.passengers 
        }
      } else {
        payload = {
          idBooking,
          bookingStatus: editData,
        }
      }

      const response = await axios.post('Bookings/UpdateBooking.php', payload)
      const res = response.data

      if (res && (res.status === 200 || res.status === true || res.status === "success")) {
        iziToast.success({
          title: 'Actualizado',
          message: 'La reserva y sus acompañantes fueron modificados con éxito',
          position: 'topRight',
        })
        return true
      }
      
      iziToast.error({
        title: 'Error',
        message: res?.message || 'Fallo al actualizar la reserva.',
        position: 'topRight',
      })
      return false
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'No se pudo conectar con el servidor para actualizar la reserva.',
        position: 'topRight',
      })
      return false
    }
  }

  const removeBooking = async (idBooking) => {
    try {
      const response = await axios.post('Bookings/DeleteBooking.php', { idBooking })
      const res = response.data

      if (res && res.status === 200) {
        iziToast.info({
          title: 'Eliminada',
          message: 'La reserva ha sido cancelada y borrada.',
          position: 'topRight',
        })
        return true
      }
      
      iziToast.error({
        title: 'Error',
        message: res?.message || 'No se pudo eliminar la reserva.',
        position: 'topRight',
      })
      return false
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'Error al intentar procesar la baja de la reserva.',
        position: 'topRight',
      })
      return false
    }
  }

  const findTraveler = async (dni) => {
    if (!dni) return null
    try {
      const response = await axios.post('Utils/CheckTraveler.php', { 
        docNumber: dni 
      })
      const res = response.data

      if (res && res.status === 200) {
        return res.data 
      } else {
        return null
      }
    } catch {
      return null
    }
  }

  const fetchBookingDetails = async (idBooking) => {
    if (!idBooking) return []
    try {
      const response = await axios.post('Bookings/GetBookingDetails.php', { idBooking })
      const res = response.data

      if (res && (res.status === 200 || res.status === true || res.status === "success")) {
        return Array.isArray(res.data) ? res.data : []
      } else {
        return []
      }
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'No se pudieron cargar los acompañantes de esta reserva.',
        position: 'topRight',
      })
      return []
    }
  }

  return {
    bookings,
    loading,
    fetchBookings,
    fetchAllBookings,
    createNewBooking,
    updateStatus,
    removeBooking,
    findTraveler, 
    fetchBookingDetails, 
  }
}