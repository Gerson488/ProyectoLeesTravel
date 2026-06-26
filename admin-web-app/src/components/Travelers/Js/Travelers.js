import { ref } from 'vue'
import axios from 'axios'
import iziToast from 'izitoast'

export function useTravelers() {
  const travelers = ref([])
  const loading = ref(false)

  const fetchTravelers = async () => {
    loading.value = true
    try {
      const response = await axios.get('Travelers/GetAllTravelers.php')
      const res = response.data

      if (res && res.status === 200) {
        travelers.value = res.data || []
      } else if (res && (res.status === 201 || res.status === 202)) {
        travelers.value = []
        iziToast.warning({ title: 'Aviso', message: res.message, position: 'topRight' })
      } else {
        travelers.value = []
      }
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'No se pudo obtener la lista de viajeros',
        position: 'topRight',
      })
    } finally {
      loading.value = false
    }
  }

  const checkDniInReniec = async (dni) => {
    try {
      const response = await axios.get('Utils/CheckId.php', {
        params: { dni: dni },
      })
      return response.data
    } catch {
      return { success: false, message: 'Error de conexión con el padrón' }
    }
  }

  const saveTraveler = async (travelerData) => {
    loading.value = true
    try {
      const endpoint = travelerData.idTraveler
        ? 'Travelers/UpdateTraveler.php'
        : 'Travelers/RegisterTraveler.php'

      const response = await axios.post(endpoint, travelerData)
      const res = response.data

      if (res && res.status === 200) {
        iziToast.success({
          title: 'Éxito',
          message: res.message || 'Operación realizada correctamente',
          position: 'topRight',
        })
        return { success: true }
      } else if (res && (res.status === 201 || res.status === 202)) {
        iziToast.warning({
          title: 'Validación',
          message: res.message || 'No se pudo procesar la solicitud',
          position: 'topRight',
        })
        return { success: false }
      } else {
        iziToast.error({
          title: 'Error',
          message: res?.message || 'Error desconocido',
          position: 'topRight',
        })
        return { success: false }
      }
    } catch {
      iziToast.error({
        title: 'Error Crítico',
        message: 'Fallo de conexión con el servidor',
        position: 'topRight',
      })
      return { success: false }
    } finally {
      loading.value = false
    }
  }

  const deleteTraveler = async (idTraveler) => {
    try {
      const response = await axios.post('Travelers/DeleteTraveler.php', { idTraveler })
      const res = response.data

      if (res && res.status === 200) {
        iziToast.success({
          title: 'Eliminado',
          message: 'El viajero ha sido retirado',
          position: 'topRight',
        })
        return true
      } else if (res && (res.status === 201 || res.status === 202)) {
        iziToast.warning({ title: 'Aviso', message: res.message, position: 'topRight' })
        return false
      }
      return false
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'No se pudo eliminar el registro',
        position: 'topRight',
      })
      return false
    }
  }

  const checkExistingInDb = async (docNumber) => {
    try {
      const response = await axios.post('Travelers/GetTravelerByDocument.php', {
        idCardPassport: docNumber,
      })
      return response.data
    } catch {
      return { status: 400 }
    }
  }

  return {
    travelers,
    loading,
    fetchTravelers,
    checkDniInReniec,
    saveTraveler,
    deleteTraveler,
    checkExistingInDb,
  }
}
