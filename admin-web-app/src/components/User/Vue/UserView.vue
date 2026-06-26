<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebarApp />

    <div class="content-wrapper flex-grow-1 p-0" style="background-color: #f4f7f6">
      <div class="container-fluid p-4" style="min-height: 100vh">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
          <div>
            <h2 class="fw-bold text-dark mb-1">Gestión de Usuarios del Sistema</h2>
            <p class="text-muted small">Vincule viajeros existentes con credenciales de acceso</p>
          </div>
          <button
            @click="openModal()"
            class="btn btn-purple btn-lg rounded-pill shadow-sm px-4 text-white"
          >
            <i class="bi bi-person-plus-fill me-2"></i> Nuevo Usuario
          </button>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-8">
            <div class="input-group shadow-sm rounded-4 overflow-hidden">
              <span class="input-group-text bg-white border-0 ps-3">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input
                v-model="filtroTexto"
                type="text"
                class="form-control border-0 py-3"
                placeholder="Buscar por Nombre o DNI..."
              />
            </div>
          </div>
          <div class="col-md-4">
            <div class="input-group shadow-sm rounded-4 overflow-hidden">
              <span class="input-group-text bg-white border-0">
                <i class="bi bi-funnel text-muted"></i>
              </span>
              <select v-model="filtroEstadoTabla" class="form-select border-0 py-3">
                <option value="todos">Todos los Estados</option>
                <option :value="1">Solo Activos</option>
                <option :value="0">Solo Inactivos</option>
              </select>
            </div>
          </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-0">
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border text-purple" role="status"></div>
              <p class="mt-2 text-muted small">Cargando personal de Lees Travel...</p>
            </div>

            <div v-else-if="usersFiltrados.length === 0" class="text-center py-5">
              <i class="bi bi-person-exclamation fs-1 text-muted opacity-50"></i>
              <p class="mt-3 text-secondary">No se encontraron usuarios con esos criterios.</p>
            </div>

            <div v-else class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                  <tr class="text-uppercase small fw-bold text-secondary">
                    <th class="ps-4">Personal / Viajero</th>
                    <th>Email de Acceso</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-end pe-4">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in usersFiltrados" :key="user.Id_User">
                    <td class="ps-4">
                      <div class="d-flex align-items-center">
                        <div class="avatar-circle me-3 bg-purple text-white shadow-sm">
                          {{ user.Full_Name ? user.Full_Name.charAt(0).toUpperCase() : 'U' }}
                        </div>
                        <div>
                          <div class="fw-bold text-dark">{{ user.Full_Name }}</div>
                          <small class="text-muted text-uppercase" style="font-size: 0.7rem"
                            >DNI: {{ user.Document_Number || 'N/A' }}</small
                          >
                        </div>
                      </div>
                    </td>
                    <td>{{ user.Email }}</td>
                    <td>
                      <span
                        :class="roleBadgeClass(user.Access_Role)"
                        class="badge rounded-pill px-3 py-2"
                      >
                        {{ user.Access_Role }}
                      </span>
                    </td>
                    <td>
                      <span
                        :class="user.User_Status === 1 ? 'text-success' : 'text-danger'"
                        class="fw-bold small"
                      >
                        ● {{ user.User_Status === 1 ? 'Activo' : 'Inactivo' }}
                      </span>
                    </td>
                    <td class="text-end pe-4">
                      <button
                        @click="openModal(user)"
                        class="btn btn-outline-primary btn-sm rounded-circle p-2 me-2 shadow-sm"
                      >
                        <i class="bi bi-pencil-square"></i>
                      </button>
                      <button
                        @click="confirmDelete(user.Id_User, user.Full_Name)"
                        class="btn btn-outline-danger btn-sm rounded-circle p-2 shadow-sm"
                      >
                        <i class="bi bi-trash3"></i>
                      </button>
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
      id="userModal"
      tabindex="-1"
      aria-hidden="true"
      data-bs-backdrop="static"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header bg-dark text-white border-0 rounded-top-4">
            <h5 class="modal-title fw-bold">
              <i class="bi me-2" :class="isEdit ? 'bi-pencil-square' : 'bi-person-plus-fill'"></i>
              {{ isEdit ? 'Actualizar Credenciales' : 'Vincular Nuevo Usuario' }}
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              @click="resetForm"
            ></button>
          </div>
          <div class="modal-body p-4 text-dark">
            <form @submit.prevent="handleSave">
              <div class="mb-3">
                <label class="form-label fw-bold small text-secondary"
                  >Buscador por DNI o ID de Viajero</label
                >
                <div class="input-group mb-2 shadow-sm rounded-3 overflow-hidden">
                  <span class="input-group-text bg-white border-end-0"
                    ><i class="bi bi-search text-muted"></i
                  ></span>
                  <input
                    v-model="searchTerm"
                    type="text"
                    class="form-control border-start-0 ps-0"
                    placeholder="Escriba el DNI..."
                    @keyup.enter="handleQuickSearch"
                    :disabled="isEdit"
                  />
                  <button
                    class="btn btn-purple text-white px-3"
                    type="button"
                    @click="handleQuickSearch"
                    :disabled="loading || isEdit"
                  >
                    <i v-if="!loading" class="bi bi-arrow-right-short fs-4"></i>
                    <span v-else class="spinner-border spinner-border-sm"></span>
                  </button>
                </div>
              </div>

              <div class="row mb-3 gx-2">
                <div class="col-4">
                  <label class="form-label fw-bold small text-secondary">DNI Confirmado</label>
                  <input
                    :value="form.documentNumber"
                    type="text"
                    class="form-control bg-light border-0 text-center fw-bold text-dark"
                    readonly
                    placeholder="---"
                  />
                </div>
                <div class="col-8">
                  <label class="form-label fw-bold small text-secondary">Nombre del Viajero</label>
                  <input
                    :value="selectedName"
                    type="text"
                    class="form-control border-0 fw-bold"
                    :class="
                      selectedName.includes('⚠️')
                        ? 'text-danger bg-danger-subtle'
                        : 'text-primary bg-primary-subtle'
                    "
                    readonly
                    placeholder="Busque un viajero..."
                  />
                </div>
              </div>

              <hr class="my-4 opacity-25" />

              <div class="mb-3">
                <label class="form-label fw-bold small text-secondary"
                  >Correo Electrónico de Acceso</label
                >
                <div class="input-group">
                  <span class="input-group-text bg-light border-0"
                    ><i class="bi bi-envelope-at text-muted"></i
                  ></span>
                  <input
                    v-model="form.email"
                    type="email"
                    class="form-control bg-light border-0 shadow-sm"
                    placeholder="ejemplo@leestravel.com"
                    required
                  />
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold small text-secondary">
                  {{
                    isEdit ? 'Nueva Contraseña (Dejar vacío para mantener)' : 'Contraseña Inicial'
                  }}
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-0"
                    ><i class="bi bi-shield-lock text-muted"></i
                  ></span>
                  <input
                    v-model="form.password"
                    type="password"
                    class="form-control bg-light border-0 shadow-sm"
                    placeholder="********"
                    :required="!isEdit"
                  />
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-secondary">Rol de Acceso</label>
                  <select
                    v-model="form.accessRole"
                    class="form-select bg-light border-0 shadow-sm"
                    required
                  >
                    <option value="Pasajero">Pasajero (App)</option>
                    <option value="Admin">Administrador</option>
                    <option value="Asesor">Asesor</option>
                    <option value="Guia">Guía Turístico</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-secondary">Estado de Cuenta</label>
                  <select v-model="form.userStatus" class="form-select bg-light border-0 shadow-sm">
                    <option :value="1">Activo</option>
                    <option :value="0">Inactivo / Bloqueado</option>
                  </select>
                </div>
              </div>

              <div class="d-grid gap-2 mt-4 pt-2">
                <button
                  type="submit"
                  class="btn btn-purple btn-lg rounded-pill text-white fw-bold shadow-sm"
                  :disabled="
                    loading || (!isEdit && !form.idTraveler) || selectedName.includes('⚠️')
                  "
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-check-circle-fill me-2"></i>
                  {{ isEdit ? 'Actualizar Información' : 'Confirmar Registro' }}
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
import { useUsers } from '../Js/User.js'
import { useAuth } from '../../Login/Js/Auth.js'
import iziToast from 'izitoast'
import '../../User/Css/User.css'
import '../../Home/Css/Home.css'

