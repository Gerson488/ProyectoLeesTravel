package com.roxcode.leestravelcruises.ui.view

import android.content.Intent
import android.os.Bundle
import android.view.View
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.databinding.FragmentPublicPublicationsBinding
import com.roxcode.leestravelcruises.ui.adapter.PromoAdapter
import com.roxcode.leestravelcruises.ui.adapter.PublicFeedAdapter
import com.roxcode.leestravelcruises.ui.viewmodel.PublicFeedViewModel

class PublicPublicationFragment : Fragment(R.layout.fragment_public_publications) {

    private lateinit var binding: FragmentPublicPublicationsBinding
    private val viewModel: PublicFeedViewModel by viewModels()
    private lateinit var feedAdapter: PublicFeedAdapter
    private lateinit var promoAdapter: PromoAdapter

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        binding = FragmentPublicPublicationsBinding.bind(view)

        setupRecyclerViews()
        setupObservers()

        viewModel.fetchPromotions()
        viewModel.fetchPublicFeed()
    }

    private fun setupRecyclerViews() {
        promoAdapter = PromoAdapter(emptyList())
        binding.rvPromotions.apply {
            layoutManager = LinearLayoutManager(context, LinearLayoutManager.HORIZONTAL, false)
            adapter = promoAdapter
        }

        feedAdapter = PublicFeedAdapter(emptyList()) { publication ->
            val intent = Intent(requireContext(), PublicationDetailActivity::class.java)
            intent.putExtra("PUBLICATION_ID", publication.idPost)
            startActivity(intent)
        }

        binding.rvPublicFeed.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = feedAdapter
        }
    }

    private fun setupObservers() {
        viewModel.promotions.observe(viewLifecycleOwner) { promosList ->
            if (promosList.isNotEmpty()) {
                binding.rvPromotions.visibility = View.VISIBLE
                promoAdapter.updateData(promosList)
            } else {
                binding.rvPromotions.visibility = View.GONE
            }
        }

        viewModel.feed.observe(viewLifecycleOwner) { list ->
            feedAdapter.updateData(list)
        }
    }
}