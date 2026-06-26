<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebarWeb />
    <div class="content-wrapper flex-grow-1 p-0" style="background-color: #f8f9fa">
      <div class="container-fluid px-4 py-5" style="min-height: 100vh">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
          <div>
            <h1 class="fw-bold text-dark mb-1">Itinerarios Diarios</h1>
            <p class="text-muted small">
              Programa el recorrido y las actividades puerto por puerto
            </p>
          </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-5 p-4 bg-white">
          <label class="fw-bold text-dark mb-2"
            ><i class="bi bi-search me-2 text-primary"></i>Selecciona un Crucero</label
          >
          <select
            class="form-select form-select-lg"
            v-model="selectedTripId"
            @change="handleTripChange"
          >
            <option value="" disabled>-- Elige un viaje del catálogo --</option>
            <option v-for="t in trips" :key="t.Id_Trip" :value="t.Id_Trip">
              🚢 {{ t.Destination_Name }} | {{ t.Ship_Name }} (Zarpe: {{ t.Start_Date }})
            </option>
          </select>
        </div>

        <div v-if="selectedTripId">
          <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h4 class="fw-bold text-success mb-0">Ruta Programada</h4>
            <button
              class="btn btn-success fw-bold px-4 rounded-pill shadow-sm"
              @click="openModal()"
            >
              <i class="bi bi-plus-lg me-2"></i> AGREGAR DÍA
            </button>
          </div>

          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-success mb-3" role="status"></div>
            <p class="text-success fw-bold">Cargando cronograma...</p>
          </div>

          <div
            v-else-if="itineraryDays.length === 0"
            class="text-center py-5 bg-white rounded-4 shadow-sm"
          >
            <i class="bi bi-calendar-x fs-1 text-muted opacity-50"></i>
            <p class="text-muted mt-3 mb-0 fs-5">El itinerario de este viaje está vacío.</p>
            <p class="text-muted small">Presiona "Agregar Día" para comenzar a armar la ruta.</p>
          </div>

          <div v-else class="row g-3">
            <div v-for="day in itineraryDays" :key="day.Id_Itinerary" class="col-12">
              <div class="card border-0 shadow-sm rounded-3">
                <div
                  class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center gap-4"
                >
                  <div
                    class="text-center px-4 border-end border-2 border-success border-opacity-25"
                    style="min-width: 120px"
                  >
                    <span class="text-uppercase text-muted small fw-bold tracking-wider">DÍA</span>
                    <h2 class="display-5 fw-bold text-success mb-0">{{ day.Day_Number }}</h2>
                  </div>

                  <div class="flex-grow-1">
                    <h4 class="fw-bold text-dark mb-1">
                      <i class="bi bi-geo-alt-fill text-danger me-2"></i>{{ day.Port_of_Call }}
                    </h4>
                    <p class="text-muted mb-2">{{ day.Activity_Description }}</p>
                    <div class="d-flex gap-3 small fw-bold">
                      <span
                        v-if="day.Arrival_Time"
                        class="text-primary bg-primary bg-opacity-10 px-2 py-1 rounded"
                      >
                        <i class="bi bi-box-arrow-in-right me-1"></i> LLegada:
                        {{ day.Arrival_Time.substring(0, 5) }}
                      </span>
                      <span
                        v-if="day.Departure_Time"
                        class="text-warning text-dark bg-warning bg-opacity-10 px-2 py-1 rounded"
                      >
                        <i class="bi bi-box-arrow-right me-1"></i> Salida:
                        {{ day.Departure_Time.substring(0, 5) }}
                      </span>
                    </div>
                  </div>

                  <div class="d-flex gap-2">
                    <button
                      class="btn btn-outline-primary rounded-circle"
                      style="width: 45px; height: 45px"
                      @click="openModal(day)"
                    >
                      <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button
                      class="btn btn-outline-danger rounded-circle"
                      style="width: 45px; height: 45px"
                      @click="deleteActivity(day.Id_Itinerary)"
                    >
                      <i class="bi bi-trash-fill"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-5 mt-5">
          <i class="bi bi-arrow-up-circle fs-1 text-muted opacity-50 mb-3 d-block"></i>
          <h4 class="text-muted">
            Por favor, selecciona un crucero en la parte superior para continuar.
          </h4>
        </div>
      </div>
    </div>

    <div
      v-if="showModal"
      class="modal-backdrop-custom d-flex justify-content-center align-items-center p-3"
      style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1050;
        backdrop-filter: blur(4px);
      "
    >
      <div
        class="modal-content-custom p-4 rounded-4 shadow-lg bg-white text-dark"
        style="max-width: 600px; width: 100%"
      >
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
          <h4 class="modal-title fw-bold text-success">
            {{ form.idItinerary ? 'Editar Día' : 'Agregar Día al Itinerario' }}
          </h4>
          <button type="button" class="btn-close" @click="closeModal"></button>
        </div>

        <form @submit.prevent="saveActivity" class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-bold small">Número de Día</label>
            <input type="number" class="form-control" v-model="form.dayNumber" min="1" required />
          </div>
          <div class="col-md-8">
            <label class="form-label fw-bold small">Puerto de Escala / Ubicación</label>
            <input
              type="text"
              class="form-control"
              v-model="form.portOfCall"
              placeholder="Ej: Nassau, Bahamas o Navegación"
              required
            />
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold small">Hora de Llegada (Opcional)</label>
            <input type="time" class="form-control" v-model="form.arrivalTime" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">Hora de Salida (Opcional)</label>
            <input type="time" class="form-control" v-model="form.departureTime" />
          </div>

          <div class="col-12">
            <label class="form-label fw-bold small">Descripción de Actividades</label>
            <textarea
              class="form-control"
              v-model="form.activityDescription"
              rows="3"
              placeholder="Ej: Día libre para conocer playas, excursiones opcionales..."
            ></textarea>
          </div>

          <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light px-4" @click="closeModal">Cancelar</button>
            <button
              type="submit"
              class="btn btn-success fw-bold px-5 shadow-sm"
              :disabled="isActionLoading"
            >
              <span
                v-if="isActionLoading"
                class="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>
              GUARDAR
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import SlidSidebarWeb from '../../Common/SlidSidebarWeb.vue'
import { useItineraryLogic } from '../Js/Itinerarios.js'

const {
  trips,
  selectedTripId,
  itineraryDays,
  loading,
  isActionLoading,
  showModal,
  form,
  fetchTrips,
  handleTripChange,
  openModal,
  closeModal,
  saveActivity,
  deleteActivity,
} = useItineraryLogic()

onMounted(() => {
  fetchTrips()
})
</script>
