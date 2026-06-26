<template>
  <div class="login-wrapper">
    <div class="card login-card shadow-lg p-4">
      <div class="card-body">
        <h2 class="card-title text-center text-primary mb-4 fw-bold">Iniciar sesión</h2>
        <h5 class="text-center text-muted mb-4">Lees Travel - Gestión</h5>

        <form @submit.prevent="handleLogin">
          <div class="mb-3">
            <label for="identifier" class="form-label">Correo o Usuario</label>
            <input
              type="text"
              class="form-control"
              id="identifier"
              v-model="identifier"
              placeholder="Ej: gerson@mail.com"
              :disabled="isLoading"
              required
              autocomplete="email"
            />
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input
              type="password"
              class="form-control"
              id="password"
              v-model="password"
              placeholder="********"
              :disabled="isLoading"
              required
              autocomplete="current-password"
            />
          </div>

          <div
            v-if="authError && authError !== 'PASAJERO_RECHAZADO'"
            class="alert alert-danger text-center py-2 small fw-bold animate__animated animate__shakeX"
          >
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ authError }}
          </div>

          <div class="d-grid mb-3">
            <button
              type="submit"
              class="btn btn-warning fw-bold py-2 shadow-sm"
              :disabled="isLoading"
            >
              <span
                v-if="isLoading"
                class="spinner-border spinner-border-sm me-2"
                role="status"
              ></span>
              {{ isLoading ? 'Validando...' : 'Entrar al Sistema' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div
      class="modal fade"
      id="deniedModal"
      tabindex="-1"
      aria-hidden="true"
      data-bs-backdrop="static"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="bg-danger text-white text-center py-4">
            <div class="display-4 mb-2">
              <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h4 class="modal-title fw-bold">Acceso Restringido</h4>
          </div>
          <div class="modal-body text-center p-4 text-dark">
            <p class="fs-5 fw-semibold text-secondary mb-3">Estimado usuario viajero</p>
            <p class="text-muted small lh-lg">
              Esta plataforma web está configurada exclusivamente para el personal operativo,
              comercial y de administración de <strong>Lees Travel Cruises</strong>.
            </p>
            <div
              class="alert bg-light border text-start d-flex align-items-center mt-3 mb-2 rounded-3"
            >
              <i class="bi bi-phone-vibrate fs-3 text-danger me-3"></i>
              <span class="small text-secondary lh-sm">
                Puedes gestionar tus cruceros, revisar itinerarios y actualizar tu ficha médica
                ingresando de forma nativa desde nuestra <strong>App Móvil</strong>.
              </span>
            </div>
          </div>
          <div class="modal-footer border-0 p-3 bg-light justify-content-center">
            <button
              type="button"
              class="btn btn-secondary px-4 rounded-pill fw-semibold shadow-sm"
              data-bs-dismiss="modal"
            >
              Entendido
            </button>
          </div>
        </div>
      </div>
    </div>

    <Loader :show="isLoading" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Modal } from 'bootstrap'
import { useAuth } from '../Js/Auth.js'
import Loader from '../../Loader/AppLoader.vue'
import '../Css/Login.css'

const router = useRouter()
const { signIn, isLoading, authError } = useAuth()
const identifier = ref('')
const password = ref('')
let modalInstance = null

const handleLogin = async () => {
  if (!identifier.value || !password.value) return

  const startTime = Date.now()
  const success = await signIn(identifier.value, password.value)

  if (success) {
    const minWait = 1000
    const duration = Date.now() - startTime
    const remainingTime = Math.max(0, minWait - duration)

    setTimeout(() => {
      router.push('/select-platform')
    }, remainingTime)
  } else if (authError.value === 'PASAJERO_RECHAZADO') {
    if (modalInstance) {
      modalInstance.show()
    }
  }
}

onMounted(() => {
  modalInstance = new Modal(document.getElementById('deniedModal'))
})
</script>
