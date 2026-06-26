<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebarApp />

    <div class="content-wrapper flex-grow-1 p-0" style="background-color: #f4f7f6">
      <div class="container-fluid p-4" style="min-height: 100vh">
        <div class="row align-items-center mb-4 mt-2">
          <div class="col-lg-5">
            <h2 class="fw-bold text-dark mb-1">Módulo de Reservas</h2>
            <p class="text-muted">Gestione los tickets y estados de viaje de los clientes</p>
          </div>

          <div class="col-lg-7">
            <div class="d-flex flex-column flex-md-row gap-3 justify-content-lg-end">
              <div
                class="input-group shadow-sm border-0 rounded-pill overflow-hidden"
                style="max-width: 400px"
              >
                <span class="input-group-text bg-white border-0 ps-3">
                  <i class="bi bi-search text-primary"></i>
                </span>
                <input
                  v-model="searchQuery"
                  type="text"
                  class="form-control border-0 py-2"
                  placeholder="Buscar por DNI o Nombre..."
                />
              </div>

              <button
                @click="openCreateModal()"
                class="btn btn-primary btn-lg rounded-pill shadow-sm px-4"
              >
                <i class="bi bi-plus-lg me-2"></i> Nueva Reserva
              </button>
            </div>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
          <button
            v-for="status in ['Todas', 'Confirmada', 'Cancelada']"
            :key="status"
            @click="activeFilter = status"
            class="btn btn-sm rounded-pill px-4 fw-bold transition-all shadow-sm"
            :class="
              activeFilter === status ? 'btn-primary' : 'btn-white bg-white text-secondary border'
            "
          >
            {{ status }}
          </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="card-body p-0">
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">Cargando reservas...</p>
            </div>

            <div v-else-if="filteredBookings.length === 0" class="text-center py-5">
              <i class="bi bi-calendar-x fs-1 text-muted opacity-50"></i>
              <p class="mt-3 text-secondary">No se encontraron reservas.</p>
            </div>

            <div v-else class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                  <tr class="text-uppercase small fw-bold text-secondary">
                    <th class="ps-4">N° Orden</th>
                    <th>Comprador / Cliente</th>
                    <th>Fecha de Registro</th>
                    <th>Estado</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in filteredBookings" :key="item.Id_Booking">
                    <td class="ps-4">
                      <span class="fw-bold text-primary">#{{ index + 1 }}</span>
                    </td>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="fw-bold text-dark">{{
                          item.Full_Name || 'Usuario sin nombre'
                        }}</span>
                        <small class="text-muted"
                          ><i class="bi bi-card-text me-1"></i>{{ item.User_Dni }}</small
                        >
                      </div>
                    </td>
                    <td>{{ formatDate(item.Booking_Date) }}</td>
                    <td>
                      <span
                        class="badge rounded-pill px-3 py-2"
                        :class="statusColorClass(item.Booking_Status)"
                      >
                        {{ item.Booking_Status }}
                      </span>
                    </td>
                    <td class="text-end pe-4">
                      <div class="d-flex justify-content-end gap-2">
                        <button
                          @click="openEditModal(item)"
                          class="btn btn-outline-primary btn-sm rounded-circle p-2 shadow-sm"
                        >
                          <i class="bi bi-pencil-square"></i>
                        </button>
                        <button
                          @click="confirmDelete(item.Id_Booking)"
                          class="btn btn-outline-danger btn-sm rounded-circle p-2 shadow-sm"
                        >
                          <i class="bi bi-trash3-fill"></i>
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

    <div
      class="modal fade"
      id="bookingModal"
      tabindex="-1"
      aria-hidden="true"
      data-bs-backdrop="static"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header bg-dark text-white border-0 rounded-top-4">
            <h5 class="modal-title fw-bold">
              <i class="bi" :class="isEditing ? 'bi-pencil-square' : 'bi-people-fill'"></i>
              {{ isEditing ? 'Editar Reserva Grupal' : 'Registrar Reserva Grupal' }}
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              aria-label="Close"
              @click="handleCancelClose"
            ></button>
          </div>
          <div class="modal-body p-4 text-dark">
            <form @submit.prevent="handleSave">
              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <label class="form-label fw-bold text-primary mb-0"
                    >Lista de Pasajeros del Grupo</label
                  >
                  <button
                    type="button"
                    @click="addPassengerField"
                    class="btn btn-outline-primary btn-sm rounded-pill"
                  >
                    <i class="bi bi-plus-circle me-1"></i> Añadir Acompañante
                  </button>
                </div>

                <div class="passenger-list-container" style="max-height: 400px; overflow-y: auto">
                  <div
                    v-for="(passenger, index) in form.passengers"
                    :key="index"
                    class="card border border-light shadow-sm mb-3 p-3 bg-light-subtle rounded-3"
                  >
                    <div class="row align-items-center">
                      <div class="col-md-5">
                        <label class="small fw-bold text-muted mb-1">
                          {{ index === 0 ? '👤 Titular / Responsable' : `👥 Acompañante ${index}` }}
                        </label>
                        <div class="input-group">
                          <span class="input-group-text bg-white border-end-0"
                            ><i class="bi bi-card-text"></i
                          ></span>
                          <input
                            v-model="passenger.dni"
                            type="text"
                            class="form-control border-start-0"
                            placeholder="Ingrese DNI..."
                            @keyup.enter="searchIndividualTraveler(index)"
                          />
                          <button
                            class="btn btn-white border"
                            type="button"
                            @click="searchIndividualTraveler(index)"
                          >
                            <i v-if="!passenger.searching" class="bi bi-search text-primary"></i>
                            <span
                              v-else
                              class="spinner-border spinner-border-sm text-primary"
                            ></span>
                          </button>
                        </div>
                      </div>
                      <div class="col-md-5 mt-3 mt-md-0">
                        <label class="small fw-bold text-muted mb-1">Nombre Completo</label>
                        <input
                          v-model="passenger.fullName"
                          type="text"
                          class="form-control bg-white"
                          readonly
                          placeholder="Resultado de búsqueda..."
                        />
                      </div>
                      <div class="col-md-2 mt-3 mt-md-0 text-end">
                        <button
                          v-if="index !== 0"
                          @click="removePassengerField(index)"
                          type="button"
                          class="btn btn-link text-danger p-0 mt-3"
                        >
                          <i class="bi bi-trash fs-5"></i>
                        </button>
                      </div>
                    </div>
                    <div
                      v-if="passenger.notFound"
                      class="mt-2 text-danger small animate__animated animate__headShake"
                    >
                      <i class="bi bi-x-circle me-1"></i> Viajero no encontrado.
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 mb-4">
                  <label class="form-label fw-bold small text-secondary"
                    >Estado de la Reserva</label
                  >
                  <select v-model="form.bookingStatus" class="form-select shadow-sm border-2">
                    <option value="Confirmada">🟢 Confirmada</option>
                    <option value="Pendiente">🟡 Pendiente</option>
                    <option value="Cancelada">🔴 Cancelada</option>
                  </select>
                </div>
              </div>

              <div class="d-grid gap-2 mt-2">
                <button
                  type="submit"
                  class="btn btn-primary btn-lg rounded-pill fw-bold shadow"
                  :disabled="loading || !isFormReady"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  {{ isEditing ? 'Actualizar Cambios' : 'Generar Reserva Grupal' }}
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
import { useBookings } from '../Js/Booking.js'
import '../Css/Booking.css'
import '../../Home/Css/Home.css'