const { users, loading, fetchAllUsers, saveUser, removeUser, findTraveler } = useUsers()
const { checkAuth } = useAuth()

let modalInstance = null
const isEdit = ref(false)
const searchTerm = ref('')
const selectedName = ref('')
const filtroTexto = ref('')
const filtroEstadoTabla = ref('todos')

const form = ref({
  idUser: null,
  idTraveler: '',
  documentNumber: '',
  email: '',
  password: '',
  accessRole: 'Asesor',
  userStatus: 1,
})

const usersFiltrados = computed(() => {
  return users.value.filter((user) => {
    const coincideTexto =
      user.Full_Name.toLowerCase().includes(filtroTexto.value.toLowerCase()) ||
      (user.Document_Number && user.Document_Number.includes(filtroTexto.value))
    const coincideEstado =
      filtroEstadoTabla.value === 'todos' || user.User_Status === filtroEstadoTabla.value

    return coincideTexto && coincideEstado
  })
})

const handleQuickSearch = async () => {
  if (!searchTerm.value) return

  const traveler = await findTraveler(searchTerm.value)

  if (traveler) {
    form.value.idTraveler = traveler.Id_Traveler
    form.value.documentNumber = traveler.Document_Number
    selectedName.value = `${traveler.First_Name} ${traveler.Last_Name}`
    iziToast.success({ title: '¡Encontrado!', message: traveler.First_Name, position: 'topRight' })
  } else {
    form.value.idTraveler = ''
    form.value.documentNumber = ''
    selectedName.value = '⚠️ No se encontró el viajero'
    iziToast.error({ title: 'Error', message: 'Datos no válidos', position: 'topRight' })
  }
}

