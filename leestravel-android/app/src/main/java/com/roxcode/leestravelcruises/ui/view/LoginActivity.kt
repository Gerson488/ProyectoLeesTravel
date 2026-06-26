package com.roxcode.leestravelcruises.ui.view

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.roxcode.leestravelcruises.databinding.ActivityLoginBinding
import com.roxcode.leestravelcruises.ui.viewmodel.LoginViewModel
import com.roxcode.leestravelcruises.utils.SessionManager
import java.net.URLEncoder

class LoginActivity : AppCompatActivity() {
    private lateinit var binding: ActivityLoginBinding
    private val viewModel: LoginViewModel by viewModels()
    private lateinit var sessionManager: SessionManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        sessionManager = SessionManager(this)

        if (sessionManager.isLoggedIn()) {
            goToMain(false)
        }

        setupObservers()

        binding.btnLogin.setOnClickListener {
            performLogin()
        }

        binding.tvContinueGuest.setOnClickListener {
            sessionManager.saveSession(-1, -1, "Pasajero", "Invitado")
            goToMain(true)
        }

        binding.tvRegister.setOnClickListener {
            showRegisterDialog()
        }
    }

    private fun showRegisterDialog() {
        MaterialAlertDialogBuilder(this)
            .setTitle("Contactanos!")
            .setMessage("Para crear tu cuenta en Lees Travel, te conectaremos vía WhatsApp con un asesor de la agencia para verificar tus credenciales. ¿Deseas continuar?")
            .setNegativeButton("Cancelar", null)
            .setPositiveButton("Contactar ahora") { _, _ ->
                openWhatsApp()
            }
            .show()
    }

    private fun showErrorDialog(message: String) {
        MaterialAlertDialogBuilder(this)
            .setTitle("Error al iniciar sesión")
            .setMessage(message)
            .setPositiveButton("Reintentar", null)
            .show()
    }

    private fun openWhatsApp() {
        val phone = "51906066682"
        val message = "Hola Lees Travel, deseo registrarme en la App."
        try {
            val intent = Intent(Intent.ACTION_VIEW)
            val url = "https://api.whatsapp.com/send?phone=$phone&text=" + URLEncoder.encode(message, "UTF-8")
            intent.data = Uri.parse(url)
            startActivity(intent)
        } catch (e: Exception) {
            Toast.makeText(this, "WhatsApp no está instalado", Toast.LENGTH_SHORT).show()
        }
    }

    private fun setupObservers() {
        viewModel.loginResponse.observe(this) { response ->
            binding.progressBar.visibility = View.GONE
            binding.btnLogin.isEnabled = true

            if (response?.status == 200) {
                response.data?.let {
                    println("DEBUG_LOGIN: Usuario -> ${it.idUser} | Rol -> ${it.role} | Nombre -> ${it.fullName}")
                    sessionManager.saveSession(it.idUser, it.idTraveler, it.role, it.fullName ?: "Usuario")

                    goToMain(false)
                }
            } else {
                val msg = response?.message ?: "Usuario o contraseña incorrectos."
                showErrorDialog(msg)
            }
        }
    }

    private fun performLogin() {
        val email = binding.etUser.text.toString().trim()
        val pass = binding.etPassword.text.toString().trim()

        if (email.isNotEmpty() && pass.isNotEmpty()) {
            binding.progressBar.visibility = View.VISIBLE
            binding.btnLogin.isEnabled = false
            viewModel.login(email, pass)
        } else {
            showErrorDialog("Por favor, completa ambos campos para iniciar sesión.")
        }
    }

    private fun goToMain(isGuest: Boolean) {
        val intent = Intent(this, MainScreenActivity::class.java)
        intent.putExtra("IS_GUEST", isGuest)
        startActivity(intent)
        finish()
    }
}