const {
  bookings,
  loading,
  fetchBookings,
  fetchAllBookings,
  createNewBooking,
  updateStatus,
  removeBooking,
  findTraveler,
  fetchBookingDetails,
} = useBookings()

let modalInstance = null
const currentUser = ref(null)
const isEditing = ref(false)

const searchQuery = ref('')
const activeFilter = ref('Todas')

const form = ref({
  idBooking: null,
  bookingStatus: 'Confirmada',
  passengers: [{ dni: '', fullName: '', idTraveler: null, searching: false, notFound: false }],
})

const isFormReady = computed(() => {
  return form.value.passengers[0] && form.value.passengers[0].fullName !== ''
})

const searchIndividualTraveler = async (index) => {
  const p = form.value.passengers[index]
  if (!p || p.dni.length < 3) return

  p.searching = true
  p.notFound = false

  try {
    const traveler = await findTraveler(p.dni)
    if (traveler) {
      p.fullName = traveler.Full_Name || `${traveler.First_Name} ${traveler.Last_Name}`
      p.idTraveler = traveler.Id_Traveler
      p.notFound = false
    } else {
      p.fullName = ''
      p.idTraveler = null
      p.notFound = true
    }
  } finally {
    p.searching = false
  }
}

const addPassengerField = () => {
  form.value.passengers.push({
    dni: '',
    fullName: '',
    idTraveler: null,
    searching: false,
    notFound: false,
  })
}

