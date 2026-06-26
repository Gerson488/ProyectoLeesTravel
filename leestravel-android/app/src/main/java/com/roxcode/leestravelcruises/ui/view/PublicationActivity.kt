package com.roxcode.leestravelcruises.ui.view

import android.content.Intent
import android.os.Bundle
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.widget.addTextChangedListener
import androidx.recyclerview.widget.LinearLayoutManager
import com.roxcode.leestravelcruises.databinding.ActivityPublicationBinding
import com.roxcode.leestravelcruises.ui.adapter.PublicationAdapter
import com.roxcode.leestravelcruises.ui.viewmodel.PublicationViewModel
import com.roxcode.leestravelcruises.utils.SessionManager

class PublicationActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPublicationBinding
    private val viewModel: PublicationViewModel by viewModels()
    private lateinit var adapter: PublicationAdapter
    private lateinit var sessionManager: SessionManager
    private var tripId: Int = 1

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPublicationBinding.inflate(layoutInflater)
        setContentView(binding.root)

        sessionManager = SessionManager(this)

        tripId = intent.getIntExtra("TRIP_ID", 1)
        binding.tvHeaderTripName.text = intent.getStringExtra("TRIP_NAME") ?: "Viaje"
        binding.tvHeaderTravelerName.text = intent.getStringExtra("TRAVELER_NAME") ?: "Pasajero"
        binding.tvHeaderDestination.text = intent.getStringExtra("DESTINATION") ?: "Destino"

        setupRecyclerView()
        setupObservers()
        setupListeners()
    }

    override fun onResume() {
        super.onResume()
        val userIdActiveInt = sessionManager.getUserId()

        viewModel.fetchPublications(tripId, userIdActiveInt)
    }

    private fun setupRecyclerView() {
        adapter = PublicationAdapter(emptyList()) { selectedPost ->
            val intent = Intent(this, ViewPublicationActivity::class.java).apply {
                putExtra("TRIP_ID", tripId)
                putExtra("POST_ID", selectedPost.idPost)
                putExtra("TITLE", selectedPost.title)
                putExtra("DESC", selectedPost.description)
                putStringArrayListExtra("GALLERY", ArrayList(selectedPost.gallery))
            }
            startActivity(intent)
        }
        binding.rvPublications.layoutManager = LinearLayoutManager(this)
        binding.rvPublications.adapter = adapter
    }

    private fun setupObservers() {
        viewModel.publications.observe(this) { posts ->
            adapter.updateData(posts)
        }
    }

    private fun setupListeners() {
        binding.btnAddPublication.setOnClickListener {
            val intent = Intent(this, ViewPublicationActivity::class.java).apply {
                putExtra("TRIP_ID", tripId)
            }
            startActivity(intent)
        }
        binding.includeToolbar.btnHeaderAction.setOnClickListener { finish() }
        binding.etSearchPublication.addTextChangedListener { text ->
            if (::adapter.isInitialized) adapter.filter(text.toString())
        }
    }
}