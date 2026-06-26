<template>
  <transition name="fade">
    <div v-if="show" class="loader-overlay">
      <div class="loader-content">
        <div class="spinner"></div>
        <p class="loader-text">Cargando...</p>
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  }
})
</script>
<style scoped>
.loader-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.7); 
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(4px); 
}
.loader-content {
  text-align: center;
}
.spinner {
  width: 65px;
  height: 65px;
  border: 6px solid #f3f3f3;
  border-top: 6px solid #ffd000; 
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 15px;
}
.loader-text {
  color: #333;
  font-weight: 600;
  font-size: 1.2rem;
  letter-spacing: 1px;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>