const removePassengerField = (index) => {
  form.value.passengers.splice(index, 1)
}

const filteredBookings = computed(() => {
  return bookings.value.filter((item) => {
    const query = searchQuery.value.toLowerCase()
    const matchesSearch =
      (item.User_Dni && item.User_Dni.toString().includes(query)) ||
      (item.Full_Name && item.Full_Name.toLowerCase().includes(query))
    const matchesStatus =
      activeFilter.value === 'Todas' || item.Booking_Status === activeFilter.value
    return matchesSearch && matchesStatus
  })
})

const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  modalInstance.show()
}

const openEditModal = async (item) => {
  isEditing.value = true
  resetForm()
  form.value.idBooking = item.Id_Booking
  form.value.bookingStatus = item.Booking_Status
  form.value.passengers = [
    {
      dni: item.User_Dni,
      fullName: item.Full_Name || 'Usuario sin nombre',
      idTraveler: null,
      searching: false,
      notFound: false,
    },
  ]
  modalInstance.show()
  try {
    const dbPassengers = await fetchBookingDetails(item.Id_Booking)
    if (dbPassengers && dbPassengers.length > 0) {
      form.value.passengers = dbPassengers.map((p) => ({
        dni: p.Id_Card_Passport || p.User_Dni || p.Dni || '',
        fullName:
          p.Full_Name || (p.First_Name ? `${p.First_Name} ${p.Last_Name}` : 'Pasajero Registrado'),
        idTraveler: p.Id_Traveler,
        searching: false,
        notFound: false,
      }))
    }
  } catch (error) {
    console.error('Error al cargar detalles de la reserva:', error)
  }
}

const handleSave = async () => {
  const validPassengers = form.value.passengers.filter((p) => p.fullName !== '')

  if (isEditing.value) {
    const success = await updateStatus(form.value.idBooking, {
      bookingStatus: form.value.bookingStatus,
      passengers: validPassengers,
    })
    if (success) {
      modalInstance.hide()
      await loadBookings()
      setTimeout(() => {
        resetForm()
      }, 300)
    }
  } else {
    const result = await createNewBooking({
      bookingStatus: form.value.bookingStatus,
      passengers: validPassengers,
    })

    if (
      result &&
      (result.success || result.status === 'success' || String(result).includes('Reserva'))
    ) {
      modalInstance.hide()
      await loadBookings()
      setTimeout(() => {
        resetForm()
      }, 300)
    }
  }
}

const handleCancelClose = () => {
  modalInstance.hide()
  setTimeout(() => {
    resetForm()
  }, 300)
}

const confirmDelete = async (id) => {
  if (confirm(`¿Estás seguro de eliminar esta reserva?`)) {
    const success = await removeBooking(id)
    if (success) await loadBookings()
  }
}

const loadBookings = async () => {
  if (!currentUser.value) return
  const role = currentUser.value.accessRole || currentUser.value.role || currentUser.value.rol || ''
  const id = currentUser.value.idUser || currentUser.value.id_user || currentUser.value.id

  if (role.toString().toLowerCase() === 'admin') {
    await fetchAllBookings()
  } else if (id) {
    await fetchBookings(id)
  } else {
    await fetchAllBookings()
  }
}

const resetForm = () => {
  form.value = {
    idBooking: null,
    bookingStatus: 'Confirmada',
    passengers: [{ dni: '', fullName: '', idTraveler: null, searching: false, notFound: false }],
  }
}

const statusColorClass = (status) => {
  if (status === 'Confirmada') return 'bg-success-subtle text-success border border-success'
  if (status === 'Pendiente') return 'bg-warning-subtle text-warning border border-warning'
  return 'bg-danger-subtle text-danger border border-danger'
}

const formatDate = (dateString) => {
  if (!dateString) return '---'
  return new Date(dateString).toLocaleDateString('es-PE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  modalInstance = new Modal(document.getElementById('bookingModal'))
  const session = localStorage.getItem('leestravel_session')
  if (session) {
    currentUser.value = JSON.parse(session)
    loadBookings()
  }
})
</script>