const openModal = (user = null) => {
  if (user) {
    isEdit.value = true
    selectedName.value = user.Full_Name
    searchTerm.value = user.Document_Number || user.Id_Traveler
    form.value = {
      idUser: user.Id_User,
      idTraveler: user.Id_Traveler,
      documentNumber: user.Document_Number,
      email: user.Email,
      accessRole: user.Access_Role === 'Vendedor' ? 'Asesor' : user.Access_Role,
      userStatus: user.User_Status,
      password: '',
    }
  } else {
    isEdit.value = false
    resetForm()
  }
  modalInstance.show()
}

const handleSave = async () => {
  const success = await saveUser(form.value, isEdit.value)
  if (success) {
    modalInstance.hide()
    await fetchAllUsers()
    resetForm()
  }
}

const confirmDelete = async (id, name) => {
  if (confirm(`¿Revocar acceso permanentemente a ${name}?`)) {
    const success = await removeUser(id)
    if (success) await fetchAllUsers()
  }
}

const resetForm = () => {
  form.value = {
    idUser: null,
    idTraveler: '',
    documentNumber: '',
    email: '',
    password: '',
    accessRole: 'Asesor',
    userStatus: 1,
  }
  selectedName.value = ''
  searchTerm.value = ''
}

const roleBadgeClass = (role) => {
  const classes = {
    Admin: 'bg-purple-subtle text-purple border border-purple',
    Asesor: 'bg-warning-subtle text-warning border border-warning',
    Guia: 'bg-success-subtle text-success border border-success',
  }
  return classes[role] || 'bg-info-subtle text-info border border-info'
}

onMounted(() => {
  modalInstance = new Modal(document.getElementById('userModal'))
  const session = checkAuth()
  if (session) fetchAllUsers()
})
</script>
