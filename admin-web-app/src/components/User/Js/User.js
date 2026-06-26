import { ref } from 'vue'
import axios from 'axios'
import iziToast from 'izitoast'

export function useUsers() {
  const users = ref([])
  const loading = ref(false)
  const error = ref(null)

  const fetchAllUsers = async () => {
    loading.value = true
    error.value = null
    try {
      const token = localStorage.getItem('leestravel_token')

      const response = await axios.get('Users/GetUsers.php', {
        headers: {
          Authorization: token ? `Bearer ${token}` : '',
        },
      })

      const res = response.data

      if (res && res.status === 200) {
        users.value = res.data.map((u) => ({
          Id_User: parseInt(u.Id_User),
          Id_Traveler: u.Id_Traveler,
          Document_Number: u.Document_Number || u.Id_Card_Passport || 'N/A',
          Username: u.First_Name || 'Usuario',
          Full_Name: u.First_Name ? `${u.First_Name} ${u.Last_Name}` : 'Sin nombre vinculado',
          Email: u.Email,
          Access_Role: u.Access_Role === 'Vendedor' ? 'Asesor' : u.Access_Role,
          User_Status: parseInt(u.User_Status),
          Photo_Path: u.Photo_Path || 'default_user.png',
        }))
      } else if (res && (res.status === 201 || res.status === 202)) {
        error.value = res.message
        iziToast.warning({ title: 'Aviso', message: error.value })
      } else {
        error.value = res?.message || 'Error al obtener la lista de usuarios.'
      }
    } catch {
      error.value = 'Error: No se pudo conectar con el servidor.'
      iziToast.error({ title: 'Fallo de Red', message: error.value })
    } finally {
      loading.value = false
    }
  }

  const findTraveler = async (term) => {
    try {
      const token = localStorage.getItem('leestravel_token')
      const response = await axios.post(
        'Users/SearchTraveler.php',
        { term },
        {
          headers: {
            Authorization: token ? `Bearer ${token}` : '',
          },
        },
      )
      const res = response.data

      if (res && res.status === 200) {
        const data = res.data
        return {
          ...data,
          Document_Number: data.Id_Card_Passport || data.Document_Number,
        }
      } else {
        return null
      }
    } catch {
      iziToast.error({ title: 'Error', message: 'No se pudo consultar el viajero.' })
      return null
    }
  }

  const saveUser = async (formData, isEdit = false) => {
    loading.value = true
    const endpoint = isEdit ? 'Users/UpdateUser.php' : 'Users/RegisterUser.php'

    const payload = isEdit
      ? {
          idUser: formData.idUser,
          email: formData.email,
          accessRole: formData.accessRole,
          userStatus: formData.userStatus,
          password: formData.password || null,
        }
      : {
          idTraveler: formData.idTraveler,
          email: formData.email,
          password: formData.password,
          accessRole: formData.accessRole,
          userStatus: 1,
        }

    try {
      const token = localStorage.getItem('leestravel_token')
      const response = await axios.post(endpoint, payload, {
        headers: {
          Authorization: token ? `Bearer ${token}` : '',
        },
      })
      const res = response.data

      if (res && res.status === 200) {
        iziToast.success({
          title: 'Éxito',
          message: isEdit ? 'Usuario actualizado' : 'Usuario creado',
          position: 'topRight',
        })
        return true
      } else if (res && (res.status === 201 || res.status === 202)) {
        iziToast.warning({
          title: 'Validación',
          message: res.message,
          position: 'topRight',
        })
        return false
      } else {
        iziToast.error({
          title: 'Atención',
          message: res?.message || 'Error no esperado en el proceso.',
          position: 'topRight',
        })
        return false
      }
    } catch {
      iziToast.error({
        title: 'Error Crítico',
        message: 'Fallo al comunicarse con el servidor.',
        position: 'topRight',
      })
      return false
    } finally {
      loading.value = false
    }
  }

  const removeUser = async (idUser) => {
    try {
      const token = localStorage.getItem('leestravel_token')
      const response = await axios.post(
        'Users/DeleteUser.php',
        { idUser },
        {
          headers: {
            Authorization: token ? `Bearer ${token}` : '',
          },
        },
      )
      const res = response.data

      if (res && res.status === 200) {
        iziToast.info({
          title: 'Eliminado',
          message: 'Acceso revocado correctamente.',
          position: 'topRight',
        })
        return true
      } else if (res && (res.status === 201 || res.status === 202)) {
        iziToast.warning({ title: 'Aviso', message: res.message, position: 'topRight' })
        return false
      }
      return false
    } catch {
      iziToast.error({ title: 'Error', message: 'No se pudo procesar la eliminación.' })
      return false
    }
  }

  return {
    users,
    loading,
    error,
    fetchAllUsers,
    findTraveler,
    saveUser,
    removeUser,
  }
}
