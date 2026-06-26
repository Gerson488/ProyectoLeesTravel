package com.roxcode.leestravelcruises.ui.view

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.databinding.FragmentMyTripsBinding
import com.roxcode.leestravelcruises.ui.adapter.TripAdapter
import com.roxcode.leestravelcruises.ui.viewmodel.TripsViewModel
import com.roxcode.leestravelcruises.utils.SessionManager
import java.net.URLEncoder

class MyTripsFragment : Fragment(R.layout.fragment_my_trips) {
    private lateinit var binding: FragmentMyTripsBinding
    private val viewModel: TripsViewModel by viewModels()
    private lateinit var tripAdapter: TripAdapter
    private lateinit var session: SessionManager

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        binding = FragmentMyTripsBinding.bind(view)
        session = SessionManager(requireContext())

        if (session.getUserId() == -1) {
            binding.rvTrips.visibility = View.GONE
            binding.chipGroupFiltros.visibility = View.GONE
            binding.layoutGuestMessage.visibility = View.VISIBLE

            binding.btnContactAgency.setOnClickListener {
                openWhatsAppContact()
            }
        } else {
            binding.layoutGuestMessage.visibility = View.GONE
            binding.rvTrips.visibility = View.VISIBLE
            binding.chipGroupFiltros.visibility = View.VISIBLE

            setupRecyclerView()
            setupObservers()
            setupFilterChips()
        }
    }

    override fun onResume() {
        super.onResume()
        if (::session.isInitialized && session.getUserId() != -1) {
            viewModel.fetchTrips(session.getIdTraveler())
            val checkedIds = binding.chipGroupFiltros.checkedChipIds
            if (checkedIds.isNotEmpty()) {
                when (checkedIds[0]) {
                    R.id.chipTodos -> viewModel.filterTrips("Todos")
                    R.id.chipCurso -> viewModel.filterTrips("En curso")
                    R.id.chipProgramado -> viewModel.filterTrips("Programado")
                    R.id.chipFinalizado -> viewModel.filterTrips("Finalizado")
                }
            }
        }
    }

    private fun openWhatsAppContact() {
        val phone = "51999888777"
        val message = "Hola Lees Travel, soy un usuario invitado y deseo información sobre mis cruceros."
        try {
            val intent = Intent(Intent.ACTION_VIEW)
            val url = "https://api.whatsapp.com/send?phone=$phone&text=" + URLEncoder.encode(message, "UTF-8")
            intent.data = Uri.parse(url)
            startActivity(intent)
        } catch (e: Exception) {
            Toast.makeText(requireContext(), "WhatsApp no está instalado", Toast.LENGTH_SHORT).show()
        }
    }

    private fun setupRecyclerView() {
        tripAdapter = TripAdapter { trip ->
            val intent = Intent(requireContext(), TripDetailActivity::class.java).apply {
                putExtra("ID_TRIP", trip.idTrip)
                putExtra("TRIP_NAME", "${trip.origin} - ${trip.destination}")
                putExtra("DESTINATION", trip.destination)
                val statusInt = when (trip.tripStatus) {
                    "En Curso" -> 1
                    "Finalizado" -> 2
                    "Cancelado" -> 3
                    else -> 0
                }
                putExtra("STATUS", statusInt)
            }
            startActivity(intent)
        }
        binding.rvTrips.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = tripAdapter
        }
    }

    private fun setupObservers() {
        viewModel.trips.observe(viewLifecycleOwner) { list ->
            if (list != null) tripAdapter.updateTrips(list)
        }
    }

    private fun setupFilterChips() {
        binding.chipGroupFiltros.setOnCheckedStateChangeListener { _, checkedIds ->
            if (checkedIds.isNotEmpty()) {
                when (checkedIds[0]) {
                    R.id.chipTodos -> viewModel.filterTrips("Todos")
                    R.id.chipCurso -> viewModel.filterTrips("En curso")
                    R.id.chipProgramado -> viewModel.filterTrips("Programado")
                    R.id.chipFinalizado -> viewModel.filterTrips("Finalizado")
                }
            }
        }
        binding.chipTodos.isChecked = true
    }
}