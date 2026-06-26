<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebarWeb />

    <div class="content-wrapper flex-grow-1 p-0 dashboard-bg">
      <div class="container-fluid py-5 px-4 px-lg-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5">
          <div>
            <h1 class="fw-bold dashboard-title mb-1">
              Dashboard Comercial
              <span class="text-primary">(Web Admin)</span>
            </h1>

            <p class="text-muted fs-5 mb-0">
              <i class="bi bi-person-circle me-2"></i>
              Bienvenido,
              <span class="fw-semibold text-dark">
                {{ userDisplayName }}
              </span>
            </p>
          </div>

          <div class="d-flex gap-3">
            <button @click="goToHub" class="btn btn-light shadow-sm rounded-pill px-4 fw-semibold">
              <i class="bi bi-grid me-2"></i>
              Entornos
            </button>

            <button
              @click="handleLogout"
              class="btn btn-danger rounded-pill px-4 fw-semibold shadow-sm"
            >
              <i class="bi bi-box-arrow-right me-2"></i>
              Salir
            </button>
          </div>
        </div>

        <div class="row g-4 mb-5">
          <div class="col-md-6 col-xl-3">
            <div class="metric-card metric-danger">
              <div class="metric-icon">
                <i class="bi bi-hourglass-split"></i>
              </div>

              <div>
                <h2>{{ metrics.pending_quotes }}</h2>
                <p>Leads Pendientes</p>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-xl-3">
            <div class="metric-card metric-success">
              <div class="metric-icon">
                <i class="bi bi-check-circle-fill"></i>
              </div>

              <div>
                <h2>{{ metrics.attended_leads }}</h2>
                <p>Leads Atendidos</p>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-xl-3">
            <div class="metric-card metric-primary">
              <div class="metric-icon">
                <i class="bi bi-map-fill"></i>
              </div>

              <div>
                <h2>{{ metrics.daily_itineraries }}</h2>
                <p>Itinerarios</p>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-xl-3">
            <div class="metric-card metric-warning">
              <div class="metric-icon">
                <i class="bi bi-clock-history"></i>
              </div>

              <div>
                <h6>{{ metrics.server_time }}</h6>
                <p>Última Sincronización</p>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <div class="col-lg-8">
            <div class="dashboard-card">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                  <h4 class="fw-bold mb-1 section-title">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                    Destinos Más Cotizados
                  </h4>

                  <p class="text-muted mb-0">Ranking comercial de destinos con mayor demanda.</p>
                </div>

                <span class="badge bg-primary-subtle text-primary px-3 py-2"> TOP DESTINOS </span>
              </div>

              <div class="chart-container">
                <Bar v-if="barChartData.labels.length" :data="barChartData" :options="barOptions" />
              </div>

              <div class="insight-box mt-4">
                <i class="bi bi-lightbulb-fill text-warning me-2"></i>

                El sistema detecta que
                <strong>
                  {{ charts.top_destinations?.[0]?.label || 'Sin datos' }}
                </strong>
                presenta actualmente el mayor interés comercial.
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="dashboard-card h-100">
              <div class="mb-4">
                <h4 class="fw-bold mb-1 section-title">
                  <i class="bi bi-pie-chart-fill text-danger me-2"></i>
                  Estado de Leads
                </h4>

                <p class="text-muted mb-0">Distribución actual de atención comercial.</p>
              </div>

              <div class="chart-container pie-container">
                <Doughnut
                  v-if="pieChartData.labels.length"
                  :data="pieChartData"
                  :options="pieOptions"
                />
              </div>

              <div class="mt-4">
                <div class="status-item" v-for="item in charts.quote_status" :key="item.label">
                  <div>
                    <strong>{{ item.label }}</strong>
                  </div>

                  <span class="badge bg-dark">
                    {{ item.qty }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-lg-8">
            <div class="dashboard-card">
              <div class="mb-4">
                <h4 class="fw-bold mb-1 section-title">
                  <i class="bi bi-graph-up-arrow text-success me-2"></i>
                  Demanda Mensual
                </h4>

                <p class="text-muted mb-0">Evolución proyectada de viajes registrados.</p>
              </div>

              <div class="chart-container">
                <Line
                  v-if="lineChartData.labels.length"
                  :data="lineChartData"
                  :options="lineOptions"
                />
              </div>

              <div class="insight-box success-box mt-4">
                <i class="bi bi-activity me-2"></i>

                El flujo comercial mantiene actividad durante los próximos meses registrados en el
                sistema.
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="dashboard-card h-100">
              <div class="mb-4">
                <h4 class="fw-bold mb-1 section-title">
                  <i class="bi bi-stars text-warning me-2"></i>
                  Resumen Ejecutivo
                </h4>

                <p class="text-muted mb-0">Interpretación rápida del dashboard.</p>
              </div>

              <div class="summary-box">
                <div class="summary-item">
                  <div class="summary-icon bg-danger-subtle text-danger">
                    <i class="bi bi-hourglass"></i>
                  </div>

                  <div>
                    <strong>{{ metrics.pending_quotes }}</strong>
                    <p>Leads requieren seguimiento inmediato.</p>
                  </div>
                </div>

                <div class="summary-item">
                  <div class="summary-icon bg-success-subtle text-success">
                    <i class="bi bi-check2-circle"></i>
                  </div>

                  <div>
                    <strong>{{ metrics.attended_leads }}</strong>
                    <p>Consultas ya fueron gestionadas.</p>
                  </div>
                </div>

                <div class="summary-item">
                  <div class="summary-icon bg-primary-subtle text-primary">
                    <i class="bi bi-map"></i>
                  </div>

                  <div>
                    <strong>{{ metrics.daily_itineraries }}</strong>
                    <p>Itinerarios activos disponibles.</p>
                  </div>
                </div>

                <div class="summary-item border-0 pb-0">
                  <div class="summary-icon bg-warning-subtle text-warning">
                    <i class="bi bi-clock"></i>
                  </div>

                  <div>
                    <strong>Actualizado</strong>
                    <p>{{ metrics.server_time }}</p>
                  </div>
                </div>
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
import { useHomeWeb } from '../Js/HomeWeb.js'
import SlidSidebarWeb from '../../Common/SlidSidebarWeb.vue'
import '../Css/Home.css'

import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  ArcElement,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Filler,
} from 'chart.js'

