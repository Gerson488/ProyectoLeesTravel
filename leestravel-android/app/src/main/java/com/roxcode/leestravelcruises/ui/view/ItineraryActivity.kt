package com.roxcode.leestravelcruises.ui.view

import android.os.Bundle
import android.util.Log
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.databinding.ActivityItineraryBinding
import com.roxcode.leestravelcruises.ui.adapter.ItineraryAdapter
import com.roxcode.leestravelcruises.ui.viewmodel.ItineraryViewModel

class ItineraryActivity : AppCompatActivity() {

    private lateinit var binding: ActivityItineraryBinding
    private val viewModel: ItineraryViewModel by viewModels()
    private lateinit var adapter: ItineraryAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityItineraryBinding.inflate(layoutInflater)
        setContentView(binding.root)

        val idTrip = intent.getIntExtra("ID_TRIP", -1)


        setupToolbar()
        setupRecyclerView()
        setupObservers()

        if (idTrip != -1) {
            viewModel.getItinerary(idTrip)
        } else {
            finish()
        }
    }

    private fun setupToolbar() {
        binding.includeToolbar.btnHeaderAction.setImageResource(R.drawable.ic_arrow_back)
        binding.includeToolbar.btnHeaderAction.setOnClickListener { finish() }
    }

    private fun setupRecyclerView() {
        adapter = ItineraryAdapter()
        binding.rvItinerary.apply {
            layoutManager = LinearLayoutManager(this@ItineraryActivity)
            adapter = this@ItineraryActivity.adapter
        }
    }

    private fun setupObservers() {
        viewModel.itinerary.observe(this) { list ->
            if (list != null) adapter.updateList(list)
        }

        viewModel.error.observe(this) { msg ->
            msg?.let { Toast.makeText(this, it, Toast.LENGTH_SHORT).show() }
        }
    }
}