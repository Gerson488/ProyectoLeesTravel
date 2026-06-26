<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebarApp />

    <div class="content-wrapper flex-grow-1 p-0" style="background-color: #f8f9fa">
      <div class="container-fluid px-4 py-5" style="min-height: 100vh">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
          <div>
            <h1 class="fw-bold text-dark mb-1">Tripulación y Pasajeros</h1>
            <p class="text-muted small">Control de Seguridad y Registro de Abordaje</p>
          </div>
          <button
            class="btn btn-warning fw-bold px-4 py-2 shadow-sm rounded-3 border-0"
            @click="openEditModal()"
          >
            <i class="bi bi-person-plus-fill me-2"></i> NUEVO PASAJERO
          </button>
        </div>

        <div class="row mb-4 g-3 justify-content-center">
          <div class="col-md-5">
            <div class="input-group shadow-sm rounded-3 overflow-hidden">
              <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="Buscar en todos los viajes por nombre o DNI..."
                v-model="searchTerm"
              />
            </div>
          </div>
          <div class="col-md-5">
            <div class="input-group shadow-sm rounded-3 overflow-hidden">
              <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-ship text-info"></i>
              </span>
              <select
                class="form-select border-start-0 ps-0"
                v-model="selectedTripId"
                @change="filterByTrip"
              >
                <option value="">Filtrar por crucero</option>
                <option v-for="trip in trips" :key="trip.Id_Trip" :value="trip.Id_Trip">
                  {{ trip.Trip_Name || trip.Destination_Name || 'Viaje #' + trip.Id_Trip }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="table-responsive" style="max-height: 68vh">
            <table class="table table-hover mb-0 align-middle text-center">
              <thead class="bg-light border-bottom">
                <tr class="small text-muted text-uppercase">
                  <th class="py-3">#</th>
                  <th class="text-start py-3">Pasajero</th>
                  <th class="py-3">Documento</th>
                  <th class="py-3">Cabina</th>
                  <th class="py-3">Crucero / Estado</th>
                  <th class="py-3">Alerta Médica</th>
                  <th class="py-3">Gestión</th>
                </tr>
              </thead>
              <tbody class="bg-white">
                <tr v-if="loading">
                  <td colspan="7" class="py-5 text-muted">Sincronizando bitácora...</td>
                </tr>
                <tr v-else-if="filteredPassengers.length === 0">
                  <td colspan="7" class="py-5 text-muted small">
                    {{
                      searchTerm
                        ? 'No se encontraron coincidencias'
                        : 'Seleccionar crucero o buscar pasajero'
                    }}
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
                    <small class="text-muted">{{ p.Id_Card_Passport }}</small>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border">{{ p.Cabin_Number }}</span>
                  </td>
                  <td>
                    <div class="mb-1">
                      <span
                        :class="
                          p.Boarding_Status == 1
                            ? 'badge bg-success-subtle text-success border border-success'
                            : 'badge bg-danger-subtle text-danger border border-danger'
                        "
                      >
                        {{ p.Boarding_Status == 1 ? 'ABORDADO' : 'PENDIENTE' }}
                      </span>
                    </div>
                    <div
                      class="small text-muted text-truncate"
                      style="max-width: 150px; margin: 0 auto"
                    >
                      <i class="bi bi-anchor small"></i> {{ p.Trip_Name || 'Viaje #' + p.Id_Trip }}
                    </div>
                  </td>

                  <td>
                    <div
                      v-if="
                        (p.Chronic_Diseases &&
                          p.Chronic_Diseases.trim() !== '' &&
                          p.Chronic_Diseases.toLowerCase() !== 'ninguna' &&
                          p.Chronic_Diseases.toLowerCase() !== 'ninguno' &&
                          p.Chronic_Diseases.toLowerCase() !== 'no tiene') ||
                        (p.Current_Medication &&
                          p.Current_Medication.trim() !== '' &&
                          p.Current_Medication.toLowerCase() !== 'ninguna' &&
                          p.Current_Medication.toLowerCase() !== 'ninguno' &&
                          p.Current_Medication.toLowerCase() !== 'no tiene')
                      "
                      class="p-2 rounded-3 bg-danger-subtle border border-danger"
                    >
                      <div class="text-danger fw-bold mb-1" style="font-size: 0.75rem">
                        <i class="bi bi-exclamation-triangle-fill"></i> CRÍTICO
                      </div>
                      <div
                        v-if="p.Chronic_Diseases"
                        class="text-dark fw-bold"
                        style="font-size: 0.78rem; line-height: 1"
                      >
                        {{ p.Chronic_Diseases }}
                      </div>
                      <div
                        v-if="p.Current_Medication"
                        class="text-muted mt-1"
                        style="font-size: 0.72rem"
                      >
                        <i class="bi bi-capsule"></i> {{ p.Current_Medication }}
                      </div>
                    </div>

                    <div
                      v-else-if="
                        p.Allergies &&
                        p.Allergies.trim() !== '' &&
                        p.Allergies.toLowerCase() !== 'ninguna' &&
                        p.Allergies.toLowerCase() !== 'ninguno' &&
                        p.Allergies.toLowerCase() !== 'no tiene'
                      "
                      class="p-2 rounded-3 bg-warning-subtle border border-warning"
                    >
                      <div class="text-warning-dark fw-bold" style="font-size: 0.72rem">
                        <i class="bi bi-shield-exclamation"></i> ALERGIA
                      </div>
                      <div class="text-dark fw-semibold" style="font-size: 0.75rem">
                        {{ p.Allergies }}
                      </div>
                    </div>

                    <div v-else class="text-success small fw-semibold">
                      <i class="bi bi-check-circle-fill"></i> Estable
                    </div>
                  </td>

                  <td>
                    <div class="d-flex justify-content-center gap-2">
                      <button
                        class="btn btn-sm btn-outline-danger border-0 rounded-circle"
                        @click="fetchMedicalByPassenger(p)"
                      >
                        <i class="bi bi-heart-pulse-fill"></i>
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary border-0 rounded-circle"
                        @click="openEditModal(p)"
                      >
                        <i class="bi bi-pencil-square"></i>
                      </button>
                      <button
                        class="btn btn-sm btn-outline-danger border-0 rounded-circle"
                        @click="deletePassenger(p.Id_Passenger)"
                      >
                        <i class="bi bi-trash3"></i>
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

    <div v-if="showEditModal" class="modal-backdrop-custom">
      <div
        class="modal-content-custom p-4 rounded-4 shadow-lg bg-white border-0"
        style="max-width: 600px; width: 100%"
      >
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="fw-bold m-0 text-dark">
            {{ passengerForm.idPassenger ? 'Editar Pasajero' : 'Registro de Pasajero' }}
          </h5>
          <button type="button" class="btn-close" @click="closeEditModal"></button>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold small">DNI / Pasaporte</label>
            <div class="input-group shadow-sm">
              <input
                type="text"
                class="form-control rounded-start-3 text-dark fw-bold"
                v-model="passengerForm.idCard"
                placeholder="Ingrese documento..."
                @keyup.enter="searchUserByDNI"
              />
              <button
                class="btn btn-warning border-0 rounded-end-3"
                type="button"
                @click="searchUserByDNI"
                :disabled="isActionLoading"
              >
                <span v-if="isActionLoading" class="spinner-border spinner-border-sm"></span>
                <i v-else class="bi bi-search"></i>
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">Nombre Completo</label>
            <input
              type="text"
              class="form-control bg-light border-0"
              v-model="passengerForm.fullName"
              placeholder="Resultado de búsqueda..."
              readonly
            />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">Cabina</label>
            <input type="text" class="form-control" v-model="passengerForm.cabinNumber" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">Crucero</label>
            <select class="form-select" v-model="passengerForm.idTrip">
              <option value="">Seleccionar...</option>
              <option v-for="t in trips" :key="t.Id_Trip" :value="t.Id_Trip">
                {{ t.Trip_Name || t.Destination_Name || 'Crucero #' + t.Id_Trip }}
              </option>
            </select>
            <input type="hidden" v-model="passengerForm.boardingStatus" />
          </div>
          <div class="col-12">
          </div>
        </div>
        <div class="mt-4 d-flex justify-content-end gap-2">
          <button class="btn btn-light px-4" @click="closeEditModal">Cancelar</button>
          <button
            class="btn btn-warning fw-bold px-4 shadow-sm"
            @click="submitPassenger"
            :disabled="isActionLoading"
          >
            GUARDAR
          </button>
        </div>
      </div>
    </div>

    <div v-if="showMedicalModal" class="modal-backdrop-custom">
      <div
        class="modal-content-custom p-4 rounded-4 shadow-lg bg-white border-0"
        style="max-width: 500px; width: 100%"
      >
        <div class="d-flex justify-content-between align-items-center mb-4 text-danger">
          <h5 class="fw-bold m-0"><i class="bi bi-heart-pulse-fill"></i> Expediente Médico</h5>
          <button type="button" class="btn-close" @click="closeMedicalModal"></button>
        </div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-bold small">Tipo de Sangre</label>
            <select class="form-select" v-model="medicalForm.typeBlood">
              <option
                v-for="tipo in ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-']"
                :key="tipo"
                :value="tipo"
              >
                {{ tipo }}
              </option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-bold small">Alergias</label>
            <input type="text" class="form-control" v-model="medicalForm.allergies" />
          </div>
          <div class="col-12">
            <label class="form-label fw-bold small">Enfermedades Crónicas</label>
            <input type="text" class="form-control" v-model="medicalForm.chronicDiseases" />
          </div>
          <div class="col-12">
            <label class="form-label fw-bold small">Medicación Actual</label>
            <input type="text" class="form-control" v-model="medicalForm.currentMedication" />
          </div>
          <div class="col-12">
            <label class="form-label fw-bold small">Observaciones</label>
            <textarea class="form-control" v-model="medicalForm.observations" rows="3"></textarea>
          </div>
        </div>
        <div class="mt-4 d-flex justify-content-end gap-2">
          <button class="btn btn-light px-4" @click="closeMedicalModal">Cerrar</button>
          <button
            class="btn btn-danger fw-bold px-4"
            @click="saveMedicalRecord"
            :disabled="loadingMedical"
          >
            GUARDAR FICHA
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import SlidSidebarApp from '../../Common/SlidSidebarApp.vue'
import { usePassengerLogic } from '../Js/Passenger.js'
import { useMedicalLogic } from '../Js/MedicalRecord.js'
import '../../Passengers/Css/Passenger.css'

const {
  loadingMedical,
  showMedicalModal,
  medicalForm,
  fetchMedicalByPassenger,
  saveMedicalRecord,
  closeMedicalModal,
} = useMedicalLogic()

const {
  passengers,
  trips,
  loading,
  isActionLoading,
  showEditModal,
  passengerForm,
  selectedTripId,
  searchUserByDNI,
  fetchTripsList,
  filterByTrip,
  deletePassenger,
  submitPassenger,
  openEditModal,
  closeEditModal,
} = usePassengerLogic()
const searchTerm = ref('')
const filteredPassengers = computed(() => {
  const q = searchTerm.value.toLowerCase().trim()
  if (!q && !selectedTripId.value) return []

  return passengers.value.filter((p) => {
    const fullName = `${p.First_Name} ${p.Last_Name}`.toLowerCase()
    const dni = p.Id_Card_Passport?.toString().toLowerCase() || ''
    const cabin = p.Cabin_Number?.toLowerCase() || ''

    const matchesSearch = q === '' || fullName.includes(q) || dni.includes(q) || cabin.includes(q)

    if (q !== '') return matchesSearch
    return matchesSearch && (selectedTripId.value == '' || p.Id_Trip == selectedTripId.value)
  })
})

onMounted(() => {
  fetchTripsList()
})
</script>