import { Bar, Doughnut, Line } from 'vue-chartjs'

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  ArcElement,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Filler,
)

const router = useRouter()

const { logout, checkAuth } = useAuth()

const { metrics, charts, fetchHomeData } = useHomeWeb()

const userDisplayName = ref('')

const goToHub = () => {
  router.push('/select-platform')
}

const handleLogout = () => {
  logout()
  localStorage.removeItem('leestravel_platform')
}

const barChartData = computed(() => ({
  labels: charts.value.top_destinations.map((d) => d.label),

  datasets: [
    {
      label: 'Cotizaciones',
      data: charts.value.top_destinations.map((d) => d.qty),
      backgroundColor: ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'],
      borderRadius: 12,
      borderSkipped: false,
    },
  ],
}))

const pieChartData = computed(() => ({
  labels: charts.value.quote_status.map((d) => d.label),

  datasets: [
    {
      data: charts.value.quote_status.map((d) => d.qty),
      backgroundColor: ['#22c55e', '#ef4444', '#3b82f6', '#f59e0b'],
      borderWidth: 0,
    },
  ],
}))

const lineChartData = computed(() => ({
  labels: charts.value.monthly_demand.map((d) => d.label),

  datasets: [
    {
      label: 'Demanda',
      data: charts.value.monthly_demand.map((d) => d.qty),
      borderColor: '#4f46e5',
      backgroundColor: 'rgba(79,70,229,0.15)',
      fill: true,
      tension: 0.4,
      pointRadius: 5,
      pointBackgroundColor: '#4f46e5',
    },
  ],
}))

const barOptions = {
  responsive: true,

  plugins: {
    legend: {
      display: false,
    },
  },
}

const pieOptions = {
  responsive: true,

  plugins: {
    legend: {
      position: 'bottom',
    },
  },
}

const lineOptions = {
  responsive: true,

  plugins: {
    legend: {
      display: false,
    },
  },
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
  background: linear-gradient(to bottom right, #f4f7fb, #eef2ff);
}

.dashboard-title {
  font-size: 2.2rem;
  color: #1e293b;
}

.metric-card {
  border-radius: 24px;
  padding: 28px;
  color: white;
  display: flex;
  align-items: center;
  gap: 20px;
  min-height: 150px;
  transition: all 0.3s ease;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

.metric-card:hover {
  transform: translateY(-6px);
}

.metric-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.metric-success {
  background: linear-gradient(135deg, #10b981, #059669);
}

.metric-primary {
  background: linear-gradient(135deg, #4f46e5, #4338ca);
}

.metric-warning {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.metric-icon {
  width: 70px;
  height: 70px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.18);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
}

.metric-card h2,
.metric-card h6 {
  margin: 0;
  font-weight: 800;
}

.metric-card p {
  margin: 0;
  opacity: 0.9;
}

.dashboard-card {
  background: white;
  border-radius: 24px;
  padding: 28px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  height: 100%;
}

.chart-container {
  position: relative;
  height: 340px;
}

.pie-container {
  height: 300px;
}

.insight-box {
  background: #eef2ff;
  border-radius: 16px;
  padding: 18px;
  color: #3730a3;
  font-weight: 500;
}

.success-box {
  background: #ecfdf5;
  color: #065f46;
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 0;
  border-bottom: 1px solid #f1f5f9;
}

.summary-box {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.summary-item {
  display: flex;
  gap: 16px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f1f5f9;
}

.summary-icon {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
}

.summary-item p {
  margin: 0;
  color: #64748b;
  font-size: 0.92rem;
}

.footer-dashboard {
  color: #1e293b;
  font-size: 0.95rem;
}
.section-title {
  color: #111827;
}

.dashboard-card p {
  color: #475569;
}

.dashboard-card h4 i {
  opacity: 0.95;
}
.dashboard-card {
  color: #1e293b;
}

.dashboard-card strong {
  color: #0f172a;
}

.status-item strong {
  color: #1e293b;
}

.text-muted {
  color: #64748b !important;
}
</style>
