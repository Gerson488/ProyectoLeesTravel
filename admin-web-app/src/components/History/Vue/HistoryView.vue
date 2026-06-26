<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebar />

    <div class="content-wrapper flex-grow-1 p-0" style="background-color: #f8f9fa">
      <div class="container-fluid px-4 py-5" style="min-height: 100vh">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
          <div>
            <h1 class="fw-bold text-dark mb-1">Bitácora de Viaje</h1>
            <p class="text-muted small">Registro histórico de incidencias y novedades</p>
          </div>
        </div>

        <div class="row g-3 mb-4 justify-content-center">
          <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
              <div class="text-muted small fw-bold">Total Incidencias</div>
              <div class="fs-3 fw-bold text-dark">{{ historyLogic.totalRecords }}</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-danger-subtle text-danger">
              <div class="small fw-bold">Médicos</div>
              <div class="fs-3 fw-bold">{{ historyLogic.totalMedical }}</div>
            </div>
          </div>
        </div>

        <div class="row mb-4 g-3">
          <div class="col-md-6">
            <select class="form-select shadow-sm" v-model="historyLogic.selectedTripId.value" @change="historyLogic.filterByTrip">
              <option value="">Seleccione el Crucero...</option>
              <option v-for="trip in historyLogic.trips.value" :key="trip.Id_Trip" :value="trip.Id_Trip">
                {{ trip.Destination_Name ? (trip.Destination_Name + ' - ' + trip.Ship_Name) : 'Viaje #' + trip.Id_Trip }}
              </option>
            </select>
          </div>
          <div class="col-md-6">
            <input type="text" class="form-control shadow-sm" v-model="historyLogic.searchTerm.value" placeholder="Buscar en la bitácora...">
          </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                  <th class="py-3 px-3">Fecha/Hora</th>
                  <th class="py-3">Descripción</th>
                  <th class="py-3">Pasajero Afectado</th>
                  <th class="py-3 text-center">Reportado por</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="historyLogic.loading.value">
                  <td colspan="4" class="py-5 text-center text-muted">Cargando registros...</td>
                </tr>
                <tr v-else-if="historyLogic.filteredHistory.value.length === 0">
                  <td colspan="4" class="py-5 text-center text-muted">No se encontraron registros.</td>
                </tr>
                <tr v-else v-for="item in historyLogic.filteredHistory.value" :key="item.Id_History">
                  <td class="text-nowrap px-3 fw-bold text-dark">{{ item.Event_Date }}</td>
                  <td class="text-dark fw-medium" style="max-width: 400px;">{{ item.Event_Description }}</td>
                  
                  <td class="text-muted small fw-bold text-dark">
                    {{ item.Passenger_Name }}
                  </td>
                  
                  <td class="text-center fw-bold text-primary">
                    {{ item.Guia_Name }}
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
import { useHistoryLogic } from '../Js/History.js'
import '../Css/History.css'

const historyLogic = useHistoryLogic()

onMounted(() => {
  if (typeof historyLogic.fetchTripsList === 'function') {
    historyLogic.fetchTripsList()
  } else {
    console.error("fetchTripsList no existe en History.js")
  }
})
</script>