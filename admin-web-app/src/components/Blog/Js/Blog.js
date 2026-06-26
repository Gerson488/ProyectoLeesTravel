  import { ref } from 'vue'
  import axios from 'axios'
  import Swal from 'sweetalert2'
  import iziToast from 'izitoast'
  import { MEDIA_BASE_URL } from '../../../config.js'

  const ADMIN_API_URL = '/Admin/';

  export function useBlogLogic() {
    const posts = ref([])
    const trips = ref([])
    const loading = ref(false)
    const isActionLoading = ref(false)
    const showPostModal = ref(false)
    const selectedFiles = ref([])
    const imagePreviews = ref([])

    const postForm = ref({
      idPost: null,
      idTrip: '',
      idUser: 1, 
      title: '',
      description: '',
      latitude: '',
      longitude: '',
    })
    const fetchPosts = async () => {
      loading.value = true
      try {
        const response = await axios.get(`${ADMIN_API_URL}GetAllPublicationsAdmin.php`)
        if (response.data && (response.data.status === true || response.data.status === 200)) {
          posts.value = response.data.data || []
        }
      } catch (errFetch) {
        console.error("Error al obtener posts:", errFetch)
        iziToast.error({ title: 'Error', message: 'No se pudo cargar el historial de publicaciones', position: 'topRight' })
      } finally {
        loading.value = false
      }
    }
    const fetchTrips = async () => {
      try {
        const response = await axios.get('Api/Trips/GetAllTrips.php')
        if (response.data && (response.data.status === 200 || response.data.status === true)) {
          trips.value = response.data.data || []
        }
      } catch (errTrips) {
        console.error('Error cargando cruceros', errTrips)
      }
    }
    const updatePostStatus = async (idPost, newStatus) => {
      try {
        isActionLoading.value = true
        const response = await axios.post(`${ADMIN_API_URL}ModeratePublicationAdmin.php`, {
          idPost: idPost,
          status: newStatus
        })
        
        if (response.data && (response.data.status === true || response.data.status === 200)) {
          iziToast.success({
            title: 'Éxito',
            message: response.data.message || `Publicación marcada como ${newStatus}`,
            position: 'topRight'
          })
          await fetchPosts() 
        } else {
          iziToast.error({ title: 'Error', message: response.data.message })
        }
      } catch (errStatus) {
        console.error("Error moderación:", errStatus)
        iziToast.error({ title: 'Error', message: 'No se pudo procesar la moderación' })
      } finally {
        isActionLoading.value = false
      }
    }

    const onFilesSelected = (event) => {
      const files = Array.from(event.target.files)
      selectedFiles.value = files
      imagePreviews.value = files.map((file) => URL.createObjectURL(file))
    }
    const openPostModal = async (post = null) => {
      if (post) {
        try {
          const response = await axios.post(`${ADMIN_API_URL}GetPublicationDetailAdmin.php`, {
            idPost: post.Id_Post
          })

          if (response.data && (response.data.status === true || response.data.status === 200)) {
            const detail = response.data.data
            postForm.value = {
              idPost: detail.Id_Post,
              idTrip: detail.Id_Trip || '',
              idUser: detail.Id_User,
              title: detail.Title,
              description: detail.Description,
              latitude: detail.Latitude,
              longitude: detail.Longitude,
            }
            imagePreviews.value = detail.gallery 
              ? detail.gallery.map((img) => `${MEDIA_BASE_URL}${img}`)
              : []
            
            showPostModal.value = true
          }
        } catch (errDetail) {
          console.error("Error detalle:", errDetail)
          iziToast.error({ title: 'Error', message: 'No se pudo obtener el detalle' })
        }
      } else {
        resetForm()
        showPostModal.value = true
      }
    }

    const closePostModal = () => {
      showPostModal.value = false
      resetForm()
    }

    const resetForm = () => {
      postForm.value = {
        idPost: null,
        idTrip: '',
        idUser: 1,
        title: '',
        description: '',
        latitude: '',
        longitude: '',
      }
      selectedFiles.value = []
      imagePreviews.value = []
    }
    const savePost = async () => {
      if (!postForm.value.title || !postForm.value.description) {
        iziToast.warning({ title: 'Atención', message: 'Título y descripción son obligatorios' })
        return
      }

      isActionLoading.value = true
      const formData = new FormData()
      formData.append('idPost', postForm.value.idPost || '')
      formData.append('idTrip', postForm.value.idTrip)
      formData.append('idUser', postForm.value.idUser)
      formData.append('title', postForm.value.title)
      formData.append('description', postForm.value.description)
      formData.append('latitude', postForm.value.latitude)
      formData.append('longitude', postForm.value.longitude)

      selectedFiles.value.forEach((file) => {
        formData.append('image[]', file)
      })
      const endpoint = postForm.value.idPost ? 'Api/Blog/UpdatePost.php' : 'Api/Blog/RegisterPost.php'

      try {
        const response = await axios.post(endpoint, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        if (response.data.status === true || response.data.status === 200) {
          Swal.fire('¡Éxito!', response.data.message, 'success')
          closePostModal()
          fetchPosts()
        } else {
          Swal.fire('Aviso', response.data.message, 'warning')
        }
      } catch (errSave) {
        console.error("Error al guardar:", errSave)
        Swal.fire('Error', 'Fallo al procesar el post', 'error')
      } finally {
        isActionLoading.value = false
      }
    }
    const handleDeletePost = async (idPost, idUser) => {
      const result = await Swal.fire({
        title: '¿Eliminar publicación?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, borrar',
      })

      if (result.isConfirmed) {
        try {
          const response = await axios.post('Api/Blog/DeletePost.php', {
            idPost: idPost,
            idUser: idUser
          })
          if (response.data.status === true || response.data.status === 200) {
            Swal.fire('Eliminado', 'Post borrado correctamente', 'success')
            fetchPosts()
          }
        } catch (errDelete) {
          console.error("Error eliminar:", errDelete)
          Swal.fire('Error', 'No se pudo eliminar el post', 'error')
        }
      }
    }

    return {
      posts,
      trips,
      loading,
      isActionLoading,
      showPostModal,
      postForm,
      imagePreviews,
      onFilesSelected,
      openPostModal,
      closePostModal,
      savePost,
      handleDeletePost,
      fetchPosts,
      fetchTrips,
      updatePostStatus, 
    }
  }