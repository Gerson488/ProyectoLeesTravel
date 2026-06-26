```vue
<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebarWeb />

    <div class="content-wrapper flex-grow-1 p-0" style="background-color: #f8f9fa">
      <div class="container-fluid px-4 py-5" style="min-height: 100vh">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
          <div>
            <h1 class="fw-bold text-dark mb-1">
              Bandeja de Prospectos

              <span
                v-if="pendingCount > 0"
                class="badge bg-danger rounded-pill fs-6 ms-2 shadow-sm pulse-badge"
              >
                {{ pendingCount }} Pendientes
              </span>
            </h1>

            <p class="text-muted small">
              Gestión de cotizaciones provenientes de la web y chatbot IA
            </p>
          </div>

          <div class="d-flex gap-2 flex-wrap">
            <button
              :class="[
                'btn fw-bold px-4 py-2 rounded-pill shadow-sm',
                audioEnabled ? 'btn-success' : 'btn-outline-secondary',
              ]"
              @click="enableAudio"
              :disabled="audioEnabled"
            >
              <i :class="audioEnabled ? 'bi bi-bell-fill' : 'bi bi-bell-slash'"></i>

              {{ audioEnabled ? 'Alertas Activadas' : 'Activar Alertas Sonoras' }}
            </button>

            <button
              @click="goToHub"
              class="btn btn-outline-secondary fw-bold px-4 py-2 rounded-pill"
            >
              <i class="bi bi-arrow-left-circle me-2"></i>
              Cambiar Entorno
            </button>

            <button
              @click="handleLogout"
              class="btn btn-outline-danger fw-bold px-4 py-2 rounded-pill"
            >
              <i class="bi bi-box-arrow-right me-2"></i>
              Cerrar sesión
            </button>
          </div>
        </div>

        <hr class="mb-5 opacity-10" />

        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary mb-3" role="status"></div>

          <p class="text-primary fw-bold">Sincronizando bandeja comercial...</p>
        </div>

        <div v-else class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light text-muted small text-uppercase">
                <tr>
                  <th class="ps-4 py-3">Fecha</th>
                  <th class="py-3">Cliente</th>
                  <th class="py-3">Contacto</th>
                  <th class="py-3">Destino</th>
                  <th class="py-3 text-center">Estado</th>
                  <th class="pe-4 py-3 text-end">Acciones</th>
                </tr>
              </thead>

              <tbody>
                <tr v-if="leads.length === 0">
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>

                    No hay cotizaciones registradas aún.
                  </td>
                </tr>

                <tr
                  v-for="lead in leads"
                  :key="lead.Id_Quote"
                  :class="{
                    'bg-warning bg-opacity-10': lead.Status === 'Pendiente',
                  }"
                >
                  <td class="ps-4 text-muted small fw-semibold">
                    {{ new Date(lead.Created_At).toLocaleDateString() }}
                  </td>

                  <td class="fw-bold text-dark">
                    <i class="bi bi-person-circle text-primary me-2 fs-5"></i>

                    {{ lead.Full_Name }}
                  </td>

                  <td>
                    <div class="small fw-semibold">
                      {{ lead.Phone }}
                    </div>

                    <div class="text-muted small" style="font-size: 0.75rem">
                      {{ lead.Email }}
                    </div>
                  </td>

                  <td>
                    <span class="badge bg-light text-dark border">
                      <i class="bi bi-geo-alt-fill text-danger me-1"></i>

                      {{ lead.Destination || 'Consulta General' }}
                    </span>
                  </td>

                  <td class="text-center">
                    <span v-if="lead.Status === 'Pendiente'" class="badge bg-danger">
                      PENDIENTE
                    </span>

                    <span v-else class="badge bg-success"> ATENDIDO </span>
                  </td>

                  <td class="pe-4 text-end">
                    <div class="btn-group shadow-sm rounded-3">
                      <a
                        :href="`mailto:${lead.Email}?subject=Cotización Lees Travel Cruises: ${lead.Destination}`"
                        class="btn btn-sm btn-outline-primary fw-bold px-3"
                      >
                        <i class="bi bi-envelope-fill me-1"></i>

                        Contactar
                      </a>

                      <button
                        v-if="lead.Status === 'Pendiente'"
                        @click="updateStatus(lead.Id_Quote, 'Atendido')"
                        class="btn btn-sm btn-success fw-bold px-3"
                      >
                        <i class="bi bi-check2-all me-1"></i>

                        Marcar Atendido
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuth } from '../../Login/Js/Auth.js'
import { useLeadLogic } from '../Js/Leads.js'
import SlidSidebarWeb from '../../Common/SlidSidebarWeb.vue'

const router = useRouter()
const { logout } = useAuth()

const { leads, loading, pendingCount, audioEnabled, enableAudio, updateStatus } = useLeadLogic()

const goToHub = () => {
  router.push('/select-platform')
}

const handleLogout = () => {
  logout()
  localStorage.removeItem('leestravel_platform')
  router.push('/login')
}
</script>

<style scoped>
.pulse-badge {
  animation: pulse-animation 2s infinite;
}

@keyframes pulse-animation {
  0% {
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
  }

  70% {
    box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
  }
}
</style>
```
