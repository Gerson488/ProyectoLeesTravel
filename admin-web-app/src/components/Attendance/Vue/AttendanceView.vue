<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    
    <SlidSidebar />

    <div class="content-wrapper flex-grow-1 p-0" style="background-color: #f8f9fa">
      <div class="container-fluid px-4 py-5" style="min-height: 100vh">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
          <div>
            <h1 class="fw-bold text-dark mb-1">Control de Asistencia</h1>
            <p class="text-muted small">Gestión y Verificación de Abordaje para la Seguridad del Cliente</p>
          </div>
          <div class="d-flex gap-2">
            <span v-if="selectedTripId" class="badge bg-warning text-dark fw-bold px-3 py-2 fs-6 rounded-3 shadow-sm d-flex align-items-center gap-2">
              <i class="bi bi-ship"></i> CRUCERO ACTIVO: #{{ selectedTripId }}
            </span>
          </div>
        </div>

        <div v-if="!loading && selectedTripId" class="row g-3 mb-4 justify-content-center">
          <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
              <div class="text-muted text-uppercase small fw-bold mb-1 text-center text-md-start">Total Pasajeros</div>
              <div class="fs-3 fw-bold text-dark text-center text-md-start">{{ totalPassengers }}</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-success-subtle border border-success">
              <div class="text-success text-uppercase small fw-bold mb-1 text-center text-md-start">Abordados</div>
              <div class="fs-3 fw-bold text-success text-center text-md-start">{{ totalAbordados }}</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-warning-subtle border border-warning">
              <div class="text-warning-dark text-uppercase small fw-bold mb-1 text-center text-md-start">Por Abordar</div>
              <div class="fs-3 fw-bold text-warning-dark text-center text-md-start">{{ totalPorAbordar }}</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-danger-subtle border border-danger">
              <div class="text-danger text-uppercase small fw-bold mb-1 text-center text-md-start">No Se Presentó</div>
              <div class="fs-3 fw-bold text-danger text-center text-md-start">{{ totalNoPresentes }}</div>
            </div>
          </div>
        </div>

        <div class="row mb-4 g-3 justify-content-center">
          <div class="col-md-4">
            <div class="input-group shadow-sm rounded-3 overflow-hidden">
              <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="Buscar por nombre o DNI..."
                v-model="searchTerm"
              />
            </div>
          </div>
          <div class="col-md-4">
            <div class="input-group shadow-sm rounded-3 overflow-hidden">
              <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-ship text-info"></i>
              </span>
              <select
                class="form-select border-start-0 ps-0 text-dark fw-semibold"
                v-model="selectedTripId"
                @change="filterByTrip"
              >
                <option value="">Seleccione el Crucero...</option>
                <option v-for="trip in trips" :key="trip.Id_Trip" :value="trip.Id_Trip">
                  {{ trip.Trip_Name || trip.Destination_Name || 'Viaje #' + trip.Id_Trip }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="input-group shadow-sm rounded-3 overflow-hidden">
              <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-funnel text-secondary"></i>
              </span>
              <select class="form-select border-start-0 ps-0 text-dark fw-semibold" v-model="statusFilter">
                <option value="Todos">Todos los Estados</option>
                <option value="Por Abordar">Por Abordar</option>
                <option value="Abordado">Abordado</option>
                <option value="No Se Presentó">No Se Presentó</option>
              </select>
            </div>
          </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="table-responsive" style="max-height: 65vh">
            <table class="table table-hover mb-0 align-middle text-center">
              <thead class="bg-light border-bottom">
                <tr class="small text-muted text-uppercase">
                  <th class="py-3">#</th>
                  <th class="text-start py-3">Pasajero</th>
                  <th class="py-3">Documento</th>
                  <th class="py-3">Cabina</th>
                  <th class="py-3" style="width: 220px;">Estado de Embarque</th>
                  <th class="py-3">Ficha Médica / Alerta</th>
                </tr>
              </thead>
              <tbody class="bg-white">
                <tr v-if="loading">
                  <td colspan="6" class="py-5 text-muted">Sincronizando estados del manifiesto seguro...</td>
                </tr>
                <tr v-else-if="filteredPassengers.length === 0">
                  <td colspan="6" class="py-5 text-muted small">
                    {{ selectedTripId ? 'No se encontraron coincidencias para los filtros aplicados' : 'Por favor, elija un crucero para desplegar el control de abordaje' }}
                  </td>
                </tr>
                <tr
                  v-else
                  v-for="(p, index) in filteredPassengers"
                  :key="p.Id_Passenger"
                  class="border-bottom border-light"
                >
                  <td class="text-muted small fw-bold">{{ index + 1 }}</td>
                  <td class="text-start">
                    <div class="fw-bold text-dark">{{ p.First_Name }} {{ p.Last_Name }}</div>
                  </td>
                  <td>
                    <small class="text-muted fw-mono">{{ p.Id_Card_Passport }}</small>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-bold">{{ p.Cabin_Number || 'N/A' }}</span>
                  </td>
                  <td>
                    <span 
                      class="badge px-3 py-2 rounded-3 w-100 shadow-sm fw-bold"
                      :class="{
                        'bg-success-subtle text-success': p.Boarding_Status === 'Abordado',
                        'bg-warning-subtle text-warning-dark': p.Boarding_Status === 'Por Abordar' || !p.Boarding_Status,
                        'bg-danger-subtle text-danger': p.Boarding_Status === 'No Se Presentó'
                      }"
                    >
                      {{ p.Boarding_Status || 'Por Abordar' }}
                    </span>
                  </td>
                  <td>
                    <div v-if="(p.Chronic_Diseases && p.Chronic_Diseases.trim() !== '' && p.Chronic_Diseases.toLowerCase() !== 'ninguna' && p.Chronic_Diseases.toLowerCase() !== 'ninguno')" 
                         class="d-inline-block p-1 px-2 rounded-2 bg-danger-subtle border border-danger small text-danger fw-bold"
                         style="font-size: 0.72rem;">
                      <i class="bi bi-exclamation-triangle-fill"></i> CRÍTICO: {{ p.Chronic_Diseases }}
                    </div>
                    <div v-else-if="p.Allergies && p.Allergies.trim() !== '' && p.Allergies.toLowerCase() !== 'ninguna' && p.Allergies.toLowerCase() !== 'ninguno'"
                         class="d-inline-block p-1 px-2 rounded-2 bg-warning-subtle border border-warning small text-warning-dark fw-bold"
                         style="font-size: 0.72rem;">
                      <i class="bi bi-shield-exclamation"></i> ALERGIA: {{ p.Allergies }}
                    </div>
                    <div v-else class="text-success small fw-semibold">
                      <i class="bi bi-shield-check"></i> Estable
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
import { onMounted } from 'vue'
import SlidSidebar from '../../Common/SlidSidebarApp.vue'
import { useAttendanceLogic } from '../Js/Attendance.js'
import '../Css/Attendance.css'

const {
  trips,
  loading,
  selectedTripId,
  searchTerm,
  statusFilter,
  filteredPassengers,
  totalPassengers,
  totalAbordados,
  totalPorAbordar,
  totalNoPresentes,
  fetchTripsList,
  filterByTrip
} = useAttendanceLogic()

onMounted(() => {
  fetchTripsList()
})
</script>