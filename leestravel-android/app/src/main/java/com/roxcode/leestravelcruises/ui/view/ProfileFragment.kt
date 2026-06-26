package com.roxcode.leestravelcruises.ui.view

import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.databinding.FragmentProfileBinding
import com.roxcode.leestravelcruises.ui.viewmodel.TravelerViewModel
import com.roxcode.leestravelcruises.utils.SessionManager

class ProfileFragment : Fragment(R.layout.fragment_profile) {
    private lateinit var binding: FragmentProfileBinding
    private val viewModel: TravelerViewModel by viewModels()
    private lateinit var sessionManager: SessionManager

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        binding = FragmentProfileBinding.bind(view)
        sessionManager = SessionManager(requireContext())

        setupObservers()

        val idTraveler = sessionManager.getIdTraveler()
        if (idTraveler != -1) {
            viewModel.fetchProfile(idTraveler)
        } else {
            Toast.makeText(requireContext(), "Error de sesión", Toast.LENGTH_SHORT).show()
        }
    }

    private fun setupObservers() {
        viewModel.traveler.observe(viewLifecycleOwner) { data ->
            data?.let {
                binding.apply {
                    etFirstName.setText(it.firstName)
                    etLastName.setText(it.lastName)
                    etNationality.setText(it.nationality)
                    etBirthDate.setText(it.birthDate)
                    etDocType.setText(it.documentType)
                    etDocNumber.setText(it.idCardPassport)
                    etEmergencyContact.setText(it.emergencyContact ?: "N/A")
                    etEmergencyPhone.setText(it.emergencyPhone ?: "N/A")
                }
            }
        }
    }
}