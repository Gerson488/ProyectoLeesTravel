package com.roxcode.leestravelcruises.ui.view

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.databinding.FragmentHomeBinding
import com.roxcode.leestravelcruises.ui.viewmodel.TripsViewModel
import com.roxcode.leestravelcruises.utils.SessionManager
import java.net.URLEncoder

class HomeFragment : Fragment(R.layout.fragment_home) {

    private lateinit var binding: FragmentHomeBinding
    private val tripsViewModel: TripsViewModel by viewModels()

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        binding = FragmentHomeBinding.bind(view)
        val session = SessionManager(requireContext())

        if (session.getUserId() == -1) {
            setupGuestUI()
        } else {
            setupUserUI(session)
        }
    }

    private fun setupGuestUI() {
        binding.layoutUserHome.visibility = View.GONE
        binding.layoutGuestHome.visibility = View.VISIBLE
        binding.tvWelcomeMessage.text = "¡Bienvenido a Lees Travel Cruises!"

        binding.btnContactGuest.setOnClickListener {
            openWhatsAppContact()
        }
    }

    private fun setupUserUI(session: SessionManager) {
        binding.layoutUserHome.visibility = View.VISIBLE
        binding.layoutGuestHome.visibility = View.GONE
        binding.tvWelcomeMessage.text = "Bienvenido a tu panel de viajero Tour Conductor de TG."

        binding.btnVerViajes.setOnClickListener {
            (activity as? MainScreenActivity)?.let { main ->
                main.supportFragmentManager.beginTransaction()
                    .setCustomAnimations(android.R.anim.fade_in, android.R.anim.fade_out)
                    .replace(R.id.nav_host_fragment, MyTripsFragment())
                    .addToBackStack(null)
                    .commit()
            }
        }

        tripsViewModel.trips.observe(viewLifecycleOwner) { trips ->
            val count = trips?.size ?: 0
            binding.tvCountTrips.text = "$count viajes asignados"
        }

        tripsViewModel.fetchTrips(session.getIdTraveler())
    }

    private fun openWhatsAppContact() {
        val phone = "51906066682"
        val message = "Hola Lees Travel, soy un usuario invitado y me gustaría recibir información sobre los próximos cruceros."
        try {
            val intent = Intent(Intent.ACTION_VIEW)
            val url = "https://api.whatsapp.com/send?phone=$phone&text=" + URLEncoder.encode(message, "UTF-8")
            intent.data = Uri.parse(url)
            startActivity(intent)
        } catch (e: Exception) {
            Toast.makeText(requireContext(), "WhatsApp no está instalado", Toast.LENGTH_SHORT).show()
        }
    }
}