package com.roxcode.leestravelcruises.ui.view

import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.widget.addTextChangedListener
import androidx.recyclerview.widget.LinearLayoutManager
import com.roxcode.leestravelcruises.databinding.ActivityAttendanceBinding
import com.roxcode.leestravelcruises.ui.adapter.AttendanceAdapter
import com.roxcode.leestravelcruises.ui.viewmodel.PassengerViewModel

class AttendanceActivity : AppCompatActivity() {

    private lateinit var binding: ActivityAttendanceBinding
    private val viewModel: PassengerViewModel by viewModels()
    private lateinit var attendanceAdapter: AttendanceAdapter
    private var idTrip: Int = -1

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAttendanceBinding.inflate(layoutInflater)
        setContentView(binding.root)
        idTrip = intent.getIntExtra("ID_TRIP", -1)
        val tripName = intent.getStringExtra("TRIP_NAME") ?: "Asistencia"

        setupToolbar(tripName)
        setupRecyclerView()
        setupObservers()
        setupSearch()
        if (idTrip != -1) {
            viewModel.getPassengers(idTrip)
        } else {
            Toast.makeText(this, "Error: No se encontró el ID del viaje", Toast.LENGTH_SHORT).show()
            finish()
        }
    }

    private fun setupToolbar(title: String) {
        setSupportActionBar(binding.toolbarAttendance)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        supportActionBar?.setDisplayShowTitleEnabled(false)
        binding.tvToolbarTitle.text = "Asistencia: $title"
        binding.toolbarAttendance.setNavigationOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }
    }

    private fun setupRecyclerView() {
        attendanceAdapter = AttendanceAdapter { passenger, newStatus ->
            viewModel.updateBoarding(
                idTrip = idTrip,
                idPassenger = passenger.idPassenger,
                status = newStatus
            )
        }

        binding.rvAttendancePassengers.apply {
            layoutManager = LinearLayoutManager(this@AttendanceActivity)
            adapter = attendanceAdapter
        }
    }

    private fun setupSearch() {
        binding.etSearchAttendance.addTextChangedListener { text ->
            if (::attendanceAdapter.isInitialized) {
                attendanceAdapter.filter(text.toString())
            }
        }
    }

    private fun setupObservers() {
        viewModel.passengers.observe(this) { list ->
            if (list != null) {
                attendanceAdapter.updateList(list)
            }
        }
        viewModel.isLoading.observe(this) { loading ->
            binding.progressBarAttendance.visibility = if (loading) View.VISIBLE else View.GONE
        }

        viewModel.boardingSuccess.observe(this) { successMessage ->
            successMessage?.let {
                Toast.makeText(this, it, Toast.LENGTH_SHORT).show()
                viewModel.getPassengers(idTrip)
            }
        }
        viewModel.error.observe(this) { errorMessage ->
            errorMessage?.let {
                Toast.makeText(this, it, Toast.LENGTH_LONG).show()
            }
        }
    }
}