import { ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

export function useMedicalLogic() {
  const loadingMedical = ref(false)
  const showMedicalModal = ref(false)
  const selectedPassenger = ref(null)
  const currentIdFile = ref(null)
  const medicalForm = ref({
    idPassenger: null,
    idTraveler: null,
    typeBlood: '',
    allergies: '',
    chronicDiseases: '',
    currentMedication: '',
    observations: '',
  })

  const fetchMedicalByPassenger = async (passenger) => {
    selectedPassenger.value = passenger
    loadingMedical.value = true
    currentIdFile.value = null
    medicalForm.value = {
      idPassenger: passenger.Id_Passenger,
      idTraveler: passenger.Id_Traveler,
      typeBlood: '',
      allergies: 'Ninguna',
      chronicDiseases: 'Ninguna',
      currentMedication: 'Ninguna',
      observations: '',
    }

    try {
      const response = await axios.post('Medical/GetMedicalRecord.php', {
        idPassenger: passenger.Id_Passenger,
      })

      const res = response.data

      if (res && res.status === 200 && res.data) {
        const data = res.data
        currentIdFile.value = data.Id_File || null
        medicalForm.value.typeBlood = data.Blood_Type || ''
        medicalForm.value.allergies = data.Allergies || 'Ninguna'
        medicalForm.value.chronicDiseases = data.Chronic_Diseases || 'Ninguna'
        medicalForm.value.currentMedication = data.Current_Medication || 'Ninguna'
        medicalForm.value.observations = data.Observations || ''

        showMedicalModal.value = true
      } else if (res && (res.status === 201 || res.status === 202)) {
        showMedicalModal.value = true
      } else {
        Swal.fire('Atención', res?.message || 'No se pudo obtener la ficha', 'warning')
      }
    } catch {
      Swal.fire('Error', 'No se pudo conectar con el servidor médico', 'error')
    } finally {
      loadingMedical.value = false
    }
  }

  const saveMedicalRecord = async () => {
    if (!medicalForm.value.typeBlood) {
      Swal.fire('Atención', 'El grupo sanguíneo es obligatorio', 'warning')
      return
    }

    loadingMedical.value = true

    const payload = {
      idPassenger: medicalForm.value.idPassenger,
      idTraveler: medicalForm.value.idTraveler,
      idFile: currentIdFile.value,
      bloodType: medicalForm.value.typeBlood,
      allergies: medicalForm.value.allergies,
      
      chronicDiseases: medicalForm.value.chronicDiseases,
      currentMedication: medicalForm.value.currentMedication,
      
      Blood_Type: medicalForm.value.typeBlood,
      Allergies: medicalForm.value.allergies,
      Chronic_Diseases: medicalForm.value.chronicDiseases,
      Current_Medication: medicalForm.value.currentMedication,
      Observations: medicalForm.value.observations
    }

    const endpoint = currentIdFile.value
      ? 'Medical/UpdateMedicalRecord.php'
      : 'Medical/RegisterMedicalRecord.php'

    try {
      const response = await axios.post(endpoint, payload)
      const res = response.data

      if (res && res.status === 200) {
        await Swal.fire({
          title: '¡Éxito!',
          text: res.message || 'Información guardada correctamente',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false,
        })

        if (selectedPassenger.value) {
          selectedPassenger.value.Blood_Type = medicalForm.value.typeBlood
          selectedPassenger.value.Allergies = medicalForm.value.allergies
          selectedPassenger.value.Chronic_Diseases = medicalForm.value.chronicDiseases
          selectedPassenger.value.Current_Medication = medicalForm.value.currentMedication
          selectedPassenger.value.Observations = medicalForm.value.observations
        }

        showMedicalModal.value = false
      } else {
        Swal.fire('Error', res?.message || 'Error al procesar la solicitud', 'error')
      }
    } catch {
      Swal.fire('Error', 'Hubo un problema de comunicación con el servidor', 'error')
    } finally {
      loadingMedical.value = false
    }
  }

  const closeMedicalModal = () => {
    showMedicalModal.value = false
  }

  return {
    loadingMedical,
    showMedicalModal,
    selectedPassenger,
    medicalForm,
    currentIdFile,
    fetchMedicalByPassenger,
    saveMedicalRecord,
    closeMedicalModal,
  }
}