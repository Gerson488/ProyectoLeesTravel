import { ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import iziToast from 'izitoast'
import { MEDIA_BASE_URL } from '../../../config.js'

export function usePromoLogic() {
  const promos = ref([])
  const trips = ref([])
  const loading = ref(false)
  const isActionLoading = ref(false)
  const showPromoModal = ref(false)
  const imagePreview = ref(null)
  const selectedFile = ref(null)

  const promoForm = ref({
    idPromo: null,
    idTrip: '',
    titleOffer: '',
    description: '',
    actionLink: '',
    specialPrice: 0,
    startDate: '',
    expirationDate: '',
    isActive: 1,
    oldImageBanner: '',
  })

  const fetchPromos = async () => {
    loading.value = true
    try {
      const response = await axios.get('Promotions/GetAllPromotionsAdmin.php')
      const res = response.data
      if (res && res.status === 200) {
        promos.value = res.data || []
      } else {
        promos.value = []
      }
    } catch {
      iziToast.error({
        title: 'Error',
        message: 'No se pudo cargar las promociones',
        position: 'topRight',
      })
    } finally {
      loading.value = false
    }
  }

  const fetchTripsForSelect = async () => {
    try {
      const response = await axios.get('Trips/GetAllTrips.php')
      const res = response.data
      if (res && res.status === 200) {
        trips.value = res.data || []
      }
    } catch {
      Swal.fire('Error', 'Fallo al obtener la lista de cruceros.', 'error')
    }
  }

  const onFileSelected = (event) => {
    const file = event.target.files[0]
    if (file) {
      selectedFile.value = file
      imagePreview.value = URL.createObjectURL(file)
    }
  }

  const openPromoModal = (promo = null) => {
    if (trips.value.length === 0) {
      fetchTripsForSelect()
    }

    if (promo) {
      promoForm.value = {
        idPromo: promo.Id_Promo,
        idTrip: promo.Id_Trip,
        titleOffer: promo.Title_Offer,
        description: promo.Description || '',
        actionLink: promo.Action_Link || '',
        specialPrice: promo.Special_Price_USD,
        startDate: promo.Start_Date || '',
        expirationDate: promo.Expiration_Date || '',
        isActive: promo.Is_Active,
        oldImageBanner: promo.Image_Banner || '',
      }
      imagePreview.value = promo.Image_Banner ? `${MEDIA_BASE_URL}${promo.Image_Banner}` : null
    } else {
      resetForm()
    }
    showPromoModal.value = true
  }

  const closePromoModal = () => {
    showPromoModal.value = false
    resetForm()
  }

  const resetForm = () => {
    promoForm.value = {
      idPromo: null,
      idTrip: '',
      titleOffer: '',
      description: '',
      actionLink: '',
      specialPrice: 0,
      startDate: '',
      expirationDate: '',
      isActive: 1,
      oldImageBanner: '',
    }
    imagePreview.value = null
    selectedFile.value = null
  }

  const savePromo = async () => {
    isActionLoading.value = true
    const formData = new FormData()

    Object.keys(promoForm.value).forEach((key) => {
      formData.append(key, promoForm.value[key] !== null ? promoForm.value[key] : '')
    })

    if (selectedFile.value) {
      formData.append('imageBanner', selectedFile.value)
    }

    const endpoint = promoForm.value.idPromo
      ? 'Promotions/UpdatePromo.php'
      : 'Promotions/RegisterPromo.php'

    try {
      const response = await axios.post(endpoint, formData)
      const res = response.data

      if (res && res.status === 200) {
        Swal.fire('¡Éxito!', res.message || 'Promoción guardada', 'success')
        closePromoModal()
        fetchPromos()
      } else {
        Swal.fire('Atención', res.message || 'Error al guardar', 'warning')
      }
    } catch {
      Swal.fire('Error', 'Fallo de conexión', 'error')
    } finally {
      isActionLoading.value = false
    }
  }

  const handleDelete = async (idPromo) => {
    const result = await Swal.fire({
      title: '¿Borrar Promoción?',
      text: 'Esta acción borrará el banner publicitario.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Eliminar',
    })

    if (result.isConfirmed) {
      try {
        const response = await axios.post('Promotions/DeletePromo.php', { idPromo })
        const res = response.data
        if (res && res.status === 200) {
          Swal.fire('Eliminado', res.message, 'success')
          fetchPromos()
        } else {
          Swal.fire('Aviso', res.message, 'warning')
        }
      } catch {
        Swal.fire('Error', 'No se pudo eliminar.', 'error')
      }
    }
  }

  return {
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
  }
}
