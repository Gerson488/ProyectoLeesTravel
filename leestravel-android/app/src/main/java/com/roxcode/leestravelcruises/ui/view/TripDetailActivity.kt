package com.roxcode.leestravelcruises.ui.view

import android.content.Intent
import android.graphics.Color
import android.net.Uri
import android.os.Bundle
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.widget.addTextChangedListener
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.databinding.ActivityTripDetailBinding
import com.roxcode.leestravelcruises.ui.adapter.PassengerAdapter
import com.roxcode.leestravelcruises.ui.viewmodel.PassengerViewModel
import com.roxcode.leestravelcruises.ui.viewmodel.TripsViewModel
import com.roxcode.leestravelcruises.utils.SessionManager
import java.net.URLEncoder

class TripDetailActivity : AppCompatActivity() {

    private lateinit var binding: ActivityTripDetailBinding
    private val viewModel: PassengerViewModel by viewModels()
    private val tripsViewModel: TripsViewModel by viewModels()
    private lateinit var adapter: PassengerAdapter

    private lateinit var session: SessionManager
    private var currentTripId: Int = -1

    private var isDataLoaded = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityTripDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)

        session = SessionManager(this)

        currentTripId = intent.getIntExtra("ID_TRIP", -1)
        val tripTitle = intent.getStringExtra("TRIP_NAME") ?: "Detalle"
        val destination = intent.getStringExtra("DESTINATION") ?: "N/A"
        val status = intent.getIntExtra("STATUS", 0)

        setupToolbar()
        setupHeader(tripTitle, destination, status)
        setupMenu(status, currentTripId, tripTitle, destination)
        setupRecyclerView()
        setupObservers()

        binding.etSearchPassenger.addTextChangedListener { text ->
            if (::adapter.isInitialized) adapter.filter(text.toString())
        }
    }

    override fun onResume() {
        super.onResume()

        if (currentTripId != -1) {
            viewModel.getPassengers(currentTripId)
        }

    }

    private fun setupToolbar() {
        binding.includeToolbar.btnHeaderAction.setImageResource(R.drawable.ic_arrow_back)
        binding.includeToolbar.btnHeaderAction.setOnClickListener { finish() }
    }

    private fun setupHeader(title: String, dest: String, status: Int) {
        binding.tvTripTitleHeader.text = title
        binding.chipDestination.text = dest
        binding.tvStatusHeader.text = if (status == 1) "En Curso" else if (status == 2) "Finalizado" else "Programado"
    }

    private fun setupMenu(status: Int, idTrip: Int, tripTitle: String, destination: String) {
        val userRole = session.getUserRole().lowercase()
        val isTripFinished = status == 2

        binding.btnActionPasajeros.apply {
            ivActionIcon.setImageResource(R.drawable.ic_group_black_24dp)
            tvActionLabel.text = "Pasajeros"
        }

        binding.btnActionBitacora.apply {
            ivActionIcon.setImageResource(R.drawable.ic_assignment)
            tvActionLabel.text = "Bitácora"
            root.setOnClickListener {
                if (isTripFinished) {
                    MaterialAlertDialogBuilder(this@TripDetailActivity)
                        .setTitle("Viaje Finalizado")
                        .setMessage("Este viaje ya ha concluido. No se pueden realizar cambios ni acceder a la bitácora.")
                        .setPositiveButton("Entendido", null)
                        .show()
                } else if (userRole == "guía" || userRole == "guia" || userRole == "tripulante") {
                    val intent = Intent(this@TripDetailActivity, HistoryListActivity::class.java).apply {
                        putExtra("idTrip", idTrip)
                        putExtra("TRIP_NAME", tripTitle)
                    }
                    startActivity(intent)
                } else {
                    MaterialAlertDialogBuilder(this@TripDetailActivity)
                        .setTitle("Acceso Restringido")
                        .setMessage("La bitácora es una herramienta de uso exclusivo para el personal guía y tripulantes.")
                        .setPositiveButton("Entendido", null)
                        .show()
                }
            }
        }

        binding.btnActionMultimedia.apply {
            ivActionIcon.setImageResource(R.drawable.ic_camera_alt)
            tvActionLabel.text = "Fotos y videos"
            root.setOnClickListener {
                if (isTripFinished) {
                    Toast.makeText(this@TripDetailActivity, "El viaje ha finalizado, no se permiten nuevas publicaciones.", Toast.LENGTH_SHORT).show()
                } else {
                    val intent = Intent(this@TripDetailActivity, PublicationActivity::class.java).apply {
                        putExtra("TRIP_ID", idTrip)
                        putExtra("TRIP_NAME", tripTitle)
                        putExtra("DESTINATION", destination)
                        putExtra("TRAVELER_NAME", "Mijhael Anthony")
                    }
                    startActivity(intent)
                }
            }
        }

        binding.btnActionAsistencia.apply {
            ivActionIcon.setImageResource(R.drawable.ic_contact_support_black_24dp)
            if (userRole == "guia" || userRole == "guía" || userRole == "tripulante") {
                tvActionLabel.text = "Tomar Asistencia"
                cardActionButton.setOnClickListener {
                    if (isTripFinished) {
                        MaterialAlertDialogBuilder(this@TripDetailActivity)
                            .setTitle("Viaje Finalizado")
                            .setMessage("Ya no es posible modificar la asistencia en un viaje finalizado.")
                            .setPositiveButton("Entendido", null)
                            .show()
                    } else {
                        val intent = Intent(this@TripDetailActivity, AttendanceActivity::class.java)
                        intent.putExtra("ID_TRIP", idTrip)
                        intent.putExtra("TRIP_NAME", tripTitle)
                        startActivity(intent)
                    }
                }
            } else {
                tvActionLabel.text = "Asistencia"
                cardActionButton.setOnClickListener { showWhatsAppConfirmationDialog() }
            }
        }

        binding.btnActionItinerario.apply {
            ivActionIcon.setImageResource(R.drawable.ic_event_note_black_24dp)
            tvActionLabel.text = "Itinerario"
            cardActionButton.setOnClickListener {
                val intent = Intent(this@TripDetailActivity, ItineraryActivity::class.java)
                intent.putExtra("ID_TRIP", idTrip)
                startActivity(intent)
            }
        }

        binding.btnActionStatus.apply {
            if (userRole == "guia" || userRole == "guía" || userRole == "tripulante") {
                val currentTripStatus = if (status == 1) "En Curso" else if (status == 2) "Finalizado" else "Programado"

                when (currentTripStatus) {
                    "Programado" -> {
                        ivActionIcon.setImageResource(R.drawable.ic_flight_takeoff_black_24dp)
                        tvActionLabel.text = "Iniciar"
                        cardActionButton.isEnabled = true
                        cardActionButton.setCardBackgroundColor(Color.parseColor("#FFFFFF"))
                    }
                    "En Curso" -> {
                        ivActionIcon.setImageResource(R.drawable.ic_flight_land_black_24dp)
                        tvActionLabel.text = "En Curso"
                        cardActionButton.isEnabled = true
                        cardActionButton.setCardBackgroundColor(Color.parseColor("#FFF3E0"))
                    }
                    else -> {
                        ivActionIcon.setImageResource(R.drawable.ic_flight_land_black_24dp)
                        tvActionLabel.text = "Terminado"
                        cardActionButton.isEnabled = false
                        cardActionButton.setCardBackgroundColor(Color.parseColor("#E0E0E0"))
                    }
                }

                cardActionButton.setOnClickListener {
                    val estadoActualEnPantalla = tvActionLabel.text.toString()
                    if (idTrip == -1) return@setOnClickListener

                    when (estadoActualEnPantalla) {
                        "Iniciar" -> {
                            MaterialAlertDialogBuilder(this@TripDetailActivity)
                                .setTitle("Zarpe de Crucero")
                                .setMessage("¿Confirmas que deseas iniciar este viaje?")
                                .setPositiveButton("Sí") { _, _ -> tripsViewModel.updateTripStatus(idTrip, "En Curso") }
                                .setNegativeButton("Volver", null).show()
                        }
                        "En Curso" -> {
                            val opcionesGestion = arrayOf("🏁 Finalizar Viaje", "↩️ Cancelar Inicio")
                            MaterialAlertDialogBuilder(this@TripDetailActivity)
                                .setTitle("Gestión del Viaje")
                                .setItems(opcionesGestion) { dialog, position ->
                                    if (position == 0) tripsViewModel.updateTripStatus(idTrip, "Finalizado")
                                    else tripsViewModel.updateTripStatus(idTrip, "Programado")
                                    dialog.dismiss()
                                }.show()
                        }
                    }
                }
            } else {
                ivActionIcon.setImageResource(R.drawable.ic_contact_support_black_24dp)
                tvActionLabel.text = "Soporte Viaje"
                cardActionButton.isEnabled = true
                cardActionButton.setCardBackgroundColor(Color.parseColor("#E1F5FE"))
                cardActionButton.setOnClickListener { showWhatsAppConfirmationDialog() }
            }
        }
    }

    private fun setupRecyclerView() {
        val userRole = session.getUserRole()
        val isPersonal = userRole.lowercase() in listOf("guía", "guia", "tripulante")

        adapter = PassengerAdapter(userRole) { passenger ->
            if (isPersonal || passenger.idPassenger == session.getIdTraveler()) {
                val intent = Intent(this, PassengerDetailActivity::class.java).apply {
                    putExtra("ID_PASSENGER", passenger.idPassenger)
                    putExtra("ID_TRIP", currentTripId)
                }
                startActivity(intent)
            } else {
                MaterialAlertDialogBuilder(this)
                    .setTitle("Acceso Denegado")
                    .setMessage("No tienes permiso para ver otros pasajeros.")
                    .setPositiveButton("Entendido", null).show()
            }
        }
        binding.rvPassengers.apply {
            layoutManager = LinearLayoutManager(this@TripDetailActivity)
            adapter = this@TripDetailActivity.adapter
        }
    }

    private fun setupObservers() {
        viewModel.passengers.observe(this) { list -> list?.let { adapter.updateList(it) } }
        tripsViewModel.tripStatusUpdateSuccess.observe(this) { nuevoEstado ->
            val statusInt = if (nuevoEstado == "En Curso") 1 else if (nuevoEstado == "Finalizado") 2 else 0
            binding.tvStatusHeader.text = nuevoEstado
            setupMenu(statusInt, currentTripId, intent.getStringExtra("TRIP_NAME") ?: "", intent.getStringExtra("DESTINATION") ?: "")
            Toast.makeText(this, "Estado: $nuevoEstado", Toast.LENGTH_SHORT).show()
        }
        tripsViewModel.error.observe(this) { Toast.makeText(this, it, Toast.LENGTH_LONG).show() }
    }

    private fun showWhatsAppConfirmationDialog() {
        MaterialAlertDialogBuilder(this)
            .setTitle("Soporte").setMessage("¿Contactar con asistencia?")
            .setPositiveButton("Ir a WhatsApp") { _, _ -> openWhatsAppSupport() }
            .setNegativeButton("Cancelar", null).show()
    }

    private fun openWhatsAppSupport() {
        try {
            val intent = Intent(Intent.ACTION_VIEW, Uri.parse("https://api.whatsapp.com/send?phone=51906066682&text=Hola"))
            startActivity(intent)
        } catch (e: Exception) { Toast.makeText(this, "WhatsApp no instalado", Toast.LENGTH_SHORT).show() }
    }
}