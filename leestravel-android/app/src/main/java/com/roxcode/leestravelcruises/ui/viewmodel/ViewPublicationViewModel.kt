package com.roxcode.leestravelcruises.ui.viewmodel

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.roxcode.leestravelcruises.data.repository.PublicationRepository
import kotlinx.coroutines.launch
import okhttp3.MultipartBody
import okhttp3.RequestBody

class ViewPublicationViewModel : ViewModel() {
    private val repository = PublicationRepository()

    private val _statusMessage = MutableLiveData<String?>()
    val statusMessage: LiveData<String?> = _statusMessage

    fun registerPublication(
        idTrip: RequestBody, idUser: RequestBody, title: RequestBody,
        description: RequestBody, latitude: RequestBody, longitude: RequestBody, images: List<MultipartBody.Part>
    ) {
        viewModelScope.launch {
            try {
                val response = repository.registerPublication(idTrip, idUser, title, description, latitude, longitude, images)
                if (response.isSuccessful) _statusMessage.value = "SUCCESS"
                else _statusMessage.value = "Error: ${response.code()}"
            } catch (e: Exception) {
                _statusMessage.value = e.localizedMessage
            }
        }
    }

    fun updatePublication(
        idPost: RequestBody, idUser: RequestBody, title: RequestBody,
        description: RequestBody, latitude: RequestBody, longitude: RequestBody,
        images: List<MultipartBody.Part>?, retainedImages: RequestBody
    ) {
        viewModelScope.launch {
            try {
                val response = repository.updatePublication(idPost, idUser, title, description, latitude, longitude, retainedImages, images)
                if (response.isSuccessful) _statusMessage.value = "SUCCESS_UPDATE"
                else _statusMessage.value = "Error al actualizar"
            } catch (e: Exception) {
                _statusMessage.value = e.localizedMessage
            }
        }
    }

    fun deletePublication(idPost: String, idUser: Int) {
        viewModelScope.launch {
            try {
                val response = repository.deletePublication(idPost, idUser)
                if (response.isSuccessful) _statusMessage.value = "SUCCESS_DELETE"
                else _statusMessage.value = "Error al eliminar"
            } catch (e: Exception) {
                _statusMessage.value = e.localizedMessage
            }
        }
    }
}