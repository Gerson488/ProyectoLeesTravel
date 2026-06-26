<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebarApp />
    <div class="content-wrapper flex-grow-1 p-0" style="background-color: #f4f7f6">
      <div class="card border-0 w-100 shadow-none" style="min-height: 100vh; border-radius: 0">
        <div class="card-body p-5">
          <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
            <div>
              <h1 class="display-6 fw-bold text-dark mb-1">Control de Moderación</h1>
              <p class="fs-5 text-muted mb-0">
                Revisa y aprueba el contenido subido por los viajeros
              </p>
            </div>
            <button
              class="btn btn-outline-secondary btn-lg rounded-pill px-4 shadow-sm"
              @click="fetchPosts"
              :disabled="loading"
            >
              <i class="bi bi-arrow-clockwise me-2" :class="{ spin: loading }"></i> Actualizar
            </button>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <div class="input-group shadow-sm rounded-3 overflow-hidden">
                <span class="input-group-text bg-white border-end-0">
                  <i class="bi bi-filter-right text-muted"></i>
                </span>
                <select class="form-select border-start-0 ps-0" v-model="filtroEstado">
                  <option value="todos">Todos los estados</option>
                  <option value="Pendiente">Pendientes de revisión</option>
                  <option value="Aprobado">Publicaciones Aprobadas</option>
                  <option value="Rechazado">Publicaciones Rechazadas</option>
                </select>
              </div>
            </div>
          </div>

          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-warning" role="status"></div>
            <p class="mt-3 text-secondary">Sincronizando con el servidor...</p>
          </div>

          <div v-else class="row g-4">
            <div v-for="post in postsFiltrados" :key="post.Id_Post" class="col-xl-4 col-md-6">
              <div
                class="card h-100 border-0 shadow-sm rounded-4 hover-card overflow-hidden transition-all cursor-pointer"
                @click="openPostModal(post)"
              >
                <div
                  class="bg-light d-flex align-items-center justify-content-center overflow-hidden"
                  style="height: 220px; position: relative"
                >
                  <img
                    v-if="post.gallery && post.gallery.length"
                    :src="`${MEDIA_BASE_URL}${post.gallery[0]}`"
                    class="w-100 h-100 object-fit-cover"
                  />
                  <div v-else class="text-center">
                    <i class="bi bi-image fs-1 text-muted opacity-25"></i>
                    <p class="small text-muted">Sin imagen</p>
                  </div>
                  <div class="position-absolute bottom-0 start-0 m-2 badge bg-dark opacity-75">
                    <i class="bi bi-person-fill me-1"></i> {{ post.First_Name }}
                  </div>
                </div>

                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <span
                      :class="[
                        'badge rounded-pill px-3',
                        post.Moderation_Status === 'Aprobado'
                          ? 'bg-success'
                          : post.Moderation_Status === 'Rechazado'
                            ? 'bg-danger'
                            : 'bg-warning text-dark',
                      ]"
                    >
                      {{ post.Moderation_Status }}
                    </span>
                    <small class="text-muted">{{ formatDate(post.Published_Date) }}</small>
                  </div>

                  <h5 class="fw-bold mb-2 text-dark text-truncate">{{ post.Title }}</h5>
                  <p class="text-muted small mb-4 text-truncate-3" style="height: 4.5em">
                    {{ post.Description }}
                  </p>

                  <div class="d-flex gap-2">
                    <button
                      class="btn btn-light btn-sm rounded-pill flex-grow-1 border"
                      @click.stop="openPostModal(post)"
                    >
                      <i class="bi bi-eye me-1"></i> Revisar
                    </button>

                    <template v-if="post.Moderation_Status === 'Pendiente'">
                      <button
                        class="btn btn-success btn-sm rounded-circle"
                        title="Aprobar"
                        @click.stop="updatePostStatus(post.Id_Post, 'Aprobado')"
                      >
                        <i class="bi bi-check-lg"></i>
                      </button>
                      <button
                        class="btn btn-danger btn-sm rounded-circle"
                        title="Rechazar"
                        @click.stop="updatePostStatus(post.Id_Post, 'Rechazado')"
                      >
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </template>

                    <template v-else>
                      <button
                        class="btn btn-outline-secondary btn-sm rounded-pill px-3"
                        title="Restablecer a Pendiente"
                        @click.stop="updatePostStatus(post.Id_Post, 'Pendiente')"
                      >
                        <i class="bi bi-arrow-counterclockwise"></i> Re-evaluar
                      </button>
                    </template>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="postsFiltrados.length === 0" class="col-12 text-center py-5">
              <i class="bi bi-inbox fs-1 text-muted"></i>
              <p class="text-muted mt-2">No hay publicaciones en esta categoría.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showPostModal"
      class="custom-modal-overlay d-flex align-items-center justify-content-center"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered w-100 px-3">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header bg-dark p-4 d-flex align-items-center justify-content-between">
            <h5 class="modal-title fw-bold text-warning m-0">DETALLES DE LA PUBLICACIÓN</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              @click="closePostModal"
            ></button>
          </div>
          <div class="modal-body p-0 bg-white" style="max-height: 80vh; overflow-y: auto">
            <div class="p-3 bg-light border-bottom">
              <div class="d-flex gap-2 overflow-auto pb-2 custom-scroll">
                <img
                  v-for="(img, idx) in imagePreviews"
                  :key="idx"
                  :src="img"
                  @click="openLightbox(idx)"
                  class="rounded-3 shadow-sm border img-clickable"
                  style="height: 280px; min-width: 320px; object-fit: cover; cursor: pointer"
                />
              </div>
              <p class="text-center small text-muted mt-2 mb-0">
                <i class="bi bi-zoom-in"></i> Haz clic en una imagen para ampliar
              </p>
            </div>

            <div class="p-4">
              <h3 class="fw-bolder text-dark mb-4" style="letter-spacing: -0.5px">
                {{ postForm.title }}
              </h3>

              <div
                class="author-comment-box p-4 rounded-3 shadow-sm"
                style="background-color: #f8f9fa; border-left: 6px solid #ffd000"
              >
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i class="bi bi-person-circle fs-1 text-secondary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="mb-0 fw-bold text-dark fs-5">
                      {{ postForm.firstName || 'Cargando...' }} {{ postForm.lastName || '' }}
                    </p>
                    <div class="d-flex gap-3 flex-wrap mt-1">
                      <span class="text-muted small"
                        ><i class="bi bi-geo-alt-fill text-danger me-1"></i>
                        {{ postForm.latitude }}, {{ postForm.longitude }}</span
                      >
                      <span class="text-muted small"
                        ><i class="bi bi-calendar-event text-primary me-1"></i>
                        {{ formatDate(postForm.publishedDate) }}</span
                      >
                    </div>
                  </div>
                </div>

                <hr class="my-3 opacity-10 border-dark" />

                <div class="description-text">
                  <h6
                    class="text-muted fw-bold small text-uppercase mb-2"
                    style="letter-spacing: 1px"
                  >
                    Comentario de la publicación:
                  </h6>
                  <p
                    class="text-dark fs-5 lh-base m-0"
                    style="white-space: pre-line; color: #2d3436 !important"
                  >
                    {{ postForm.description }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light p-3 gap-2 border-top">
            <button class="btn btn-secondary px-4 rounded-pill fw-bold" @click="closePostModal">
              Cerrar
            </button>

            <template v-if="postForm.moderationStatus === 'Pendiente'">
              <button
                class="btn btn-danger px-4 rounded-pill fw-bold shadow-sm"
                @click="updatePostStatus(postForm.idPost, 'Rechazado')"
              >
                <i class="bi bi-x-circle me-1"></i> Rechazar
              </button>
              <button
                class="btn btn-success px-4 rounded-pill fw-bold shadow-sm"
                @click="updatePostStatus(postForm.idPost, 'Aprobado')"
              >
                <i class="bi bi-check-circle me-1"></i> Aprobar Publicación
              </button>
            </template>

            <template v-else>
              <button
                class="btn btn-warning px-4 rounded-pill fw-bold shadow-sm"
                @click="updatePostStatus(postForm.idPost, 'Pendiente')"
              >
                <i class="bi bi-arrow-counterclockwise me-1"></i> Devolver a Pendiente
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showLightbox" class="lightbox-overlay" @click.self="closeLightbox">
      <button class="lightbox-close" @click="closeLightbox"><i class="bi bi-x-lg"></i></button>
      <button class="lightbox-nav prev" @click="prevImage" v-if="imagePreviews.length > 1">
        <i class="bi bi-chevron-left"></i>
      </button>
      <div class="lightbox-content">
        <img :src="imagePreviews[activeImageIdx]" class="lightbox-img shadow-lg" />
        <div class="lightbox-counter text-white mt-3 fw-bold">
          {{ activeImageIdx + 1 }} / {{ imagePreviews.length }}
        </div>
      </div>
      <button class="lightbox-nav next" @click="nextImage" v-if="imagePreviews.length > 1">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import SlidSidebarApp from '../../Common/SlidSidebarApp.vue'
import { useBlogLogic } from '../Js/Blog.js'
import { MEDIA_BASE_URL } from '../../../config.js'
import '../../Blog/Css/Blog.css'

const {
  posts,
  loading,
  showPostModal,
  postForm,
  imagePreviews,
  openPostModal,
  closePostModal,
  updatePostStatus,
  fetchPosts,
} = useBlogLogic()

const filtroEstado = ref('todos')
const showLightbox = ref(false)
const activeImageIdx = ref(0)

watch(showPostModal, (isOpen) => {
  if (isOpen && posts.value.length > 0) {
    const current = posts.value.find((p) => p.Id_Post === postForm.value.idPost)
    if (current) {
      postForm.value.firstName = current.First_Name
      postForm.value.lastName = current.Last_Name
      postForm.value.publishedDate = current.Published_Date
      postForm.value.moderationStatus = current.Moderation_Status
    }
  }
})

const openLightbox = (index) => {
  activeImageIdx.value = index
  showLightbox.value = true
}
const closeLightbox = () => {
  showLightbox.value = false
}
const nextImage = () => {
  activeImageIdx.value = (activeImageIdx.value + 1) % imagePreviews.value.length
}
const prevImage = () => {
  activeImageIdx.value =
    (activeImageIdx.value - 1 + imagePreviews.value.length) % imagePreviews.value.length
}

const postsFiltrados = computed(() => {
  if (filtroEstado.value === 'todos') return posts.value
  return posts.value.filter((p) => p.Moderation_Status === filtroEstado.value)
})

const formatDate = (dateString) => {
  if (!dateString) return 'Fecha no disponible'
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  fetchPosts()
})
</script>
