<template>
  <div
    class="selection-wrapper d-flex align-items-center justify-content-center min-vh-100 bg-primary bg-gradient"
  >
    <div class="container">
      <div class="text-center mb-5 text-white">
        <h1 class="display-4 fw-bold">Lees Travel Cruises</h1>
        <p class="lead text-white-50">Selecciona el entorno de gestión que deseas utilizar</p>
      </div>

      <div class="row justify-content-center g-4">
        <div class="col-md-5">
          <div
            class="card h-100 border-0 shadow-lg selection-card text-center p-4"
            @click="goHome('web')"
          >
            <div class="card-body">
              <div
                class="icon-box mb-4 mx-auto bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center"
              >
                <i class="bi bi-globe2 display-3 text-primary"></i>
              </div>

              <h2 class="fw-bold mb-3">Página Web</h2>

              <p class="text-muted">
                Administra el contenido público, blog, promociones y prospectos de la web.
              </p>

              <button class="btn btn-primary mt-3 px-4 rounded-pill">Entrar al Panel Web</button>
            </div>
          </div>
        </div>

        <div class="col-md-5">
          <div
            class="card h-100 border-0 shadow-lg selection-card text-center p-4"
            @click="goHome('app')"
          >
            <div class="card-body">
              <div
                class="icon-box mb-4 mx-auto bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center"
              >
                <i class="bi bi-phone display-3 text-warning"></i>
              </div>

              <h2 class="fw-bold mb-3">App Móvil</h2>

              <p class="text-muted">
                Control total de reservas, pasajeros, itinerarios y notificaciones push.
              </p>

              <button class="btn btn-warning mt-3 px-4 rounded-pill text-white fw-bold">
                Entrar al Panel App
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../Js/Auth.js'

const router = useRouter()
const { checkAuth } = useAuth()

const goHome = (platform) => {
  localStorage.setItem('leestravel_platform', platform)

  if (platform === 'web') {
    router.push('/home')
  } else {
    router.push('/app-dashboard')
  }
}

onMounted(() => {
  const session = checkAuth()

  if (!session) {
    router.push('/')
    return
  }

  if (session.Access_Role === 'Pasajero') {
    router.push('/')
  }
})
</script>

<style scoped>
.selection-wrapper {
  background: linear-gradient(135deg, #1e4b7a 0%, #245a91 100%) !important;
}

.selection-card {
  cursor: pointer;
  transition:
    transform 0.3s ease,
    box-shadow 0.3s ease;
  border-radius: 20px;
}

.selection-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
}

.icon-box {
  width: 120px;
  height: 120px;
}
</style>
