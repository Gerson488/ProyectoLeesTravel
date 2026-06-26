package com.roxcode.leestravelcruises.ui.view

import android.content.Intent
import android.content.res.ColorStateList
import android.graphics.Color
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.databinding.ActivityPassengerDetailBinding
import com.roxcode.leestravelcruises.ui.viewmodel.PassengerViewModel
import com.roxcode.leestravelcruises.utils.SessionManager

class PassengerDetailActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPassengerDetailBinding
    private val viewModel: PassengerViewModel by viewModels()
    private lateinit var session: SessionManager

    private var currentTripId: Int = -1

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPassengerDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)

        session = SessionManager(this)
        val idToView = intent.getIntExtra("ID_PASSENGER", -1)
        currentTripId = intent.getIntExtra("ID_TRIP", -1)

        setupToolbar()
        setupTabs()
        setupObservers()

        if (idToView != -1) {
            viewModel.loadPassengerDetail(
                idPassengerToView = idToView,
                idTravelerRequesting = session.getIdTraveler(),
                roleRequesting = session.getUserRole()
            )
        } else {
            finish()
        }
    }

    private fun setupToolbar() {
        binding.includeToolbar.btnHeaderAction.setImageResource(R.drawable.ic_menu_white_24dp)
        binding.includeToolbar.btnHeaderAction.setOnClickListener { finish() }
    }

    private fun setupTabs() {
        updateTabUI(isGeneral = true)

        binding.btnTabGeneral.setOnClickListener {
            updateTabUI(isGeneral = true)
        }
        binding.btnTabMedical.setOnClickListener {
            updateTabUI(isGeneral = false)
        }
    }

    private fun updateTabUI(isGeneral: Boolean) {
        val colorActive = getColor(R.color.primary)
        val colorInactive = Color.TRANSPARENT
        val textActive = getColor(R.color.white)
        val textInactive = getColor(R.color.text_medium)

        if (isGeneral) {
            binding.btnTabGeneral.backgroundTintList = ColorStateList.valueOf(colorActive)
            binding.btnTabGeneral.setTextColor(textActive)
            binding.btnTabMedical.backgroundTintList = ColorStateList.valueOf(colorInactive)
            binding.btnTabMedical.setTextColor(textInactive)
            binding.layoutGeneralInfo.visibility = View.VISIBLE
            binding.layoutMedicalInfo.visibility = View.GONE
        } else {
            binding.btnTabGeneral.backgroundTintList = ColorStateList.valueOf(colorInactive)
            binding.btnTabGeneral.setTextColor(textInactive)
            binding.btnTabMedical.backgroundTintList = ColorStateList.valueOf(colorActive)
            binding.btnTabMedical.setTextColor(textActive)
            binding.layoutGeneralInfo.visibility = View.GONE
            binding.layoutMedicalInfo.visibility = View.VISIBLE
        }
    }

    private fun setupObservers() {
        viewModel.passengerDetail.observe(this) { p ->
            p?.let {
                binding.tvPassengerNameHeader.text = "${it.firstName} ${it.lastName}"
                binding.tvAgeHeader.text = if (it.age > 0) "${it.age} años" else "Edad N/D"

                binding.fieldFullName.tvFieldValue.text = "${it.firstName} ${it.lastName}"
                binding.fieldBirth.tvFieldValue.text = it.birthDate ?: "Sin fecha"
                binding.fieldCountry.tvFieldValue.text = it.nationality ?: "Sin nacionalidad"

                binding.fieldDocType.tvFieldValue.text = when(it.documentType) {
                    "DNI" -> "DNI - Nacional"
                    "PAS" -> "Pasaporte"
                    "CE" -> "Carnet Extranjería"
                    else -> it.documentType ?: "N/D"
                }
                binding.fieldDoc.tvFieldValue.text = it.idCardPassport

                binding.fieldGender.tvFieldValue.text = when(it.gender) {
                    "M" -> "Masculino"
                    "F" -> "Femenino"
                    "Otro" -> "Otro"
                    else -> "No especificado"
                }

                binding.tvMedicalConditions.text = """
                🩸 Grupo Sanguíneo: ${it.bloodType ?: "N/D"}
                ⚠️ Alergias: ${it.allergies ?: "Ninguna"}
                📋 Enfermedades: ${it.chronicDiseases ?: "Ninguna"}
                🔍 Observaciones: ${it.observations ?: "Sin observaciones"}
            """.trimIndent()

                val userRole = session.getUserRole()
                if (userRole == "Guia" || userRole == "Admin") {
                    binding.btnNewIncident.visibility = View.VISIBLE
                }
                binding.btnNewIncident.setOnClickListener { _ ->
                    val targetPassengerId = it.idPassenger

                    try {
                        val destinationClass = Class.forName("com.roxcode.leestravelcruises.ui.view.AddIncidentActivity")
                        val intentIncident = Intent(this@PassengerDetailActivity, destinationClass).apply {
                            putExtra("ID_PASSENGER", targetPassengerId)
                            putExtra("PASSENGER_NAME", "${it.firstName} ${it.lastName}")
                            putExtra("ID_TRIP", currentTripId)
                        }
                        startActivity(intentIncident)
                    } catch (e: ClassNotFoundException) {
                        Toast.makeText(this@PassengerDetailActivity, "La pantalla de incidentes está siendo limpiada o no existe.", Toast.LENGTH_SHORT).show()
                    }
                }
            }
        }

        viewModel.error.observe(this) { msg ->
            msg?.let {
                Toast.makeText(this, it, Toast.LENGTH_LONG).show()
                if (it.contains("403")) finish()
            }
        }
    }
}