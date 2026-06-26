import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import iziToast from 'izitoast'

export function useAuth() {
  const router = useRouter()
  const isLoading = ref(false)
  const authError = ref('')

  const signIn = async (identifier, passwordInput) => {
    authError.value = ''
    isLoading.value = true

    try {
      const response = await axios.post('Users/Login.php', {
        email: identifier,
        password: passwordInput,
      })

      const result = response.data

      if (result && result.status === 200 && result.data?.token) {
        if (result.data.Access_Role === 'Pasajero') {
          isLoading.value = false
          authError.value = 'PASAJERO_RECHAZADO'
          return false
        }

        localStorage.setItem('leestravel_token', result.data.token)
        localStorage.setItem('leestravel_session', JSON.stringify(result.data))

        isLoading.value = false

        iziToast.success({
          title: '¡Bienvenido!',
          message: result.message || 'Acceso concedido',
          position: 'topRight',
          theme: 'dark',
          backgroundColor: '#198754',
        })

        return true
      }

      isLoading.value = false
      authError.value = result?.message || 'El correo o la contraseña no coinciden.'

      iziToast.error({
        title: 'Error de Acceso',
        message: authError.value,
        position: 'topRight',
        backgroundColor: '#dc3545',
      })

      return false
    } catch {
      isLoading.value = false
      authError.value = 'No se pudo conectar con el servidor. Verifica Apache o CORS.'

      iziToast.error({
        title: 'Servidor Offline',
        message: authError.value,
        position: 'topCenter',
      })

      return false
    }
  }

  const checkAuth = () => {
    const session = localStorage.getItem('leestravel_session')
    const token = localStorage.getItem('leestravel_token')

    if (!session || !token) {
      logout(false)
      return null
    }

    try {
      const payload = JSON.parse(atob(token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/')))

      const currentTime = Math.floor(Date.now() / 1000)

      if (payload.exp < currentTime) {
        logout(false)
        return null
      }

      return JSON.parse(session)
    } catch {
      logout(false)
      return null
    }
  }

  const getAuthHeaders = () => {
    const token = localStorage.getItem('leestravel_token')

    return {
      Authorization: `Bearer ${token}`,
    }
  }

  const logout = (showMessage = true) => {
    localStorage.removeItem('leestravel_token')
    localStorage.removeItem('leestravel_session')
    localStorage.removeItem('leestravel_platform')

    router.push('/')

    if (showMessage) {
      iziToast.info({
        title: 'Sesión Finalizada',
        message: 'Has salido del sistema de cruceros.',
        position: 'bottomCenter',
      })
    }
  }

  return {
    signIn,
    logout,
    checkAuth,
    getAuthHeaders,
    isLoading,
    authError,
  }
}
