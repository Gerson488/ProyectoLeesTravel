<template>
  <div class="main-layout container-fluid min-vh-100 d-flex flex-column flex-lg-row p-0">
    <SlidSidebarWeb />

    <div class="content-wrapper flex-grow-1 p-0" style="background-color: #ffffff">
      <div class="container-fluid px-4 py-5" style="min-height: 100vh">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
          <div>
            <h1 class="fw-bold text-dark mb-1">Campañas y Promociones</h1>
            <p class="text-muted small">Administra los banners y ofertas destacadas de la web</p>
          </div>
          <button
            class="btn btn-warning text-white fw-bold px-4 py-2 shadow-sm rounded-pill"
            @click="openPromoModal()"
          >
            <i class="bi bi-megaphone-fill me-2"></i>NUEVA OFERTA
          </button>
        </div>

        <hr class="mb-5 opacity-10" />

        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-warning mb-3" role="status"></div>
          <p class="text-warning fw-bold">Cargando promociones...</p>
        </div>

        <div v-else class="row g-4">
          <div v-if="promos.length === 0" class="col-12 text-center py-5">
            <i class="bi bi-tags fs-1 text-muted opacity-50"></i>
            <p class="text-muted mt-2">No hay ofertas activas en este momento.</p>
          </div>

          <div v-for="promo in promos" :key="promo.Id_Promo" class="col-12 col-md-6 col-xl-4">
            <div
              class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden"
              style="transition: all 0.3s ease"
            >
              <div class="position-relative overflow-hidden" style="height: 200px">
                <img
                  :src="
                    promo.Image_Banner
                      ? `${MEDIA_BASE_URL}${promo.Image_Banner}`
                      : 'https://via.placeholder.com/400x200?text=Banner'
                  "
                  class="card-img-top h-100 w-100"
                  style="object-fit: cover; transition: transform 0.5s ease"
                />
                <div class="position-absolute top-0 end-0 m-3">
                  <span v-if="promo.Is_Active == 1" class="badge bg-success shadow-sm px-3 py-2"
                    >ACTIVA</span
                  >
                  <span v-else class="badge bg-secondary shadow-sm px-3 py-2">PAUSADA</span>
                </div>
              </div>

              <div class="card-body p-4 d-flex flex-column bg-white">
                <h5 class="fw-bold text-dark mb-2">{{ promo.Title_Offer }}</h5>
                <p class="small text-muted mb-3">
                  <i class="bi bi-ship me-1"></i> {{ promo.Destination_Name }} -
                  {{ promo.Ship_Name }}
                </p>

                <div
                  class="d-flex justify-content-between align-items-center mb-4 bg-light p-3 rounded-3 border"
                >
                  <div>
                    <div class="text-muted" style="font-size: 0.75rem">Válida hasta:</div>
                    <div class="fw-bold text-danger">{{ promo.Expiration_Date }}</div>
                  </div>
                  <div class="text-end">
                    <div class="text-muted" style="font-size: 0.75rem">Precio Especial:</div>
                    <div class="fw-bold text-success fs-5">
                      ${{ Number(promo.Special_Price_USD).toLocaleString() }}
                    </div>
                  </div>
                </div>

                <div class="mt-auto pt-3 border-top d-flex justify-content-end gap-2">
                  <button
                    class="btn btn-outline-primary btn-sm px-3"
                    @click="openPromoModal(promo)"
                  >
                    <i class="bi bi-pencil-square"></i> Editar
                  </button>
                  <button
                    class="btn btn-outline-danger btn-sm px-3"
                    @click="handleDelete(promo.Id_Promo)"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showPromoModal"
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
        style="max-width: 750px; width: 100%; max-height: 90vh; overflow-y: auto"
      >
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
          <h4 class="modal-title fw-bold text-warning">
            {{ promoForm.idPromo ? '🏷️ Editar Promoción' : '🏷️ Crear Promoción' }}
          </h4>
          <button type="button" class="btn-close" @click="closePromoModal"></button>
        </div>

        <form @submit.prevent="savePromo" class="row g-3">
          <div class="col-12 mb-2 text-center">
            <div
              class="position-relative rounded-4 border-dashed d-flex flex-column align-items-center justify-content-center p-3 border-2"
              style="min-height: 160px; background-color: #f8f9fa; border: 2px dashed #dee2e6"
            >
              <img
                v-if="imagePreview"
                :src="imagePreview"
                class="rounded-3 shadow-sm mb-2"
                style="max-height: 120px; width: 100%; object-fit: cover"
              />
              <div v-else class="text-center text-muted py-3">
                <i class="bi bi-images fs-1 text-warning"></i>
                <p class="small mb-0">Sube un Banner (Recomendado 1200x400px)</p>
              </div>
              <input
                type="file"
                @change="onFileSelected"
                style="position: absolute; width: 100%; height: 100%; opacity: 0; cursor: pointer"
                accept="image/*"
              />
            </div>
          </div>

          <div class="col-md-12">
            <label class="form-label fw-bold small">Viaje / Crucero Asociado</label>
            <select class="form-select" v-model="promoForm.idTrip" required>
              <option value="" disabled>Seleccione un viaje del catálogo</option>
              <option v-for="t in trips" :key="t.Id_Trip" :value="t.Id_Trip">
                {{ t.Destination_Name }} - {{ t.Ship_Name }} ({{ t.Start_Date }})
              </option>
            </select>
          </div>

          <div class="col-md-8">
            <label class="form-label fw-bold small">Título de la Oferta (Slogan)</label>
            <input
              type="text"
              class="form-control"
              v-model="promoForm.titleOffer"
              required
              placeholder="Ej: 2x1 en el Caribe"
            />
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold small">Precio Promocional (USD)</label>
            <input type="number" class="form-control" v-model="promoForm.specialPrice" required />
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold small">Fecha Inicio Promo</label>
            <input type="date" class="form-control" v-model="promoForm.startDate" />
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold small">Fecha Fin Promo</label>
            <input type="date" class="form-control" v-model="promoForm.expirationDate" required />
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold small">Estado de la Promo</label>
            <select class="form-select" v-model="promoForm.isActive">
              <option :value="1">Activa (Visible)</option>
              <option :value="0">Pausada (Oculta)</option>
            </select>
          </div>

          <div class="col-md-12">
            <label class="form-label fw-bold small">Link de Acción (Opcional)</label>
            <input
              type="text"
              class="form-control"
              v-model="promoForm.actionLink"
              placeholder="https://..."
            />
          </div>

          <div class="col-12">
            <label class="form-label fw-bold small">Descripción / Términos</label>
            <textarea
              class="form-control"
              v-model="promoForm.description"
              rows="2"
              placeholder="Condiciones de la oferta..."
            ></textarea>
          </div>

          <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light px-4" @click="closePromoModal">
              Cancelar
            </button>
            <button
              type="submit"
              class="btn btn-warning text-white fw-bold px-5 shadow-sm"
              :disabled="isActionLoading"
            >
              <span
                v-if="isActionLoading"
                class="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>
              {{ promoForm.idPromo ? 'ACTUALIZAR PROMOCIÓN' : 'PUBLICAR BANNER' }}
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
import { usePromoLogic } from '../Js/Promociones.js'
import { MEDIA_BASE_URL } from '../../../config.js'

const {
  promos,
  trips,
  loading,
  isActionLoading,
  showPromoModal,
  promoForm,
  imagePreview,
  fetchPromos,
  onFileSelected,
  openPromoModal,
  closePromoModal,
  savePromo,
  handleDelete,
} = usePromoLogic()

onMounted(() => {
  fetchPromos()
})
</script>
