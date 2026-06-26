<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebarApp />
    <div class="content-wrapper flex-grow-1 p-0" style="background-color: #f4f7f6">
      <div class="container-fluid p-4" style="min-height: 100vh">
        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h3 class="fw-bold text-dark m-0">
                <i class="bi bi-people-fill me-2 text-primary"></i>Gestión de Viajeros
              </h3>
              <button @click="openModal()" class="btn btn-primary px-4 rounded-pill shadow-sm">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Viajero
              </button>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-8">
                <div class="input-group shadow-sm">
                  <span class="input-group-text bg-white border-2 border-end-0">
                    <i class="bi bi-search text-primary"></i>
                  </span>
                  <input
                    v-model="searchQuery"
                    type="text"
                    class="form-control border-2 border-start-0"
                    placeholder="Buscar por nombre, apellido o documento..."
                  />
                </div>
              </div>
              <div class="col-md-4">
                <select v-model="selectedNationality" class="form-select border-2 shadow-sm">
                  <option value="">🌍 Todas las nacionalidades</option>
                  <option v-for="nation in uniqueNationalities" :key="nation" :value="nation">
                    {{ nation }}
                  </option>
                </select>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Viajero</th>
                    <th>Documento</th>
                    <th>Nacionalidad</th>
                    <th>Emergencia</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="t in filteredTravelers" :key="t.Id_Traveler">
                    <td>
                      <div class="fw-bold text-dark">{{ t.Last_Name }}, {{ t.First_Name }}</div>
                      <small class="text-muted">{{ t.Birth_Date }} | {{ t.Gender }}</small>
                    </td>
                    <td>
                      <span class="badge bg-info-subtle text-info border border-info px-3"
                        >{{ t.Document_Type }}: {{ t.Id_Card_Passport }}</span
                      >
                    </td>
                    <td>{{ t.Nationality }}</td>
                    <td>
                      <div class="small fw-semibold text-dark">{{ t.Emergency_Contact }}</div>
                      <div class="small text-primary fw-bold">{{ t.Emergency_Phone }}</div>
                    </td>
                    <td class="text-center">
                      <button
                        @click="openModal(t)"
                        class="btn btn-sm btn-outline-warning me-2 border-0 rounded-circle p-2"
                      >
                        <i class="bi bi-pencil-square fs-5"></i>
                      </button>
                      <button
                        @click="confirmDelete(t.Id_Traveler)"
                        class="btn btn-sm btn-outline-danger border-0 rounded-circle p-2"
                      >
                        <i class="bi bi-trash3 fs-5"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredTravelers.length === 0">
                    <td colspan="5" class="text-center py-4 text-muted">
                      No se encontraron viajeros con esos criterios.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="travelerModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title fw-bold">
              {{ isEditing ? 'Actualizar Información' : 'Registro de Viajero' }}
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body p-4 text-dark">
            <form @submit.prevent="handleSave">
              <div class="row g-3 mb-4">
                <div class="col-12">
                  <div
                    class="form-check form-switch p-3 bg-light rounded-3 d-flex align-items-center border"
                  >
                    <input
                      class="form-check-input ms-0 me-3"
                      type="checkbox"
                      v-model="isManualMode"
                      id="modeSwitch"
                      style="width: 3rem; height: 1.5rem"
                    />
                    <label class="form-check-label fw-bold m-0" for="modeSwitch">
                      {{
                        isManualMode
                          ? '🔓 Modo Manual (Pasaporte / CE / Edición Libre)'
                          : '🔒 Modo DNI (Consulta RENIEC Automática)'
                      }}
                    </label>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label small fw-bold text-muted text-uppercase"
                    >Tipo Doc.</label
                  >
                  <select
                    v-model="form.documentType"
                    class="form-select border-2"
                    :disabled="!isManualMode"
                  >
                    <option value="DNI">DNI (Perú)</option>
                    <option value="PAS">PASAPORTE</option>
                    <option value="CE">CARNET EXTR.</option>
                  </select>
                </div>

                <div class="col-md-8 position-relative">
                  <label class="form-label small fw-bold text-muted text-uppercase"
                    >Número de Documento</label
                  >
                  <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-2 border-end-0">
                      <i class="bi bi-card-text"></i>
                    </span>
                    <input
                      v-model="form.idCardPassport"
                      type="text"
                      class="form-control border-2 border-start-0 border-end-0"
                      :placeholder="isManualMode ? 'Ingrese nro...' : 'Escriba 8 dígitos...'"
                      maxlength="15"
                      required
                    />
                    <button
                      v-if="form.documentType === 'DNI'"
                      class="btn btn-primary border-2 px-3"
                      type="button"
                      @click="buscarDniReniec"
                      :disabled="searching || form.idCardPassport.length !== 8"
                    >
                      <i v-if="!searching" class="bi bi-search"></i>
                      <span v-else class="spinner-border spinner-border-sm"></span>
                    </button>
                  </div>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-muted text-uppercase">Nombres</label>
                  <input
                    v-model="form.firstName"
                    type="text"
                    class="form-control"
                    :readonly="!isManualMode && form.firstName !== ''"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-muted text-uppercase"
                    >Apellidos</label
                  >
                  <input
                    v-model="form.lastName"
                    type="text"
                    class="form-control"
                    :readonly="!isManualMode && form.lastName !== ''"
                    required
                  />
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold text-muted text-uppercase"
                    >Nacionalidad</label
                  >
                  <input v-model="form.nationality" type="text" class="form-control" />
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold text-muted text-uppercase"
                    >Fecha Nac.</label
                  >
                  <input v-model="form.birthDate" type="date" class="form-control" />
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold text-muted text-uppercase">Género</label>
                  <select v-model="form.gender" class="form-select">
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>
              </div>

              <div class="row g-3 mt-3 p-3 border rounded-3 bg-light">
                <div class="col-12 mt-0">
                  <span class="badge bg-danger mb-2">Contacto de Emergencia</span>
                </div>
                <div class="col-md-7">
                  <input
                    v-model="form.emergencyContact"
                    type="text"
                    class="form-control shadow-sm"
                    placeholder="Nombre completo del contacto"
                  />
                </div>
                <div class="col-md-5">
                  <input
                    v-model="form.emergencyPhone"
                    type="text"
                    class="form-control shadow-sm"
                    placeholder="Teléfono / WhatsApp"
                  />
                </div>
              </div>

              <div class="d-grid mt-4">
                <button
                  type="submit"
                  class="btn btn-primary btn-lg rounded-pill shadow"
                  :disabled="loading"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  {{ isEditing ? 'Guardar Cambios' : 'Finalizar Registro' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { Modal } from 'bootstrap'
import SlidSidebarApp from '../../Common/SlidSidebarApp.vue'
import { useTravelers } from '../Js/Travelers.js'
import iziToast from 'izitoast'
import '../Css/Traveler.css'

const { travelers, loading, fetchTravelers, saveTraveler, deleteTraveler, checkDniInReniec } =
  useTravelers()
const searchQuery = ref('')
const selectedNationality = ref('')

const isEditing = ref(false)
const isManualMode = ref(false)
const searching = ref(false)
let modalInstance = null

const form = ref({
  idTraveler: null,
  firstName: '',
  lastName: '',
  birthDate: '',
  gender: 'M',
  nationality: 'Peruana',
  documentType: 'DNI',
  idCardPassport: '',
  emergencyContact: '',
  emergencyPhone: '',
})

const buscarDniReniec = async () => {
  const dni = form.value.idCardPassport

  if (dni.length !== 8) {
    iziToast.warning({ title: 'Atención', message: 'El DNI debe tener 8 dígitos' })
    return
  }

  searching.value = true
  try {
    const data = await checkDniInReniec(dni)
    if (data && (data.success || data.status === 200)) {
      const nombreCompleto = data.nombreCompleto || data.data?.nombreCompleto || ''
      const partes = nombreCompleto.split(' ')

      if (partes.length >= 3) {
        form.value.lastName = `${partes[partes.length - 2]} ${partes[partes.length - 1]}`
        form.value.firstName = partes.slice(0, partes.length - 2).join(' ')
      } else {
        form.value.firstName = nombreCompleto
      }

      iziToast.success({ title: 'Éxito', message: 'Datos recuperados de RENIEC' })
    } else {
      iziToast.warning({
        title: 'Atención',
        message: 'No se encontró información. Verifique el número.',
      })
    }
  } finally {
    searching.value = false
  }
}

const filteredTravelers = computed(() => {
  return travelers.value.filter((t) => {
    const query = searchQuery.value.toLowerCase()
    const matchesSearch =
      t.First_Name?.toLowerCase().includes(query) ||
      t.Last_Name?.toLowerCase().includes(query) ||
      t.Id_Card_Passport?.includes(query)
    const matchesNationality =
      selectedNationality.value === '' || t.Nationality === selectedNationality.value

    return matchesSearch && matchesNationality
  })
})

const uniqueNationalities = computed(() => {
  const nations = travelers.value.map((t) => t.Nationality).filter((n) => n)
  return [...new Set(nations)].sort()
})

const openModal = (data = null) => {
  if (data) {
    isEditing.value = true
    isManualMode.value = true
    form.value = {
      idTraveler: data.Id_Traveler,
      firstName: data.First_Name,
      lastName: data.Last_Name,
      birthDate: data.Birth_Date,
      gender: data.Gender,
      nationality: data.Nationality,
      documentType: data.Document_Type,
      idCardPassport: data.Id_Card_Passport,
      emergencyContact: data.Emergency_Contact,
      emergencyPhone: data.Emergency_Phone,
    }
  } else {
    isEditing.value = false
    isManualMode.value = false
    resetForm()
  }
  modalInstance.show()
}

const handleSave = async () => {
  const result = await saveTraveler(form.value)
  if (result.success) {
    modalInstance.hide()
    fetchTravelers()
  }
}

const confirmDelete = async (id) => {
  if (confirm('¿Está seguro de eliminar este viajero de forma permanente?')) {
    const success = await deleteTraveler(id)
    if (success) fetchTravelers()
  }
}

const resetForm = () => {
  form.value = {
    idTraveler: null,
    firstName: '',
    lastName: '',
    birthDate: '',
    gender: 'M',
    nationality: 'Peruana',
    documentType: 'DNI',
    idCardPassport: '',
    emergencyContact: '',
    emergencyPhone: '',
  }
}

onMounted(() => {
  modalInstance = new Modal(document.getElementById('travelerModal'))
  fetchTravelers()
})
</script>
