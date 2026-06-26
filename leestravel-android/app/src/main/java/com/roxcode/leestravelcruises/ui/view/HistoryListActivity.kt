package com.roxcode.leestravelcruises.ui.view

import android.content.Intent
import android.os.Bundle
import android.view.View
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.databinding.ActivityHistoryListBinding
import com.roxcode.leestravelcruises.ui.adapter.HistoryAdapter
import com.roxcode.leestravelcruises.ui.viewmodel.HistoryViewModel

class HistoryListActivity : AppCompatActivity() {

    private lateinit var binding: ActivityHistoryListBinding
    private val viewModel: HistoryViewModel by viewModels()
    private lateinit var adapter: HistoryAdapter

    private var idTrip: Int = 0
    private var tripName: String? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityHistoryListBinding.inflate(layoutInflater)
        setContentView(binding.root)

        idTrip = intent.getIntExtra("idTrip", 0)
        tripName = intent.getStringExtra("TRIP_NAME")

        binding.includeToolbar.btnHeaderAction.setOnClickListener {
            finish()
        }

        setupRecyclerView()
        setupObservers()

        binding.fabAddHistory.setOnClickListener {
            val intent = Intent(this, RegisterHistoryActivity::class.java).apply {
                putExtra("idTrip", idTrip)
                putExtra("TRIP_NAME", tripName)
            }
            startActivity(intent)
        }
    }

    override fun onResume() {
        super.onResume()
        viewModel.getHistoryByTrip(idTrip)
    }

    private fun setupRecyclerView() {
        adapter = HistoryAdapter(emptyList())
        binding.rvHistoryList.layoutManager = LinearLayoutManager(this)
        binding.rvHistoryList.adapter = adapter
    }

    private fun setupObservers() {
        viewModel.historyList.observe(this) { list ->
            if (list.isNullOrEmpty()) {
                binding.tvEmptyState.visibility = View.VISIBLE
            } else {
                binding.tvEmptyState.visibility = View.GONE
                adapter.updateList(list)
            }
        }
    }
}