package com.roxcode.leestravelcruises.ui.view

import android.os.Bundle
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import com.roxcode.leestravelcruises.data.model.HistoryModel
import com.roxcode.leestravelcruises.data.model.PassengerModel
import com.roxcode.leestravelcruises.databinding.ActivityRegisterHistoryBinding
import com.roxcode.leestravelcruises.ui.viewmodel.HistoryViewModel
import com.roxcode.leestravelcruises.ui.viewmodel.PassengerViewModel
import com.roxcode.leestravelcruises.utils.SessionManager
import java.text.SimpleDateFormat
import java.util.*

class RegisterHistoryActivity : AppCompatActivity() {

    private lateinit var binding: ActivityRegisterHistoryBinding
    private val viewModel: HistoryViewModel by viewModels()
    private val passengerViewModel: PassengerViewModel by viewModels()
    private lateinit var sessionManager: SessionManager

    private var idTrip: Int = 0
    private var pasajeroSeleccionado: PassengerModel? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityRegisterHistoryBinding.inflate(layoutInflater)
        setContentView(binding.root)

        sessionManager = SessionManager(this)
        idTrip = intent.getIntExtra("idTrip", 0)

        val tripName = intent.getStringExtra("TRIP_NAME") ?: "Sin nombre de viaje"

        binding.includeToolbar.btnHeaderAction.setOnClickListener {
            finish()
        }

        setupHeader(tripName)
        cargarListaPasajeros()

        binding.btnSubmitHistory.setOnClickListener {
            validateAndSubmit()
        }

        viewModel.operationSuccess.observe(this) { message ->
            if (message != null) {
                Toast.makeText(this, message, Toast.LENGTH_SHORT).show()
                finish()
            }
        }
    }

    private fun cargarListaPasajeros() {
        passengerViewModel.getPassengers(idTrip)
        passengerViewModel.passengers.observe(this) { lista ->
            if (lista != null) {
                val nombres = lista.map { "${it.firstName} ${it.lastName}" }
                val adapter = ArrayAdapter(this, android.R.layout.simple_dropdown_item_1line, nombres)
                binding.actvPassenger.setAdapter(adapter)

                binding.actvPassenger.setOnItemClickListener { _, _, position, _ ->
                    pasajeroSeleccionado = lista[position]
                }
            }
        }
    }

    private fun setupHeader(tripName: String) {
        binding.tvTripInfo.text = "Viaje: $tripName"
        val userName = sessionManager.getUserName()
        binding.tvReporterName.text = "Reportado por: $userName"

        val dateFormat = SimpleDateFormat("dd/MM/yyyy HH:mm", Locale.getDefault())
        binding.tvDateTime.text = "Fecha: ${dateFormat.format(Date())}"
    }

    private fun validateAndSubmit() {
        val description = binding.etDescription.text.toString()

        if (pasajeroSeleccionado == null) {
            Toast.makeText(this, "Por favor, selecciona un pasajero de la lista", Toast.LENGTH_SHORT).show()
            return
        }

        if (description.isEmpty()) {
            Toast.makeText(this, "Escribe una descripción", Toast.LENGTH_SHORT).show()
            return
        }

        val newHistory = HistoryModel(
            idTrip = idTrip,
            idPassenger = pasajeroSeleccionado!!.idPassenger,
            idGuiaUser = sessionManager.getUserId(),
            eventDescription = description,
            eventDate = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault()).format(Date())
        )

        viewModel.registerLog(newHistory)
    }
}