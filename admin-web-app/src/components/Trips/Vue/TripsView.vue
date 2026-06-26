<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebarWeb />

    <div class="content-wrapper flex-grow-1 p-0" style="background-color: #ffffff">
      <div class="container-fluid px-4 py-5" style="min-height: 100vh">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
          <div>
            <h1 class="fw-bold text-dark mb-1">Catálogo de Cruceros</h1>
            <p class="text-muted small">
              Administración de rutas, flota y tarifas del mercado peruano
            </p>
          </div>
          <button
            class="btn btn-primary fw-bold px-4 py-2 shadow-sm rounded-pill"
            @click="openTripModal()"
          >
            <i class="bi bi-plus-circle-fill me-2"></i>NUEVO CRUCERO
          </button>
        </div>

        <hr class="mb-5 opacity-10" />

        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary mb-3" role="status"></div>
          <p class="text-primary fw-bold">Sincronizando flota de LEES Travel...</p>
        </div>

        <div v-else class="row g-4">
          <div v-if="trips.length === 0" class="col-12 text-center py-5">
            <i class="bi bi-ship fs-1 text-muted opacity-50"></i>
            <p class="text-muted mt-2">No hay cruceros programados actualmente.</p>
          </div>

          <div v-for="trip in trips" :key="trip.Id_Trip" class="col-12 col-md-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 trip-card-white overflow-hidden">
              <div class="position-relative overflow-hidden" style="height: 220px">
                <img
                  :src="
                    trip.Trip_Photo
                      ? `${MEDIA_BASE_URL}${trip.Trip_Photo}`
                      : 'https://via.placeholder.com/400x200?text=LEES+Travel'
                  "
                  class="card-img-top h-100 w-100 object-fit-cover transition-img"
                />
                <div class="position-absolute top-0 end-0 m-3">
                  <span class="badge bg-primary text-white fw-bold fs-6 shadow">
                    $ {{ Number(trip.Price).toLocaleString() }}
                  </span>
                </div>
              </div>

              <div class="card-body p-4 d-flex flex-column bg-white">
                <div class="mb-3">
                  <h4 class="fw-bold text-dark mb-0 text-uppercase">{{ trip.Destination_Name }}</h4>
                  <span class="text-primary fw-semibold small">
                    <i class="bi bi-ship me-1"></i> {{ trip.Ship_Name }} ({{ trip.Cruise_Line }})
                  </span>
                </div>

                <div class="bg-light rounded-3 p-3 mb-4 border border-1 shadow-sm">
                  <div class="row g-2 align-items-center text-dark">
                    <div class="col-5 small">
                      <div class="text-muted text-uppercase" style="font-size: 0.7rem">Salida</div>
                      <div class="fw-bold">{{ trip.Departure_Port }}</div>
                      <div class="text-primary fw-bold" style="font-size: 0.75rem">
                        {{ trip.Start_Date }}
                      </div>
                    </div>
                    <div class="col-2 text-center text-muted">
                      <i class="bi bi-arrow-right fs-5"></i>
                      <div style="font-size: 0.7rem">{{ trip.Duration_Nights }} Noches</div>
                    </div>
                    <div class="col-5 small text-end">
                      <div class="text-muted text-uppercase" style="font-size: 0.7rem">Llegada</div>
                      <div class="fw-bold">{{ trip.Arrival_Port }}</div>
                      <div class="text-danger fw-bold" style="font-size: 0.75rem">
                        {{ trip.End_Date }}
                      </div>
                    </div>
                  </div>
                </div>

                <div
                  class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center"
                >
                  <div class="small">
                    <i class="bi bi-people-fill text-muted me-1"></i>
                    <span class="text-muted">Capacidad:</span>
                    <b class="text-dark ms-1">{{ trip.Max_Capacity }}</b>
                  </div>
                  <div class="btn-group">
                    <button
                      class="btn btn-outline-secondary border-0 btn-sm px-3"
                      @click="openTripModal(trip)"
                    >
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button
                      class="btn btn-outline-danger border-0 btn-sm px-3"
                      @click="handleDelete(trip.Id_Trip)"
                    >
                      <i class="bi bi-trash3-fill"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showTripModal"
      class="modal-backdrop-custom d-flex justify-content-center align-items-center p-3"
      style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1050;
        backdrop-filter: blur(4px);
      "
    >
      <div
        class="modal-content-custom p-4 rounded-4 shadow-lg bg-white text-dark"
        style="max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto"
      >
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
          <h4 class="modal-title fw-bold text-primary">
            {{ tripForm.idTrip ? '🚢 Editar Itinerario' : '🚢 Programar Crucero' }}
          </h4>
          <button type="button" class="btn-close" @click="closeTripModal"></button>
        </div>

        <form @submit.prevent="saveTrip" class="row g-3">
          <div class="col-12 mb-2 text-center">
            <div
              class="upload-zone position-relative rounded-4 border-dashed d-flex flex-column align-items-center justify-content-center p-3 border-2"
              style="min-height: 180px; background-color: #f8f9fa; border: 2px dashed #dee2e6"
            >
              <img
                v-if="imagePreview"
                :src="imagePreview"
                class="rounded-3 shadow-sm mb-2"
                style="max-height: 140px; width: 100%; object-fit: cover"
              />
              <div v-else class="text-center text-muted py-3">
                <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>
                <p class="small mb-0">Seleccione una fotografía del destino</p>
              </div>
              <input
                type="file"
                @change="onFileSelected"
                style="position: absolute; width: 100%; height: 100%; opacity: 0; cursor: pointer"
                accept="image/*"
              />
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold small">Destino Turístico</label>
            <input type="text" class="form-control" v-model="tripForm.destinationName" required />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">Embarcación</label>
            <input type="text" class="form-control" v-model="tripForm.shipName" required />
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold small">Puerto Origen</label>
            <input type="text" class="form-control" v-model="tripForm.departurePort" required />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">Puerto Destino</label>
            <input type="text" class="form-control" v-model="tripForm.arrivalPort" required />
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold small">Fecha de Inicio (Zarpe)</label>
            <input type="date" class="form-control" v-model="tripForm.startDate" required />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">Fecha de Finalización (Llegada)</label>
            <input type="date" class="form-control" v-model="tripForm.endDate" required />
          </div>

          <div class="col-md-3">
            <label class="form-label fw-bold small">Precio (USD)</label>
            <input type="number" class="form-control" v-model="tripForm.price" required />
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold small">Pasajeros Máx.</label>
            <input type="number" class="form-control" v-model="tripForm.maxCapacity" required />
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold small">Naviera</label>
            <input type="text" class="form-control" v-model="tripForm.cruiseLine" required />
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold small">Noches</label>
            <input type="number" class="form-control" v-model="tripForm.durationNights" required />
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold small">¿Requiere Visa?</label>
            <select class="form-select" v-model="tripForm.requiresVisa">
              <option :value="0">No requiere</option>
              <option :value="1">Sí, es obligatoria</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">¿Incluye Vuelo?</label>
            <select class="form-select" v-model="tripForm.includesFlight">
              <option :value="0">No, solo crucero</option>
              <option :value="1">Sí, vuelo incluido</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label fw-bold small">Descripción del Viaje</label>
            <textarea
              class="form-control"
              v-model="tripForm.description"
              rows="3"
              required
              placeholder="Describe los detalles del itinerario..."
            ></textarea>
          </div>

          <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light px-4" @click="closeTripModal">
              Cancelar
            </button>
            <button
              type="submit"
              class="btn btn-primary fw-bold px-5 shadow-sm"
              :disabled="isActionLoading"
            >
              <span
                v-if="isActionLoading"
                class="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>
              {{ tripForm.idTrip ? 'ACTUALIZAR' : 'PUBLICAR' }}
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
import { useTripLogic } from '../Js/Trips.js'
import { MEDIA_BASE_URL } from '../../../config.js'
import '../Css/Trips.css'

const {
  trips,
  loading,
  isActionLoading,
  showTripModal,
  tripForm,
  imagePreview,
  fetchTrips,
  onFileSelected,
  openTripModal,
  closeTripModal,
  saveTrip,
  handleDelete,
} = useTripLogic()

onMounted(() => {
  fetchTrips()
})
</script>
