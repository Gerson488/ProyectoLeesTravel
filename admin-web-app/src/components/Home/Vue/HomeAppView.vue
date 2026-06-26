<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebarApp />

    <div class="content-wrapper flex-grow-1 p-0 dashboard-bg">
      <div class="card border-0 w-100 shadow-none bg-transparent" style="min-height: 100vh">
        <div class="card-body p-5">
          <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
            <div>
              <h1 class="display-6 fw-bold text-dark mb-1">
                Dashboard Operativo
                <span class="text-warning fs-4">(App Móvil)</span>
              </h1>

              <p class="fs-5 text-muted mb-0">
                <i class="bi bi-person-circle me-2"></i>
                Bienvenido,
                <span class="text-primary fw-semibold">
                  {{ userDisplayName }}
                </span>
              </p>
            </div>

            <div class="d-flex gap-3">
              <button @click="goToHub" class="btn btn-outline-secondary rounded-pill fw-bold px-4">
                <i class="bi bi-arrow-left-circle me-2"></i>
                Cambiar Entorno
              </button>

              <button
                @click="handleLogout"
                class="btn btn-outline-danger rounded-pill fw-bold px-4"
              >
                <i class="bi bi-box-arrow-right me-2"></i>
                Cerrar sesión
              </button>
            </div>
          </div>

          <div class="row g-4 mb-5">
            <div class="col-lg-3 col-md-6">
              <div class="metric-card card-blue">
                <i class="bi bi-people-fill fs-1"></i>
                <h2>{{ metrics.total_passengers }}</h2>
                <p>Pasajeros Registrados</p>
              </div>
            </div>

            <div class="col-lg-3 col-md-6">
              <div class="metric-card card-green">
                <i class="bi bi-calendar-check-fill fs-1"></i>
                <h2>{{ metrics.total_bookings }}</h2>
                <p>Reservas Totales</p>
              </div>
            </div>

            <div class="col-lg-3 col-md-6">
              <div class="metric-card card-orange">
                <i class="bi bi-newspaper fs-1"></i>
                <h2>{{ metrics.pending_posts }}</h2>
                <p>Posts Pendientes</p>
              </div>
            </div>

            <div class="col-lg-3 col-md-6">
              <div class="metric-card card-red">
                <i class="bi bi-heart-pulse-fill fs-1"></i>
                <h2>{{ metrics.critical_health_alerts }}</h2>
                <p>Alertas Médicas</p>
              </div>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-lg-7">
              <div class="dashboard-card">
                <h4 class="chart-title">
                  <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                  Actividad Operacional
                </h4>

                <div style="height: 350px">
                  <Bar :data="barChartData" :options="barOptions" />
                </div>

                <div class="chart-insight mt-4">
                  <h6 class="fw-bold text-dark">
                    <i class="bi bi-lightbulb-fill text-warning me-2"></i>
                    Interpretación
                  </h6>

                  <p class="text-muted mb-0">
                    El sistema móvil actualmente registra
                    <strong>{{ metrics.total_bookings }}</strong>
                    reservas operativas y
                    <strong>{{ metrics.total_passengers }}</strong>
                    pasajeros activos. Esto refleja el comportamiento general de uso de la
                    aplicación administrativa.
                  </p>
                </div>
              </div>
            </div>

            <div class="col-lg-5">
              <div class="dashboard-card">
                <h4 class="chart-title">
                  <i class="bi bi-pie-chart-fill text-danger me-2"></i>
                  Estado General APP
                </h4>

                <div style="height: 350px">
                  <PolarArea :data="polarChartData" :options="polarOptions" />
                </div>

                <div class="chart-insight mt-4">
                  <h6 class="fw-bold text-dark">
                    <i class="bi bi-lightbulb-fill text-warning me-2"></i>
                    Interpretación
                  </h6>

                  <p class="text-muted mb-0">
                    El ecosistema móvil muestra una mayor concentración en pasajeros y reservas. Las
                    alertas médicas permanecen controladas, mientras que las publicaciones
                    pendientes requieren monitoreo operativo.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="dashboard-card mt-4">
            <div class="d-flex justify-content-between flex-wrap gap-3">
              <div>
                <h5 class="fw-bold text-dark">
                  <i class="bi bi-clock-history text-warning me-2"></i>
                  Última sincronización
                </h5>

                <p class="text-muted mb-0">
                  {{ metrics.server_time }}
                </p>
              </div>

              <div class="text-end">
                <h5 class="fw-bold text-success">Sistema Operativo Estable</h5>

                <p class="text-muted mb-0">Dashboard actualizado correctamente.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../Login/Js/Auth.js'
import { useHomeApp } from '../Js/HomeApp.js'
import SlidSidebarApp from '../../Common/SlidSidebarApp.vue'

import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  ArcElement,
  RadialLinearScale,
  CategoryScale,
  LinearScale,
} from 'chart.js'

import { Bar, PolarArea } from 'vue-chartjs'

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  ArcElement,
  RadialLinearScale,
  CategoryScale,
  LinearScale,
)

const router = useRouter()

const { logout, checkAuth } = useAuth()
const { metrics, fetchHomeData } = useHomeApp()

const userDisplayName = ref('')

const goToHub = () => {
  router.push('/select-platform')
}

const handleLogout = () => {
  logout()
  localStorage.removeItem('leestravel_platform')
}

const barChartData = computed(() => ({
  labels: ['Pasajeros', 'Reservas', 'Posts', 'Alertas'],
  datasets: [
    {
      label: 'Operaciones',
      data: [
        metrics.value.total_passengers,
        metrics.value.total_bookings,
        metrics.value.pending_posts,
        metrics.value.critical_health_alerts,
      ],
      backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#ef4444'],
      borderRadius: 12,
    },
  ],
}))

const polarChartData = computed(() => ({
  labels: ['Pasajeros', 'Reservas', 'Posts', 'Alertas'],
  datasets: [
    {
      data: [
        metrics.value.total_passengers,
        metrics.value.total_bookings,
        metrics.value.pending_posts,
        metrics.value.critical_health_alerts,
      ],
      backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444'],
    },
  ],
}))

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
}

const polarOptions = {
  responsive: true,
  maintainAspectRatio: false,
}

onMounted(async () => {
  const userData = checkAuth()

  if (!userData) {
    router.push('/login')
    return
  }

  userDisplayName.value = userData.Full_Name || userData.fullName || userData.Email || 'Usuario'

  await fetchHomeData()
})
</script>

<style scoped>
.dashboard-bg {
  background: linear-gradient(135deg, #eef4ff, #f8fafc);
}

.metric-card {
  border-radius: 24px;
  padding: 30px;
  color: white;
  text-align: center;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
  transition: 0.3s ease;
}

.metric-card:hover {
  transform: translateY(-8px);
}

.card-blue {
  background: linear-gradient(135deg, #2563eb, #60a5fa);
}

.card-green {
  background: linear-gradient(135deg, #16a34a, #4ade80);
}

.card-orange {
  background: linear-gradient(135deg, #ea580c, #f59e0b);
}

.card-red {
  background: linear-gradient(135deg, #dc2626, #f87171);
}

.dashboard-card {
  background: white;
  border-radius: 24px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.chart-title {
  color: #111827;
  font-weight: 700;
  margin-bottom: 20px;
}

.chart-insight {
  background: #f9fafb;
  border-radius: 18px;
  padding: 20px;
}
</style>
