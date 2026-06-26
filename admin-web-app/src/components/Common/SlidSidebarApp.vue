<template>
  <nav
    ref="sidebarRef"
    class="sidebar bg-dark p-3 d-none d-lg-flex flex-column align-items-center shadow-lg flex-shrink-0"
  >
    <div class="d-flex flex-column align-items-center gap-2 w-100">
    <h2 class="text-center mt-4 mb-2 fw-bold title-brand">Lees Travel</h2>

    <hr class="sidebar-divider" />

    <div class="sidebar-section">Gestión Operativa App</div>

    <RouterLink to="/app-dashboard" class="sidebar-btn">
      <i class="bi bi-house-door-fill me-3 fs-4 text-warning"></i>
      <span>Dashboard</span>
    </RouterLink>

    <RouterLink to="/travelers" class="sidebar-btn">
      <i class="bi bi-person-badge-fill me-3 fs-4 text-primary"></i>
      <span>Viajeros</span>
    </RouterLink>

    <RouterLink to="/users" class="sidebar-btn">
      <i class="bi bi-person-lock me-3 fs-4" style="color: #b19cd9"></i>
      <span>Gestión Usuarios</span>
    </RouterLink>

    <RouterLink to="/bookings" class="sidebar-btn">
      <i class="bi bi-journal-check me-3 fs-4 text-warning"></i>
      <span>Agrupaciones</span>
    </RouterLink>

    <RouterLink to="/passengers" class="sidebar-btn">
      <i class="bi bi-people-fill me-3 fs-4 text-success"></i>
      <span>Pasajeros</span>
    </RouterLink>

    <RouterLink to="/asistencia" class="sidebar-btn">
    <i class="bi bi-calendar-check me-3 fs-4 text-warning"></i>
    <span>Asistencia</span>
    </RouterLink>

    <RouterLink to="/blog" class="sidebar-btn">
      <i class="bi bi-newspaper me-3 fs-4 text-info"></i>
      <span>Publicaciones</span>
    </RouterLink>
    
    <RouterLink to="/history" class="sidebar-btn">
      <i class="bi bi-journal-text me-3 fs-4 text-info"></i>
      <span>Bitácora</span>
    </RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const sidebarRef = ref(null)

const handleScroll = () => {
  if (sidebarRef.value) {
    sessionStorage.setItem('sidebar-scroll-app', sidebarRef.value.scrollTop)
  }
}

onMounted(() => {
  const savedScroll = sessionStorage.getItem('sidebar-scroll-app')

  if (savedScroll && sidebarRef.value) {
    sidebarRef.value.scrollTop = savedScroll
  }

  sidebarRef.value?.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  sidebarRef.value?.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.sidebar {
  width: 280px;
  min-width: 280px;
  max-width: 280px;
  height: 100vh;
  position: sticky;
  top: 0;
  overflow-y: auto;
  z-index: 1000;
}

.title-brand {
  letter-spacing: 2px;
  color: #ffd000;
}

.sidebar-divider {
  width: 70%;
  margin: 0 auto 24px auto;
  border-top: 2px solid #fff;
  opacity: 0.3;
}

.sidebar-section {
  width: 100%;
  padding-left: 1.5rem;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  color: #6c757d;
  text-transform: uppercase;
  font-size: 0.8rem;
  font-weight: bold;
  letter-spacing: 1px;
}

.sidebar-btn {
  width: 100%;
  display: flex;
  align-items: center;
  padding: 1rem;
  padding-left: 1.5rem;
  text-decoration: none;
  color: white;
  border-left: 5px solid transparent;
  transition:
    background-color 0.2s ease,
    border-left 0.2s ease,
    padding-left 0.2s ease;
}

.sidebar-btn:hover,
.router-link-active {
  background-color: rgba(255, 255, 255, 0.1);
  border-left: 5px solid #ffd000;
  color: #ffd000;
}

.sidebar-btn:hover {
  padding-left: 1.7rem;
}

.sidebar::-webkit-scrollbar {
  width: 6px;
}

.sidebar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.15);
  border-radius: 10px;
}
</style>
