package com.roxcode.leestravelcruises.ui.viewmodel

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.roxcode.leestravelcruises.data.model.ApiResponse
import com.roxcode.leestravelcruises.data.model.LoginData
import com.roxcode.leestravelcruises.data.repository.LoginRepository
import kotlinx.coroutines.launch

class LoginViewModel : ViewModel() {
    private val repository = LoginRepository()

    private val _loginResponse = MutableLiveData<ApiResponse<LoginData>?>()
    val loginResponse: LiveData<ApiResponse<LoginData>?> = _loginResponse

    fun login(email: String, pass: String) {
        viewModelScope.launch {
            try {
                val response = repository.login(email, pass)
                if (response.isSuccessful) {
                    _loginResponse.value = response.body()
                } else {
                    _loginResponse.value = ApiResponse(response.code(), "Error: ${response.code()}", null)
                }
            } catch (e: Exception) {
                _loginResponse.value = ApiResponse(500, e.localizedMessage ?: "Error de red", null)
            }
        }
    }
}